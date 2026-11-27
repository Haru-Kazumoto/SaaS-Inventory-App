<?php

namespace Tests\Feature\Concerns;

use App\Domain\MasterData\Models\Category;
use App\Domain\MasterData\Models\Uom;
use App\Domain\MasterData\Models\Warehouse;
use App\Domain\Organization\Enums\OrganizationRole;
use App\Domain\Organization\Enums\UnitRole;
use App\Domain\Organization\Models\Organization;
use App\Domain\Organization\Models\Unit;
use App\Domain\Organization\Support\OrganizationContext;
use App\Models\User;

/** Helper pembuat organisasi, unit, anggota, dan data master untuk test. */
trait InteractsWithTenant
{
    protected function makeOrganization(string $name = 'Org Uji'): Organization
    {
        return Organization::create(['name' => $name, 'slug' => str($name)->slug().'-'.uniqid(), 'status' => 'active', 'template' => 'school']);
    }

    protected function makeUnit(Organization $org, string $code): Unit
    {
        return Unit::withoutGlobalScopes()->create(['organization_id' => $org->id, 'name' => $code, 'code' => $code]);
    }

    protected function makeUser(Organization $org, OrganizationRole $role = OrganizationRole::Owner, array $units = []): User
    {
        $user = User::create([
            'organization_id' => $org->id,
            'organization_role' => $role,
            'username' => 'u'.uniqid(),
            'fullname' => 'User '.uniqid(),
            'password' => 'password',
        ]);

        foreach ($units as $unitId => $unitRole) {
            $user->unitMemberships()->create(['unit_id' => $unitId, 'role' => $unitRole instanceof UnitRole ? $unitRole : UnitRole::from($unitRole)]);
        }

        return $user;
    }

    /** Login + set konteks tenant (seperti middleware ResolveOrganization). */
    protected function actAs(User $user): static
    {
        $this->actingAs($user);
        app(OrganizationContext::class)->set($user->organization, $user);

        return $this;
    }

    /** @return array{warehouse: Warehouse, category: Category, uom: Uom} */
    protected function masterData(Organization $org, ?Unit $unit = null): array
    {
        $attrs = fn (string $code) => ['organization_id' => $org->id, 'unit_id' => $unit?->id, 'name' => $code, 'code' => $code.uniqid()];

        return [
            'warehouse' => Warehouse::withoutGlobalScopes()->create($attrs('GDG')),
            'category' => Category::withoutGlobalScopes()->create($attrs('CAT')),
            'uom' => Uom::withoutGlobalScopes()->create($attrs('PCS')),
        ];
    }
}
