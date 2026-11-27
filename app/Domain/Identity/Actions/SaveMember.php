<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Data\MemberData;
use App\Domain\Organization\Enums\OrganizationRole;
use App\Domain\Organization\Enums\UnitRole;
use App\Domain\Organization\Models\UnitMember;
use App\Domain\Organization\Support\Access;
use App\Domain\Organization\Support\CurrentOrganization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Tambah / ubah anggota organisasi beserta perannya per unit. Khusus admin organisasi. */
final class SaveMember
{
    public function handle(MemberData $data, ?User $member = null): User
    {
        Access::authorizeOrganization();

        $actor = auth()->user();
        $isOwnerChange = $data->organizationRole === OrganizationRole::Owner || $member?->organization_role === OrganizationRole::Owner;
        if ($isOwnerChange && $actor->organization_role !== OrganizationRole::Owner) {
            throw ValidationException::withMessages(['organization_role' => 'Hanya pemilik yang dapat mengatur peran pemilik.']);
        }

        if ($member && $member->is($actor) && $data->organizationRole === OrganizationRole::Member) {
            throw ValidationException::withMessages(['organization_role' => 'Anda tidak bisa menurunkan peran akun sendiri.']);
        }

        $unitIds = CurrentOrganization::units()->pluck('id')->all();
        $memberships = collect($data->memberships)
            ->filter(fn ($m) => in_array((int) $m['unit_id'], $unitIds, true))
            ->unique('unit_id')
            ->values();

        if ($data->organizationRole === OrganizationRole::Member && $memberships->isEmpty()) {
            throw ValidationException::withMessages(['memberships' => 'Anggota harus terdaftar minimal di satu unit.']);
        }

        $avatarPath = $data->avatar?->storeAs('avatars', 'avatar_'.Str::uuid().'.'.$data->avatar->getClientOriginalExtension(), 'public');

        return DB::transaction(function () use ($data, $member, $memberships, $avatarPath) {
            $attributes = [
                'organization_id' => CurrentOrganization::id(),
                'organization_role' => $data->organizationRole,
                'fullname' => $data->fullname,
                'username' => $data->username,
                'email' => $data->email,
                'phone' => $data->phone,
            ];
            if ($data->password) {
                $attributes['password'] = $data->password;
            }
            if ($avatarPath) {
                $attributes['profile_image'] = $avatarPath;
            }

            if ($member) {
                $old = $avatarPath ? $member->profile_image : null;
                $member->update($attributes);
                if ($old) {
                    DB::afterCommit(fn () => Storage::disk('public')->delete($old));
                }
            } else {
                $member = User::create($attributes);
            }

            // peran per unit: admin organisasi tidak butuh keanggotaan unit
            UnitMember::where('user_id', $member->id)->delete();
            if ($data->organizationRole === OrganizationRole::Member) {
                foreach ($memberships as $m) {
                    UnitMember::create([
                        'unit_id' => (int) $m['unit_id'],
                        'user_id' => $member->id,
                        'role' => UnitRole::tryFrom($m['role']) ?? UnitRole::Staff,
                    ]);
                }
            }

            return $member;
        });
    }
}
