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
        Schema::create('borrower', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('supervisor_name')->nullable();
            $table->string('phone', 20);
            $table->string('room_name', 20);
            $table->text('description', 100)->nullable();
            
            $table->foreignId('major_id')->nullable()->constrained('major')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrower');
    }
};
