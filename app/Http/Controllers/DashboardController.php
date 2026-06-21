<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Dashboard page with stats cards.
     */
    public function index(): \Inertia\Response
    {
        $stats = $this->getStats();

        $recentTransactions = Transaction::query()
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    /**
     * Transactions list page with search, filter, pagination.
     */
    public function transactions(Request $request): \Inertia\Response
    {
        $validated = $request->validate([
            'search' => 'sometimes|string|max:255',
            'status' => ['sometimes', Rule::in(TransactionStatus::values())],
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $query = Transaction::query()->latest();

        if (isset($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('external_id', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhere('qrisify_transaction_id', 'like', "%{$search}%");
            });
        }

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['date_from'])) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }

        if (isset($validated['date_to'])) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }

        $transactions = $query->paginate($validated['per_page'] ?? 15)->withQueryString();

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $validated,
        ]);
    }

    /**
     * Transaction detail page.
     */
    public function show(string $id): \Inertia\Response
    {
        $transaction = Transaction::findOrFail($id);

        // Auto-expire if past expiry time
        if ($transaction->status === TransactionStatus::Pending && $transaction->expires_at->isPast()) {
            $transaction->update(['status' => TransactionStatus::Expired]);
            $transaction->refresh();
        }

        return Inertia::render('Transactions/Show', [
            'transaction' => $transaction,
        ]);
    }

    /**
     * Calculate dashboard stats.
     */
    private function getStats(): array
    {
        // Aggregate query for today: 1 query, returns total + success counts + revenue
        $todayStats = Transaction::query()
            ->whereDate('created_at', today())
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as success,
                SUM(CASE WHEN status = ? THEN amount_total ELSE 0 END) as revenue
            ', [TransactionStatus::Success->value, TransactionStatus::Success->value])
            ->first();

        // Aggregate query for this week: same pattern
        $weekStats = Transaction::query()
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as success,
                SUM(CASE WHEN status = ? THEN amount_total ELSE 0 END) as revenue
            ', [TransactionStatus::Success->value, TransactionStatus::Success->value])
            ->first();

        $pendingCount = Transaction::where('status', TransactionStatus::Pending)->count();

        $todayTotal = (int) $todayStats->total;
        $todaySuccess = (int) $todayStats->success;
        $successRate = $todayTotal > 0 ? round(($todaySuccess / $todayTotal) * 100, 1) : 0;

        return [
            'today' => [
                'count' => $todayTotal,
                'success' => $todaySuccess,
                'revenue' => (int) $todayStats->revenue,
            ],
            'this_week' => [
                'count' => (int) $weekStats->total,
                'success' => (int) $weekStats->success,
                'revenue' => (int) $weekStats->revenue,
            ],
            'success_rate' => $successRate,
            'pending' => $pendingCount,
        ];
    }
}
