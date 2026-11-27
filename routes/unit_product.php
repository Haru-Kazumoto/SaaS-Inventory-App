<?php

use App\Http\Controllers\UnitProductController;
use Illuminate\Support\Facades\Route;

Route::name('unit-product.')->prefix('unit-product')->group(function () {
    Route::get('', [UnitProductController::class, 'index'])->name("index");
    Route::post('', [UnitProductController::class, 'store'])->name('store');
    Route::put('{unitProduct}', [UnitProductController::class, 'update'])->name("update");
    Route::delete('{unitProduct}', [UnitProductController::class, 'delete'])->name("delete");
})->middleware(['auth', 'web']);
