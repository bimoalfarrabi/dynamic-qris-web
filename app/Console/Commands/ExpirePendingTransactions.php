<?php

namespace App\Console\Commands;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpirePendingTransactions extends Command
{
    protected $signature = 'app:expire-pending-transactions';

    protected $description = 'Expire pending transactions that have passed their expires_at timestamp';

    public function handle(): int
    {
        $count = Transaction::query()
            ->where('status', TransactionStatus::Pending)
            ->where('expires_at', '<', now())
            ->update([
                'status' => TransactionStatus::Expired,
                'updated_at' => now(),
            ]);

        if ($count > 0) {
            Log::info('Auto-expired pending transactions', ['count' => $count]);
            $this->info("Expired {$count} pending transaction(s).");
        } else {
            $this->info('No pending transactions to expire.');
        }

        return self::SUCCESS;
    }
}
