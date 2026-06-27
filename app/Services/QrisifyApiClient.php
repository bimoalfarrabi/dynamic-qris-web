<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class QrisifyApiClient
{
    private string $baseUrl;

    private string $apiKey;

    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('qrisify.base_url'), '/');
        $this->apiKey = config('qrisify.api_key');
        $this->timeout = (int) config('qrisify.timeout', 30);
    }

    /**
     * Create a new transaction on QRIS-ify.
     *
     * @param  array  $payload  {amount, external_id?, expiry_seconds?}
     * @return array QRIS-ify response data
     *
     * @throws RuntimeException on API error
     */
    public function createTransaction(array $payload): array
    {
        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
            ->timeout($this->timeout)
            ->post("{$this->baseUrl}/api/v1/transactions", $payload);

        if ($response->failed()) {
            Log::error('QRIS-ify create transaction failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload,
            ]);

            throw new RuntimeException(
                "QRIS-ify API error ({$response->status()}): ".$response->body(),
                $response->status()
            );
        }

        return $response->json('data', $response->json());
    }

    /**
     * Fetch QR code image for a transaction from QRIS-ify.
     *
     * @return array {content: binary, content_type: string}
     */
    public function getQrImage(string $qrisifyTransactionId): array
    {
        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'Accept' => 'image/png',
        ])
            ->timeout($this->timeout)
            ->get("{$this->baseUrl}/api/v1/transactions/{$qrisifyTransactionId}/qr");

        if ($response->failed()) {
            Log::error('QRIS-ify get QR image failed', [
                'status' => $response->status(),
                'transaction_id' => $qrisifyTransactionId,
            ]);

            throw new RuntimeException(
                "QRIS-ify QR image error ({$response->status()})",
                $response->status()
            );
        }

        return [
            'content' => $response->body(),
            'content_type' => $response->header('Content-Type', 'image/png'),
        ];
    }

    /**
     * Ping QRIS-ify API and return connection details for diagnostics.
     *
     * @return array{ok: bool, status_code: int|null, response_time_ms: int, error: string|null}
     */
    public function ping(): array
    {
        $start = hrtime(true);
        try {
            // ponytail: /api/platform-stats is public + no auth, lightest valid ping
            $response = Http::withHeaders(['Accept' => 'application/json'])
                ->timeout(30)
                ->get("{$this->baseUrl}/api/platform-stats");

            $ms = (int) round((hrtime(true) - $start) / 1_000_000);

            return [
                'ok' => $response->successful(),
                'status_code' => $response->status(),
                'response_time_ms' => $ms,
                'error' => $response->successful() ? null : $response->body(),
            ];
        } catch (\Throwable $e) {
            $ms = (int) round((hrtime(true) - $start) / 1_000_000);

            return [
                'ok' => false,
                'status_code' => null,
                'response_time_ms' => $ms,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify HMAC-SHA256 webhook signature from QRIS-ify.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = config('qrisify.webhook_secret');

        if ($secret === '' || $signature === '') {
            return false;
        }

        $computed = hash_hmac('sha256', $payload, $secret);

        return hash_equals($computed, $signature);
    }
}
