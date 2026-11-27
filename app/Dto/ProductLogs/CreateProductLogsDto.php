<?php

namespace App\Dto\ProductLogs;

class CreateProductLogsDto
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $move_type,
        public int $quantity,
        public int $delta,
        public string $ref_type,
        public int $ref_id, // transaction id
        public string $line_no,
        public string $condition,
        public int $product_id,
        public string $occured_at
    ) {}

    public function toArray(): array
    {
        return [
            'move_type' => $this->move_type,
            'quantity' => $this->quantity,
            'delta' => $this->delta,
            'ref_type' => $this->ref_type,
            'ref_id' => $this->ref_id,
            'line_no' => $this->line_no,
            'condition' => $this->condition,
            'product_id' => $this->product_id,
            'occured_at' => $this->occured_at
        ];
    }
}
