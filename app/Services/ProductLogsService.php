<?php

namespace App\Services;

use App\Dto\ProductLogs\CreateProductLogsDto;
use App\Repositories\Interfaces\ProductLogsRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ProductLogsService
{
    public function __construct(
        protected ProductLogsRepositoryInterface $productLogsRepository
    ) {}

    public function store(CreateProductLogsDto $data)
    {
        return DB::transaction(function () use ($data) {
            return $this->productLogsRepository->store($data);
        });
    }

    public function getProductsWarehouse(int $product_id)
    {
        return $this->productLogsRepository->getProductsWarehouse($product_id);
    }

    public function getOffByMajor(int $major_id,?string $sub_type, ?string $product_name, ?int $page = null)
    {
        return $this->productLogsRepository->getOffByMajor($major_id, $sub_type, $product_name, $page);
    }

    public function writeOff(int $product_id, int $warehouse_id, int $quantity)
    {
        return DB::transaction(function () use ($product_id, $warehouse_id, $quantity) {
            return $this->productLogsRepository->writeOff($product_id, $warehouse_id, $quantity);
        });
    }

    public function restock(int $product_id, int $warehouse_id, int $quantity)
    {
        return DB::transaction(function() use ($product_id, $warehouse_id, $quantity) {
            return $this->productLogsRepository->restock($product_id, $warehouse_id, $quantity);
        });
    }

    public function getStatisticsPerYear(string $year, int $major_id)
    {
        return $this->productLogsRepository->getStatisticsPerYear($year, $major_id);
    }

    public function countBorrowed(int $major_id)
    {
        return $this->productLogsRepository->countBorrowed($major_id);
    }

    public function getRestockProducts(?int $majorId,?string $product_name, ?string $type, ?string $occured_at)
    {
        return $this->productLogsRepository->getRestockProducts($majorId, $product_name, $type, $occured_at);
    }
}
