<?php

use App\Http\Controllers\Identity\MemberController;
use Illuminate\Support\Facades\Route;

/* Anggota organisasi (khusus admin organisasi). */
Route::middleware('auth')->resource('members', MemberController::class)->except('show');
