<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // INI BAKAL JADI CORE NIH 
        Schema::create('product_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('action', ['IN','OUT','OPENING','RESTOCK','WRITE_OFF','RECLASSIFY','TRANSFER','ADJUSTMENT_NEG','ADJUSTMENT_POS']);
            $table->enum('condition',['GOOD','DAMAGED','LOST','OUT','OFF']);

            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_logs');
    }
};
