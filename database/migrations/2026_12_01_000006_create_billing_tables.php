<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penagihan SaaS (area Platform): paket, langganan, tagihan, pembayaran.
 * Nominal uang dalam Rupiah (integer).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price_monthly')->default(0);
            $table->unsignedBigInteger('price_yearly')->default(0);
            $table->unsignedInteger('max_units')->nullable();      // null = tanpa batas
            $table->unsignedInteger('max_users')->nullable();
            $table->unsignedInteger('max_products')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans');
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->enum('status', ['trialing', 'active', 'past_due', 'cancelled', 'expired'])->default('active');
            $table->unsignedBigInteger('price');                    // harga terkunci saat berlangganan
            $table->date('current_period_start');
            $table->date('current_period_end');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->enum('status', ['unpaid', 'paid', 'void'])->default('unpaid');
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('tax')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->date('issued_at');
            $table->date('due_at');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_at']);
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('amount');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->enum('method', ['bank_transfer', 'virtual_account', 'qris', 'cash', 'other'])->default('bank_transfer');
            $table->enum('status', ['pending', 'success', 'failed', 'refunded'])->default('success');
            $table->string('provider')->default('manual');          // manual | midtrans | xendit
            $table->string('reference')->nullable();
            $table->timestamp('paid_at');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }
};
