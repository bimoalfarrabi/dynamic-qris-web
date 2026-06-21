<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\WebhookStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'qrisify_transaction_id',
        'external_id',
        'amount_requested',
        'unique_code',
        'amount_total',
        'status',
        'qris_string',
        'webhook_status',
        'payment_provider',
        'expires_at',
        'paid_at',
        'cancelled_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'amount_requested' => 'integer',
        'unique_code' => 'integer',
        'amount_total' => 'integer',
        'status' => TransactionStatus::class,
        'webhook_status' => WebhookStatus::class,
    ];

    // ── Scopes ──────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', TransactionStatus::Pending);
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', TransactionStatus::Success);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', TransactionStatus::Cancelled);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', TransactionStatus::Expired);
    }

    // ── Helpers ─────────────────────────────────────────────

    public function isCancellable(): bool
    {
        return $this->status === TransactionStatus::Pending;
    }

    public function isCancelled(): bool
    {
        return $this->status === TransactionStatus::Cancelled;
    }
}
