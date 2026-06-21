<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case Pending = 'PENDING';
    case Success = 'SUCCESS';
    case Expired = 'EXPIRED';
    case Cancelled = 'CANCELLED';

    /**
     * Check if the transaction can be cancelled.
     * Only PENDING transactions are cancellable.
     */
    public function isCancellable(): bool
    {
        return $this === self::Pending;
    }

    /**
     * Check if the transaction is in a terminal state
     * (no further state transitions possible).
     */
    public function isTerminal(): bool
    {
        return in_array($this, [self::Success, self::Expired, self::Cancelled], true);
    }

    /**
     * Get all values as an array (for validation rules).
     *
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
