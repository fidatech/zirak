<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'telegram' => [
        // From @BotFather. Leave empty to hide Telegram login and the bot.
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'bot_username' => env('TELEGRAM_BOT_USERNAME'), // without @
        // Random string; Telegram sends it back in a header so only Telegram can call the webhook.
        'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
    ],

    'bunny' => [
        // Bunny Stream video library ID (videos are embedded by their video ID)
        'library_id' => env('BUNNY_LIBRARY_ID'),
        // Library API key (Stream → library → API): creates videos and signs uploads. Server only.
        'api_key' => env('BUNNY_API_KEY'),
        // Library read-only API key: verifies Bunny's webhook signatures.
        'read_only_key' => env('BUNNY_READ_ONLY_KEY'),
    ],

    'hesabpay' => [
        // fake = local demo checkout, sandbox = HesabPay test API, live = real payments
        'driver' => env('HESABPAY_DRIVER', 'fake'),
        'api_key' => env('HESABPAY_API_KEY'),
        'base_url' => env('HESABPAY_BASE_URL', env('HESABPAY_DRIVER') === 'live' ? 'https://api.hesab.com' : 'https://api-sandbox.hesab.com'),
    ],

];
