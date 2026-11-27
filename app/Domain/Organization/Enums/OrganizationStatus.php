<?php

namespace App\Domain\Organization\Enums;

enum OrganizationStatus: string
{
    case Trial = 'trial';
    case Active = 'active';
    case PastDue = 'past_due';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Trial => 'Masa coba',
            self::Active => 'Aktif',
            self::PastDue => 'Menunggak',
            self::Suspended => 'Ditangguhkan',
            self::Cancelled => 'Berhenti',
        };
    }
}
