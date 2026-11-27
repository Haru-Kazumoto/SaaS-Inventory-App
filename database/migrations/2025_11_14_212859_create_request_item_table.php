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
        Schema::create('request_item', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name');
            $table->integer('quantity');
            $table->string('unit');
            $table->string('link_shop')->nullable();
            $table->string('image_item')->nullable();
            $table->string('reason_request');
            $table->string("request_status");
            $table->dateTime('proceed_at')->nullable();

            $table->foreignId('requestor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('major_id')->constrained('major')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_item');
    }
};
