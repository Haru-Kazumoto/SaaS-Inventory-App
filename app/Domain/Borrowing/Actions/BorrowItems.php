<?php

namespace App\Domain\Borrowing\Actions;

use App\Domain\Borrowing\Data\BorrowData;
use App\Domain\Borrowing\Enums\TxType;
use App\Domain\Borrowing\Models\Borrower;
use App\Domain\Borrowing\Models\Transaction;
use App\Domain\Inventory\Enums\MoveType;
use App\Domain\Inventory\Enums\StockCondition;
use App\Domain\Inventory\Ledger\StockLedger;
use App\Domain\Inventory\Ledger\StockMovement;
use App\Domain\Inventory\Models\Product;
use App\Domain\Organization\Support\Access;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Catat peminjaman / pemakaian barang. Stok kondisi Bagus di gudang asal berkurang (BORROW_OUT).
 * Barang habis pakai tidak perlu dikembalikan.
 */
final class BorrowItems
{
    public function __construct(private StockLedger $ledger) {}

    public function handle(BorrowData $data): Transaction
    {
        Access::authorizeOperate($data->unitId);

        if ($data->lines === []) {
            throw ValidationException::withMessages(['lines' => 'Minimal satu barang dipinjam.']);
        }

        return DB::transaction(function () use ($data) {
            $borrower = $this->resolveBorrower($data);

            $tx = Transaction::create([
                'unit_id' => $data->unitId,
                'tx_number' => TransactionNumber::next('PJM'),
                'tx_type' => TxType::Borrow,
                'status' => 'POSTED',
                'borrower_id' => $borrower->id,
                'occurred_at' => now(),
                'due_at' => $data->dueAt,
                'description' => $data->description,
                'created_by' => auth()->id(),
            ]);

            foreach (array_values($data->lines) as $i => $line) {
                $product = Product::find($line['product_id']);
                if (! $product || $product->unit_id !== $data->unitId) {
                    throw ValidationException::withMessages(["lines.{$i}.product_id" => 'Barang tidak tersedia di unit ini.']);
                }

                $tx->lines()->create([
                    'line_no' => $i + 1,
                    'product_id' => $product->id,
                    'warehouse_id' => $line['warehouse_id'],
                    'quantity' => $line['quantity'],
                    'condition_from' => StockCondition::Good->value,
                ]);

                $this->ledger->move(new StockMovement(
                    $product->id,
                    (int) $line['warehouse_id'],
                    MoveType::BorrowOut,
                    StockCondition::Good,
                    (int) $line['quantity'],
                    false,
                    'transaction',
                    $tx->id,
                    $i + 1,
                ), "lines.{$i}.quantity");
            }

            return $tx;
        });
    }

    /** Pakai kontak yang sama bila nomor identitas cocok; selain itu buat kontak baru. */
    private function resolveBorrower(BorrowData $data): Borrower
    {
        $attributes = [
            'unit_id' => $data->unitId,
            'fullname' => $data->borrowerName,
            'identity_number' => $data->identityNumber,
            'phone' => $data->phone,
            'responsible_name' => $data->responsibleName,
            'usage_location' => $data->usageLocation,
        ];

        if ($data->identityNumber) {
            $existing = Borrower::where('identity_number', $data->identityNumber)->first();
            if ($existing) {
                $existing->update($attributes);

                return $existing;
            }
        }

        return Borrower::create($attributes);
    }
}
