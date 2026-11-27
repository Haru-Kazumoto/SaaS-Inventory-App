<?php

namespace App\Dto\Products;

use Illuminate\Http\UploadedFile;

class CreateProductsDto
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $name,
        public string $code,
        public ?string $brand_name,
        public string $type,
        public int $price,
        public string $labeling_date,
        public ?UploadedFile $image_product,
        // public int $warehouse_id,
        public ?int $major_id,
        public int $sub_type_id,
        public int $unit_id
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'brand_name' => $this->brand_name,
            'type' => $this->type,
            'price' => $this->price,
            'labeling_date' => $this->labeling_date,
            'image_product' => $this->image_product,
            // 'warehouse_id' => $this->warehouse_id,
            'major_id' => $this->major_id,
            'sub_type_id' => $this->sub_type_id,
            'unit_id' => $this->unit_id,
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}

// DTO PERSIST (ke repository) — menyimpan path string
class PersistProductDto
{
    public function __construct(
        public string $name,
        public string $code,
        public ?string $brand_name,
        public string $type,
        public int $price,
        public string $labeling_date,
        public ?string $image_product, // path
        public int $warehouse_id,
        public ?int $major_id,
        public int $sub_type_id,
        public int $unit_id
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'brand_name' => $this->brand_name,
            'type' => $this->type,
            'price' => $this->price,
            'labeling_date' => $this->labeling_date,
            'image_product' => $this->image_product,
            'warehouse_id' => $this->warehouse_id,
            'major_id' => $this->major_id,
            'sub_type_id' => $this->sub_type_id,
            'unit_id' => $this->unit_id,
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}

final class CreateOpeningLineDto
{
    public function __construct(
        public int $product_id,
        public int $warehouse_id,
        public int $quantity,
        public ?string $condition, // GOOD/DAMAGED (hindari OFF untuk opening)
        public int $line_no
    ) {}
}

final class CreateOpeningPayload
{
    public function __construct(
        public string $occurred_at,              // 'Y-m-d H:i:s'
        /** @var CreateOpeningLineDto[] */
        public array $lines
    ) {}
}
