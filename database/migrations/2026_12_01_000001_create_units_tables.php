<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Unit = bagian organisasi pemilik barang (Jurusan / Divisi / Cabang / …, label dari terminologi).
 * unit_members = keanggotaan user di unit beserta perannya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('units')->nullOnDelete(); // cadangan hierarki
            $table->string('name');
            $table->string('code', 50);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'code']);
        });

        Schema::create('unit_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['admin', 'staff', 'viewer'])->default('staff');
            $table->timestamps();

            $table->unique(['unit_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_members');
        Schema::dropIfExists('units');
    }
};
