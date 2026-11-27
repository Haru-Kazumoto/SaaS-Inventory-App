<?php

namespace App\Repositories\Interfaces;

use App\Dto\ProductLogs\CreateProductLogsDto;

interface ProductLogsRepositoryInterface
{
    public function store(CreateProductLogsDto $data);
    public function bulkInsert(array $rows);
    public function getProductsWarehouse(int $product_id);
    public function getOnHandByBucket(int $product_id);
    public function onHand(int $productId, int $warehouseId, ?string $condition = 'GOOD');
    public function borrowedOutstanding(int $borrowerId, int $productId, ?int $warehouseId = null);
    public function writeOff(int $product_id, int $warehouse_id, int $quantity, string $fromCondition = 'GOOD');
    public function getOffByMajor(int $major_id, ?string $sub_type, ?string $product_name, ?int $page = null);
    public function getLedger(int $transaction_id);
    public function restock(
        int $product_id,
        int $warehouse_id,
        int $quantity,
        string $condition = 'GOOD'
    );
    public function usableOnHand(int $productId, int $warehouseId);
    public function getStatisticsPerYear(string $year, int $major_id);
    public function countBorrowed(int $major_id);
    public function getRestockProducts(?int $majorId,?string $product_name, ?string $type, ?string $occured_at);
}
