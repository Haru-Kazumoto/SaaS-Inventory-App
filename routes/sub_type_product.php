<?php

use App\Http\Controllers\SubTypeProductController;
use Illuminate\Support\Facades\Route;

Route::name('sub-type-product.')
    ->prefix('sub-type')
    ->group(function () {

        Route::get('', [SubTypeProductController::class, 'index'])->name('index');
        Route::post('', [SubTypeProductController::class, 'store'])->name('store');
        Route::put('{subTypeProduct}',[SubTypeProductController::class, 'update'])->name('update');
        Route::delete('{subTypeProduct}',[SubTypeProductController::class,'delete'])->name('delete');
        
    })->middleware(['auth', 'web']);
