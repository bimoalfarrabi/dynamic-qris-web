<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QrisifyApiClient;
use Illuminate\Http\JsonResponse;

class StatusController extends Controller
{
    public function __construct(private QrisifyApiClient $qrisify) {}

    /**
     * Check QRIS-ify API connectivity.
     *
     * GET /api/status/qrisify
     */
    public function qrisify(): JsonResponse
    {
        $result = $this->qrisify->ping();

        return response()->json([
            'ok' => $result['ok'],
            'status_code' => $result['status_code'],
            'response_time_ms' => $result['response_time_ms'],
            'error' => $result['error'],
            'checked_at' => now()->toIso8601String(),
        ]);
    }
}
