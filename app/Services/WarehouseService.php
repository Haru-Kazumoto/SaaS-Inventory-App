<?php

namespace App\Services;

use App\Http\Requests\WarehouseRequest;
use App\Repositories\Interfaces\WarehouseRepositoryInterface;
use App\Models\Warehouse;

class WarehouseService
{

    private WarehouseRepositoryInterface $warehouseRepository;

    /**
     * Create a new class instance.
     */
    public function __construct(WarehouseRepositoryInterface $warehouseRepository)
    {
        $this->warehouseRepository = $warehouseRepository;
    }

    public function getByCode(string $code)
    {
        return $this->warehouseRepository->getByCode($code);
    }

    public function getAllByProductsQuantityInformation(int $major_id, int $product_id)
    {
        return $this->warehouseRepository->getAllByProductsQuantityInformation($major_id, $product_id);
    }

    public function getWarehouseByMajorId(?int $major_id)
    {
        return $this
            ->warehouseRepository
            ->indexByMajorId($major_id);
    }

    public function storeWarehouse(array $warehouseRequest)
    {
        return $this
            ->warehouseRepository
            ->store($warehouseRequest);
    }

    public function delete(Warehouse $warehouse)
    {
        return $this->warehouseRepository->delete($warehouse);
    }

    public function update(array $data, Warehouse $warehouse)
    {
        return $this->warehouseRepository->update($data, $warehouse);
    }
}
