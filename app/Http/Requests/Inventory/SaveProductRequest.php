<?php

namespace App\Http\Requests\Inventory;

use App\Domain\Inventory\Data\ProductData;
use App\Domain\Inventory\Data\StockLine;
use App\Domain\Inventory\Enums\ProductType;
use App\Domain\Inventory\Enums\StockCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class SaveProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses unit dicek di Action
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => strtoupper(trim((string) $this->input('name'))),
            'code' => strtoupper(preg_replace('/\s+/', '_', trim((string) $this->input('code')))),
        ]);
    }

    public function rules(): array
    {
        return [
            'unit_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::enum(ProductType::class)],
            'category_id' => ['required', 'integer'],
            'uom_id' => ['required', 'integer'],
            'brand_name' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'integer', 'min:0'],
            'labeling_date' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'max:4096'],
            'stock' => [$this->isMethod('post') ? 'required' : 'present', 'array'],
            'stock.*.warehouse_id' => ['required', 'integer'],
            'stock.*.condition' => ['required', Rule::in(['GOOD', 'DAMAGED'])],
            'stock.*.quantity' => ['required', 'integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'unit_id' => 'unit',
            'name' => 'nama barang',
            'code' => 'kode barang',
            'type' => 'tipe',
            'category_id' => 'kategori',
            'uom_id' => 'satuan',
            'price' => 'harga',
            'labeling_date' => 'tanggal pelabelan',
            'image' => 'gambar',
            'stock' => 'stok per gudang',
            'stock.*.warehouse_id' => 'gudang',
            'stock.*.quantity' => 'jumlah',
        ];
    }

    public function toData(): ProductData
    {
        return new ProductData(
            unitId: $this->integer('unit_id'),
            name: $this->input('name'),
            code: $this->input('code'),
            type: ProductType::from($this->input('type')),
            categoryId: $this->integer('category_id') ?: null,
            uomId: $this->integer('uom_id') ?: null,
            brandName: $this->filled('brand_name') ? strtoupper($this->input('brand_name')) : null,
            price: $this->integer('price'),
            labelingDate: $this->filled('labeling_date') ? Carbon::parse($this->input('labeling_date')) : null,
            image: $this->file('image'),
            stock: array_map(
                fn ($l) => new StockLine((int) $l['warehouse_id'], StockCondition::from($l['condition']), (int) $l['quantity']),
                $this->input('stock', []),
            ),
        );
    }
}
