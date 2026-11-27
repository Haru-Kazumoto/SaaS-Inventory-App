<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Organisasi (tenant) & pengguna.
 *
 * Akses pengguna:
 * - is_platform_admin   → pengelola SaaS (area /platform), tidak terikat organisasi.
 * - organization_role   → owner | admin (kelola seluruh organisasi) | member (akses lewat unit_members).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('template', ['school', 'campus', 'office', 'community'])->default('school');
            $table->enum('status', ['trial', 'active', 'past_due', 'suspended', 'cancelled'])->default('trial');
            $table->json('settings')->nullable();          // terminologi, zona waktu, branding
            $table->string('owner_name')->nullable();
            $table->string('owner_email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->text('notes')->nullable();               // catatan internal platform
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->cascadeOnDelete();
            $table->enum('organization_role', ['owner', 'admin', 'member'])->nullable();
            $table->boolean('is_platform_admin')->default(false);
            $table->string('username')->unique();
            $table->string('fullname');
            $table->string('email')->nullable()->unique();
            $table->string('phone', 30)->nullable();
            $table->string('password');
            $table->string('profile_image')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['organization_id', 'organization_role']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('organizations');
    }
};
