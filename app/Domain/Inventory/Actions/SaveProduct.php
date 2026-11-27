<?php

namespace App\Domain\Inventory\Actions;

use App\Domain\Inventory\Data\ProductData;
use App\Domain\Inventory\Data\StockLine;
use App\Domain\Inventory\Enums\MoveType;
use App\Domain\Inventory\Enums\StockCondition;
use App\Domain\Inventory\Ledger\StockLedger;
use App\Domain\Inventory\Ledger\StockMovement;
use App\Domain\Inventory\Models\Product;
use App\Domain\MasterData\Models\Category;
use App\Domain\MasterData\Models\Uom;
use App\Domain\MasterData\Models\Warehouse;
use App\Domain\Organization\Support\Access;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Buat barang baru (stok awal dicatat sebagai OPENING) atau ubah barang
 * (stok direkonsiliasi ke target: selisih dicatat ADJUSTMENT_POS / ADJUSTMENT_NEG).
 */
final class SaveProduct
{
    public function __construct(private StockLedger $ledger) {}

    public function handle(ProductData $data, ?Product $product = null): Product
    {
        Access::authorizeManage($data->unitId);
        if ($product && $product->unit_id !== $data->unitId) {
            Access::authorizeManage($product->unit_id);
        }

        $this->assertUsable(Category::class, $data->categoryId, $data->unitId, 'category_id', 'Kategori');
        $this->assertUsable(Uom::class, $data->uomId, $data->unitId, 'uom_id', 'Satuan');
        foreach ($data->stock as $i => $line) {
            $this->assertUsable(Warehouse::class, $line->warehouseId, $data->unitId, "stock.{$i}.warehouse_id", 'Gudang');
        }

        $imagePath = $data->image
            ? $data->image->storeAs('products', 'product_'.Str::uuid().'.'.$data->image->getClientOriginalExtension(), 'public')
            : null;

        return DB::transaction(function () use ($data, $product, $imagePath) {
            $attributes = [
                'unit_id' => $data->unitId,
                'category_id' => $data->categoryId,
                'uom_id' => $data->uomId,
                'code' => $data->code,
                'name' => $data->name,
                'brand_name' => $data->brandName,
                'type' => $data->type,
                'price' => $data->price,
                'labeling_date' => $data->labelingDate,
            ];

            if ($product) {
                $oldImage = $imagePath ? $product->image_path : null;
                $product->update($imagePath ? [...$attributes, 'image_path' => $imagePath] : $attributes);
                $this->reconcile($product, $data->stock);
                if ($oldImage) {
                    DB::afterCommit(fn () => Storage::disk('public')->delete($oldImage));
                }

                return $product;
            }

            $product = Product::create([...$attributes, 'image_path' => $imagePath]);

            foreach ($this->merge($data->stock) as $line) {
                if ($line->quantity > 0) {
                    $this->ledger->move(new StockMovement(
                        $product->id, $line->warehouseId, MoveType::Opening, $line->condition, $line->quantity, true, 'opening',
                    ));
                }
            }

            return $product;
        });
    }

    /** Samakan saldo tiap bucket (gudang × kondisi Bagus/Rusak) dengan target. */
    private function reconcile(Product $product, array $target): void
    {
        $targetQty = [];
        foreach ($this->merge($target) as $line) {
            $targetQty[$line->key()] = $line->quantity;
        }

        $current = $this->ledger->balances($product->id);
        $stockable = array_map(fn ($c) => $c->value, StockCondition::stockable());

        foreach (array_unique([...array_keys($current), ...array_keys($targetQty)]) as $key) {
            [$warehouseId, $condition] = explode('|', $key);
            if (! in_array($condition, $stockable, true)) {
                continue; // bucket lain (mis. OUT) tidak diubah dari form
            }

            $diff = ($targetQty[$key] ?? 0) - ($current[$key] ?? 0);
            if ($diff === 0) {
                continue;
            }

            $this->ledger->move(new StockMovement(
                $product->id,
                (int) $warehouseId,
                $diff > 0 ? MoveType::AdjustmentPos : MoveType::AdjustmentNeg,
                StockCondition::from($condition),
                abs($diff),
                $diff > 0,
                'adjustment',
                reason: 'Penyesuaian stok dari form ubah barang',
            ));
        }
    }

    /** @param StockLine[] $lines  @return StockLine[] gabung baris gudang+kondisi yang sama */
    private function merge(array $lines): array
    {
        $merged = [];
        foreach ($lines as $line) {
            $key = $line->key();
            $merged[$key] = new StockLine($line->warehouseId, $line->condition, ($merged[$key]->quantity ?? 0) + $line->quantity);
        }

        return array_values($merged);
    }

    /** Data master harus milik organisasi aktif dan bersama/milik unit barang. */
    private function assertUsable(string $modelClass, ?int $id, int $unitId, string $field, string $label): void
    {
        if ($id === null) {
            return;
        }

        /** @var Model|null $record */
        $record = $modelClass::find($id);
        if (! $record || ($record->unit_id !== null && $record->unit_id !== $unitId)) {
            throw ValidationException::withMessages([$field => "{$label} tidak tersedia untuk unit ini."]);
        }
    }
}
