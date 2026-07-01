<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StatusController;
use Illuminate\Support\Facades\Route;

// ── Auth ─────────────────────────────────────────────────
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Dashboard (auth required) ────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/transactions', [DashboardController::class, 'transactions'])->name('transactions.index');
    Route::get('/transactions/{id}', [DashboardController::class, 'show'])->name('transactions.show');
    Route::get('/account', [AuthController::class, 'changePasswordForm'])->name('account');
    Route::put('/account/password', [AuthController::class, 'changePassword'])->name('password.update');
    Route::put('/account/email', [AuthController::class, 'changeEmail'])->name('email.update');
    Route::get('/status', [StatusController::class, 'index'])->name('status');
});
