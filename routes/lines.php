<?php

use App\Http\Controllers\ProductLogsController;
use App\Http\Controllers\TransactionLinesController;
use Illuminate\Support\Facades\Route;

Route::name('lines.')->prefix('lines')->group(function() {
    Route::get('/durable', [TransactionLinesController::class, 'indexBorrowedDurableProducts'])->name('durable');
    Route::get('/consumable', [TransactionLinesController::class, 'indexBorrowedConsumableProducts'])->name('consumable');
    Route::get('/restocks', [ProductLogsController::class, 'indexRestockProducts'])->name('restocks');
})->middleware('auth');