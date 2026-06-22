<?php

use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ── Health check (no auth) ──────────────────────────────────
Route::get('/health', fn () => response()->json(['status' => 'ok']));

// ── Authenticated routes (Android app) ─────────────────────
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('transactions', TransactionController::class)->only(['index', 'store', 'show']);
    Route::get('/transactions/{id}/qr', [TransactionController::class, 'qrImage']);
    Route::post('/transactions/{id}/cancel', [TransactionController::class, 'cancel']);
});

// ── Webhook (no auth, verified via HMAC signature, rate limited) ─────
Route::middleware('throttle:30,1')->post('/webhook/qrisify', [WebhookController::class, 'qrisify']);
