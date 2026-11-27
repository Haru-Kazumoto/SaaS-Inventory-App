<?php

namespace App\Domain\Platform\Data;

use App\Domain\Platform\Enums\PaymentMethod;
use Carbon\CarbonInterface;

final readonly class PaymentData
{
    public function __construct(
        public int $amount,
        public PaymentMethod $method,
        public CarbonInterface $paidAt,
        public ?string $reference = null,
        public ?string $notes = null,
        public string $provider = 'manual',
    ) {}
}
