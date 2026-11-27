<?php

namespace App\Domain\Platform\Actions;

use App\Domain\Organization\Enums\OrganizationRole;
use App\Domain\Organization\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Buat akun pemilik (owner) untuk organisasi, dengan password acak.
 *
 * @return array{0: User, 1: string} user & password asli (ditampilkan sekali)
 */
final class CreateOrganizationOwner
{
    public function handle(Organization $organization, string $fullname, string $email, ?string $username = null): array
    {
        return DB::transaction(function () use ($organization, $fullname, $email, $username) {
            $password = Str::password(12, symbols: false);

            $user = User::create([
                'organization_id' => $organization->id,
                'organization_role' => OrganizationRole::Owner,
                'username' => $username ?: $this->uniqueUsername($email),
                'fullname' => $fullname,
                'email' => $email,
                'password' => $password,
            ]);

            $organization->fill([
                'owner_name' => $organization->owner_name ?: $fullname,
                'owner_email' => $organization->owner_email ?: $email,
            ])->save();

            return [$user, $password];
        });
    }

    private function uniqueUsername(string $email): string
    {
        $base = Str::of(Str::before($email, '@'))->lower()->replaceMatches('/[^a-z0-9._]/', '')->toString() ?: 'owner';
        $username = $base;
        $i = 2;

        while (User::where('username', $username)->exists()) {
            $username = $base.$i++;
        }

        return $username;
    }
}
