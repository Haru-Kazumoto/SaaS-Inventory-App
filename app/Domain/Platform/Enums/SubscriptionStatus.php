<?php

namespace App\Domain\Platform\Enums;

enum SubscriptionStatus: string
{
    case Trialing = 'trialing';
    case Active = 'active';
    case PastDue = 'past_due';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    /** Status yang dianggap langganan "berjalan" (paling banyak satu per organisasi). */
    public static function running(): array
    {
        return [self::Trialing, self::Active, self::PastDue];
    }
}
