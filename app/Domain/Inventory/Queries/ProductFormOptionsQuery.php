<?php

namespace App\Domain\Inventory\Queries;

use App\Domain\Inventory\Models\Product;
use App\Domain\MasterData\Models\Category;
use App\Domain\MasterData\Models\Uom;
use App\Domain\MasterData\Models\Warehouse;
use App\Domain\Organization\Support\Access;
use App\Domain\Organization\Support\CurrentOrganization;

/** Pilihan untuk form tambah/ubah barang. unit_id null = data bersama (berlaku untuk semua unit). */
final class ProductFormOptionsQuery
{
    public function get(?Product $product = null): array
    {
        $manageable = Access::manageableUnitIds();
        $visibleUnits = fn ($q) => $q->whereNull('unit_id')->orWhereIn('unit_id', $manageable ?: [0]);

        $options = [
            'units' => CurrentOrganization::units()->whereIn('id', $manageable)
                ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])->values()->all(),
            'categories' => Category::where($visibleUnits)->orderBy('name')->get(['id', 'name', 'unit_id'])->toArray(),
            'uoms' => Uom::where($visibleUnits)->orderBy('name')->get(['id', 'name', 'unit_id'])->toArray(),
            'warehouses' => Warehouse::where($visibleUnits)->orderBy('name')->get(['id', 'name', 'unit_id'])->toArray(),
        ];

        if ($product) {
            $options['product'] = [
                'id' => $product->id,
                'unit_id' => $product->unit_id,
                'name' => $product->name,
                'code' => $product->code,
                'brand_name' => $product->brand_name,
                'type' => $product->type->value,
                'price' => $product->price,
                'labeling_date' => $product->labeling_date?->toDateString(),
                'category_id' => $product->category_id,
                'uom_id' => $product->uom_id,
                'image_url' => $product->imageUrl(),
            ];
            $options['stock'] = app(ProductDetailQuery::class)->stockByWarehouse($product->id);
        }

        return $options;
    }
}
