<?php

use App\Http\Controllers\ProductLogsController;
use Illuminate\Support\Facades\Route;

Route::name('product-logs.')->prefix('product-logs')->group(function() {
    Route::post('/write-off', [ProductLogsController::class, 'writeOff'])->name("write-off");
    Route::post('/restock', [ProductLogsController::class, 'restock'])->name("restock");
})->middleware(['auth']);