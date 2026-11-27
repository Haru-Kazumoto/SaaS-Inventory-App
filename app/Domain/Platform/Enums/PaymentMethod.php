<?php

namespace App\Domain\Platform\Enums;

enum PaymentMethod: string
{
    case BankTransfer = 'bank_transfer';
    case VirtualAccount = 'virtual_account';
    case Qris = 'qris';
    case Cash = 'cash';
    case Other = 'other';
}
