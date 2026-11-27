<?php

namespace Tests\Feature\Organization;

use App\Domain\Organization\Enums\OrganizationRole;
use App\Domain\Organization\Enums\UnitRole;
use App\Domain\Organization\Models\Organization;
use App\Domain\Organization\Models\Unit;
use App\Domain\Organization\Support\CurrentOrganization;
use App\Domain\Organization\Support\OrganizationContext;
use App\Domain\Organization\Support\OrgScope;
use App\Domain\Platform\Actions\CreateOrganizationOwner;
use App\Domain\Platform\Actions\SaveOrganization;
use App\Domain\Platform\Data\OrganizationData;
use App\Domain\Organization\Enums\OrganizationTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TenancyTest extends TestCase
{
    use RefreshDatabase;

    private function org(string $name, string $status = 'active', string $template = 'school'): Organization
    {
        return Organization::create(['name' => $name, 'slug' => str($name)->slug().uniqid(), 'status' => $status, 'template' => $template]);
    }

    private function user(Organization $org, OrganizationRole $role = OrganizationRole::Owner): User
    {
        return User::create([
            'username' => 'u'.uniqid(),
            'fullname' => 'User '.$org->name,
            'password' => 'password',
            'organization_id' => $org->id,
            'organization_role' => $role,
        ]);
    }

    private function actAs(User $user): void
    {
        app(OrganizationContext::class)->set($user->organization, $user);
    }

    public function test_global_scope_isolates_data_between_organizations(): void
    {
        $a = $this->org('Org A');
        $b = $this->org('Org B');
        Unit::withoutGlobalScopes()->create(['organization_id' => $a->id, 'name' => 'Unit A', 'code' => 'A1']);
        Unit::withoutGlobalScopes()->create(['organization_id' => $b->id, 'name' => 'Unit B', 'code' => 'B1']);

        $this->actAs($this->user($a));

        $this->assertSame(['Unit A'], Unit::pluck('name')->all());
        $this->assertNull(Unit::where('code', 'B1')->first(), 'Data organisasi lain tidak boleh terlihat');
    }

    public function test_creating_fills_organization_id_and_codes_are_unique_per_organization(): void
    {
        $a = $this->org('Org A');
        $b = $this->org('Org B');

        $this->actAs($this->user($a));
        $unitA = Unit::create(['name' => 'TJKT', 'code' => 'TJKT']);
        $this->assertSame($a->id, $unitA->organization_id);

        // kode yang sama boleh dipakai organisasi lain
        $this->actAs($this->user($b));
        $unitB = Unit::create(['name' => 'TJKT', 'code' => 'TJKT']);
        $this->assertSame($b->id, $unitB->organization_id);
    }

    public function test_member_only_accesses_own_units_and_org_admin_accesses_all(): void
    {
        $org = $this->org('Org A');
        $u1 = Unit::withoutGlobalScopes()->create(['organization_id' => $org->id, 'name' => 'U1', 'code' => 'U1']);
        $u2 = Unit::withoutGlobalScopes()->create(['organization_id' => $org->id, 'name' => 'U2', 'code' => 'U2']);

        $member = $this->user($org, OrganizationRole::Member);
        $u1->members()->create(['user_id' => $member->id, 'role' => UnitRole::Admin]);

        $this->actAs($member);
        $this->assertSame([$u1->id], CurrentOrganization::unitIds());

        $query = OrgScope::units(DB::table('units'), 'id', $u2->id);
        $this->assertSame(0, $query->count(), 'Anggota tidak boleh membuka unit lain lewat parameter');

        $this->actAs($this->user($org, OrganizationRole::Admin));
        $this->assertEqualsCanonicalizing([$u1->id, $u2->id], CurrentOrganization::unitIds());
    }

    public function test_query_builder_scope_requires_context(): void
    {
        $this->expectException(\LogicException::class);
        OrgScope::apply(DB::table('products as p'), 'p');
    }

    public function test_suspended_organization_is_blocked_and_user_without_org_is_rejected(): void
    {
        Route::middleware('web')->get('/__tenant-probe', fn () => 'ok');

        $this->actingAs($this->user($this->org('Aktif')))->get('/__tenant-probe')->assertOk();

        $this->actingAs($this->user($this->org('Tangguh', 'suspended')))
            ->get('/__tenant-probe')
            ->assertStatus(403)
            ->assertSee('OrganizationInactive');

        $orphan = User::create(['username' => 'orphan', 'fullname' => 'Orphan', 'password' => 'x']);
        $this->actingAs($orphan)->get('/__tenant-probe')->assertForbidden();
    }

    public function test_terms_follow_template_and_can_be_overridden(): void
    {
        $office = $this->org('Kantor', template: 'office');
        $this->assertSame('Divisi', $office->terms()['unit']);

        $office->update(['settings' => ['terms' => ['unit' => 'Departemen']]]);
        $this->assertSame('Departemen', $office->fresh()->terms()['unit']);
    }

    public function test_platform_onboarding_creates_default_unit_and_owner_account(): void
    {
        $org = app(SaveOrganization::class)->handle(new OrganizationData(
            name: 'Komunitas Baru',
            template: OrganizationTemplate::Community,
        ));

        $this->assertSame(1, Unit::withoutGlobalScopes()->where('organization_id', $org->id)->count());

        [$owner, $password] = app(CreateOrganizationOwner::class)->handle($org, 'Budi', 'budi@contoh.id');

        $this->assertSame(OrganizationRole::Owner, $owner->organization_role);
        $this->assertSame('budi', $owner->username);
        $this->assertTrue(\Hash::check($password, $owner->password));
        $this->assertSame('budi@contoh.id', $org->fresh()->owner_email);
    }
}
