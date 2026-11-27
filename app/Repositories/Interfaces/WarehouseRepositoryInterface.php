<?php

namespace App\Repositories\Interfaces;

use App\Http\Requests\WarehouseRequest;
use App\Models\Warehouse;

interface WarehouseRepositoryInterface
{
    public function getByCode(string $code);
    public function getAllByProductsQuantityInformation(int $major_id, int $product_id);
    public function index();
    public function indexByMajorId(?int $major_id);
    public function store(array $data);
    public function update(array $data, Warehouse $warehouse);
    public function delete(Warehouse $warehouse);
}
