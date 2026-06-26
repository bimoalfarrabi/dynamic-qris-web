<?php

namespace App\Http\Controllers;

use App\Services\QrisifyApiClient;
use Inertia\Inertia;

class StatusController extends Controller
{
    public function __construct(private QrisifyApiClient $qrisify) {}

    public function index(): \Inertia\Response
    {
        $result = $this->qrisify->ping();

        return Inertia::render('Status', [
            'qrisify' => [
                'ok' => $result['ok'],
                'status_code' => $result['status_code'],
                'response_time_ms' => $result['response_time_ms'],
                'error' => $result['error'],
                'checked_at' => now()->toIso8601String(),
            ],
            'base_url' => config('qrisify.base_url'),
        ]);
    }
}
