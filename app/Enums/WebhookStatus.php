<?php

namespace App\Enums;

enum WebhookStatus: string
{
    case Unsent = 'UNSENT';
    case SentSuccess = 'SENT_SUCCESS';
    case Failed = 'FAILED';

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
