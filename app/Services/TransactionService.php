<?php

namespace App\Services;

use App\Dto\Transaction\CreateOpeningPayload;
use App\Dto\Transaction\CreateTransactionDto;
use App\Dto\Transaction\FilterIndexDto;
use App\Dto\TransactionLines\CreateTransactionLineDto;
use App\Repositories\Interfaces\ProductLogsRepositoryInterface;
use App\Repositories\Interfaces\TransactionLinesRepositoryInterface;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public function __construct(
        protected TransactionRepositoryInterface $transactionRepository,
        protected TransactionLinesRepositoryInterface $transactionLinesRepository,
        protected ProductLogsRepositoryInterface $productLogsRepository
    ) {}

    public function getAll(int $major_id, FilterIndexDto $filter)
    {
        return $this->transactionRepository->getAll($major_id, $filter);
    }

    public function getAllToday(int $major_id)
    {
        return $this->transactionRepository->getAllToday($major_id);
    }

    /**
     * Ambil detail lengkap 1 transaksi:
     * - header
     * - lines (Barang, gudang, kondisi, qty)
     * - ledger (product_logs) yang tercatat untuk transaksi tsb
     * - ringkasan total_qty (dari lines) & total_delta (dari ledger)
     */
    public function getDetail(int $txId): array
    {
        $header = $this->transactionRepository->getById($txId);
        if (!$header) {
            throw new ModelNotFoundException("Transaksi #{$txId} tidak ditemukan");
        }

        if ($header->tx_type === 'BORROW') {
            $header->status = $this->transactionRepository->getComputedStatus($txId) ?? $header->status;
        }

        $lines  = $this->transactionLinesRepository->getByTransactionId($txId);
        $ledger = $this->productLogsRepository->getLedger($txId);

        // Ringkasan
        $totalQty   = (int) ($lines->sum('quantity') ?? 0);
        $totalDelta = (int) ($ledger->sum('delta') ?? 0);

        return [
            'header'      => $header,
            'lines'       => $lines,
            'ledger'      => $ledger,
            'summary'     => [
                'total_lines' => $lines->count(),
                'total_qty'   => $totalQty,
                'total_delta' => $totalDelta,
            ],
        ];
    }

    /**
     * BORROW_OUT: kurangi stok dari warehouse/condition (satu entry delta -qty)
     * $lines: array of [
     *   product_id:int, warehouse_id:int, quantity:int, condition?:'GOOD'|'DAMAGED'
     * ]
     */
    public function borrowOut(int $borrowerId, string $occurredAt, array $lines, ?string $description = null): int
    {
        if (empty($lines)) {
            throw ValidationException::withMessages(['lines' => 'Minimal 1 baris peminjaman']);
        }

        return DB::transaction(function () use ($borrowerId, $occurredAt, $lines, $description) {

            // 1) header transaksi
            $txId = $this->transactionRepository->store(new CreateTransactionDto(
                tx_type: 'BORROW',
                borrower_id: $borrowerId,
                occurred_at: $occurredAt,
                created_by: Auth::id(),
                description: $description,
                returned_at: null
            ));

            // 2) validasi & lines
            $norm = [];
            $i = 1;
            foreach ($lines as $l) {
                $pid = (int)($l['product_id'] ?? 0);
                $wid = (int)($l['warehouse_id'] ?? 0);
                $qty = (int)($l['quantity'] ?? 0);
                $cond = $l['condition'] ?? 'GOOD';
                if ($pid <= 0 || $wid <= 0 || $qty <= 0) {
                    throw ValidationException::withMessages(['lines' => 'product/warehouse/qty tidak valid']);
                }
                if (!in_array($cond, ['GOOD', 'DAMAGED'], true)) {
                    throw ValidationException::withMessages(['condition' => 'Hanya GOOD/DAMAGED']);
                }

                // Cek saldo usable di bucket sumber
                $onHand = $this->productLogsRepository->onHand($pid, $wid, $cond);
                if ($onHand < $qty) {
                    throw ValidationException::withMessages([
                        "lines.$i" => "Saldo gudang tidak cukup (on hand: $onHand)"
                    ]);
                }

                $norm[] = [
                    'product_id'     => $pid,
                    'warehouse_id'   => $wid,
                    'quantity'       => $qty,
                    'condition_from' => $cond,
                    'line_no'        => $i++,
                ];
            }

            $this->transactionLinesRepository->bulkInsert($txId, $norm);

            // 3) ledger (delta -qty, move_type BORROW_OUT)
            $rows = [];
            foreach ($norm as $ln) {
                $rows[] = [
                    'product_id'        => $ln['product_id'],
                    'warehouse_id'      => $ln['warehouse_id'],
                    'condition'         => $ln['condition_from'],
                    'move_type'         => 'BORROW_OUT',
                    'quantity'          => $ln['quantity'],
                    'delta'             => -$ln['quantity'],
                    'ref_type'          => 'transaction',
                    'ref_id'            => $txId,
                    'line_no'           => $ln['line_no'],
                    'occurred_at'       => $occurredAt,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                    // 'actor_user_id'     => Auth::id(),
                    // 'actor_borrower_id' => $borrowerId,
                ];
            }
            $this->productLogsRepository->bulkInsert($rows);

            return $txId;
        });
    }

    /**
     * Update transaksi BORROW beserta lines-nya dengan logika diff.
     * - Remove: ADJUSTMENT_POS (+oldQty), delete line
     * - Add   : BORROW_OUT (-newQty), insert line
     * - Change qty: +/- sesuai diff
     * - Change product/warehouse: treat as remove+add
     */
    public function updateBorrow(int $txId, string $occurredAt, ?string $description, array $lines): void
    {
        DB::transaction(function () use ($txId, $occurredAt, $description, $lines) {
            // 0) Pastikan header adalah BORROW
            $header = $this->transactionRepository->findById($txId);
            if (!$header || $header->tx_type !== 'BORROW') {
                throw ValidationException::withMessages(['transaction' => 'Transaksi tidak valid']);
            }

            // 1) Ambil lines lama
            $old = $this->transactionLinesRepository->getByTransaction($txId); // collection of {id, product_id, warehouse_id, quantity, line_no}

            // 2) Normalisasi lines baru (array) → map keyed
            // UI wajib kirim line_id untuk baris lama; baris baru line_id null
            $newLines = [];
            foreach ($lines as $i => $l) {
                $newLines[] = [
                    'line_id'      => $l['line_id'] ?? null,
                    'product_id'   => (int)$l['product_id'],
                    'warehouse_id' => (int)$l['warehouse_id'],
                    'quantity'     => (int)$l['quantity'],
                    'line_no'      => $i + 1, // re-sequence
                ];
            }

            // 3) Index lama & baru
            $oldById = [];
            foreach ($old as $o) {
                $oldById[$o->id] = $o;
            }

            $newById = [];
            foreach ($newLines as $n) {
                if ($n['line_id']) $newById[$n['line_id']] = $n;
            }

            $logsToInsert = [];
            $now = now();

            // 4) Hapus yang tidak ada lagi di new → kembalikan stok
            foreach ($old as $o) {
                if (!isset($newById[$o->id])) {
                    // ADJUSTMENT_POS: kembalikan
                    $logsToInsert[] = [
                        'product_id'   => $o->product_id,
                        'warehouse_id' => $o->warehouse_id,
                        'condition'    => 'GOOD',          // asumsi
                        'move_type'    => 'ADJUSTMENT_POS',
                        'quantity'     => (int)$o->quantity,
                        'delta'        => +(int)$o->quantity,
                        'ref_type'     => 'transaction',
                        'ref_id'       => $txId,
                        'line_no'      => 0,
                        'occurred_at'  => $occurredAt,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];
                    // delete line
                    $this->transactionLinesRepository->deleteById($o->id);
                }
            }

            // 5) Tambah / ubah
            foreach ($newLines as $n) {
                if ($n['line_id'] === null) {
                    // Tambah line baru → validasi stok usable
                    $needed = $n['quantity'];
                    $usable = $this->productLogsRepository->usableOnHand($n['product_id'], $n['warehouse_id']);
                    if ($usable < $needed) {
                        throw ValidationException::withMessages([
                            'lines' => "Stok tidak cukup (butuh {$needed}, tersedia {$usable}) untuk product {$n['product_id']} @wh {$n['warehouse_id']}"
                        ]);
                    }
                    // insert line
                    $newId = $this->transactionLinesRepository->insert($txId, $n['product_id'], $n['warehouse_id'], $n['quantity'], $n['line_no']);

                    // logs BORROW_OUT (-qty)
                    $logsToInsert[] = [
                        'product_id'   => $n['product_id'],
                        'warehouse_id' => $n['warehouse_id'],
                        'condition'    => 'GOOD',
                        'move_type'    => 'BORROW_OUT',
                        'quantity'     => $n['quantity'],
                        'delta'        => -$n['quantity'],
                        'ref_type'     => 'transaction',
                        'ref_id'       => $txId,
                        'line_no'      => $n['line_no'],
                        'occurred_at'  => $occurredAt,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];
                } else {
                    // Update existing
                    $o = $oldById[$n['line_id']] ?? null;
                    if (!$o) continue;

                    $changedPair = ($o->product_id != $n['product_id'] || $o->warehouse_id != $n['warehouse_id']);
                    if ($changedPair) {
                        // Treat as delete old + add new
                        // 5a. kembalikan yang lama
                        $logsToInsert[] = [
                            'product_id'   => $o->product_id,
                            'warehouse_id' => $o->warehouse_id,
                            'condition'    => 'GOOD',
                            'move_type'    => 'ADJUSTMENT_POS',
                            'quantity'     => (int)$o->quantity,
                            'delta'        => +(int)$o->quantity,
                            'ref_type'     => 'transaction',
                            'ref_id'       => $txId,
                            'line_no'      => 0,
                            'occurred_at'  => $occurredAt,
                            'created_at'   => $now,
                            'updated_at'   => $now,
                        ];
                        // 5b. cek stok baru
                        $needed = $n['quantity'];
                        $usable = $this->productLogsRepository->usableOnHand($n['product_id'], $n['warehouse_id']);
                        if ($usable < $needed) {
                            throw ValidationException::withMessages([
                                'lines' => "Stok tidak cukup untuk pindah (butuh {$needed}, tersedia {$usable})"
                            ]);
                        }
                        // update line ke pasangan baru & qty baru
                        $this->transactionLinesRepository->updateById($o->id, $n['product_id'], $n['warehouse_id'], $n['quantity'], $n['line_no']);
                        // 5c. pinjam di lokasi baru
                        $logsToInsert[] = [
                            'product_id'   => $n['product_id'],
                            'warehouse_id' => $n['warehouse_id'],
                            'condition'    => 'GOOD',
                            'move_type'    => 'BORROW_OUT',
                            'quantity'     => $n['quantity'],
                            'delta'        => -$n['quantity'],
                            'ref_type'     => 'transaction',
                            'ref_id'       => $txId,
                            'line_no'      => $n['line_no'],
                            'occurred_at'  => $occurredAt,
                            'created_at'   => $now,
                            'updated_at'   => $now,
                        ];
                    } else {
                        // Pair sama → cek diff qty
                        $diff = (int)$n['quantity'] - (int)$o->quantity;
                        if ($diff === 0) {
                            // hanya mungkin line_no berubah
                            if ((int)$o->line_no !== (int)$n['line_no']) {
                                $this->transactionLinesRepository->updateLineNo($o->id, $n['line_no']);
                            }
                            continue;
                        }
                        if ($diff > 0) {
                            // butuh tambahan → validasi
                            $usable = $this->productLogsRepository->usableOnHand($n['product_id'], $n['warehouse_id']);
                            if ($usable < $diff) {
                                throw ValidationException::withMessages([
                                    'lines' => "Stok tidak cukup menambah qty (butuh {$diff}, tersedia {$usable})"
                                ]);
                            }
                            // log pinjam tambahan
                            $logsToInsert[] = [
                                'product_id'   => $n['product_id'],
                                'warehouse_id' => $n['warehouse_id'],
                                'condition'    => 'GOOD',
                                'move_type'    => 'BORROW_OUT',
                                'quantity'     => $diff,
                                'delta'        => -$diff,
                                'ref_type'     => 'transaction',
                                'ref_id'       => $txId,
                                'line_no'      => $n['line_no'],
                                'occurred_at'  => $occurredAt,
                                'created_at'   => $now,
                                'updated_at'   => $now,
                            ];
                        } else {
                            // pengurangan → kembalikan selisih
                            $selisih = abs($diff);
                            $logsToInsert[] = [
                                'product_id'   => $n['product_id'],
                                'warehouse_id' => $n['warehouse_id'],
                                'condition'    => 'GOOD',
                                'move_type'    => 'ADJUSTMENT_POS',
                                'quantity'     => $selisih,
                                'delta'        => +$selisih,
                                'ref_type'     => 'transaction',
                                'ref_id'       => $txId,
                                'line_no'      => $n['line_no'],
                                'occurred_at'  => $occurredAt,
                                'created_at'   => $now,
                                'updated_at'   => $now,
                            ];
                        }
                        // update qty & line_no
                        $this->transactionLinesRepository->updateQuantityAndLineNo($o->id, $n['quantity'], $n['line_no']);
                    }
                }
            }

            // 6) Simpan logs
            if (!empty($logsToInsert)) {
                $this->productLogsRepository->bulkInsert($logsToInsert);
            }

            // 7) Update header (occurred_at / description)
            $this->transactionRepository->updateHeader($txId, [
                'occurred_at' => $occurredAt,
                'description' => $description,
                'updated_at'  => now(),
            ]);
        });
    }

    /**
     * RETURN: tambah stok ke warehouse/condition tujuan (satu entry delta +qty)
     * $lines: array of [
     *   product_id:int, to_warehouse_id:int, quantity:int, to_condition?:'GOOD'|'DAMAGED'
     * ]
     * Validasi: outstanding (BORROW_OUT - RETURN) borrower >= qty
     */
    public function returnBorrow(
        int $borrowTxId,
        int $borrowerId,
        string $occurredAt,
        array $lines,
        ?string $description = null
    ): int {
        if (empty($lines)) {
            throw ValidationException::withMessages(['lines' => 'Minimal 1 baris pengembalian']);
        }

        return DB::transaction(function () use ($borrowerId, $occurredAt, $lines, $description, $borrowTxId) {

            // --- 0) Prefetch tipe Barang agar tidak N+1
            $productIds = array_values(array_unique(array_map(fn($l) => (int)($l['product_id'] ?? 0), $lines)));
            if (empty($productIds)) {
                throw ValidationException::withMessages(['lines' => 'product_id tidak valid']);
            }
            /** @var array<int,string> $types (product_id => type) */
            $types = DB::table('products')->whereIn('id', $productIds)->pluck('type', 'id')->all();

            // --- 1) Filter: hanya DURABLE yang diproses; consumable di-skip
            $durableLines = array_values(array_filter($lines, function ($l) use ($types) {
                $pid = (int)($l['product_id'] ?? 0);
                return ($types[$pid] ?? null) === 'DURABLE';
            }));

            if (empty($durableLines)) {
                // Tidak ada barang durable untuk dikembalikan: anggap sukses “no-op”
                // (atau boleh dilempar validation error sesuai kebutuhan UX)
                return 0;
            }

            // --- 2) Buat header RETURN
            $txId = $this->transactionRepository->store(new CreateTransactionDto(
                tx_type: 'RETURN',
                borrower_id: $borrowerId,
                occurred_at: $occurredAt,
                created_by: Auth::id(),
                description: $description,
                returned_at: null, // opsional; biasanya field ini dipakai untuk status BORROW, bukan RETURN
                parent_borrow_tx_id: $borrowTxId // link ke transaksi BORROW yang direturn
            ));

            // --- 3) Normalisasi + validasi per line (hanya durable)
            $norm = [];
            $i = 1;
            foreach ($durableLines as $l) {
                $pid = (int)($l['product_id'] ?? 0);
                $toW = (int)($l['to_warehouse_id'] ?? 0);
                $qty = (int)($l['quantity'] ?? 0);
                $toC = $l['to_condition'] ?? 'GOOD';

                if ($pid <= 0 || $toW <= 0 || $qty <= 0) {
                    throw ValidationException::withMessages([
                        "lines.$i" => 'product/warehouse/qty tidak valid'
                    ]);
                }
                if (!in_array($toC, ['GOOD', 'DAMAGED'], true)) {
                    throw ValidationException::withMessages(["lines.$i.to_condition" => 'Hanya GOOD/DAMAGED']);
                }

                // Outstanding untuk borrower+product (tanpa parent link).
                // Jika ingin lebih ketat per gudang, buat metode repo yang terima warehouse_id juga.
                $outstanding = $this->productLogsRepository->borrowedOutstanding($borrowerId, $pid);
                if ($outstanding < $qty) {
                    throw ValidationException::withMessages([
                        "lines.$i.quantity" => "Qty kembali melebihi outstanding pinjaman (sisa: $outstanding)"
                    ]);
                }

                $norm[] = [
                    'product_id'    => $pid,
                    'warehouse_id'  => $toW,
                    'quantity'      => $qty,
                    'condition_to'  => $toC,
                    'line_no'       => $i++,
                ];
            }

            // --- 4) Simpan lines RETURN
            $this->transactionLinesRepository->bulkInsert(
                $txId,
                array_map(function ($x) {
                    return [
                        'product_id'     => $x['product_id'],
                        'warehouse_id'   => $x['warehouse_id'],
                        'quantity'       => $x['quantity'],
                        'condition_from' => null,
                        'condition_to'   => $x['condition_to'],
                        'line_no'        => $x['line_no'],
                    ];
                }, $norm)
            );

            // --- 5) Turunkan ledger product_logs (delta +qty) move_type=RETURN
            $rows = [];
            foreach ($norm as $ln) {
                $rows[] = [
                    'product_id'   => $ln['product_id'],
                    'warehouse_id' => $ln['warehouse_id'],
                    'condition'    => $ln['condition_to'],
                    'move_type'    => 'RETURN',
                    'quantity'     => $ln['quantity'],
                    'delta'        => +$ln['quantity'],
                    'ref_type'     => 'transaction',
                    'ref_id'       => $txId,
                    'line_no'      => $ln['line_no'],
                    'occurred_at'  => $occurredAt,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }
            $this->productLogsRepository->bulkInsert($rows);

            return $txId;
        });
    }


    /**
     * POSTING OPENING dari pembuatan product.
     * @param int $productId Barang yang baru dibuat
     * @param CreateOpeningPayload $payload
     * @return int $transactionId
     */
    public function postOpeningForProduct(int $productId, CreateOpeningPayload $payload): int
    {
        // Validasi
        foreach ($payload->lines as $l) {
            if ($l->quantity <= 0) {
                throw ValidationException::withMessages(['quantity' => ['Quantity opening harus > 0']]);
            }
            if ($l->warehouse_id <= 0) {
                throw ValidationException::withMessages(['warehouse_id' => ['Warehouse wajib diisi pada setiap baris.']]);
            }
        }

        // 1) Header transaksi OPENING (tanpa warehouse di header; warehouse ada per line/log)
        $trxId = $this->transactionRepository->store(new CreateTransactionDto(
            tx_type: 'OPENING',
            borrower_id: null,
            occurred_at: $payload->occurred_at,
            created_by: Auth::id(),
            description: 'Opening saat create product ID ' . $productId,
            returned_at: null
        ));

        // 2) transaction_lines (tanpa warehouse_id; line_no ikut DTO)
        //    condition_from/condition_to sesuai kebutuhan—untuk opening cukup condition_to
        $lines = [];
        foreach ($payload->lines as $l) {
            $lines[] = [
                'product_id' => $l->product_id,
                'quantity' => $l->quantity,
                'condition_from' => 'GOOD',
                'condition_to' => $l->condition ?? 'GOOD',
                'line_no' => $l->line_no,
                'warehouse_id' => null
            ];
        }

        $this->transactionLinesRepository->bulkInsert($trxId, $lines);

        // 3) product_logs (ledger) — pakai warehouse per line, delta = +qty, condition = status
        $rows = [];
        foreach ($payload->lines as $l) {
            $rows[] = [
                'product_id'   => $l->product_id,
                'warehouse_id' => $l->warehouse_id,
                'condition'    => $l->condition ?? 'GOOD',
                'move_type'    => 'OPENING',
                'quantity'     => $l->quantity,
                'delta'        => +$l->quantity,
                'ref_type'     => 'transaction',
                'ref_id'       => $trxId,
                'line_no'      => $l->line_no,
                'occurred_at'  => $payload->occurred_at,
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }
        $this->productLogsRepository->bulkInsert($rows);

        return $trxId;
    }
}
