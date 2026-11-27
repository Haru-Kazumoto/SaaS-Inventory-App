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
            $table->string('tx_type')->after('tx_number');
            $table->dateTime('occurred_at')->after('tx_number');
            $table->enum('status', ['DRAFT','POSTED'])->change()->after('occurred_at');
            $table->text('description')->after('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction', function (Blueprint $table) {
            $table->dropColumn(['tx_type','occured_at','status','description']);
        });
    }
};
