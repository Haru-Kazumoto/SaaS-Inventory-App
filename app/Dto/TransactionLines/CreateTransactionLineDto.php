<?php

namespace App\Dto\TransactionLines;

class CreateTransactionLineDto {
    public function __construct(
        public int $line_no,
        public int $quantity,
        public ?string $condition_from,
        public ?string $condition_to,
        public int $product_id,
    ){}
}