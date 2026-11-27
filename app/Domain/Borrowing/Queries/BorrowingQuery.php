<?php

namespace App\Domain\Borrowing\Queries;

use App\Domain\Borrowing\Models\Transaction;
use App\Domain\Inventory\Enums\ProductType;
use App\Domain\Organization\Support\Access;
use App\Domain\Organization\Support\OrgScope;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class BorrowingQuery
{
    public function __construct(private LoanStatusQuery $status) {}

    /** @param array{borrower_name?: ?string, status?: ?string, unit_id?: ?string} $filters */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = DB::table('transactions as t')
            ->join('borrowers as b', 'b.id', '=', 't.borrower_id')
            ->join('units as un', 'un.id', '=', 't.unit_id')
            ->leftJoin('users as u', 'u.id', '=', 't.created_by')
            ->joinSub($this->status->subquery(), 'ls', 'ls.borrow_tx_id', '=', 't.id')
            ->where('t.tx_type', 'BORROW')
            ->tap(fn ($q) => OrgScope::apply($q, 't'))
            ->tap(fn ($q) => OrgScope::units($q, 't.unit_id', ! empty($filters['unit_id']) ? (int) $filters['unit_id'] : null))
            ->when($filters['borrower_name'] ?? null, fn ($q, $v) => $q->where('b.fullname', 'like', "%{$v}%"))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('ls.computed_status', $v))
            ->orderByDesc('t.occurred_at')
            ->orderByDesc('t.id')
            ->selectRaw('
                t.id, t.tx_number, t.unit_id, un.name AS unit_name, t.description, t.created_at, t.occurred_at, t.due_at,
                b.fullname AS borrower_name, b.phone AS borrower_phone, b.identity_number,
                b.responsible_name, b.usage_location,
                u.fullname AS creator_name,
                ls.products_count, ls.outstanding_qty, ls.computed_status
            ');

        return $query->paginate($perPage)->withQueryString()->through(fn ($r) => [
            ...(array) $r,
            'products_count' => (int) $r->products_count,
            'outstanding_qty' => (int) $r->outstanding_qty,
            'is_overdue' => $r->due_at && $r->computed_status !== 'COMPLETED' && $r->due_at < today()->toDateString(),
            'can_operate' => Access::canOperate((int) $r->unit_id),
        ]);
    }

    public function detail(Transaction $tx): array
    {
        $tx->loadMissing(['borrower', 'unit:id,name', 'creator:id,fullname', 'lines.product.uom:id,name', 'lines.warehouse:id,name', 'returns.lines', 'returns.creator:id,fullname']);

        $returnedByLine = DB::table('transaction_lines')
            ->whereIn('borrow_line_id', $tx->lines->pluck('id'))
            ->groupBy('borrow_line_id')
            ->selectRaw('borrow_line_id, SUM(quantity) AS qty')
            ->pluck('qty', 'borrow_line_id');

        return [
            'transaction' => [
                'id' => $tx->id,
                'tx_number' => $tx->tx_number,
                'status' => $this->status->statusFor($tx->id),
                'unit_id' => $tx->unit_id,
                'unit_name' => $tx->unit?->name,
                'occurred_at' => $tx->occurred_at?->toIso8601String(),
                'created_at' => $tx->created_at?->toIso8601String(),
                'due_at' => $tx->due_at?->toDateString(),
                'returned_at' => $tx->returned_at?->toIso8601String(),
                'description' => $tx->description,
                'created_by_name' => $tx->creator?->fullname,
                'borrower_name' => $tx->borrower?->fullname,
                'borrower_phone' => $tx->borrower?->phone,
                'identity_number' => $tx->borrower?->identity_number,
                'responsible_name' => $tx->borrower?->responsible_name,
                'usage_location' => $tx->borrower?->usage_location,
            ],
            'lines' => $tx->lines->map(fn ($l) => [
                'id' => $l->id,
                'product_id' => $l->product_id,
                'product_name' => $l->product?->name,
                'product_code' => $l->product?->code,
                'product_type' => $l->product?->type?->value,
                'uom_name' => $l->product?->uom?->name,
                'warehouse_id' => $l->warehouse_id,
                'warehouse_name' => $l->warehouse?->name,
                'quantity' => $l->quantity,
                'returned_qty' => (int) ($returnedByLine[$l->id] ?? 0),
                'outstanding' => $l->product?->type === ProductType::Durable
                    ? max(0, $l->quantity - (int) ($returnedByLine[$l->id] ?? 0))
                    : 0,
            ])->values(),
            'returns' => $tx->returns->sortByDesc('occurred_at')->values()->map(fn ($r) => [
                'id' => $r->id,
                'tx_number' => $r->tx_number,
                'occurred_at' => $r->occurred_at?->toIso8601String(),
                'created_by_name' => $r->creator?->fullname,
                'total_qty' => (int) $r->lines->sum('quantity'),
            ]),
            'summary' => [
                'total_lines' => $tx->lines->count(),
                'total_qty' => (int) $tx->lines->sum('quantity'),
            ],
            'canOperate' => Access::canOperate($tx->unit_id),
        ];
    }
}
