<?php

namespace App\Domain\Borrowing\Enums;

/** Status pinjaman yang DIHITUNG (bukan kolom transactions.status). */
enum BorrowStatus: string
{
    case Borrowed = 'POSTED';                 // belum ada yang kembali
    case PartiallyReturned = 'PARTIAL_RETURNED';
    case Completed = 'COMPLETED';             // semua barang tidak habis pakai sudah kembali (atau hanya habis pakai)

    public function label(): string
    {
        return match ($this) {
            self::Borrowed => 'Dipinjam',
            self::PartiallyReturned => 'Kembali sebagian',
            self::Completed => 'Selesai',
        };
    }
}
