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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('brand_name')->nullable();
            $table->enum('type',['DURABLE','CONSUMABLE']);
            $table->decimal('price', 15, 2);
            $table->date('labeling_date');
            $table->string('image_product')->nullable();

            $table->foreignId('warehouse_id')->nullable()->constrained('warehouse')->nullOnDelete();
            $table->foreignId('major_id')->nullable()->constrained('major')->nullOnDelete();
            $table->foreignId('sub_type_id')->nullable()->constrained('sub_type_products')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('unit_products')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
