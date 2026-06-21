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
            'Authorization' => 'Bearer '.$this->apiKey,
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
            'Authorization' => 'Bearer '.$this->apiKey,
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
