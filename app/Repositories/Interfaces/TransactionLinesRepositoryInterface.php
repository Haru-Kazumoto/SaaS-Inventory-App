<?php

namespace App\Repositories\Interfaces;

interface TransactionLinesRepositoryInterface
{
    public function bulkInsert(int $transactionId, array $lines): void;
    public function getByTransactionId(int $transactionId);
    public function getNextLineNo(int $transactionId): int;
    public function getByTransaction(int $txId);
    public function deleteById(int $id);
    public function insert(int $txId, int $productId, int $warehouseId, int $qty, int $lineNo);
    public function updateById(int $id, int $productId, int $warehouseId, int $qty, int $lineNo);
    public function updateLineNo(int $id, int $lineNo);
    public function updateQuantityAndLineNo(int $id, int $qty, int $lineNo);
    public function getBorrowedDurableProductLines(
        ?int $major_id = null,
        ?string $borrower_name = null,
        ?string $product_code = null,
        ?int $warehouse_id = null,
        ?string $date = null
    );
    public function getBorrowedConsumableProductLines(
        ?int $major_id = null,
        ?string $borrower_name = null,
        ?string $product_code = null,
        ?int $warehouse_id = null,
        ?string $date = null
    );
}
