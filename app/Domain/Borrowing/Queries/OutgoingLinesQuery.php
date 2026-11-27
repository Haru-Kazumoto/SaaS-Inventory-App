<?php

namespace App\Domain\Borrowing\Queries;

use App\Domain\Organization\Support\OrgScope;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/** Pergerakan barang keluar: tidak habis pakai yang masih dipinjam & riwayat pemakaian habis pakai. */
final class OutgoingLinesQuery
{
    /** @param array{borrower_name?: ?string, product_code?: ?string, warehouse_id?: ?string, date?: ?string, unit_id?: ?string} $filters */
    public function durableOutstanding(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $returned = DB::table('transaction_lines')
            ->whereNotNull('borrow_line_id')
            ->groupBy('borrow_line_id')
            ->selectRaw('borrow_line_id, SUM(quantity) AS qty');

        return $this->base($filters)
            ->leftJoinSub($returned, 'r', 'r.borrow_line_id', '=', 'tl.id')
            ->where('p.type', 'DURABLE')
            ->whereRaw('tl.quantity - COALESCE(r.qty, 0) > 0')
            ->selectRaw('(tl.quantity - COALESCE(r.qty, 0)) AS outstanding_qty')
            ->paginate($perPage)->withQueryString()
            ->through(fn ($r) => [...(array) $r, 'outstanding_qty' => (int) $r->outstanding_qty]);
    }

    public function consumables(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->base($filters)
            ->where('p.type', 'CONSUMABLE')
            ->selectRaw('tl.quantity AS outstanding_qty')
            ->paginate($perPage)->withQueryString()
            ->through(fn ($r) => [...(array) $r, 'outstanding_qty' => (int) $r->outstanding_qty]);
    }

    private function base(array $filters): Builder
    {
        return DB::table('transaction_lines as tl')
            ->join('transactions as t', 't.id', '=', 'tl.transaction_id')
            ->join('borrowers as b', 'b.id', '=', 't.borrower_id')
            ->join('products as p', 'p.id', '=', 'tl.product_id')
            ->join('warehouses as w', 'w.id', '=', 'tl.warehouse_id')
            ->leftJoin('uoms as u', 'u.id', '=', 'p.uom_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->where('t.tx_type', 'BORROW')
            ->tap(fn ($q) => OrgScope::apply($q, 't'))
            ->tap(fn ($q) => OrgScope::units($q, 't.unit_id', ! empty($filters['unit_id']) ? (int) $filters['unit_id'] : null))
            ->when($filters['borrower_name'] ?? null, fn ($q, $v) => $q->where('b.fullname', 'like', "%{$v}%"))
            ->when($filters['product_code'] ?? null, fn ($q, $v) => $q->where('p.code', 'like', "%{$v}%"))
            ->when($filters['warehouse_id'] ?? null, fn ($q, $v) => $q->where('tl.warehouse_id', (int) $v))
            ->when($filters['date'] ?? null, fn ($q, $v) => $q->whereDate('t.occurred_at', $v))
            ->orderByDesc('t.occurred_at')
            ->orderBy('tl.line_no')
            ->addSelect([
                'tl.id', 't.id as transaction_id', 'b.fullname as borrower_name', 'p.name as product_name', 'p.code as product_code',
                'w.name as warehouse_name', 'u.name as uom_name', 'c.name as category_name', 't.occurred_at as borrowed_at',
            ]);
    }
}
