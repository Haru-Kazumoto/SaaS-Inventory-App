<?php

use App\Http\Controllers\Reporting\DashboardController;
use Illuminate\Support\Facades\Route;

/*
| Aplikasi tenant (organisasi). Konteks organisasi di-set oleh middleware ResolveOrganization (grup web).
| Route per domain ada di file terpisah.
*/

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});

require __DIR__.'/auth.php';
require __DIR__.'/master-data.php';
require __DIR__.'/inventory.php';
require __DIR__.'/borrowing.php';
require __DIR__.'/procurement.php';
require __DIR__.'/members.php';
require __DIR__.'/platform.php';
