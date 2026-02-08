<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Credentials
    |--------------------------------------------------------------------------
    |
    | If you're using API credentials, change these settings. Get your
    | credentials from https://dashboard.nexmo.com | 'Settings'.
    |
    */

    'api_key'    => function_exists('env') ? env('VONAGE_KEY', '') : '',
    'api_secret' => function_exists('env') ? env('VONAGE_SECRET', '') : '',

    /*
    |--------------------------------------------------------------------------
    | Signature Secret
    |--------------------------------------------------------------------------
    |
    | If you're using a signature secret, use this section. This can be used
    | without an `api_secret` for some APIs, as well as with an `api_secret`
    | for all APIs.
    |
    */

    'signature_secret' => function_exists('env') ? env('VONAGE_SIGNATURE_SECRET', '') : '',

    /*
    |--------------------------------------------------------------------------
    | Private Key
    |--------------------------------------------------------------------------
    |
    | Private keys are used to generate JWTs for authentication. Generation is
    | handled by the library. JWTs are required for newer APIs, such as voice
    | and media
    |
    */

    'private_key' => function_exists('env') ? env('VONAGE_PRIVATE_KEY', '') : '',
    'application_id' => function_exists('env') ? env('VONAGE_APPLICATION_ID', '250782187688') : '250782187688', // DoctorOnTap LLC Application ID

    /*
    |--------------------------------------------------------------------------
    | Application Identifiers
    |--------------------------------------------------------------------------
    |
    | Add an application name and version here to identify your application when
    | making API calls
    |
    */

    'app' => ['name' => function_exists('env') ? env('VONAGE_APP_NAME', 'VonageLaravel') : 'VonageLaravel',
              'version' => function_exists('env') ? env('VONAGE_APP_VERSION', '0.0.1') : '0.0.1'],

    /*
    |--------------------------------------------------------------------------
    | Client Override
    |--------------------------------------------------------------------------
    |
    | In the event you need to use this with vonage/client-core, this can be set
    | to provide a custom HTTP client.
    |
    */

    'http_client' => function_exists('env') ? env('VONAGE_HTTP_CLIENT', '') : '',

    /*
    |--------------------------------------------------------------------------
    | WhatsApp & Communication Settings
    |--------------------------------------------------------------------------
    |
    | Custom settings for DoctorOnTap integration.
    |
    */

    'whatsapp_enabled' => function_exists('env') ? env('VONAGE_WHATSAPP_ENABLED', false) : false,
    'whatsapp_number'  => function_exists('env') ? env('VONAGE_WHATSAPP_NUMBER', '405228299348572') : '405228299348572', // DoctorOnTap LLC WhatsApp Number
    'whatsapp_sandbox' => function_exists('env') ? env('VONAGE_WHATSAPP_SANDBOX', false) : false,

    'voice_enabled'    => function_exists('env') ? env('VONAGE_VOICE_ENABLED', false) : false,
    'voice_number'     => function_exists('env') ? env('VONAGE_VOICE_NUMBER', '') : '',

    'brand_name'       => function_exists('env') ? env('VONAGE_BRAND_NAME', 'DoctorOnTap') : 'DoctorOnTap',

    /*
    |--------------------------------------------------------------------------
    | Video API (OpenTok) Settings
    |--------------------------------------------------------------------------
    */

    'video_enabled'    => function_exists('env') ? env('VONAGE_VIDEO_ENABLED', false) : false,
    'video_api_key'    => function_exists('env') ? env('VONAGE_VIDEO_API_KEY', '') : '',
    'video_api_secret' => function_exists('env') ? env('VONAGE_VIDEO_API_SECRET', '') : '',
    'video_location'   => function_exists('env') ? env('VONAGE_VIDEO_LOCATION', 'auto') : 'auto',
];