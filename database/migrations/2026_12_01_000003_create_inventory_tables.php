<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Barang & ledger stok.
 * Stok TIDAK disimpan sebagai kolom: stok = SUM(product_logs.delta) per (product, warehouse, condition).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();          // unit pemilik
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('uom_id')->nullable()->constrained('uoms')->nullOnDelete();
            $table->string('code');                                                         // boleh sama (barang berbeda kondisi/merk)
            $table->string('name');
            $table->string('brand_name')->nullable();
            $table->enum('type', ['DURABLE', 'CONSUMABLE']);
            $table->unsignedBigInteger('price')->default(0);                                // Rupiah
            $table->date('labeling_date')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'unit_id', 'code']);
            $table->index(['organization_id', 'name']);
        });

        Schema::create('product_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->enum('move_type', ['OPENING', 'RESTOCK', 'BORROW_OUT', 'RETURN', 'WRITE_OFF', 'ADJUSTMENT_POS', 'ADJUSTMENT_NEG']);
            $table->enum('condition', ['GOOD', 'DAMAGED', 'LOST', 'OUT', 'OFF']);
            $table->unsignedInteger('quantity');
            $table->integer('delta');                                                       // +masuk / -keluar
            $table->string('ref_type', 30)->default('transaction');                        // transaction | manual
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->unsignedInteger('line_no')->nullable();
            $table->string('reason')->nullable();                                           // mis. alasan penghapusan
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('occurred_at');
            $table->timestamps();

            $table->index(['product_id', 'warehouse_id', 'condition']);
            $table->index(['ref_type', 'ref_id']);
            $table->index(['organization_id', 'move_type', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_logs');
        Schema::dropIfExists('products');
    }
};
