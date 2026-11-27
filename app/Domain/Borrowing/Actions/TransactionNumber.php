<?php

namespace App\Domain\Borrowing\Actions;

use App\Domain\Borrowing\Models\Transaction;

/** Nomor transaksi berurutan per organisasi per hari: PJM-20261201-0001 / KMB-… */
final class TransactionNumber
{
    public static function next(string $prefix): string
    {
        $base = $prefix.'-'.now()->format('Ymd').'-';

        $last = Transaction::where('tx_number', 'like', $base.'%')
            ->lockForUpdate()
            ->orderByDesc('tx_number')
            ->value('tx_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $base.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
