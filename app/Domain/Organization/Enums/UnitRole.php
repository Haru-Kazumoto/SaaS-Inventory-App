<?php

namespace App\Domain\Organization\Enums;

/** Peran user di satu unit. */
enum UnitRole: string
{
    case Admin = 'admin';   // kelola barang, stok, peminjaman, pengadaan di unit
    case Staff = 'staff';   // catat peminjaman & pengembalian, lihat stok
    case Viewer = 'viewer'; // hanya melihat

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin unit',
            self::Staff => 'Staf',
            self::Viewer => 'Viewer',
        };
    }
}
