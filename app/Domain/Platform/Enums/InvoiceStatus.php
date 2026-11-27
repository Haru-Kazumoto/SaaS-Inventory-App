<?php

namespace App\Domain\Platform\Enums;

/**
 * Status tersimpan. "Jatuh tempo/terlambat" bukan status tersimpan,
 * melainkan turunan: unpaid + due_at < hari ini (lihat Invoice::displayStatus()).
 */
enum InvoiceStatus: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Void = 'void';
}
