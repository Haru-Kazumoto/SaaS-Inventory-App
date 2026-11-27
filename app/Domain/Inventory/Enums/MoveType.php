<?php

namespace App\Domain\Inventory\Enums;

enum MoveType: string
{
    case Opening = 'OPENING';               // stok awal saat barang dibuat
    case Restock = 'RESTOCK';               // barang masuk
    case BorrowOut = 'BORROW_OUT';          // keluar karena dipinjam / dipakai
    case Return = 'RETURN';                 // kembali dari peminjaman
    case WriteOff = 'WRITE_OFF';            // dihapuskan
    case AdjustmentPos = 'ADJUSTMENT_POS';  // koreksi stok (+)
    case AdjustmentNeg = 'ADJUSTMENT_NEG';  // koreksi stok (−)
}
