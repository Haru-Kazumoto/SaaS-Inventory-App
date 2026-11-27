<?php

namespace App\Services;

use App\Repositories\Interfaces\TransactionLinesRepositoryInterface;

class TransactionLinesService
{
    public function __construct(
        protected TransactionLinesRepositoryInterface $transactionLineRepositoryInterface
    ) {}

    public function getByTransaction(int $transaction_id)
    {
        return $this->transactionLineRepositoryInterface->getByTransactionId($transaction_id);
    }

    public function getBorrowedDurableProductLines(
        ?int $major_id,
        ?string $borrower_name,
        ?string $product_code,
        ?int $warehouse_id = null,
        ?string $date = null
    ) {
        return $this->transactionLineRepositoryInterface->getBorrowedDurableProductLines($major_id, $borrower_name, $product_code, $warehouse_id, $date);
    }

    public function getBorrowedConsumableProductLines(
        ?int $major_id,
        ?string $borrower_name,
        ?string $product_code,
        ?int $warehouse_id = null,
        ?string $date = null
    ) {
        return $this->transactionLineRepositoryInterface->getBorrowedConsumableProductLines($major_id, $borrower_name, $product_code, $warehouse_id, $date);
    }
}
