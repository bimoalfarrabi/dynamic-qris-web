<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Enums\WebhookStatus;
use App\Models\Transaction;
use App\Services\QrisifyApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private readonly QrisifyApiClient $qrisify
    ) {}

    /**
     * Handle QRIS-ify webhook: qris.payment.success
     *
     * POST /api/webhook/qrisify
     *
     * Flow:
     * 1. Verify HMAC-SHA256 signature
     * 2. Find transaction by qrisify_transaction_id
     * 3. If status = CANCELLED → log rejection, return 200 (prevent retry)
     * 4. Else → update status = SUCCESS
     */
    public function qrisify(Request $request): JsonResponse
    {
        $signature = $request->header(config('qrisify.webhook_signature_header'), '');
        $rawPayload = $request->getContent();

        // 1. Verify signature
        if (! $this->qrisify->verifyWebhookSignature($rawPayload, $signature)) {
            Log::warning('QRIS-ify webhook signature verification failed', [
                'signature' => $signature,
            ]);

            return response()->json([
                'message' => 'Invalid signature',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $payload = $request->json()->all();
        $event = $payload['event'] ?? '';
        $data = $payload['data'] ?? [];

        Log::info('QRIS-ify webhook received', [
            'event' => $event,
            'transaction_id' => $data['transaction_id'] ?? null,
        ]);

        // Only handle payment success
        if ($event !== 'qris.payment.success') {
            Log::info('QRIS-ify webhook ignored (unhandled event)', ['event' => $event]);

            return response()->json(['message' => 'Event ignored']);
        }

        $qrisifyTransactionId = $data['transaction_id'] ?? null;

        if (! $qrisifyTransactionId) {
            Log::warning('QRIS-ify webhook missing transaction_id', ['payload' => $payload]);

            return response()->json(['message' => 'Missing transaction_id'], Response::HTTP_BAD_REQUEST);
        }

        // Process inside DB transaction with pessimistic locking to prevent
        // race condition between webhook and cancel endpoint
        return DB::transaction(function () use ($qrisifyTransactionId, $data) {
            // 2. Find transaction with row lock
            $transaction = Transaction::where('qrisify_transaction_id', $qrisifyTransactionId)
                ->lockForUpdate()
                ->first();

            if (! $transaction) {
                Log::warning('QRIS-ify webhook: transaction not found', [
                    'qrisify_transaction_id' => $qrisifyTransactionId,
                ]);

                // Return 200 to prevent retry — transaction might be from another system
                return response()->json(['message' => 'Transaction not found']);
            }

            // 3. If CANCELLED → reject update
            if ($transaction->isCancelled()) {
                Log::warning('Payment received for cancelled transaction', [
                    'transaction_id' => $transaction->id,
                    'qrisify_transaction_id' => $qrisifyTransactionId,
                    'amount_total' => $data['amount_total'] ?? null,
                    'payment_provider' => $data['payment_provider'] ?? null,
                ]);

                return response()->json([
                    'message' => 'Transaction already cancelled',
                ], Response::HTTP_OK);
            }

            // 4. Update status = SUCCESS
            $transaction->update([
                'status' => TransactionStatus::Success,
                'webhook_status' => WebhookStatus::SentSuccess,
                'payment_provider' => $data['payment_provider'] ?? $transaction->payment_provider,
                'paid_at' => isset($data['paid_at']) ? now()->parse($data['paid_at']) : now(),
                'amount_total' => $data['amount_total'] ?? $transaction->amount_total,
            ]);

            Log::info('Transaction marked as SUCCESS via webhook', [
                'transaction_id' => $transaction->id,
                'payment_provider' => $transaction->payment_provider,
            ]);

            return response()->json([
                'message' => 'Transaction updated successfully',
            ]);
        });
    }
}
