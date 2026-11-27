<?php

namespace App\Domain\Inventory\Ledger;

use Illuminate\Validation\ValidationException;

final class InsufficientStock
{
    public static function make(string $productName, string $warehouseName, int $available, int $requested, string $field = 'quantity'): ValidationException
    {
        return ValidationException::withMessages([
            $field => "Stok {$productName} di {$warehouseName} tidak cukup (tersedia {$available}, diminta {$requested}).",
        ]);
    }
}
