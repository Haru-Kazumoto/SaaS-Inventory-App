<?php

namespace App\Domain\Inventory\Actions;

use App\Domain\Inventory\Enums\MoveType;
use App\Domain\Inventory\Enums\StockCondition;
use App\Domain\Inventory\Ledger\StockLedger;
use App\Domain\Inventory\Ledger\StockMovement;
use App\Domain\Inventory\Models\Product;
use App\Domain\MasterData\Models\Warehouse;
use App\Domain\Organization\Support\Access;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Barang masuk (tercatat di halaman Barang Masuk). */
final class RestockProduct
{
    public function __construct(private StockLedger $ledger) {}

    public function handle(Product $product, int $warehouseId, int $quantity, StockCondition $condition = StockCondition::Good): void
    {
        Access::authorizeManage($product->unit_id);
        $this->assertWarehouse($product, $warehouseId);

        DB::transaction(fn () => $this->ledger->move(new StockMovement(
            $product->id, $warehouseId, MoveType::Restock, $condition, $quantity, true, 'manual',
        )));
    }

    private function assertWarehouse(Product $product, int $warehouseId): void
    {
        $warehouse = Warehouse::find($warehouseId);
        if (! $warehouse || ($warehouse->unit_id !== null && $warehouse->unit_id !== $product->unit_id)) {
            throw ValidationException::withMessages(['warehouse_id' => 'Gudang tidak tersedia untuk barang ini.']);
        }
    }
}
