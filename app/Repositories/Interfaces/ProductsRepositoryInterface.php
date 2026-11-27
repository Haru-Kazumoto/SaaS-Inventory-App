<?php

namespace App\Repositories\Interfaces;

use App\Dto\Products\CreateProductsDto;
use App\Dto\Products\FilterIndexDto;
use App\Dto\Products\PersistProductDto;
use App\Dto\Products\UpdateProductsDto;
use Illuminate\Http\UploadedFile;

interface ProductsRepositoryInterface
{
    public function getAll(?FilterIndexDto $filter, ?int $major_id, ?int $page);
    public function getById(int $id);
    public function getProductDetailWithAggregates(int $product_id);
    public function store(array $data);
    public function update(UpdateProductsDto $data, int $id);
    public function softDelete(int $id);
    public function count(int $major_id);
    public function countFromAllMajor();
}