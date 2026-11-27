<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Peminjaman: peminjam, transaksi (BORROW / RETURN) & baris barangnya.
 * transaction.status disimpan POSTED; status pinjaman sebenarnya DIHITUNG (Dipinjam / Kembali sebagian / Selesai).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrowers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('fullname');
            $table->string('identity_number', 50)->nullable();   // NIS / NIP / ID karyawan / NIK
            $table->string('phone', 30)->nullable();
            $table->string('responsible_name')->nullable();      // penanggung jawab
            $table->string('usage_location')->nullable();        // lokasi pemakaian
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'fullname']);
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('tx_number', 40);
            $table->enum('tx_type', ['BORROW', 'RETURN']);
            $table->enum('status', ['DRAFT', 'POSTED'])->default('POSTED');
            $table->foreignId('borrower_id')->nullable()->constrained('borrowers')->nullOnDelete();
            $table->foreignId('parent_borrow_tx_id')->nullable()->constrained('transactions')->nullOnDelete(); // RETURN → BORROW asal
            $table->dateTime('occurred_at');
            $table->date('due_at')->nullable();                  // jatuh tempo pengembalian
            $table->dateTime('returned_at')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['organization_id', 'tx_number']);
            $table->index(['organization_id', 'tx_type', 'occurred_at']);
        });

        Schema::create('transaction_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->unsignedInteger('line_no');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->enum('condition_from', ['GOOD', 'DAMAGED', 'LOST'])->default('GOOD');
            $table->enum('condition_to', ['GOOD', 'DAMAGED', 'LOST'])->nullable();
            // baris RETURN menunjuk baris BORROW yang dikembalikan → sisa pinjaman = qty - SUM(return)
            $table->foreignId('borrow_line_id')->nullable()->constrained('transaction_lines')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['transaction_id', 'line_no']);
        });

        // Ringkasan peminjam hari ini per unit (dipakai dashboard).
        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW daily_borrowers AS
            SELECT
                tx.organization_id,
                tx.unit_id,
                b.fullname,
                b.phone,
                tx.created_at,
                COUNT(tl.id) AS total_items
            FROM transactions tx
            INNER JOIN borrowers b ON b.id = tx.borrower_id
            LEFT JOIN transaction_lines tl ON tl.transaction_id = tx.id
            WHERE tx.tx_type = 'BORROW'
              AND DATE(tx.created_at) = CURRENT_DATE
            GROUP BY tx.id, tx.organization_id, tx.unit_id, b.fullname, b.phone, tx.created_at
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS daily_borrowers');
        Schema::dropIfExists('transaction_lines');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('borrowers');
    }
};
