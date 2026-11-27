<?php

namespace App\Domain\Platform\Enums;

use Carbon\CarbonInterface;

enum BillingCycle: string
{
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    public function label(): string
    {
        return $this === self::Monthly ? 'Bulanan' : 'Tahunan';
    }

    /** Akhir periode (inklusif) bila periode dimulai pada $start. */
    public function periodEnd(CarbonInterface $start): CarbonInterface
    {
        return $this === self::Monthly
            ? $start->copy()->addMonthNoOverflow()->subDay()
            : $start->copy()->addYearNoOverflow()->subDay();
    }
}
