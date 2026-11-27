<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductsMonthlyReport
{
    /**
     * Ambil data rekap bulanan CONSUMABLE per-Barang, per-hari (hanya hari yang ada gerak).
     * @param int  $majorId
     * @param int  $year   e.g. 2025
     * @param int  $month  1..12
     * @param bool $withCombinedDays kalau true kembalikan 'days' gabungan (in ∪ out)
     */
    public function build(
        int $majorId,
        int $year,
        int $month,
        ?string $subTypeCode = null,   // <— gunakan code sub type
        bool $withCombinedDays = true
    ): array {
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end   = (clone $start)->endOfMonth()->endOfDay();

        // 1) hari aktif RESTOCK & BORROW_OUT
        $daysIn = DB::table('product_logs as pl')
            ->join('products as p', 'p.id', '=', 'pl.product_id')
            ->leftJoin('sub_type_products as st', 'st.id', '=', 'p.sub_type_id')
            ->where('p.type', 'CONSUMABLE')
            ->where('p.major_id', $majorId)
            ->when($subTypeCode, fn($q, $c) => $q->where('st.code', $c))
            ->where('pl.move_type', 'RESTOCK')
            ->whereBetween('pl.occurred_at', [$start, $end])
            ->selectRaw('DAY(pl.occurred_at) as d')
            ->groupBy('d')->orderBy('d')
            ->pluck('d')->map(fn($d) => (int)$d)->all();

        $daysOut = DB::table('product_logs as pl')
            ->join('products as p', 'p.id', '=', 'pl.product_id')
            ->leftJoin('sub_type_products as st', 'st.id', '=', 'p.sub_type_id')
            ->where('p.type', 'CONSUMABLE')
            ->where('p.major_id', $majorId)
            ->when($subTypeCode, fn($q, $c) => $q->where('st.code', $c))
            ->where('pl.move_type', 'BORROW_OUT')
            ->whereBetween('pl.occurred_at', [$start, $end])
            ->selectRaw('DAY(pl.occurred_at) as d')
            ->groupBy('d')->orderBy('d')
            ->pluck('d')->map(fn($d) => (int)$d)->all();

        $days = $withCombinedDays
            ? collect($daysIn)->merge($daysOut)->unique()->sort()->values()->all()
            : [];

        // 2) daftar barang
        $products = DB::table('products as p')
            ->leftJoin('unit_products as u', 'u.id', '=', 'p.unit_id')
            ->leftJoin('sub_type_products as st', 'st.id', '=', 'p.sub_type_id')
            ->where('p.type', 'CONSUMABLE')
            ->where('p.major_id', $majorId)
            ->when($subTypeCode, fn($q, $c) => $q->where('st.code', $c))
            ->orderBy('p.name')
            ->get(['p.id', 'p.name', 'p.code', DB::raw('COALESCE(u.name,"") as unit_name')]);

        // 3) opening (sebelum bulan) — HARUS join products utk filter major+type
        $opening = DB::table('product_logs as pl')
            ->join('products as p', 'p.id', '=', 'pl.product_id')
            ->leftJoin('sub_type_products as st', 'st.id', '=', 'p.sub_type_id')
            ->where('p.type', 'CONSUMABLE')
            ->where('p.major_id', $majorId)
            ->when($subTypeCode, fn($q, $c) => $q->where('st.code', $c))
            ->where('pl.occurred_at', '<', $start)
            ->where(function ($q) {
                $q->whereNull('pl.condition')->orWhere('pl.condition', '<>', 'OFF');
            })
            ->groupBy('pl.product_id')
            ->selectRaw('pl.product_id, COALESCE(SUM(pl.delta),0) AS opening')
            ->pluck('opening', 'product_id');

        // 4) matriks harian
        $inRows = DB::table('product_logs as pl')
            ->join('products as p', 'p.id', '=', 'pl.product_id')
            ->leftJoin('sub_type_products as st', 'st.id', '=', 'p.sub_type_id')
            ->where('p.type', 'CONSUMABLE')
            ->where('p.major_id', $majorId)
            ->when($subTypeCode, fn($q, $c) => $q->where('st.code', $c))
            ->where('pl.move_type', 'RESTOCK')
            ->whereBetween('pl.occurred_at', [$start, $end])
            ->groupBy('pl.product_id', 'd')
            ->selectRaw('pl.product_id, DAY(pl.occurred_at) as d, SUM(pl.delta) as qty')
            ->get();

        $outRows = DB::table('product_logs as pl')
            ->join('products as p', 'p.id', '=', 'pl.product_id')
            ->leftJoin('sub_type_products as st', 'st.id', '=', 'p.sub_type_id')
            ->where('p.type', 'CONSUMABLE')
            ->where('p.major_id', $majorId)
            ->when($subTypeCode, fn($q, $c) => $q->where('st.code', $c))
            ->where('pl.move_type', 'BORROW_OUT')
            ->whereBetween('pl.occurred_at', [$start, $end])
            ->groupBy('pl.product_id', 'd')
            ->selectRaw('pl.product_id, DAY(pl.occurred_at) as d, SUM(pl.quantity) as qty')
            ->get();

        // 5) bentuk rows
        $rows = [];
        foreach ($products as $p) {
            $rows[$p->id] = [
                'product_id' => (int)$p->id,
                'name' => $p->name,
                'code' => $p->code,
                'unit' => $p->unit_name,
                'opening' => (int)($opening[$p->id] ?? 0),
                'in_daily' => [],
                'out_daily' => [],
                'in_total' => 0,
                'out_total' => 0,
                'closing' => 0,
            ];
        }
        foreach ($inRows as $r) {
            if (isset($rows[$r->product_id])) $rows[$r->product_id]['in_daily'][(int)$r->d] = (int)$r->qty;
        }
        foreach ($outRows as $r) {
            if (isset($rows[$r->product_id])) $rows[$r->product_id]['out_daily'][(int)$r->d] = (int)$r->qty;
        }

        foreach ($rows as &$r) {
            $r['in_total']  = array_sum($r['in_daily']);
            $r['out_total'] = array_sum($r['out_daily']);
            $r['closing']   = $r['opening'] + $r['in_total'] - $r['out_total'];
        }

        return [
            'period'   => $start->translatedFormat('F Y'),
            'days_in'  => $daysIn,
            'days_out' => $daysOut,
            'days'     => $days,
            'rows'     => array_values($rows),
        ];
    }
}
