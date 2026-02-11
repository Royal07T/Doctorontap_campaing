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

    'vonage' => [
        // SMS Configuration - Choose one method:
        // Method 1: Legacy SMS API (simpler, uses API key/secret)
        'api_key' => env('VONAGE_API_KEY') ?: env('VONAGE_KEY'),
        'api_secret' => env('VONAGE_API_SECRET') ?: env('VONAGE_SECRET'),
        'key' => env('VONAGE_KEY') ?: env('VONAGE_API_KEY'), // Alias for backward compatibility
        'secret' => env('VONAGE_SECRET') ?: env('VONAGE_API_SECRET'), // Alias for backward compatibility
        
        // Method 2: Messages API (newer, uses Application with JWT)
        'application_id' => env('VONAGE_APPLICATION_ID', '250782187688'), // DoctorOnTap LLC Application ID
        'private_key_path' => env('VONAGE_PRIVATE_KEY_PATH'), // Path to private key file
        'private_key' => env('VONAGE_PRIVATE_KEY'), // Or inline private key (newlines as \n)
        
        // Common settings
        'brand_name' => env('VONAGE_BRAND_NAME', 'DoctorOnTap'),
        'sms_from' => env('VONAGE_SMS_FROM') ?: env('VONAGE_BRAND_NAME', 'DoctorOnTap'),
        'api_method' => env('VONAGE_API_METHOD', 'legacy'), // 'legacy' or 'messages'
        'enabled' => env('VONAGE_ENABLED', false),
        'messages_sandbox' => env('VONAGE_WHATSAPP_SANDBOX', false),
        
        // WhatsApp Configuration (requires Messages API)
        'whatsapp_enabled' => env('VONAGE_WHATSAPP_ENABLED', false),
        'whatsapp_number' => env('VONAGE_WHATSAPP_NUMBER', '405228299348572'), // DoctorOnTap LLC WhatsApp Business Number
        'whatsapp_id' => env('VONAGE_WHATSAPP_ID'), // WhatsApp Business Number ID (for Messages API 'from' parameter)
        'whatsapp' => [
            'from_phone_number' => env('WHATSAPP_PHONE_NUMBER') ?: env('VONAGE_WHATSAPP_NUMBER', '405228299348572'),
            'business_number_id' => env('VONAGE_WHATSAPP_ID'), // WhatsApp Business Number ID (preferred for Messages API)
        ],
        
        // Voice API Configuration
        'voice_enabled' => env('VONAGE_VOICE_ENABLED', false),
        'voice_number' => env('VONAGE_VOICE_NUMBER'), // Your Vonage phone number for outbound calls
        'voice_webhook_url' => env('VONAGE_VOICE_WEBHOOK_URL'), // Base URL for voice webhooks
        
        // MMS Configuration (for SMS with media)
        'mms_number' => env('VONAGE_MMS_NUMBER'), // Your Vonage phone number for MMS (can use same as voice_number)
        
        // Video API Configuration (for in-app video consultations)
        // NOTE: Video API uses separate credentials from Messages API
        'video_enabled' => env('VONAGE_VIDEO_ENABLED', false),
        'video_api_key' => env('VONAGE_VIDEO_API_KEY'), // Separate API key for Video (optional, defaults to main API_KEY)
        'video_api_secret' => env('VONAGE_VIDEO_API_SECRET'), // Separate API secret for Video (optional, defaults to main API_SECRET)
        'video_location' => env('VONAGE_VIDEO_LOCATION', 'us'), // Data center location: us, eu, ap, etc.
        'video_timeout' => env('VONAGE_VIDEO_TIMEOUT', 30), // Timeout in seconds
        
        // Conversations API Configuration (for in-app chat consultations)
        'conversation_enabled' => env('VONAGE_CONVERSATION_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Provider Selection
    |--------------------------------------------------------------------------
    |
    | Choose which SMS provider to use: 'termii' or 'vonage'
    | The selected provider must be enabled in its respective config above.
    |
    */
    'sms_provider' => env('SMS_PROVIDER', 'vonage'), // Options: 'termii' or 'vonage'

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Provider Selection
    |--------------------------------------------------------------------------
    |
    | Choose which WhatsApp provider to use: 'termii' or 'vonage'
    | The selected provider must be enabled in its respective config above.
    | Note: Vonage WhatsApp requires Messages API (not Legacy SMS API)
    |
    */
    'whatsapp_provider' => env('WHATSAPP_PROVIDER', 'vonage'), // Options: 'termii' or 'vonage'

    'pusher' => [
        'app_id' => env('PUSHER_APP_ID'),
        'key' => env('PUSHER_APP_KEY'),
        'secret' => env('PUSHER_APP_SECRET'),
        'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
        'host' => env('PUSHER_HOST') ?: 'api-'.env('PUSHER_APP_CLUSTER', 'mt1').'.pusher.com',
        'port' => env('PUSHER_PORT', 443),
        'scheme' => env('PUSHER_SCHEME', 'https'),
        'encrypted' => true,
        'useTLS' => env('PUSHER_SCHEME', 'https') === 'https',
    ],

    'pusher_beams' => [
        'instance_id' => env('PUSHER_BEAMS_INSTANCE_ID'),
        'secret_key' => env('PUSHER_BEAMS_SECRET_KEY'),
        'enabled' => env('PUSHER_BEAMS_ENABLED', false),
        'webhook_username' => env('PUSHER_BEAMS_WEBHOOK_USERNAME'),
        'webhook_password' => env('PUSHER_BEAMS_WEBHOOK_PASSWORD'),
    ],

];
