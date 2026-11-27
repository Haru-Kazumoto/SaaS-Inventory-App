<?php

use App\Http\Controllers\Borrowing\BorrowingController;
use App\Http\Controllers\Borrowing\MovementController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('borrowing')->name('borrowing.')->group(function () {
        Route::get('/', [BorrowingController::class, 'index'])->name('index');
        Route::get('create', [BorrowingController::class, 'create'])->name('create');
        Route::post('/', [BorrowingController::class, 'store'])->name('store');
        Route::get('{transaction}', [BorrowingController::class, 'show'])->name('show');
        Route::get('{transaction}/return', [BorrowingController::class, 'returnForm'])->name('return');
        Route::post('{transaction}/return', [BorrowingController::class, 'returnStore'])->name('return.store');
    });

    // Pergerakan barang keluar
    Route::get('movements/loaned', [MovementController::class, 'loaned'])->name('movements.loaned');
    Route::get('movements/consumed', [MovementController::class, 'consumed'])->name('movements.consumed');
});
