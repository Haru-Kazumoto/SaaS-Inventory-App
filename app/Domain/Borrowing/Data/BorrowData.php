<?php

namespace App\Domain\Borrowing\Data;

use Carbon\CarbonInterface;

final readonly class BorrowData
{
    /**
     * @param  array<int, array{product_id: int, warehouse_id: int, quantity: int}>  $lines
     */
    public function __construct(
        public int $unitId,
        public string $borrowerName,
        public ?string $identityNumber,
        public ?string $phone,
        public ?string $responsibleName,
        public ?string $usageLocation,
        public ?string $description,
        public ?CarbonInterface $dueAt,
        public array $lines,
    ) {}
}
