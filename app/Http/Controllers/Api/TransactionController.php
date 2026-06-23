<?php

namespace App\Http\Controllers\Api;

use App\Enums\TransactionStatus;
use App\Enums\WebhookStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexTransactionRequest;
use App\Http\Requests\StoreTransactionRequest;
use App\Models\Transaction;
use App\Services\QrisifyApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function __construct(
        private readonly QrisifyApiClient $qrisify
    ) {}

    /**
     * List transactions with optional status filter.
     *
     * GET /api/transactions?status=PENDING&page=1
     */
    public function index(IndexTransactionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $query = Transaction::query()->latest();

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $transactions = $query->paginate($validated['per_page'] ?? 15);

        return response()->json($transactions);
    }

    /**
     * Create transaction (proxy to QRIS-ify, save to DB).
     *
     * POST /api/transactions
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $qrisifyPayload = [
                'amount' => $validated['amount'],
                'external_id' => $validated['external_id'] ?? 'TXN-'.now()->format('YmdHis').'-'.mt_rand(100, 999),
                'unique_code' => 0,
            ];

            if (isset($validated['expiry_minutes'])) {
                $qrisifyPayload['expiry_minutes'] = $validated['expiry_minutes'];
            }

            $qrisifyData = $this->qrisify->createTransaction($qrisifyPayload);

            $expiryMinutes = $validated['expiry_minutes'] ?? 15;
            $expiresAt = now()->addMinutes($expiryMinutes);

            $transaction = Transaction::create([
                'qrisify_transaction_id' => $qrisifyData['transaction_id'] ?? null,
                'external_id' => $qrisifyData['external_id'] ?? $validated['external_id'] ?? null,
                'amount_requested' => $qrisifyData['amount_requested'] ?? $validated['amount'],
                'unique_code' => $qrisifyData['unique_code'] ?? null,
                'amount_total' => $qrisifyData['amount_total'] ?? null,
                'status' => TransactionStatus::Pending,
                'qris_string' => $qrisifyData['qris_string'] ?? null,
                'webhook_status' => WebhookStatus::Unsent,
                'expires_at' => $expiresAt,
            ]);

            return response()->json([
                'data' => $transaction,
            ], Response::HTTP_CREATED);

        } catch (\Throwable $e) {
            Log::error('Failed to create transaction', [
                'error' => $e->getMessage(),
                'payload' => $validated,
            ]);

            return response()->json([
                'message' => 'Failed to create transaction',
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_GATEWAY);
        }
    }

    /**
     * Get transaction detail.
     *
     * GET /api/transactions/{id}
     */
    public function show(string $id): JsonResponse
    {
        $transaction = Transaction::findOrFail($id);

        // Auto-expire if past expiry time and still PENDING
        if ($transaction->status === TransactionStatus::Pending && $transaction->expires_at->isPast()) {
            $transaction->update(['status' => TransactionStatus::Expired]);
            $transaction->refresh();
        }

        return response()->json([
            'data' => $transaction,
        ]);
    }

    /**
     * Proxy QR image from QRIS-ify.
     *
     * GET /api/transactions/{id}/qr
     */
    public function qrImage(string $id): Response
    {
        $transaction = Transaction::findOrFail($id);

        if (! $transaction->qrisify_transaction_id) {
            abort(404, 'QRIS-ify transaction ID not available');
        }

        // Cache QR image for 5 minutes (QR doesn't change for same transaction)
        $cacheKey = "qr_image_{$transaction->qrisify_transaction_id}";

        try {
            $qr = Cache::remember($cacheKey, 300, function () use ($transaction) {
                return $this->qrisify->getQrImage($transaction->qrisify_transaction_id);
            });

            return response($qr['content'], 200, [
                'Content-Type' => $qr['content_type'],
                'Cache-Control' => 'public, max-age=300',
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch QR image', [
                'transaction_id' => $id,
                'error' => $e->getMessage(),
            ]);

            abort(502, 'Failed to fetch QR image');
        }
    }

    /**
     * Cancel a pending transaction.
     *
     * POST /api/transactions/{id}/cancel
     */
    public function cancel(string $id): JsonResponse
    {
        // Transaction with pessimistic locking to prevent race condition with webhook
        $transaction = DB::transaction(function () use ($id) {
            $tx = Transaction::where('id', $id)->lockForUpdate()->first();

            if (! $tx) {
                abort(404);
            }

            if (! $tx->isCancellable()) {
                throw ValidationException::withMessages([
                    'status' => "Transaction cannot be cancelled (current status: {$tx->status->value})",
                ]);
            }

            $tx->update([
                'status' => TransactionStatus::Cancelled,
                'cancelled_at' => now(),
            ]);

            return $tx;
        });

        Log::info('Transaction cancelled', [
            'transaction_id' => $transaction->id,
            'cancelled_at' => $transaction->cancelled_at,
        ]);

        return response()->json([
            'data' => $transaction->refresh(),
        ]);
    }
}
