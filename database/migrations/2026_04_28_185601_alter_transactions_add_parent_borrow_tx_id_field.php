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
        Schema::table('transaction', function (Blueprint $table) {
            $table->foreignId('parent_borrow_tx_id')
                ->nullable()
                ->constrained('transaction')
                ->nullOnDelete()
                ->after('borrower_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction', function (Blueprint $table) {
            $table->dropForeign(['parent_borrow_tx_id']);
            $table->dropColumn('parent_borrow_tx_id');
        });
    }
};
