<?php

namespace App\Domain\Inventory\Enums;

enum StockCondition: string
{
    case Good = 'GOOD';
    case Damaged = 'DAMAGED';
    case Lost = 'LOST';
    case Out = 'OUT';   // cadangan (tidak dipakai ledger saat ini)
    case Off = 'OFF';   // dihapuskan (rusak/pensiun) — bukan stok aktif

    /** Kondisi yang boleh diisi/diubah lewat form & restock. */
    public static function stockable(): array
    {
        return [self::Good, self::Damaged];
    }

    public function label(): string
    {
        return match ($this) {
            self::Good => 'Bagus',
            self::Damaged => 'Rusak',
            self::Lost => 'Hilang',
            self::Out => 'Dipinjam',
            self::Off => 'Dihapuskan',
        };
    }
}
