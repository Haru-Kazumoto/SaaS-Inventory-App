<?php

namespace App\Domain\Platform\Data;

final readonly class InvoiceLineData
{
    public function __construct(
        public string $description,
        public int $quantity,
        public int $unitPrice,
    ) {}

    public function amount(): int
    {
        return $this->quantity * $this->unitPrice;
    }
}
