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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'korapay' => [
        'secret_key' => env('KORAPAY_SECRET_KEY'),
        'public_key' => env('KORAPAY_PUBLIC_KEY'),
        'encryption_key' => env('KORAPAY_ENCRYPTION_KEY'),
        'api_url' => env('KORAPAY_API_URL', 'https://api.korapay.com/merchant/api/v1'),
        'enforce_webhook_signature' => env('KORAPAY_ENFORCE_WEBHOOK_SIGNATURE', true),
    ],

    'termii' => [
        // SMS Configuration
        'api_key' => env('TERMII_API_KEY'),
        'secret_key' => env('TERMII_SECRET_KEY'),
        'sender_id' => env('TERMII_SENDER_ID', 'DoctorOnTap'),
        'base_url' => env('TERMII_BASE_URL', 'https://v3.api.termii.com'),
        'channel' => env('TERMII_CHANNEL', 'generic'),
        'enabled' => env('TERMII_ENABLED', true),
        
        // WhatsApp Configuration
        'whatsapp_device_id' => env('TERMII_WHATSAPP_DEVICE_ID'),
        'whatsapp_enabled' => env('TERMII_WHATSAPP_ENABLED', false),
    ],

];
