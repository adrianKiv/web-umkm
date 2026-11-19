<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rekomendasi (service Go, eksperimen skripsi)
    |--------------------------------------------------------------------------
    |
    | engine:
    |   php    -> 100% kode lama, service Go tidak dipanggil (default, aman)
    |   shadow -> hitung keduanya, tetap pakai hasil PHP, selisih dicatat ke log
    |   go     -> pakai hasil service Go, fallback ke PHP kalau service gagal
    |
    */

    'rekomendasi' => [
        'url' => env('REKOMENDASI_URL', 'http://127.0.0.1:8081'),
        'key' => env('REKOMENDASI_API_KEY'),
        'engine' => env('REKOMENDASI_ENGINE', 'php'),
        'timeout' => (float) env('REKOMENDASI_TIMEOUT', 0.8),
    ],

];
