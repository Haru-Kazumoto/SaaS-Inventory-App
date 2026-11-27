<?php

use App\Http\Controllers\ProductsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth','web'])->name('products.')->prefix('products')->group(function () {
    Route::get('', [ProductsController::class, 'index'])->name('index');
    Route::get('/create', [ProductsController::class, 'create'])->name('create');
    Route::get('/off', [ProductsController::class, 'indexOffProducts'])->name('indexOff');
    Route::get('{major}', [ProductsController::class, 'indexMajor'])->name('major');
    Route::get('export/{major}', [ProductsController::class, 'export'])->name('export');
    Route::get('export/off/{major}', [ProductsController::class, 'exportOffProducts'])->name('exportOff');
    Route::get('{products}/detail', [ProductsController::class, 'show'])->name('show');
    Route::get('{products}/edit', [ProductsController::class, 'edit'])->name('edit');
    Route::post('', [ProductsController::class, 'store'])->name('store');
    Route::delete('{products}/softdelete', [ProductsController::class, 'softDelete'])->name('destroy');
    Route::put('{products}/update', [ProductsController::class, 'update'])->name('update');
});
