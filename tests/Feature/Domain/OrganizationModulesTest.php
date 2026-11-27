<?php

namespace Tests\Feature\Domain;

use App\Domain\Identity\Actions\DeleteMember;
use App\Domain\Identity\Actions\SaveMember;
use App\Domain\Identity\Data\MemberData;
use App\Domain\MasterData\Actions\DeleteMasterData;
use App\Domain\MasterData\Actions\SaveMasterData;
use App\Domain\MasterData\MasterDataType;
use App\Domain\Organization\Enums\OrganizationRole;
use App\Domain\Organization\Enums\UnitRole;
use App\Domain\Procurement\Actions\ProcessRequests;
use App\Domain\Procurement\Actions\SubmitRequest;
use App\Domain\Procurement\Enums\RequestStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Feature\Concerns\InteractsWithTenant;
use Tests\TestCase;

class OrganizationModulesTest extends TestCase
{
    use InteractsWithTenant, RefreshDatabase;

    public function test_master_data_codes_unique_per_org_and_in_use_cannot_be_deleted(): void
    {
        $org = $this->makeOrganization();
        $unit = $this->makeUnit($org, 'TJKT');
        $this->actAs($this->makeUser($org));

        $save = app(SaveMasterData::class);
        $shared = $save->handle(MasterDataType::Uoms, ['name' => 'Pcs', 'code' => 'PCS', 'unit_id' => null]);
        $this->assertNull($shared->unit_id);

        try {
            $save->handle(MasterDataType::Uoms, ['name' => 'Pcs 2', 'code' => 'PCS', 'unit_id' => $unit->id]);
            $this->fail('Kode ganda harus ditolak');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('code', $e->errors());
        }

        // unit yang masih punya anggota tidak bisa dihapus
        $this->makeUser($org, OrganizationRole::Member, [$unit->id => UnitRole::Staff]);
        $this->expectException(ValidationException::class);
        app(DeleteMasterData::class)->handle(MasterDataType::Units, $unit);
    }

    public function test_unit_admin_cannot_create_shared_master_data(): void
    {
        $org = $this->makeOrganization();
        $unit = $this->makeUnit($org, 'TE');
        $this->actAs($this->makeUser($org, OrganizationRole::Member, [$unit->id => UnitRole::Admin]));

        $own = app(SaveMasterData::class)->handle(MasterDataType::Warehouses, ['name' => 'Gudang TE', 'code' => 'G_TE', 'unit_id' => $unit->id]);
        $this->assertSame($unit->id, $own->unit_id);

        $this->expectException(ValidationException::class);
        app(SaveMasterData::class)->handle(MasterDataType::Warehouses, ['name' => 'Bersama', 'code' => 'G_ALL', 'unit_id' => null]);
    }

    public function test_procurement_submit_by_unit_admin_and_review_by_org_admin_only(): void
    {
        $org = $this->makeOrganization();
        $unit = $this->makeUnit($org, 'TO');
        $unitAdmin = $this->makeUser($org, OrganizationRole::Member, [$unit->id => UnitRole::Admin]);

        $this->actAs($unitAdmin);
        $req = app(SubmitRequest::class)->handle(['unit_id' => $unit->id, 'item_name' => 'OBENG', 'quantity' => 3, 'uom' => 'PCS', 'reason' => 'Praktik']);

        try {
            app(ProcessRequests::class)->handle([$req->id], RequestStatus::Approved);
            $this->fail('Anggota unit tidak boleh menyetujui');
        } catch (HttpException) {
        }

        $this->actAs($this->makeUser($org));
        $this->assertSame(1, app(ProcessRequests::class)->handle([$req->id], RequestStatus::Approved));
        $this->assertSame(RequestStatus::Approved, $req->fresh()->status);
        $this->assertSame(0, app(ProcessRequests::class)->handle([$req->id], RequestStatus::Rejected), 'Yang sudah diproses tidak diproses ulang');
    }

    public function test_members_with_unit_roles_and_protections(): void
    {
        $org = $this->makeOrganization();
        $unit = $this->makeUnit($org, 'TU');
        $owner = $this->makeUser($org);
        $this->actAs($owner);

        $member = app(SaveMember::class)->handle(new MemberData(
            'Siti', 'siti.tu', 'siti@uji.id', null, 'password123', OrganizationRole::Member,
            [['unit_id' => $unit->id, 'role' => 'staff']], null,
        ));
        $this->assertSame(UnitRole::Staff->value, $member->units()->first()->pivot->role);

        try {
            app(SaveMember::class)->handle(new MemberData('Tanpa Unit', 'tanpa.unit', null, null, 'password123', OrganizationRole::Member, [], null));
            $this->fail('Anggota tanpa unit harus ditolak');
        } catch (ValidationException) {
        }

        $this->expectException(ValidationException::class);
        app(DeleteMember::class)->handle($owner);
    }
}
