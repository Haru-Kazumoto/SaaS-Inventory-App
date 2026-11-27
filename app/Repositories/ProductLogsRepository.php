<?php

namespace App\Repositories;

use App\Dto\ProductLogs\CreateProductLogsDto;
use App\Models\ProductLogs;
use App\Models\Products;
use App\Repositories\Interfaces\ProductLogsRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Override;

class ProductLogsRepository implements ProductLogsRepositoryInterface
{
    public function store(CreateProductLogsDto $data)
    {
        return ProductLogs::create($data->toArray());
    }

    public function bulkInsert(array $rows)
    {
        if (!empty($rows)) {
            DB::table('product_logs')->insert($rows);
        }
    }

    public function getProductsWarehouse(int $product_id)
    {
        return DB::table('product_logs as pl')
            ->join('warehouse as w', 'w.id', '=', 'pl.warehouse_id')
            ->where('pl.product_id', $product_id)
            ->where(function ($q) {
                $q->whereNull('pl.condition')
                    ->orWhere('pl.condition', '<>', 'OFF');
            })
            ->groupBy('pl.warehouse_id', 'pl.condition', 'w.name', 'w.id')
            ->orderBy('w.name')
            ->selectRaw('
                pl.warehouse_id,
                pl.condition,
                w.name AS warehouse_name,
                w.id as warehouse_id,
                COALESCE(SUM(pl.delta), 0) AS on_hand
            ')
            ->get();
    }

    public function getOnHandByBucket(int $product_id): array
    {
        // on_hand = SUM(delta), exclude OFF
        $rows = DB::table('product_logs as pl')
            ->where('pl.product_id', $product_id)
            ->where(function ($q) {
                $q->whereNull('pl.condition')
                    ->orWhere('pl.condition', '<>', 'OFF');
            })
            ->groupBy('pl.warehouse_id', 'pl.condition')
            ->selectRaw('pl.warehouse_id, pl.`condition`, COALESCE(SUM(pl.delta),0) as on_hand')
            ->get();

        // kunci "whId|COND"
        $map = [];
        foreach ($rows as $r) {
            $key = $r->warehouse_id . '|' . ($r->condition ?? 'GOOD');
            $map[$key] = (int)$r->on_hand;
        }
        return $map;
    }

    public function onHand(int $productId, int $warehouseId, ?string $condition = 'GOOD')
    {
        return (int) DB::table('product_logs')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where(function ($q) {
                $q->whereNull('condition')->orWhere('condition', '<>', 'OFF');
            })
            ->when($condition, fn($q, $c) => $q->where('condition', $c))
            ->sum('delta');
    }

    public function getOffByMajor(
        int $major_id,
        ?string $sub_type,
        ?string $product_name,
        ?int $page = null
    ) {
        $query = DB::table('product_logs as pl')
            ->join('products as p', 'p.id', '=', 'pl.product_id')
            ->join('warehouse as w', 'w.id', '=', 'pl.warehouse_id')
            ->join('sub_type_products as st', 'st.id', '=', 'p.sub_type_id')
            ->join('unit_products as up', 'up.id', '=', 'p.unit_id')
            ->where('p.major_id', $major_id)
            ->where('pl.condition', 'OFF')
            ->when($sub_type, fn($q, $st) => $q->where('st.code', $st))
            ->when($product_name, fn($q, $pn) => $q->where('p.name', 'LIKE', "%{$pn}%"))
            ->groupBy(
                'pl.product_id',
                'p.name',
                'p.code',
                'p.brand_name',
                'pl.warehouse_id',
                'w.name',
                'pl.created_at',
                'st.name',
                'up.name'
            )
            ->orderBy('p.name')
            ->selectRaw('
                pl.product_id,
                p.name,
                p.code,
                p.brand_name,
                pl.warehouse_id,
                pl.created_at AS off_date,
                st.name AS sub_type_name,
                up.name AS unit_name,
                w.name AS warehouse_name,
                COALESCE(SUM(pl.delta), 0) AS off_qty
            ')
            ->latest('pl.created_at');

        if (!empty($page)) {
            return $query->paginate($page);
        }

        return $query->get();
    }


    // outstanding pinjaman per borrower & Barang (opsional per warehouse)
    public function borrowedOutstanding(int $borrowerId, int $productId, ?int $warehouseId = null): int
    {
        $out = DB::table('product_logs')
            ->where('move_type', 'BORROW_OUT')
            ->where('product_id', $productId)
            // ->where('actor_borrower_id', $borrowerId)
            ->when($warehouseId, fn($q, $w) => $q->where('warehouse_id', $w))
            ->sum('quantity');

        $ret = DB::table('product_logs')
            ->where('move_type', 'RETURN')
            ->where('product_id', $productId)
            // ->where('actor_borrower_id', $borrowerId)
            ->when($warehouseId, fn($q, $w) => $q->where('warehouse_id', $w)) // ini warehouse tujuan return; boleh dihilangkan jika tak relevan
            ->sum('quantity');

        return (int) ($out - $ret);
    }

    public function getLedger(int $transaction_id)
    {
        // Semua catatan ledger (double-entry) yang berelasi ke transaksi ini
        return DB::table('product_logs as pl')
            ->leftJoin('products as p', 'p.id', '=', 'pl.product_id')
            ->leftJoin('warehouse as w', 'w.id', '=', 'pl.warehouse_id')
            // ->leftJoin('users as u', 'u.id', '=', 'pl.actor_user_id')
            ->where('pl.ref_type', 'transaction')
            ->where('pl.ref_id', $transaction_id)
            ->orderBy('pl.id')
            ->selectRaw("
                pl.id, pl.product_id, p.code as product_code, p.name as product_name,
                pl.warehouse_id, w.name as warehouse_name,
                pl.condition, pl.move_type,
                pl.quantity, pl.delta,
                pl.line_no,
                pl.occurred_at,
                pl.created_at
            ")
            ->get();
    }

    public function writeOff(
        int $product_id,
        int $warehouse_id,
        int $quantity,
        string $fromCondition = 'GOOD'
    ): void {
        if ($quantity <= 0) {
            throw ValidationException::withMessages(['quantity' => 'quantity harus > 0']);
        }

        DB::transaction(function () use ($product_id, $warehouse_id, $quantity, $fromCondition) {
            // cek saldo per kondisi asal di gudang tsb
            $saldoFrom = DB::table('product_logs')
                ->where('product_id', $product_id)
                ->where('warehouse_id', $warehouse_id)
                ->where('condition', $fromCondition)   // penting: per kondisi asal
                ->sum('delta');

            if ($saldoFrom < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Quantity di gudang tidak mencukupi untuk mengeluarkan barang."
                ]);
            }

            $when = now();

            // A) OUT dari kondisi asal → ini yang mengurangi tampilanmu
            DB::table('product_logs')->insert([
                'product_id'   => $product_id,
                'warehouse_id' => $warehouse_id,
                'condition'    => $fromCondition,     // GOOD / DAMAGED
                'move_type'    => 'WRITE_OFF',
                'quantity'     => $quantity,
                'delta'        => -$quantity,
                'ref_type'     => 'manual',
                'ref_id'       => null,
                'line_no'      => 1,
                'occurred_at'  => $when,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // B) IN ke OFF → audit saja (nanti di-exclude dari tampilan)
            DB::table('product_logs')->insert([
                'product_id'   => $product_id,
                'warehouse_id' => $warehouse_id,
                'condition'    => 'OFF',
                'move_type'    => 'WRITE_OFF',
                'quantity'     => $quantity,
                'delta'        => +$quantity,
                'ref_type'     => 'manual',
                'ref_id'       => null,
                'line_no'      => 2,
                'occurred_at'  => $when,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        });
    }

    public function restock(
        int $product_id,
        int $warehouse_id,
        int $quantity,
        string $condition = 'GOOD',
    ) {
        // 1) Validasi dasar
        if ($quantity <= 0) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'quantity' => 'Quantity harus > 0'
            ]);
        }
        if (!in_array($condition, ['GOOD', 'DAMAGED'], true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'condition' => 'Condition harus GOOD atau DAMAGED'
            ]);
        }

        // 2) Tulis ledger (single entry, delta positif)
        DB::table('product_logs')->insert([
            'product_id'    => $product_id,
            'warehouse_id'  => $warehouse_id,
            'condition'     => $condition,
            'move_type'     => 'RESTOCK',
            'quantity'      => $quantity,
            'delta'         => +$quantity,              // tambah stok
            'ref_type'      => 'manual',                // ganti sesuai sumber (po, invoice, dsb.)
            'ref_id'        => null,
            'line_no'       => 1,
            'occurred_at'   => $occurred_at ?? now(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    public function usableOnHand(int $productId, int $warehouseId): int
    {
        return (int) DB::table('product_logs')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where(function ($q) {
                $q->whereNull('condition')->orWhere('condition', '<>', 'OFF');
            })
            ->sum('delta');
    }

    //BUG SO HARD, CAN'T EVEN SHOWING THE RESULT. BRUH
    public function getStatisticsPerYear(string $year, int $major_id)
    {
        $raw = DB::table('product_logs as pl')
            ->join('products as p', 'p.id', '=', 'pl.product_id')
            ->where('p.type', 'CONSUMABLE')
            ->where('p.major_id', $major_id)
            ->whereYear('pl.created_at', $year)
            ->selectRaw("
                MONTH(pl.created_at) AS month_num,
                COALESCE(SUM(CASE WHEN pl.move_type IN ('RESTOCK')    THEN pl.delta  ELSE 0 END), 0) AS restock_total,
                COALESCE(SUM(CASE WHEN pl.move_type IN ('BORROW_OUT') THEN -pl.delta ELSE 0 END), 0) AS out_total
            ")
            ->groupByRaw('MONTH(pl.created_at)')
            ->orderBy('month_num')
            ->get()
            ->keyBy('month_num');

        // dd($raw->toArray()); // boleh cek dulu hasil mentahnya di sini

        $months = collect(range(1, 12))->map(function (int $m) use ($raw, $year) {
            // cari row yang month_num = $m
            $row = $raw->firstWhere('month_num', $m);
            return [
                'month_num'     => $m,
                'month_label'   => Carbon::createFromDate($year, $m, 1)->format('M'),
                'restock_total' => $row->restock_total ?? 0,
                'out_total'     => $row->out_total ?? 0,
            ];
        });

        return $months;
    }

    public function countBorrowed(int $major_id)
    {
        // Total yang masih dipinjam = BORROW_OUT qty - RETURN qty
        $outstanding = DB::table('product_logs as pl')
            ->join('products as p', 'p.id', '=', 'pl.product_id')
            ->where('p.major_id', $major_id)
            ->where('p.type', 'DURABLE')
            ->whereIn('pl.move_type', ['BORROW_OUT', 'RETURN'])
            ->sum(DB::raw("
                CASE
                    WHEN pl.move_type = 'BORROW_OUT' THEN pl.quantity
                    WHEN pl.move_type = 'RETURN'     THEN -pl.quantity
                    ELSE 0
                END
            "));

        // jaga-jaga biar nggak negatif karena data anomali
        return max(0, (int) $outstanding);
    }

    public function getRestockProducts(?int $majorId, ?string $product_name, ?string $type, ?string $occured_at)
    {
        $logs = DB::table('product_logs as pl')
            ->select(
                'pl.id as log_id',
                'pl.move_type',
                'pl.quantity',
                'p.name as product_name',
                'p.code',
                'p.type',
                'p.major_id',
                'up.name as unit_name',
                'm.name as major_name',
                'w.name as warehouse_name',
                'pl.occurred_at',
                DB::raw("
                    CASE
                        WHEN p.type = 'CONSUMABLE' THEN 'HABIS PAKAI'
                        WHEN p.type = 'DURABLE' THEN 'TIDAK HABIS PAKAI'
                        ELSE p.type
                    END as type_label
                ")
            )
            ->when($majorId, function ($query, $value) {
                $query->where('m.id', '=', $value);
            })
            ->when($type, function ($query, $value) {
                $query->where('p.type', '=', $value);
            })
            ->when($occured_at, function ($query, $value) {
                $query->whereDate('pl.occurred_at', '=', Carbon::parse($value)->toDateString());
            })
            ->when($product_name, function ($query, $value) {
                $query->where('p.name', 'like', "%{$value}%");
            })
            ->join('products as p', 'p.id', '=', 'pl.product_id')
            ->join('warehouse as w', 'w.id', '=', 'pl.warehouse_id')
            ->join('major as m', 'm.id', '=', 'p.major_id')
            ->join('unit_products as up', 'up.id', '=', 'p.unit_id')
            ->where('pl.move_type', 'RESTOCK')
            ->orderByDesc('pl.occurred_at')
            ->paginate();

        return $logs;
    }
}
