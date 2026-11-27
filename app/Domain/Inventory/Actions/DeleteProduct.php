<?php

namespace App\Domain\Inventory\Actions;

use App\Domain\Inventory\Models\Product;
use App\Domain\Organization\Support\Access;

/** Soft delete: riwayat stok & transaksi tetap utuh. */
final class DeleteProduct
{
    public function handle(Product $product): void
    {
        Access::authorizeManage($product->unit_id);
        $product->delete();
    }
}
