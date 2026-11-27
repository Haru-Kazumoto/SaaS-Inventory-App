<?php

namespace App\Domain\Inventory\Actions;

use App\Domain\Inventory\Enums\StockCondition;
use App\Domain\Inventory\Ledger\StockLedger;
use App\Domain\Inventory\Models\Product;
use App\Domain\Organization\Support\Access;
use Illuminate\Support\Facades\DB;

/** Hapuskan barang (rusak / pensiun / hilang) dari gudang. Tercatat di Barang Dihapuskan. */
final class WriteOffProduct
{
    public function __construct(private StockLedger $ledger) {}

    public function handle(
        Product $product,
        int $warehouseId,
        int $quantity,
        StockCondition $from = StockCondition::Good,
        ?string $reason = null,
    ): void {
        Access::authorizeManage($product->unit_id);

        DB::transaction(fn () => $this->ledger->writeOff($product->id, $warehouseId, $quantity, $from, $reason));
    }
}
