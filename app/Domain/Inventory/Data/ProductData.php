<?php

namespace App\Domain\Inventory\Data;

use App\Domain\Inventory\Enums\ProductType;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;

final readonly class ProductData
{
    /**
     * @param  StockLine[]  $stock  target stok per gudang & kondisi (stok awal saat buat / target saat ubah)
     */
    public function __construct(
        public int $unitId,
        public string $name,
        public string $code,
        public ProductType $type,
        public ?int $categoryId,
        public ?int $uomId,
        public ?string $brandName,
        public int $price,
        public ?CarbonInterface $labelingDate,
        public ?UploadedFile $image,
        public array $stock,
    ) {}
}
