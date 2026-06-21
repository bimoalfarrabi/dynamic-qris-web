<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Services\QrisifyApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    private function mockQrisifyClient(): void
    {
        $mock = Mockery::mock(QrisifyApiClient::class);
        $this->app->instance(QrisifyApiClient::class, $mock);
    }

    // ── Auth ────────────────────────────────────────────────

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/transactions')->assertUnauthorized();
    }

    public function test_authenticated_request_returns_transactions(): void
    {
        Transaction::factory()->count(3)->create();

        $this->withToken($this->token)
            ->getJson('/api/transactions')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'status', 'amount_requested']],
                'current_page',
                'total',
            ]);
    }

    // ── Create Transaction ──────────────────────────────────

    public function test_create_transaction_proxies_to_qrisify_and_saves(): void
    {
        $qrisifyId = Str::uuid()->toString();

        $mock = Mockery::mock(QrisifyApiClient::class);
        $mock->shouldReceive('createTransaction')
            ->once()
            ->with(Mockery::on(fn ($p) => $p['amount'] === 50000))
            ->andReturn([
                'transaction_id' => $qrisifyId,
                'external_id' => 'ORDER-001',
                'amount_requested' => 50000,
                'unique_code' => 123,
                'amount_total' => 50123,
                'qris_string' => '00020101021226...',
            ]);
        $this->app->instance(QrisifyApiClient::class, $mock);

        $this->withToken($this->token)
            ->postJson('/api/transactions', [
                'amount' => 50000,
                'external_id' => 'ORDER-001',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'PENDING')
            ->assertJsonPath('data.qrisify_transaction_id', $qrisifyId)
            ->assertJsonPath('data.amount_requested', 50000)
            ->assertJsonPath('data.amount_total', 50123);

        $this->assertDatabaseHas('transactions', [
            'qrisify_transaction_id' => $qrisifyId,
            'status' => 'PENDING',
        ]);
    }

    public function test_create_transaction_validates_amount(): void
    {
        $this->withToken($this->token)
            ->postJson('/api/transactions', ['amount' => 0])
            ->assertUnprocessable();
    }

    public function test_create_transaction_returns_502_on_qrisify_error(): void
    {
        $mock = Mockery::mock(QrisifyApiClient::class);
        $mock->shouldReceive('createTransaction')
            ->andThrow(new \RuntimeException('QRIS-ify API error (500)'));
        $this->app->instance(QrisifyApiClient::class, $mock);

        $this->withToken($this->token)
            ->postJson('/api/transactions', ['amount' => 50000])
            ->assertStatus(502);
    }

    // ── Show Transaction ───────────────────────────────────

    public function test_show_transaction_returns_detail(): void
    {
        $transaction = Transaction::factory()->create();

        $this->withToken($this->token)
            ->getJson("/api/transactions/{$transaction->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $transaction->id);
    }

    public function test_show_transaction_auto_expires_pending_past_expiry(): void
    {
        $transaction = Transaction::factory()->create([
            'status' => 'PENDING',
            'expires_at' => now()->subMinutes(5),
        ]);

        $this->withToken($this->token)
            ->getJson("/api/transactions/{$transaction->id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'EXPIRED');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'EXPIRED',
        ]);
    }

    // ── Cancel Transaction ─────────────────────────────────

    public function test_cancel_pending_transaction_succeeds(): void
    {
        $transaction = Transaction::factory()->create(['status' => 'PENDING']);

        $this->withToken($this->token)
            ->postJson("/api/transactions/{$transaction->id}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'CANCELLED')
            ->assertJsonPath('data.cancelled_at', fn ($v) => $v !== null);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'CANCELLED',
        ]);
    }

    public function test_cancel_already_success_transaction_fails(): void
    {
        $transaction = Transaction::factory()->create(['status' => 'SUCCESS']);

        $this->withToken($this->token)
            ->postJson("/api/transactions/{$transaction->id}/cancel")
            ->assertStatus(422);
    }

    public function test_cancel_already_cancelled_transaction_fails(): void
    {
        $transaction = Transaction::factory()->create(['status' => 'CANCELLED']);

        $this->withToken($this->token)
            ->postJson("/api/transactions/{$transaction->id}/cancel")
            ->assertStatus(422);
    }

    public function test_cancel_expired_transaction_fails(): void
    {
        $transaction = Transaction::factory()->create(['status' => 'EXPIRED']);

        $this->withToken($this->token)
            ->postJson("/api/transactions/{$transaction->id}/cancel")
            ->assertStatus(422);
    }
}
