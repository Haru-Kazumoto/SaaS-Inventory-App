<?php

namespace App\Domain\Borrowing\Actions;

use App\Domain\Borrowing\Enums\TxType;
use App\Domain\Borrowing\Models\Transaction;
use App\Domain\Borrowing\Models\TransactionLine;
use App\Domain\Borrowing\Queries\LoanStatusQuery;
use App\Domain\Inventory\Enums\MoveType;
use App\Domain\Inventory\Enums\ProductType;
use App\Domain\Inventory\Enums\StockCondition;
use App\Domain\Inventory\Ledger\StockLedger;
use App\Domain\Inventory\Ledger\StockMovement;
use App\Domain\MasterData\Models\Warehouse;
use App\Domain\Organization\Support\Access;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Catat pengembalian (bisa sebagian) untuk satu transaksi pinjam.
 * Tiap baris kembali menunjuk baris pinjam (borrow_line_id) → sisa pinjaman per baris pasti tepat.
 */
final class ReturnItems
{
    public function __construct(private StockLedger $ledger) {}

    /**
     * @param  array<int, array{borrow_line_id: int, quantity: int, condition?: string, warehouse_id?: ?int}>  $lines
     */
    public function handle(Transaction $borrow, array $lines, ?string $description = null): Transaction
    {
        Access::authorizeOperate($borrow->unit_id);

        if ($borrow->tx_type !== TxType::Borrow) {
            throw ValidationException::withMessages(['transaction' => 'Transaksi ini bukan peminjaman.']);
        }

        $lines = array_values(array_filter($lines, fn ($l) => (int) ($l['quantity'] ?? 0) > 0));
        if ($lines === []) {
            throw ValidationException::withMessages(['lines' => 'Isi jumlah pengembalian minimal pada satu barang.']);
        }

        return DB::transaction(function () use ($borrow, $lines, $description) {
            $tx = Transaction::create([
                'unit_id' => $borrow->unit_id,
                'tx_number' => TransactionNumber::next('KMB'),
                'tx_type' => TxType::Return,
                'status' => 'POSTED',
                'borrower_id' => $borrow->borrower_id,
                'parent_borrow_tx_id' => $borrow->id,
                'occurred_at' => now(),
                'description' => $description,
                'created_by' => auth()->id(),
            ]);

            foreach ($lines as $i => $line) {
                /** @var TransactionLine|null $borrowLine */
                $borrowLine = $borrow->lines()->with('product')->lockForUpdate()->find($line['borrow_line_id']);
                if (! $borrowLine) {
                    throw ValidationException::withMessages(["lines.{$i}" => 'Baris pinjaman tidak ditemukan.']);
                }
                if ($borrowLine->product->type !== ProductType::Durable) {
                    throw ValidationException::withMessages(["lines.{$i}" => "{$borrowLine->product->name} barang habis pakai, tidak perlu dikembalikan."]);
                }

                $outstanding = $borrowLine->quantity - (int) $borrowLine->returnLines()->sum('quantity');
                $qty = (int) $line['quantity'];
                if ($qty > $outstanding) {
                    throw ValidationException::withMessages([
                        "lines.{$i}.quantity" => "Jumlah kembali {$borrowLine->product->name} melebihi sisa pinjaman ({$outstanding}).",
                    ]);
                }

                $condition = StockCondition::tryFrom($line['condition'] ?? 'GOOD') ?? StockCondition::Good;
                if (! in_array($condition, StockCondition::stockable(), true)) {
                    throw ValidationException::withMessages(["lines.{$i}.condition" => 'Kondisi hanya Bagus atau Rusak.']);
                }

                $warehouseId = (int) ($line['warehouse_id'] ?? $borrowLine->warehouse_id);
                $warehouse = Warehouse::find($warehouseId);
                if (! $warehouse || ($warehouse->unit_id !== null && $warehouse->unit_id !== $borrow->unit_id)) {
                    throw ValidationException::withMessages(["lines.{$i}.warehouse_id" => 'Gudang tujuan tidak tersedia.']);
                }

                $tx->lines()->create([
                    'line_no' => $i + 1,
                    'product_id' => $borrowLine->product_id,
                    'warehouse_id' => $warehouseId,
                    'quantity' => $qty,
                    'condition_to' => $condition->value,
                    'borrow_line_id' => $borrowLine->id,
                ]);

                $this->ledger->move(new StockMovement(
                    $borrowLine->product_id, $warehouseId, MoveType::Return, $condition, $qty, true, 'transaction', $tx->id, $i + 1,
                ));
            }

            if (app(LoanStatusQuery::class)->outstandingFor($borrow->id) === 0) {
                $borrow->update(['returned_at' => now()]);
            }

            return $tx;
        });
    }
}
