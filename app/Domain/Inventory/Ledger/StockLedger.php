<?php

namespace App\Domain\Inventory\Ledger;

use App\Domain\Inventory\Enums\MoveType;
use App\Domain\Inventory\Enums\StockCondition;
use App\Domain\Inventory\Models\Product;
use App\Domain\Inventory\Models\ProductLog;
use App\Domain\MasterData\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * SATU-SATUNYA pintu perubahan stok (tabel product_logs).
 * Stok = SUM(delta) per (product, warehouse, condition). Bucket OFF bukan stok aktif.
 *
 * Pemanggil wajib berada di dalam DB::transaction().
 */
final class StockLedger
{
    /** Catat satu pergerakan; keluar ditolak bila saldo bucket tidak cukup. */
    public function move(StockMovement $m, string $errorField = 'quantity'): ProductLog
    {
        if (DB::transactionLevel() === 0) {
            throw new LogicException('StockLedger::move harus dipanggil di dalam DB::transaction().');
        }
        if ($m->quantity <= 0) {
            throw new LogicException('Quantity pergerakan stok harus > 0.');
        }

        // kunci baris produk agar pergerakan stok produk yang sama berjalan berurutan
        $product = Product::withTrashed()->whereKey($m->productId)->lockForUpdate()->firstOrFail();
        $warehouse = Warehouse::findOrFail($m->warehouseId);

        if ($warehouse->organization_id !== $product->organization_id) {
            throw new LogicException('Gudang dan barang berbeda organisasi.');
        }

        if (! $m->incoming) {
            $available = $this->onHand($m->productId, $m->warehouseId, $m->condition);
            if ($available < $m->quantity) {
                throw InsufficientStock::make($product->name, $warehouse->name, $available, $m->quantity, $errorField);
            }
        }

        return ProductLog::create([
            'organization_id' => $product->organization_id,
            'product_id' => $m->productId,
            'warehouse_id' => $m->warehouseId,
            'move_type' => $m->type,
            'condition' => $m->condition,
            'quantity' => $m->quantity,
            'delta' => $m->incoming ? $m->quantity : -$m->quantity,
            'ref_type' => $m->refType,
            'ref_id' => $m->refId,
            'line_no' => $m->lineNo,
            'reason' => $m->reason,
            'created_by' => auth()->id(),
            'occurred_at' => $m->occurredAt ?? now(),
        ]);
    }

    /** Hapuskan barang: keluar dari kondisi asal + catatan masuk ke OFF (audit). */
    public function writeOff(int $productId, int $warehouseId, int $quantity, StockCondition $from, ?string $reason = null): void
    {
        $this->move(new StockMovement($productId, $warehouseId, MoveType::WriteOff, $from, $quantity, false, 'manual', null, 1, $reason));
        $this->move(new StockMovement($productId, $warehouseId, MoveType::WriteOff, StockCondition::Off, $quantity, true, 'manual', null, 2, $reason));
    }

    public function onHand(int $productId, int $warehouseId, StockCondition $condition = StockCondition::Good): int
    {
        return (int) DB::table('product_logs')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('condition', $condition->value)
            ->sum('delta');
    }

    /**
     * Saldo stok aktif per bucket.
     *
     * @return array<string, int> "warehouseId|CONDITION" => qty
     */
    public function balances(int $productId): array
    {
        return DB::table('product_logs')
            ->where('product_id', $productId)
            ->where('condition', '!=', StockCondition::Off->value)
            ->groupBy('warehouse_id', 'condition')
            ->selectRaw('warehouse_id, `condition`, SUM(delta) as qty')
            ->get()
            ->mapWithKeys(fn ($r) => ["{$r->warehouse_id}|{$r->condition}" => (int) $r->qty])
            ->all();
    }
}
