<?php

namespace App\Dto\Transaction;

class CreateTransactionDto {
    public function __construct(
        public string $tx_type,
        public string $occurred_at,
        public ?string $description,
        public ?int $borrower_id,
        public int $created_by,
        public ?string $returned_at,
        public ?int $parent_borrow_tx_id = null // new field for linking return transactions to their corresponding borrow transactions
    ){}
}