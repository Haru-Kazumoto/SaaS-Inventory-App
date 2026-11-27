<?php

use App\Http\Controllers\Platform\DashboardController;
use App\Http\Controllers\Platform\InvoiceController;
use App\Http\Controllers\Platform\OrganizationController;
use App\Http\Controllers\Platform\PaymentController;
use App\Http\Controllers\Platform\PlanController;
use App\Http\Controllers\Platform\UserController;
use Illuminate\Support\Facades\Route;

/*
| Area Platform: pengelola SaaS (role DEVELOPER).
*/
Route::middleware(['auth', 'platform'])->prefix('platform')->name('platform.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('organizations', [OrganizationController::class, 'index'])->name('organizations.index');
    Route::post('organizations', [OrganizationController::class, 'store'])->name('organizations.store');
    Route::get('organizations/{organization}', [OrganizationController::class, 'show'])->name('organizations.show');
    Route::put('organizations/{organization}', [OrganizationController::class, 'update'])->name('organizations.update');
    Route::patch('organizations/{organization}/status', [OrganizationController::class, 'updateStatus'])->name('organizations.status');
    Route::post('organizations/{organization}/subscription', [OrganizationController::class, 'changeSubscription'])->name('organizations.subscription');
    Route::post('organizations/{organization}/owner', [OrganizationController::class, 'createOwner'])->name('organizations.owner');

    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

    Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
    Route::post('plans', [PlanController::class, 'store'])->name('plans.store');
    Route::put('plans/{plan}', [PlanController::class, 'update'])->name('plans.update');

    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::patch('invoices/{invoice}/void', [InvoiceController::class, 'void'])->name('invoices.void');
    Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('invoices.payments.store');

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
});
