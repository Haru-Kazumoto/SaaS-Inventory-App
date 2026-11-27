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
        Schema::table('product_logs', function (Blueprint $table) {
            $table->enum('move_type', ['OPENING','RESTOCK','BORROW_OUT','RETURN'])->after('id');
            $table->integer('quantity')->after('move_type');
            $table->integer('delta')->after('quantity');
            $table->string('ref_type')->default('transaction')->after('delta');
            $table->integer('ref_id')->nullable()->after('ref_type');
            $table->integer('line_no')->after('ref_id');
            $table->dateTime('occurred_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_logs', function (Blueprint $table) {
            $table->dropColumn([
                'move_type',
                'quantity',
                'delta',
                'ref_type',
                'ref_id',
                'line_no',
                'occurred_at',
                'action'
            ]);
        });
    }
};
