<?php

return [

    'base_url' => env('QRISIFY_BASE_URL', 'https://api.qris-ify.com'),

    'api_key' => env('QRISIFY_API_KEY', ''),

    'webhook_secret' => env('QRISIFY_WEBHOOK_SECRET', ''),

    /*
    |----------------------------------------------------------------------
    | Webhook signature header name
    | QRIS-ify mengirim HMAC-SHA256 signature di header ini
    |----------------------------------------------------------------------
    */
    'webhook_signature_header' => env('QRISIFY_WEBHOOK_SIGNATURE_HEADER', 'X-Qrisify-Signature'),

    /*
    |----------------------------------------------------------------------
    | Request timeout (detik) untuk panggilan API ke QRIS-ify
    |----------------------------------------------------------------------
    */
    'timeout' => env('QRISIFY_TIMEOUT', 30),

];
