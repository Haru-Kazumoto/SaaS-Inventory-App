<?php

namespace App\Domain\Inventory\Queries;

use App\Domain\Inventory\Models\Product;
use App\Domain\Organization\Support\Access;
use Illuminate\Support\Facades\DB;

final class ProductDetailQuery
{
    public function get(Product $product): array
    {
        $product->loadMissing(['unit:id,name', 'category:id,name', 'uom:id,name']);

        $agg = DB::table('product_logs')
            ->where('product_id', $product->id)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN `condition` = 'GOOD'    THEN delta ELSE 0 END), 0) AS good_qty,
                COALESCE(SUM(CASE WHEN `condition` = 'DAMAGED' THEN delta ELSE 0 END), 0) AS damaged_qty,
                COALESCE(SUM(CASE WHEN `condition` = 'OFF'     THEN delta ELSE 0 END), 0) AS off_qty,
                COALESCE(SUM(CASE WHEN `condition` <> 'OFF'    THEN delta ELSE 0 END), 0) AS usable_qty,
                COALESCE(SUM(CASE WHEN move_type = 'BORROW_OUT' THEN quantity ELSE 0 END), 0)
                  - COALESCE(SUM(CASE WHEN move_type = 'RETURN' THEN quantity ELSE 0 END), 0) AS out_qty
            ")
            ->first();

        return [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'brand_name' => $product->brand_name,
                'type' => $product->type->value,
                'price' => $product->price,
                'labeling_date' => $product->labeling_date?->toDateString(),
                'created_at' => $product->created_at?->toIso8601String(),
                'unit_id' => $product->unit_id,
                'unit_name' => $product->unit?->name,
                'category_name' => $product->category?->name,
                'uom_name' => $product->uom?->name,
                'image_url' => $product->imageUrl(),
                'good_qty' => (int) $agg->good_qty,
                'damaged_qty' => (int) $agg->damaged_qty,
                'off_qty' => (int) $agg->off_qty,
                'usable_qty' => (int) $agg->usable_qty,
                'out_qty' => max(0, (int) $agg->out_qty),
            ],
            'stock' => $this->stockByWarehouse($product->id),
            'warehouses' => $this->warehousesFor($product),
            'canManage' => Access::canManage($product->unit_id),
        ];
    }

    /** Saldo per gudang × kondisi (tanpa OFF & tanpa saldo 0). */
    public function stockByWarehouse(int $productId): array
    {
        return DB::table('product_logs as pl')
            ->join('warehouses as w', 'w.id', '=', 'pl.warehouse_id')
            ->where('pl.product_id', $productId)
            ->where('pl.condition', '!=', 'OFF')
            ->groupBy('pl.warehouse_id', 'w.name', 'pl.condition')
            ->havingRaw('SUM(pl.delta) <> 0')
            ->orderBy('w.name')
            ->selectRaw('pl.warehouse_id, w.name AS warehouse_name, pl.condition, SUM(pl.delta) AS on_hand')
            ->get()
            ->map(fn ($r) => [...(array) $r, 'on_hand' => (int) $r->on_hand])
            ->all();
    }

    /** Gudang yang bisa dipakai barang (milik unitnya + bersama) beserta saldo barang ini. */
    public function warehousesFor(Product $product): array
    {
        return DB::table('warehouses as w')
            ->leftJoin('product_logs as pl', fn ($j) => $j->on('pl.warehouse_id', '=', 'w.id')->where('pl.product_id', $product->id))
            ->where('w.organization_id', $product->organization_id)
            ->where(fn ($q) => $q->whereNull('w.unit_id')->orWhere('w.unit_id', $product->unit_id))
            ->groupBy('w.id', 'w.name')
            ->orderBy('w.name')
            ->selectRaw("
                w.id, w.name,
                COALESCE(SUM(CASE WHEN pl.`condition` <> 'OFF' THEN pl.delta ELSE 0 END), 0) AS current_quantity,
                COALESCE(SUM(CASE WHEN pl.`condition` = 'GOOD' THEN pl.delta ELSE 0 END), 0) AS good_quantity,
                COALESCE(SUM(CASE WHEN pl.`condition` = 'DAMAGED' THEN pl.delta ELSE 0 END), 0) AS damaged_quantity
            ")
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'current_quantity' => (int) $r->current_quantity,
                'good_quantity' => (int) $r->good_quantity,
                'damaged_quantity' => (int) $r->damaged_quantity,
            ])
            ->all();
    }
}
