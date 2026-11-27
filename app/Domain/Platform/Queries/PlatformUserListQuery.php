<?php

namespace App\Domain\Platform\Queries;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** Semua akun di seluruh organisasi (lintas tenant), khusus admin platform. */
final class PlatformUserListQuery
{
    /** @param array{q?: ?string, organization_id?: ?string, role?: ?string} $filters */
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return User::query()
            ->with(['organization:id,name', 'units:id,name'])
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('fullname', 'like', "%{$term}%")
                ->orWhere('username', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")))
            ->when($filters['organization_id'] ?? null, function ($q, $id) {
                $id === 'none' ? $q->whereNull('organization_id') : $q->where('organization_id', $id);
            })
            ->when($filters['role'] ?? null, function ($q, $role) {
                $role === 'platform'
                    ? $q->where('is_platform_admin', true)
                    : $q->where('organization_role', $role);
            })
            ->orderBy('fullname')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (User $u) => [
                'id' => $u->id,
                'fullname' => $u->fullname,
                'username' => $u->username,
                'email' => $u->email,
                'phone' => $u->phone,
                'role' => $u->accessLabel(),
                'role_code' => $u->isPlatformAdmin() ? 'platform' : $u->organization_role?->value,
                'organization_id' => $u->organization_id,
                'organization' => $u->organization?->name,
                'unit' => $u->units->pluck('name')->implode(', ') ?: null,
                'profile_image_url' => $u->profile_image_url,
                'created_at' => $u->created_at?->toIso8601String(),
            ]);
    }
}
