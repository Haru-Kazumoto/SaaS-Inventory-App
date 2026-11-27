<?php

namespace App\Domain\Identity\Queries;

use App\Domain\Organization\Support\CurrentOrganization;
use App\Models\User;

final class MemberQuery
{
    public function list(): array
    {
        return User::query()
            ->where('organization_id', CurrentOrganization::id())
            ->with('units:id,name')
            ->orderBy('fullname')
            ->get()
            ->map(fn (User $u) => $this->row($u))
            ->all();
    }

    public function form(?User $member = null): array
    {
        return [
            'units' => CurrentOrganization::units()->map->only(['id', 'name'])->values(),
            'member' => $member ? [
                ...$this->row($member->load('units:id,name')),
                'memberships' => $member->units->map(fn ($u) => ['unit_id' => $u->id, 'role' => $u->pivot->role])->values(),
            ] : null,
            'canAssignOwner' => auth()->user()->organization_role?->value === 'owner',
        ];
    }

    private function row(User $u): array
    {
        return [
            'id' => $u->id,
            'fullname' => $u->fullname,
            'username' => $u->username,
            'email' => $u->email,
            'phone' => $u->phone,
            'profile_image_url' => $u->profile_image_url,
            'organization_role' => $u->organization_role?->value,
            'role_label' => $u->organization_role?->label(),
            'units' => $u->units->map(fn ($unit) => ['id' => $unit->id, 'name' => $unit->name, 'role' => $unit->pivot->role])->values(),
        ];
    }
}
