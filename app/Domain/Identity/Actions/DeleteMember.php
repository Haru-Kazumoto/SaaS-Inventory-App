<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Organization\Enums\OrganizationRole;
use App\Domain\Organization\Support\Access;
use App\Domain\Organization\Support\CurrentOrganization;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class DeleteMember
{
    public function handle(User $member): void
    {
        Access::authorizeOrganization();

        if ($member->organization_id !== CurrentOrganization::id()) {
            abort(404);
        }
        if ($member->is(auth()->user())) {
            throw ValidationException::withMessages(['delete' => 'Anda tidak bisa menghapus akun sendiri.']);
        }
        if ($member->organization_role === OrganizationRole::Owner) {
            throw ValidationException::withMessages(['delete' => 'Akun pemilik tidak bisa dihapus.']);
        }

        $member->delete(); // unit_members ikut terhapus (cascade)
    }
}
