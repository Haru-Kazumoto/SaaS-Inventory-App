<?php

use App\Http\Controllers\MasterData\MasterDataController;
use Illuminate\Support\Facades\Route;

/* Data master: unit (admin organisasi), penyimpanan, kategori, satuan. {type}: units|warehouses|categories|uoms */
Route::middleware('auth')->prefix('master/{type}')->name('master-data.')
    ->whereIn('type', ['units', 'warehouses', 'categories', 'uoms'])
    ->group(function () {
        Route::get('/', [MasterDataController::class, 'index'])->name('index');
        Route::post('/', [MasterDataController::class, 'store'])->name('store');
        Route::put('{id}', [MasterDataController::class, 'update'])->whereNumber('id')->name('update');
        Route::delete('{id}', [MasterDataController::class, 'destroy'])->whereNumber('id')->name('destroy');
    });
