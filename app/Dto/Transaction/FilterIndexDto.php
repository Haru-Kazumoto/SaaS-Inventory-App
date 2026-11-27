<?php

namespace App\Dto\Transaction;

class FilterIndexDto
{
    public function __construct(
        public ?int $creator_id,
        public ?string $borrower_name,
        public ?string $status,
        public ?string $created_at = null
    ) {}
}
