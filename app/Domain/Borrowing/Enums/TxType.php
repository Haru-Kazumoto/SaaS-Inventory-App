<?php

namespace App\Domain\Borrowing\Enums;

enum TxType: string
{
    case Borrow = 'BORROW';
    case Return = 'RETURN';
}
