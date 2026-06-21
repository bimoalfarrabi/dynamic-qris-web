<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ── Auth ────────────────────────────────────────────────

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/transactions')->assertRedirect('/login');
    }

    // ── Dashboard ───────────────────────────────────────────

    public function test_dashboard_displays_stats_and_recent_transactions(): void
    {
        Transaction::factory()->count(3)->create();

        $this->actingAs($this->user)
            ->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->has('stats')
                ->has('stats.today')
                ->has('stats.this_week')
                ->has('stats.success_rate')
                ->has('stats.pending')
                ->has('recentTransactions', 3)
            );
    }

    public function test_dashboard_stats_calculate_correctly(): void
    {
        Transaction::factory()->success()->create([
            'amount_total' => 50000,
            'created_at' => now(),
        ]);
        Transaction::factory()->create([
            'status' => 'PENDING',
            'created_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->where('stats.today.count', 2)
                ->where('stats.today.success', 1)
                ->where('stats.today.revenue', 50000)
                ->where('stats.success_rate', 50)
                ->where('stats.pending', 1)
            );
    }

    // ── Transactions List ───────────────────────────────────

    public function test_transactions_page_displays_paginated_list(): void
    {
        Transaction::factory()->count(20)->create();

        $this->actingAs($this->user)
            ->get('/transactions')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Transactions/Index')
                ->has('transactions.data', 15)  // default per_page
                ->has('filters')
            );
    }

    public function test_transactions_page_filters_by_status(): void
    {
        Transaction::factory()->success()->count(2)->create();
        Transaction::factory()->count(3)->create(['status' => 'PENDING']);

        $this->actingAs($this->user)
            ->get('/transactions?status=SUCCESS')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Transactions/Index')
                ->has('transactions.data', 2)
            );
    }

    public function test_transactions_page_filters_by_search(): void
    {
        Transaction::factory()->create(['external_id' => 'FINDME-001']);
        Transaction::factory()->count(3)->create();

        $this->actingAs($this->user)
            ->get('/transactions?search=FINDME')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Transactions/Index')
                ->has('transactions.data', 1)
            );
    }

    public function test_transactions_page_filters_by_date_range(): void
    {
        Transaction::factory()->create(['created_at' => now()->subDays(5)]);
        Transaction::factory()->create(['created_at' => now()]);

        $this->actingAs($this->user)
            ->get('/transactions?date_from=' . now()->toDateString())
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Transactions/Index')
                ->has('transactions.data', 1)
            );
    }

    // ── Transaction Detail ──────────────────────────────────

    public function test_transaction_detail_shows_transaction(): void
    {
        $transaction = Transaction::factory()->create();

        $this->actingAs($this->user)
            ->get("/transactions/{$transaction->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Transactions/Show')
                ->has('transaction')
                ->where('transaction.id', $transaction->id)
            );
    }

    public function test_transaction_detail_auto_expires_pending(): void
    {
        $transaction = Transaction::factory()->create([
            'status' => 'PENDING',
            'expires_at' => now()->subMinutes(5),
        ]);

        $this->actingAs($this->user)
            ->get("/transactions/{$transaction->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Transactions/Show')
                ->where('transaction.status', 'EXPIRED')
            );

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'EXPIRED',
        ]);
    }

    public function test_transaction_detail_returns_404_for_invalid_id(): void
    {
        $this->actingAs($this->user)
            ->get('/transactions/nonexistent-uuid')
            ->assertNotFound();
    }
}
