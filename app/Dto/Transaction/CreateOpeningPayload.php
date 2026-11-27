<?php

namespace App\Dto\Transaction;

class CreateOpeningPayload {
    public function __construct(
        public string $occurred_at,
        public array $lines
    ){}
}