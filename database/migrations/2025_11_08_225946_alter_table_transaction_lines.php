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
        Schema::table('transaction_lines', function (Blueprint $table) {
            $table->unsignedInteger('line_no')->after('id');
            $table->integer('quantity')->after('line_no');
            $table->enum('condition_from', ['GOOD','DAMAGED','LOST'])->after('quantity');
            $table->enum('condition_to', ['GOOD','DAMAGED','LOST'])->after('condition_from')->nullable();

            $table->index('line_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_lines', function (Blueprint $table) {
            $table->dropColumn([
                'line_no',
                'quantity',
                'condition_from',
                'condition_to'
            ]);
        });
    }
};
