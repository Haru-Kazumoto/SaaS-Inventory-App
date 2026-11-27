<?php

use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::name('borrowing.')->prefix('borrowing')->group(function () {
    Route::get('', [TransactionController::class, 'index'])->name('index');
    Route::get('/create', [TransactionController::class, 'create'])->name('create');
    Route::get('{transaction}/return', [TransactionController::class, 'returning'])->name("returning");
    Route::get('{transaction}/detail', [TransactionController::class, 'show'])->name('show');
    Route::post('', [TransactionController::class, 'store'])->name('store');
    Route::post('/return', [TransactionController::class, 'returnBorrow'])->name('returnBorrow');
    
    Route::put('{id}/update', [TransactionController::class, 'update'])->name('update');
})->middleware('auth');
