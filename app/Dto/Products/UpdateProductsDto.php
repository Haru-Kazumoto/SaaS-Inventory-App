<?php

namespace App\Dto\Products;

class UpdateProductsDto
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
        public ?string $image_product,
        public int $warehouse_id,
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
            'sub_type_id' => $this->sub_type_id,
            'unit_id' => $this->unit_id
        ];
    }
}
