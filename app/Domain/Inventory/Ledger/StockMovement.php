<?php

namespace App\Domain\Inventory\Ledger;

use App\Domain\Inventory\Enums\MoveType;
use App\Domain\Inventory\Enums\StockCondition;
use Carbon\CarbonInterface;

/** Satu pergerakan stok pada satu bucket (product × warehouse × condition). */
final readonly class StockMovement
{
    public function __construct(
        public int $productId,
        public int $warehouseId,
        public MoveType $type,
        public StockCondition $condition,
        public int $quantity,                 // selalu positif
        public bool $incoming,                // true = +qty, false = −qty
        public string $refType = 'manual',
        public ?int $refId = null,
        public ?int $lineNo = null,
        public ?string $reason = null,
        public ?CarbonInterface $occurredAt = null,
    ) {}
}
