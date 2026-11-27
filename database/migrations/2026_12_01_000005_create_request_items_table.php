<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Pengajuan pengadaan barang oleh unit, ditinjau admin organisasi. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('item_name');
            $table->unsignedInteger('quantity');
            $table->string('uom', 50);                          // satuan teks bebas (barang belum tentu ada di master)
            $table->string('reference_url')->nullable();        // link toko
            $table->string('image_path')->nullable();
            $table->text('reason')->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('processed_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_items');
    }
};
