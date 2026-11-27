<?php

use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::prefix('warehouse')
    ->middleware(['auth', 'web'])
    ->name('warehouse.')
    ->group(function () {
        Route::get('', [WarehouseController::class, 'index'])->name('index');
        Route::get('{product_id}/json', [WarehouseController::class, 'indexJson'])->name('index-json');
        Route::get('/create', [WarehouseController::class, 'create'])->name('create');
        Route::post('', [WarehouseController::class, 'store'])->name('store');
        Route::get('/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('edit');
        Route::put('/{warehouse}/update', [WarehouseController::class, 'update'])->name('update');
        Route::delete('/{warehouse}/delete', [WarehouseController::class, 'destroy'])->name('delete');
    });
