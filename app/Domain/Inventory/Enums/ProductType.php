<?php

namespace App\Domain\Inventory\Enums;

enum ProductType: string
{
    case Durable = 'DURABLE';       // tidak habis pakai: dipinjam & dikembalikan
    case Consumable = 'CONSUMABLE'; // habis pakai: keluar dan tidak kembali

    public function label(): string
    {
        return $this === self::Durable ? 'Tidak habis pakai' : 'Habis pakai';
    }
}
