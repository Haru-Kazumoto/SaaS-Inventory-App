<?php

use App\Http\Controllers\Procurement\ProcurementController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('procurement')->name('procurement.')->group(function () {
    Route::get('/', [ProcurementController::class, 'index'])->name('index');
    Route::get('create', [ProcurementController::class, 'create'])->name('create');
    Route::post('/', [ProcurementController::class, 'store'])->name('store');
    Route::post('process', [ProcurementController::class, 'process'])->name('process');
    Route::get('export', [ProcurementController::class, 'export'])->name('export');
});
