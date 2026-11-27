<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportItemData;
use App\Http\Controllers\ExportItemsData;
use App\Http\Controllers\ExportRequestItemData;
use App\Http\Controllers\ExportTransactionData;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequestItemController;
use App\Http\Controllers\SuperadminController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    if (Auth::check()) {
        dd(Auth::user());
        $role = Auth::user()->role->name;
        return match ($role) {
            'DEVELOPER' => redirect()->route('dashboard.internal'),
            'SUPERADMIN' => redirect()->route('dashboard.superadmin'),
            default => redirect()->route('dashboard'),
        };
    }

    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard/superadmin', [DashboardController::class, 'indexSuperadmin'])->name('dashboard.superadmin');
    Route::get('/dashboard/internal', [DashboardController::class, 'indexInternal'])->name('dashboard.internal');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('', [UserController::class, 'store'])->name('store');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::get('{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
    });

    Route::prefix('item')->name('item-management.')->group(function () {
        Route::get('index', [ItemController::class, 'index'])->name('index');
        Route::get('create', [ItemController::class, 'create'])->name('create');
        Route::post('/', [ItemController::class, 'store'])->name('store');
        Route::get('/{items}/detail', [ItemController::class, 'show'])->name('show');
        Route::get('/{items}/edit', [ItemController::class, 'edit'])->name('edit');
        Route::put('/{items}', [ItemController::class, 'update'])->name('update');
        Route::delete('/{items}', [ItemController::class, 'destroy'])->name('destroy');

        // RESPONSE JSON
        Route::get('get-items', [ItemController::class, 'getProductsByMajor'])->name('get-all');

        //export data
        // Route::get('/export',ExportItemData::class)->name('export');
        Route::get('/export', [ExportItemsData::class, 'export'])->name('export');

        // PER MAJOR
        Route::name('major.')->group(function () {
            Route::get('/tjkt', [SuperadminController::class, 'indexTJKT'])->name('tjkt');
            Route::get('/te', [SuperadminController::class, 'indexTE'])->name('te');
            Route::get('/to', [SuperadminController::class, 'indexTO'])->name('to');
            Route::get('/ak', [SuperadminController::class, 'indexAK'])->name('ak');
            Route::get('/tu', [SuperadminController::class, 'indexTU'])->name('tu');

            Route::get('/{items}/edit/{code}', [SuperadminController::class, 'show'])->name('detail');
        });
    });

    // Route::prefix('transactions')->name('transaction.')->group(function() {
    //     Route::get('', [TransactionController::class, 'index'])->name('index');
    //     Route::get('create', [TransactionController::class, 'create'])->name('create');
    //     Route::post('', [TransactionController::class, 'store'])->name('store');
    //     Route::get('/{transaction}/edit', [TransactionController::class, 'edit'])->name('edit');
    //     Route::put('/{transaction}', [TransactionController::class, 'update'])->name('update');
    //     Route::delete('/{transaction}', [TransactionController::class, 'destroy'])->name('destroy');
    //     Route::get('{transaction}/detail', [TransactionController::class, 'show'])->name('show');

    //     // index list
    //     Route::get('/items',[TransactionController::class, 'indexItemsOut'])->name('items');

    //     //restore
    //     Route::get('/{transaction}/restore', [TransactionController::class, 'reStoreTransaction'])->name('restore');
    //     Route::put('/{transaction}/submit-restore', [TransactionController::class, 'submitRestoreTransaction'])->name('restore.submit');

    //     //export
    //     Route::get('/export', ExportTransactionData::class)->name('export');
    // });

    Route::prefix('request-item')->name('request-item.')->group(function () {
        Route::get('', [RequestItemController::class, 'index'])->name('index');
        Route::get('/create', [RequestItemController::class, 'create'])->name('create');
        Route::post('', [RequestItemController::class, 'store'])->name('store');
        Route::get('{requestItem}/process', [RequestItemController::class, 'processRequest'])->name('process');
        Route::patch('{requestItem}/process', [RequestItemController::class, 'storeProcessRequest'])->name('process');
        Route::get('{requestItem}/detail', [RequestItemController::class, 'processRequest'])->name('detail');

        Route::post('/bulk/assign', [RequestItemController::class, 'bulkAssignments'])->name('bulk.assign');

        Route::get('/export', [ExportRequestItemData::class, 'export'])->name('export');
    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('', [ReportController::class, 'index'])->name('index');
        Route::get('/create', [ReportController::class, 'create'])->name('create');
        Route::post('/reports', [ReportController::class, 'store'])->name('post');
        Route::get('{report}/detail', [ReportController::class, 'show'])->name('show');
    });
});

require __DIR__ . '/auth.php';
require __DIR__ . '/sub_type_product.php';
require __DIR__ . '/unit_product.php';
require __DIR__ . '/warehouse.php';
require __DIR__ . '/products.php';
require __DIR__ . '/product_logs.php';
require __DIR__ . '/borrowing.php';
require __DIR__ . '/lines.php';

