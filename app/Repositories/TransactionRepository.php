<?php

namespace App\Repositories;

use App\Dto\Transaction\CreateTransactionDto;
use App\Dto\Transaction\FilterIndexDto;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function store(CreateTransactionDto $data): int
    {
        return DB::table('transaction')->insertGetId([
            'tx_number'             => $this->makeTxNo($data->tx_type),
            'tx_type'               => $data->tx_type,
            'borrower_id'           => $data->borrower_id,
            'status'                => 'POSTED',
            'occurred_at'           => $data->occurred_at,
            'created_by'            => $data->created_by,
            'description'           => $data->description,
            'returned_at'           => $data->returned_at,
            'parent_borrow_tx_id'   => $data->parent_borrow_tx_id,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);
    }

    public function getAll(int $major_id, FilterIndexDto $filter)
    {
        return $this->buildBorrowStatusQuery($major_id, $filter)->paginate(15);
    }

    /**
     * Status peminjaman hasil kalkulasi (POSTED / PARTIAL_RETURNED / COMPLETED)
     * untuk satu transaksi BORROW. Kolom transaction.status selalu POSTED.
     */
    public function getComputedStatus(int $txId): ?string
    {
        return $this->buildBorrowStatusQuery(null, new FilterIndexDto(null, null, null), $txId)
            ->value('computed_status');
    }

    private function buildBorrowStatusQuery(?int $major_id, FilterIndexDto $filter, ?int $txId = null)
    {
        // 1) Subquery: baris BORROW (HANYA DURABLE)
        $borrowLines = DB::table('transaction as t')
            ->join('transaction_lines as tl', 'tl.transaction_id', '=', 't.id')
            ->join('products as p', 'p.id', '=', 'tl.product_id')
            ->where('t.tx_type', 'BORROW')
            ->where('p.type', 'DURABLE') // <-- hanya durable
            ->when($filter->created_at, fn($query, $date) => $query->whereDay('t.created_at', $date))
            ->selectRaw("
                t.id           AS borrow_tx_id,
                t.borrower_id,
                t.occurred_at,
                tl.product_id,
                tl.warehouse_id,
                tl.line_no,
                tl.quantity    AS borrowed_qty
            ");

        // 2) Subquery: total RETURN per borrower+product+warehouse (HANYA DURABLE)
        $returnTotals = DB::table('product_logs as pl')
            ->join('transaction as t', function ($j) {
                $j->on('t.id', '=', 'pl.ref_id')
                    ->where('pl.ref_type', '=', 'transaction')
                    ->where('pl.move_type', '=', 'RETURN');
            })
            ->join('products as p2', 'p2.id', '=', 'pl.product_id')
            ->where('p2.type', 'DURABLE') // <-- hanya durable
            ->groupBy('t.borrower_id', 'pl.product_id', 'pl.warehouse_id')
            ->selectRaw("
                t.borrower_id,
                pl.product_id,
                pl.warehouse_id,
                SUM(pl.quantity) AS total_returned
            ");

        // 3) Window: borrowed_before
        $borrowWithBefore = DB::query()->fromSub($borrowLines, 'bl')
            ->selectRaw("
                bl.*,
                SUM(bl.borrowed_qty) OVER (
                PARTITION BY bl.borrower_id, bl.product_id, bl.warehouse_id
                ORDER BY bl.occurred_at, bl.borrow_tx_id, bl.line_no
                ROWS BETWEEN UNBOUNDED PRECEDING AND 1 PRECEDING
                ) AS borrowed_before
            ");

        // 4) FIFO allocation
        $fifoAlloc = DB::query()
            ->fromSub($borrowWithBefore, 'bwb')
            ->leftJoinSub($returnTotals, 'rt', function ($j) {
                $j->on('rt.borrower_id', '=', 'bwb.borrower_id')
                    ->on('rt.product_id',  '=', 'bwb.product_id')
                    ->on('rt.warehouse_id', '=', 'bwb.warehouse_id');
            })
            ->selectRaw("
                bwb.borrow_tx_id,
                bwb.borrower_id,
                bwb.occurred_at,
                bwb.product_id,
                bwb.warehouse_id,
                bwb.line_no,
                bwb.borrowed_qty,
                COALESCE(rt.total_returned, 0)   AS total_returned,
                COALESCE(bwb.borrowed_before, 0) AS borrowed_before,
                LEAST(
                bwb.borrowed_qty,
                GREATEST(COALESCE(rt.total_returned,0) - COALESCE(bwb.borrowed_before,0), 0)
                ) AS returned_allocated_to_line
            ");

        // 5) Agregasi per transaksi BORROW (durable only)
        $perBorrow = DB::query()->fromSub($fifoAlloc, 'fa')
            ->groupBy('fa.borrow_tx_id')
            ->selectRaw("
                fa.borrow_tx_id,
                SUM(fa.borrowed_qty)               AS borrowed_qty,
                SUM(fa.returned_allocated_to_line) AS returned_qty
            ");

        // 6) Base + flag has_durable
        $base = DB::table('transaction as tx')
            ->join('borrower as br', 'br.id', '=', 'tx.borrower_id')
            ->join('users as u', 'u.id', '=', 'tx.created_by')
            ->leftJoinSub($perBorrow, 'pb', 'pb.borrow_tx_id', '=', 'tx.id')
            ->where('tx.tx_type', 'BORROW')
            ->when($major_id !== null, fn($q) => $q->where('br.major_id', $major_id))
            ->when($txId !== null, fn($q) => $q->where('tx.id', $txId))
            ->when($filter?->borrower_name, fn($q, $name) => $q->where('br.fullname', 'LIKE', "%{$name}%"))
            ->when($filter?->creator_id,  fn($q, $id)   => $q->where('tx.created_by', $id))
            ->selectRaw("
                tx.id,
                br.fullname AS borrower_name,
                br.supervisor_name AS borrower_supervisor,
                br.phone AS borrower_phone,
                br.room_name AS borrower_room,
                tx.description,
                tx.created_at,
                u.fullname AS creator_name,
                (SELECT name FROM roles WHERE id = u.role_id LIMIT 1) AS creator_role,
                (SELECT COUNT(*) FROM transaction_lines tl WHERE tl.transaction_id = tx.id) AS products_count,
                COALESCE(pb.borrowed_qty, 0) AS borrowed_qty,   -- DURABLE only
                COALESCE(pb.returned_qty, 0) AS returned_qty,   -- DURABLE only

                -- Flag: ada durable di transaksi ini?
                EXISTS (
                SELECT 1
                FROM transaction_lines tl2
                JOIN products p3 ON p3.id = tl2.product_id
                WHERE tl2.transaction_id = tx.id
                    AND p3.type = 'DURABLE'
                LIMIT 1
                ) AS has_durable
            ");

        // 7) Bungkus → computed_status
        $wrapped = DB::query()->fromSub($base, 'q')
            ->selectRaw("
                q.*,
                (q.borrowed_qty - q.returned_qty) AS outstanding,
                CASE
                WHEN q.has_durable = 0
                    THEN 'COMPLETED'                              -- semua consumable
                WHEN q.borrowed_qty > 0 AND (q.borrowed_qty - q.returned_qty) = 0
                    THEN 'COMPLETED'                              -- durable tapi sudah lunas
                WHEN q.returned_qty > 0 AND (q.borrowed_qty - q.returned_qty) > 0
                    THEN 'PARTIAL_RETURNED'                       -- sebagian durable kembali
                ELSE 'POSTED'                                      -- belum ada return durable
                END AS computed_status
            ");

        $final = DB::query()
            ->fromSub($wrapped, 'wr')
            ->when($filter?->status, fn($query, $status) => $query->where('wr.computed_status', $status))
            ->orderBy('wr.created_at', 'desc');

        return $final;
    }

    public function getAllToday(int $major_id)
    {
        return DB::table('daily_borrowers')
            ->where('major_id', $major_id)
            ->get();
    }

    public function getById(int $id)
    {
        return DB::table('transaction as t')
            ->leftJoin('borrower as b', 'b.id', '=', 't.borrower_id')   // kalau ada tabel borrower
            ->leftJoin('users as u', 'u.id', '=', 't.created_by')
            ->where('t.id', $id)
            ->selectRaw("
                t.id, 
                t.tx_number, 
                t.tx_type, 
                t.status,
                t.borrower_id, 
                b.fullname as borrower_name,
                b.phone as borrower_phone,
                b.supervisor_name as borrower_supervisor,
                b.room_name as borrower_room,
                t.occurred_at, 
                t.description,
                t.created_by, 
                u.fullname as created_by_name,
                t.created_at, 
                t.updated_at
            ")
            ->first();
    }

    public function findById(int $id)
    {
        return DB::table('transaction')->where('id', $id)->first();
    }

    public function updateHeader(int $id, array $fields)
    {
        DB::table('transaction')->where('id', $id)->update($fields);
    }

    private function makeTxNo(string $type): string
    {
        return 'TRX-' . substr($type, 0, 4) . '-' . now()->format('Ymd') . '-' . str_pad((string)random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}
