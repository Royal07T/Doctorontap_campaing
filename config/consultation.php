<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Voice Mode Implementation
    |--------------------------------------------------------------------------
    |
    | IMPORTANT: Voice consultations use Vonage Video API in audio-only mode,
    | NOT Vonage Voice API (telephony).
    |
    | When consultation_mode = 'voice':
    | - Uses Vonage Video API with audio-only WebRTC
    | - No PSTN/telephony calls are made
    | - Same infrastructure as video consultations, but without video stream
    |
    | This ensures:
    | - Consistent WebRTC-based communication
    | - No phone number requirements
    | - Lower latency than PSTN
    | - Better quality for in-app consultations
    |
    */
    'voice_mode_implementation' => 'vonage_video_audio_only',

    /*
    |--------------------------------------------------------------------------
    | Consultation Mode Documentation
    |--------------------------------------------------------------------------
    |
    | Available consultation modes:
    |
    | - 'whatsapp': Traditional WhatsApp-based consultation (legacy)
    | - 'voice': Audio-only WebRTC using Vonage Video API
    | - 'video': Video WebRTC using Vonage Video API
    | - 'chat': Text-based chat using Vonage Conversations API
    |
    */
    'modes' => [
        'whatsapp' => [
            'description' => 'WhatsApp-based consultation',
            'uses_vonage' => false,
            'requires_phone' => true,
        ],
        'voice' => [
            'description' => 'Audio-only WebRTC consultation',
            'uses_vonage' => true,
            'vonage_api' => 'video', // Uses Video API, not Voice API
            'requires_phone' => false,
        ],
        'video' => [
            'description' => 'Video WebRTC consultation',
            'uses_vonage' => true,
            'vonage_api' => 'video',
            'requires_phone' => false,
        ],
        'chat' => [
            'description' => 'Text-based chat consultation',
            'uses_vonage' => true,
            'vonage_api' => 'conversations',
            'requires_phone' => false,
        ],
    ],
];

