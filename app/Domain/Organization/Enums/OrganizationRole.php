<?php

namespace App\Domain\Organization\Enums;

/** Peran user di tingkat organisasi. */
enum OrganizationRole: string
{
    case Owner = 'owner';   // pemilik: semua hak admin + billing
    case Admin = 'admin';   // admin organisasi: kelola semua unit, anggota, data master
    case Member = 'member'; // akses lewat keanggotaan unit (unit_members)

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Pemilik',
            self::Admin => 'Admin organisasi',
            self::Member => 'Anggota',
        };
    }

    public function managesOrganization(): bool
    {
        return $this !== self::Member;
    }
}
