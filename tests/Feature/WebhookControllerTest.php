<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Enums\WebhookStatus;
use App\Models\Transaction;
use App\Services\QrisifyApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class WebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $webhookSecret = 'test-webhook-secret';

    protected function setUp(): void
    {
        parent::setUp();
        config(['qrisify.webhook_secret' => $this->webhookSecret]);
    }

    private function signPayload(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->webhookSecret);
    }

    private function webhookPayload(string $qrisifyTransactionId, array $extra = []): array
    {
        return array_merge([
            'event' => 'qris.payment.success',
            'created_at' => now()->toISOString(),
            'data' => array_merge([
                'transaction_id' => $qrisifyTransactionId,
                'external_id' => 'ORDER-001',
                'status' => 'SUCCESS',
                'amount_requested' => 50000,
                'unique_code' => 123,
                'amount_total' => 50123,
                'payment_provider' => 'gopay',
                'merchant_name' => 'Toko Budi',
                'paid_at' => now()->toISOString(),
            ], $extra),
        ], []);
    }

    // ── Signature Verification ──────────────────────────────

    public function test_webhook_with_invalid_signature_returns_401(): void
    {
        $payload = json_encode($this->webhookPayload(Str::uuid()->toString()));

        $this->postJson('/api/webhook/qrisify', (array) json_decode($payload), [
            config('qrisify.webhook_signature_header') => 'invalid-signature',
        ])->assertUnauthorized();
    }

    public function test_webhook_with_missing_signature_returns_401(): void
    {
        $this->postJson('/api/webhook/qrisify', $this->webhookPayload(Str::uuid()->toString()))
            ->assertUnauthorized();
    }

    // ── Payment Success ─────────────────────────────────────

    public function test_webhook_updates_pending_transaction_to_success(): void
    {
        $qrisifyId = Str::uuid()->toString();
        $transaction = Transaction::factory()->create([
            'qrisify_transaction_id' => $qrisifyId,
            'status' => 'PENDING',
        ]);

        $payload = json_encode($this->webhookPayload($qrisifyId));
        $signature = $this->signPayload($payload);

        $this->postJson('/api/webhook/qrisify', (array) json_decode($payload), [
            config('qrisify.webhook_signature_header') => $signature,
        ])->assertOk();

        $transaction->refresh();

        $this->assertEquals(TransactionStatus::Success, $transaction->status);
        $this->assertEquals(WebhookStatus::SentSuccess, $transaction->webhook_status);
        $this->assertEquals('gopay', $transaction->payment_provider);
        $this->assertNotNull($transaction->paid_at);
    }

    // ── Cancelled Transaction Rejection ─────────────────────

    public function test_webhook_rejects_payment_for_cancelled_transaction(): void
    {
        $qrisifyId = Str::uuid()->toString();
        $transaction = Transaction::factory()->create([
            'qrisify_transaction_id' => $qrisifyId,
            'status' => 'CANCELLED',
            'cancelled_at' => now(),
        ]);

        $payload = json_encode($this->webhookPayload($qrisifyId));
        $signature = $this->signPayload($payload);

        $this->postJson('/api/webhook/qrisify', (array) json_decode($payload), [
            config('qrisify.webhook_signature_header') => $signature,
        ])->assertOk(); // 200 to prevent QRIS-ify retry

        $transaction->refresh();

        $this->assertEquals(TransactionStatus::Cancelled, $transaction->status); // status unchanged
        $this->assertNotEquals(WebhookStatus::SentSuccess, $transaction->webhook_status);
    }

    // ── Edge Cases ──────────────────────────────────────────

    public function test_webhook_with_unknown_transaction_returns_200(): void
    {
        $payload = json_encode($this->webhookPayload(Str::uuid()->toString()));
        $signature = $this->signPayload($payload);

        $this->postJson('/api/webhook/qrisify', (array) json_decode($payload), [
            config('qrisify.webhook_signature_header') => $signature,
        ])->assertOk();
    }

    public function test_webhook_ignores_non_payment_success_events(): void
    {
        $qrisifyId = Str::uuid()->toString();
        Transaction::factory()->create([
            'qrisify_transaction_id' => $qrisifyId,
            'status' => 'PENDING',
        ]);

        $payloadArray = $this->webhookPayload($qrisifyId);
        $payloadArray['event'] = 'qris.transaction.expired';
        $payload = json_encode($payloadArray);
        $signature = $this->signPayload($payload);

        $this->postJson('/api/webhook/qrisify', (array) json_decode($payload), [
            config('qrisify.webhook_signature_header') => $signature,
        ])->assertOk();

        // Status should remain PENDING
        $this->assertDatabaseHas('transactions', [
            'qrisify_transaction_id' => $qrisifyId,
            'status' => 'PENDING',
        ]);
    }
}
