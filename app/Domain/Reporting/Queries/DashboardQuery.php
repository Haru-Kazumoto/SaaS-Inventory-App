<?php

namespace App\Domain\Reporting\Queries;

use App\Domain\Borrowing\Queries\LoanStatusQuery;
use App\Domain\Organization\Support\CurrentOrganization;
use App\Domain\Organization\Support\OrgScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/** Angka dashboard, selalu dibatasi organisasi aktif & unit yang boleh diakses. */
final class DashboardQuery
{
    public function __construct(private LoanStatusQuery $loans) {}

    /** Dashboard operasional (anggota unit). */
    public function operational(int $period): array
    {
        $unitIds = CurrentOrganization::unitIds() ?: [0];

        return [
            'count' => [
                'total_products' => DB::table('products')->where('organization_id', CurrentOrganization::id())
                    ->whereIn('unit_id', $unitIds)->whereNull('deleted_at')->count(),
                'total_borrowed' => $this->borrowedOutstanding($unitIds),
                'total_request' => DB::table('request_items')->where('organization_id', CurrentOrganization::id())
                    ->whereIn('unit_id', $unitIds)->whereYear('created_at', $period)->count(),
            ],
            'gradual_statistics' => $this->monthlyConsumables($unitIds, $period),
            'todays_borrower' => DB::table('daily_borrowers')
                ->where('organization_id', CurrentOrganization::id())
                ->whereIn('unit_id', $unitIds)
                ->orderByDesc('created_at')
                ->get(['fullname', 'phone', 'created_at', 'total_items']),
            'period' => $period,
        ];
    }

    /** Dashboard admin organisasi: ringkasan semua unit + pengajuan terbaru. */
    public function organization(): array
    {
        $units = DB::table('units as un')
            ->leftJoin('products as p', fn ($j) => $j->on('p.unit_id', '=', 'un.id')->whereNull('p.deleted_at'))
            ->where('un.organization_id', CurrentOrganization::id())
            ->groupBy('un.id', 'un.name', 'un.code')
            ->orderBy('un.name')
            ->selectRaw('un.id, un.name, un.code, COUNT(p.id) AS products_count')
            ->get()
            ->map(fn ($u) => [...(array) $u, 'products_count' => (int) $u->products_count]);

        $requests = DB::table('request_items as ri')
            ->join('units as un', 'un.id', '=', 'ri.unit_id')
            ->tap(fn ($q) => OrgScope::apply($q, 'ri'))
            ->orderByDesc('ri.created_at')
            ->select(['ri.id', 'ri.item_name', 'ri.quantity', 'ri.uom', 'ri.reason', 'ri.status', 'un.name as unit_name', 'ri.created_at'])
            ->paginate(10);

        return [
            'count_data' => [
                'all' => (int) $units->sum('products_count'),
                'request_item' => DB::table('request_items')->where('organization_id', CurrentOrganization::id())->count(),
                'borrowed' => $this->borrowedOutstanding($units->pluck('id')->all() ?: [0]),
            ],
            'units' => $units->values(),
            'request_items' => $requests,
        ];
    }

    private function borrowedOutstanding(array $unitIds): int
    {
        return (int) DB::query()
            ->fromSub($this->loans->subquery(), 'ls')
            ->join('transactions as t', 't.id', '=', 'ls.borrow_tx_id')
            ->where('t.organization_id', CurrentOrganization::id())
            ->whereIn('t.unit_id', $unitIds)
            ->sum('ls.outstanding_qty');
    }

    /** Barang habis pakai: masuk (RESTOCK) vs keluar (BORROW_OUT) per bulan. */
    private function monthlyConsumables(array $unitIds, int $year): array
    {
        $raw = DB::table('product_logs as pl')
            ->join('products as p', 'p.id', '=', 'pl.product_id')
            ->where('pl.organization_id', CurrentOrganization::id())
            ->whereIn('p.unit_id', $unitIds)
            ->where('p.type', 'CONSUMABLE')
            ->whereYear('pl.occurred_at', $year)
            ->groupByRaw('MONTH(pl.occurred_at)')
            ->selectRaw("
                MONTH(pl.occurred_at) AS month_num,
                COALESCE(SUM(CASE WHEN pl.move_type = 'RESTOCK' THEN pl.delta ELSE 0 END), 0) AS restock_total,
                COALESCE(SUM(CASE WHEN pl.move_type = 'BORROW_OUT' THEN -pl.delta ELSE 0 END), 0) AS out_total
            ")
            ->get()
            ->keyBy('month_num');

        return collect(range(1, 12))->map(fn (int $m) => [
            'month_num' => $m,
            'month_label' => Carbon::createFromDate($year, $m, 1)->format('M'),
            'restock_total' => (int) ($raw[$m]->restock_total ?? 0),
            'out_total' => (int) ($raw[$m]->out_total ?? 0),
        ])->all();
    }
}
