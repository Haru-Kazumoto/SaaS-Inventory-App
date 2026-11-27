<?php

namespace App\Domain\Procurement\Enums;

enum RequestStatus: string
{
    case Pending = 'PENDING';
    case Approved = 'APPROVED';
    case Rejected = 'REJECTED';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
        };
    }
}
