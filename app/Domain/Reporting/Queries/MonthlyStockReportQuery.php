<?php

namespace App\Domain\Reporting\Queries;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Laporan bulanan barang habis pakai: saldo awal, masuk (RESTOCK) & keluar (BORROW_OUT) per hari, saldo akhir.
 * Dipakai export Excel "Laporan Barang Bulanan".
 */
final class MonthlyStockReportQuery
{
    public function build(int $organizationId, ?int $unitId, int $year, int $month, ?int $categoryId = null): array
    {
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end = (clone $start)->endOfMonth()->endOfDay();

        $logs = fn (): Builder => DB::table('product_logs as pl')
            ->join('products as p', 'p.id', '=', 'pl.product_id')
            ->where('pl.organization_id', $organizationId)
            ->where('p.type', 'CONSUMABLE')
            ->when($unitId, fn ($q, $v) => $q->where('p.unit_id', $v))
            ->when($categoryId, fn ($q, $v) => $q->where('p.category_id', $v));

        $daily = fn (string $moveType, string $qtyExpr) => $logs()
            ->where('pl.move_type', $moveType)
            ->whereBetween('pl.occurred_at', [$start, $end])
            ->groupBy('pl.product_id', 'd')
            ->selectRaw("pl.product_id, DAY(pl.occurred_at) AS d, SUM({$qtyExpr}) AS qty")
            ->get();

        $inRows = $daily('RESTOCK', 'pl.delta');
        $outRows = $daily('BORROW_OUT', 'pl.quantity');

        $days = $inRows->pluck('d')->merge($outRows->pluck('d'))->map(fn ($d) => (int) $d)->unique()->sort()->values()->all();

        $products = DB::table('products as p')
            ->leftJoin('uoms as u', 'u.id', '=', 'p.uom_id')
            ->where('p.organization_id', $organizationId)
            ->where('p.type', 'CONSUMABLE')
            ->whereNull('p.deleted_at')
            ->when($unitId, fn ($q, $v) => $q->where('p.unit_id', $v))
            ->when($categoryId, fn ($q, $v) => $q->where('p.category_id', $v))
            ->orderBy('p.name')
            ->get(['p.id', 'p.name', 'p.code', DB::raw("COALESCE(u.name, '') AS uom_name")]);

        $opening = $logs()
            ->where('pl.occurred_at', '<', $start)
            ->where('pl.condition', '!=', 'OFF')
            ->groupBy('pl.product_id')
            ->selectRaw('pl.product_id, SUM(pl.delta) AS opening')
            ->pluck('opening', 'product_id');

        $rows = [];
        foreach ($products as $p) {
            $rows[$p->id] = [
                'name' => $p->name, 'code' => $p->code, 'unit' => $p->uom_name,
                'opening' => (int) ($opening[$p->id] ?? 0),
                'in_daily' => [], 'out_daily' => [], 'in_total' => 0, 'out_total' => 0, 'closing' => 0,
            ];
        }
        foreach ($inRows as $r) {
            if (isset($rows[$r->product_id])) {
                $rows[$r->product_id]['in_daily'][(int) $r->d] = (int) $r->qty;
            }
        }
        foreach ($outRows as $r) {
            if (isset($rows[$r->product_id])) {
                $rows[$r->product_id]['out_daily'][(int) $r->d] = (int) $r->qty;
            }
        }
        foreach ($rows as &$r) {
            $r['in_total'] = array_sum($r['in_daily']);
            $r['out_total'] = array_sum($r['out_daily']);
            $r['closing'] = $r['opening'] + $r['in_total'] - $r['out_total'];
        }

        return ['period' => $start->translatedFormat('F Y'), 'days' => $days, 'rows' => array_values($rows)];
    }
}
