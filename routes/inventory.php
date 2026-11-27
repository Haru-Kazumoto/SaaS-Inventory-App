<?php

use App\Http\Controllers\Inventory\ProductController;
use App\Http\Controllers\Inventory\StockController;
use App\Http\Controllers\Reporting\ExportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    // Barang
    Route::get('products/export/monthly', [ExportController::class, 'monthlyStock'])->name('products.export');
    Route::resource('products', ProductController::class);

    // Stok
    Route::post('products/{product}/restock', [StockController::class, 'restock'])->name('products.restock');
    Route::post('products/{product}/write-off', [StockController::class, 'writeOff'])->name('products.write-off');
    Route::get('products/{product}/stock', [StockController::class, 'stock'])->name('products.stock');

    // Pergerakan stok non-peminjaman
    Route::get('movements/restocks', [StockController::class, 'restocks'])->name('movements.restocks');
    Route::get('movements/write-offs', [StockController::class, 'writeOffs'])->name('movements.write-offs');
    Route::get('movements/write-offs/export', [ExportController::class, 'writeOffs'])->name('movements.write-offs.export');
});
