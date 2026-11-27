<?php

namespace App\Dto\ProductLogs;

class CreateManualLogsDto {
    public function __construct(
        public int $product_id,
        public int $warehouse_id,
        public int $quantity,
        public string $fromCondition = 'GOOD',
        public string $toCondition = 'GOOD',
        public string $moveType = 'OPENING'
    ){}

    public function toArray(): array
    {
        return [
            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'quantity' => $this->quantity,
            'fromCondition' => $this->fromCondition,
            'toCondition' => $this->toCondition,
            'moveType' => $this->moveType
        ];
    }
}