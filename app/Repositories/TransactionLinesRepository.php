<?php

namespace App\Repositories;

use App\Repositories\Interfaces\TransactionLinesRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TransactionLinesRepository implements TransactionLinesRepositoryInterface
{
    public function bulkInsert(int $transactionId, array $lines): void
    {
        if (empty($lines)) return;
        $rows = [];
        $i = 1;
        foreach ($lines as $l) {
            $rows[] = [
                'transaction_id' => $transactionId,
                'product_id'     => $l['product_id'],
                'warehouse_id'   => $l['warehouse_id'],
                'line_no'        => $l['line_no'] ?? $i++,
                'quantity'       => $l['quantity'],
                'condition_from' => $l['condition_from'] ?? 'GOOD',
                'condition_to'   => $l['condition_to']   ?? null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }
        DB::table('transaction_lines')->insert($rows);
    }

    public function getNextLineNo(int $transactionId): int
    {
        $max = DB::table('transaction_lines')
            ->where('transaction_id', $transactionId)
            ->max('line_no');

        return (int)$max + 1;
    }

    public function getByTransactionId(int $transactionId)
    {
        return DB::table('transaction_lines as tl')
            ->join('products as p', 'p.id', '=', 'tl.product_id')
            ->leftJoin('warehouse as w', 'w.id', '=', 'tl.warehouse_id')
            ->leftJoin('unit_products as up', 'up.id', '=', 'p.unit_id')
            ->where('tl.transaction_id', $transactionId)
            ->orderBy('tl.line_no')
            ->selectRaw("
                tl.id, tl.transaction_id, tl.line_no,
                tl.product_id, p.code as product_code, p.name as product_name,
                tl.warehouse_id, w.name as warehouse_name,
                tl.quantity,
                tl.condition_from, tl.condition_to,
                up.name as unit_name
            ")
            ->get();
    }

    public function getByTransaction(int $txId)
    {
        return DB::table('transaction_lines')
            ->where('transaction_id', $txId)
            ->select('id', 'product_id', 'warehouse_id', 'quantity', 'line_no')
            ->orderBy('line_no')->get();
    }
    public function deleteById(int $id)
    {
        DB::table('transaction_lines')->where('id', $id)->delete();
    }
    public function insert(int $txId, int $productId, int $warehouseId, int $qty, int $lineNo): int
    {
        return DB::table('transaction_lines')->insertGetId([
            'transaction_id' => $txId,
            'product_id'     => $productId,
            'warehouse_id'   => $warehouseId,
            'quantity'       => $qty,
            'line_no'        => $lineNo,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }
    public function updateById(int $id, int $productId, int $warehouseId, int $qty, int $lineNo)
    {
        DB::table('transaction_lines')->where('id', $id)->update([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'quantity' => $qty,
            'line_no' => $lineNo,
            'updated_at' => now()
        ]);
    }
    public function updateLineNo(int $id, int $lineNo)
    {
        DB::table('transaction_lines')->where('id', $id)->update(['line_no' => $lineNo, 'updated_at' => now()]);
    }

    public function updateQuantityAndLineNo(int $id, int $qty, int $lineNo)
    {
        DB::table('transaction_lines')->where('id', $id)->update(['quantity' => $qty, 'line_no' => $lineNo, 'updated_at' => now()]);
    }

    public function getBorrowedDurableProductLines(
        ?int $major_id = null,
        ?string $borrower_name = null,
        ?string $product_code = null,
        ?int $warehouse_id = null,
        ?string $date = null
    ) {
        // Subquery BORROW_OUT agregat + last created_at
        $borrowSub = DB::table('product_logs as pl')
            ->join('transaction as t', function ($j) {
                $j->on('t.id', '=', 'pl.ref_id')
                    ->where('pl.ref_type', 'transaction');
            })
            ->join('products as p', 'p.id', '=', 'pl.product_id')
            ->where('pl.move_type', 'BORROW_OUT')
            ->where('p.type', 'DURABLE')
            ->when($major_id,     fn($q, $v) => $q->where('p.major_id', $v))
            ->when($warehouse_id, fn($q, $v) => $q->where('pl.warehouse_id', $v))
            ->when($date,         fn($q, $v) => $q->whereDate('t.created_at', $v))
            ->groupBy('t.borrower_id', 'pl.product_id', 'pl.warehouse_id', 't.created_at')
            ->selectRaw('
            t.borrower_id,
            pl.product_id,
            pl.warehouse_id,
            SUM(pl.quantity) AS borrowed_qty,
            t.created_at     AS borrowed_at
        ');

        // Subquery RETURN agregat
        $returnSub = DB::table('product_logs as pl')
            ->join('transaction as t', function ($j) {
                $j->on('t.id', '=', 'pl.ref_id')
                    ->where('pl.ref_type', 'transaction');
            })
            ->join('products as p', 'p.id', '=', 'pl.product_id')
            ->where('pl.move_type', 'RETURN')
            ->where('p.type', 'DURABLE')
            ->groupBy('t.borrower_id', 'pl.product_id', 'pl.warehouse_id')
            ->selectRaw('
            t.borrower_id,
            pl.product_id,
            pl.warehouse_id,
            SUM(pl.quantity) AS returned_qty
        ');

        return DB::query()->fromSub($borrowSub, 'b')
            ->leftJoinSub($returnSub, 'r', function ($j) {
                $j->on('r.borrower_id',  '=', 'b.borrower_id')
                    ->on('r.product_id',  '=', 'b.product_id')
                    ->on('r.warehouse_id', '=', 'b.warehouse_id');
            })
            ->join('products as p',          'p.id',  '=', 'b.product_id')
            ->join('unit_products as up',    'up.id', '=', 'p.unit_id')
            ->join('sub_type_products as sp', 'sp.id', '=', 'p.sub_type_id')
            ->join('warehouse as w',         'w.id',  '=', 'b.warehouse_id')
            ->join('borrower  as br',        'br.id', '=', 'b.borrower_id')
            ->whereRaw('(b.borrowed_qty - COALESCE(r.returned_qty,0)) > 0')
            ->when($borrower_name, fn($q, $v) => $q->where('br.fullname', 'like', "%$v%"))
            ->when($product_code,  fn($q, $v) => $q->where('p.code',      'like', "%$v%"))
            ->orderByDesc('b.borrowed_at')
            ->orderBy('p.name')
            ->paginate(15, [
                'b.borrower_id',
                'br.fullname  as borrower_name',
                'p.name       as product_name',
                'p.code       as product_code',
                'w.name       as warehouse_name',
                DB::raw('(b.borrowed_qty - COALESCE(r.returned_qty,0)) as outstanding_qty'),
                'up.name      as unit_name',
                'sp.name      as sub_type_name',
                'b.borrowed_at as borrowed_at',
            ]);
    }

    public function getBorrowedConsumableProductLines(
        ?int $major_id = null,
        ?string $borrower_name = null,
        ?string $product_code = null,
        ?int $warehouse_id = null,
        ?string $date = null
    ) {
        return DB::table('product_logs as pl')
            ->join('transaction as t', function ($j) {
                $j->on('t.id', '=', 'pl.ref_id')
                    ->where('pl.ref_type', 'transaction');
            })
            ->join('products as p', 'p.id', '=', 'pl.product_id')
            ->join('warehouse as w', 'w.id', '=', 'pl.warehouse_id')
            ->join('borrower as br', 'br.id', '=', 't.borrower_id')
            ->join('unit_products as up', 'up.id', '=', 'p.unit_id')
            ->join('sub_type_products as sp', 'sp.id', '=', 'p.sub_type_id')
            ->where('p.type', 'CONSUMABLE')
            ->where('pl.move_type', 'BORROW_OUT')
            ->when($major_id,      fn($q, $v) => $q->where('p.major_id', $v))
            ->when($borrower_name, fn($q, $v) => $q->where('br.fullname', 'like', "%$v%"))
            ->when($product_code,  fn($q, $v) => $q->where('p.code', 'like', "%$v%"))
            ->when($warehouse_id,  fn($q, $v) => $q->where('pl.warehouse_id', $v))
            ->when($date,          fn($q, $v) => $q->whereDate('t.created_at', $v))
            ->orderByDesc('t.created_at')
            ->orderBy('p.name')
            ->paginate(15, [
                't.borrower_id',
                'br.fullname as borrower_name',
                'p.name      as product_name',
                'p.code      as product_code',
                'w.name      as warehouse_name',
                'up.name     as unit_name',
                'sp.name     as sub_type_name',
                'pl.quantity as outstanding_qty',
                't.created_at',
            ]);
    }
}
