<?php

namespace App\Domain\Inventory\Data;

use App\Domain\Inventory\Enums\StockCondition;

final readonly class StockLine
{
    public function __construct(
        public int $warehouseId,
        public StockCondition $condition,
        public int $quantity,
    ) {}

    public function key(): string
    {
        return "{$this->warehouseId}|{$this->condition->value}";
    }
}
