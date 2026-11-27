<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Data master milik organisasi. unit_id NULL = dipakai bersama seluruh organisasi.
 * - uoms        : satuan barang (PCS, BOX, ROLL, …)
 * - categories  : kategori barang
 * - warehouses  : gudang / lokasi penyimpanan
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['uoms', 'categories', 'warehouses'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
                $table->string('name', 100);
                $table->string('code', 100);
                $table->string('description', 255)->nullable();
                $table->timestamps();

                $table->unique(['organization_id', 'code']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('uoms');
    }
};
