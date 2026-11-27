<?php

namespace App\Domain\Borrowing\Queries;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Status & sisa pinjaman per transaksi BORROW (hanya barang tidak habis pakai yang dihitung).
 *
 *   borrowed    = SUM(qty baris pinjam barang DURABLE)
 *   returned    = SUM(qty baris kembali yang menunjuk baris tsb)
 *   computed_status: tanpa barang durable / returned >= borrowed → COMPLETED,
 *                    returned > 0 → PARTIAL_RETURNED, selain itu → POSTED
 */
final class LoanStatusQuery
{
    /** Subquery: borrow_tx_id, products_count, borrowed_qty, returned_qty, outstanding_qty, computed_status. */
    public function subquery(): Builder
    {
        $returned = DB::table('transaction_lines')
            ->whereNotNull('borrow_line_id')
            ->groupBy('borrow_line_id')
            ->selectRaw('borrow_line_id, SUM(quantity) AS qty');

        $perTx = DB::table('transaction_lines as bl')
            ->join('transactions as t', 't.id', '=', 'bl.transaction_id')
            ->join('products as p', 'p.id', '=', 'bl.product_id')
            ->leftJoinSub($returned, 'r', 'r.borrow_line_id', '=', 'bl.id')
            ->where('t.tx_type', 'BORROW')
            ->groupBy('bl.transaction_id')
            ->selectRaw("
                bl.transaction_id AS borrow_tx_id,
                COUNT(bl.id) AS products_count,
                COALESCE(SUM(CASE WHEN p.type = 'DURABLE' THEN bl.quantity ELSE 0 END), 0) AS borrowed_qty,
                COALESCE(SUM(CASE WHEN p.type = 'DURABLE' THEN COALESCE(r.qty, 0) ELSE 0 END), 0) AS returned_qty
            ");

        return DB::query()->fromSub($perTx, 'ls')->selectRaw("
            ls.*,
            (ls.borrowed_qty - ls.returned_qty) AS outstanding_qty,
            CASE
                WHEN ls.borrowed_qty = 0 OR ls.returned_qty >= ls.borrowed_qty THEN 'COMPLETED'
                WHEN ls.returned_qty > 0 THEN 'PARTIAL_RETURNED'
                ELSE 'POSTED'
            END AS computed_status
        ");
    }

    public function statusFor(int $borrowTxId): string
    {
        return (string) (DB::query()->fromSub($this->subquery(), 's')->where('borrow_tx_id', $borrowTxId)->value('computed_status') ?? 'COMPLETED');
    }

    public function outstandingFor(int $borrowTxId): int
    {
        return (int) DB::query()->fromSub($this->subquery(), 's')->where('borrow_tx_id', $borrowTxId)->value('outstanding_qty');
    }
}
