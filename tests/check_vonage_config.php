<?php

/**
 * Check Vonage Configuration
 * Shows what's configured and what needs to be fixed
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 Vonage Configuration Check\n";
echo "=============================\n\n";

// Check all Vonage services
$services = [
    'SMS' => [
        'enabled' => config('services.vonage.enabled', false),
        'api_key' => config('services.vonage.api_key'),
        'api_secret' => config('services.vonage.api_secret'),
        'method' => config('services.vonage.api_method', 'legacy'),
    ],
    'WhatsApp' => [
        'enabled' => config('services.vonage.whatsapp_enabled', false),
        'application_id' => config('services.vonage.application_id'),
        'whatsapp_number' => config('services.vonage.whatsapp.from_phone_number'),
        'private_key_path' => config('services.vonage.private_key_path'),
        'private_key_set' => !empty(config('services.vonage.private_key_path')) || !empty(config('services.vonage.private_key')),
    ],
    'Voice' => [
        'enabled' => config('services.vonage.voice_enabled', false),
        'application_id' => config('services.vonage.application_id'),
        'voice_number' => config('services.vonage.voice_number'),
        'private_key_set' => !empty(config('services.vonage.private_key_path')) || !empty(config('services.vonage.private_key')),
    ],
    'Video' => [
        'enabled' => config('services.vonage.video_enabled', false),
        'api_key' => config('services.vonage.video_api_key') ?: config('services.vonage.api_key'),
        'api_secret' => config('services.vonage.video_api_secret') ?: config('services.vonage.api_secret'),
        'is_file_path' => false,
    ],
];

foreach ($services as $serviceName => $config) {
    echo "📱 {$serviceName} Service:\n";
    echo str_repeat('-', 40) . "\n";
    
    if ($serviceName === 'Video') {
        $apiSecret = $config['api_secret'];
        $isFilePath = file_exists($apiSecret) || 
                      (is_string($apiSecret) && (str_contains($apiSecret, '/') || str_contains($apiSecret, '\\')));
        
        echo "  Enabled: " . ($config['enabled'] ? '✅ Yes' : '❌ No') . "\n";
        echo "  API Key: " . ($config['api_key'] ? '✅ Set (' . substr($config['api_key'], 0, 8) . '...)' : '❌ Not Set') . "\n";
        
        if ($isFilePath) {
            echo "  API Secret: ❌ Set to FILE PATH (This is the problem!)\n";
            echo "             Current: {$apiSecret}\n";
            echo "             ⚠️  OpenTok needs the actual SECRET VALUE, not a file path\n";
        } else {
            echo "  API Secret: " . ($apiSecret ? '✅ Set (' . substr($apiSecret, 0, 20) . '...)' : '❌ Not Set') . "\n";
        }
    } else {
        echo "  Enabled: " . ($config['enabled'] ? '✅ Yes' : '❌ No') . "\n";
        
        if (isset($config['api_key'])) {
            echo "  API Key: " . ($config['api_key'] ? '✅ Set' : '❌ Not Set') . "\n";
            echo "  API Secret: " . ($config['api_secret'] ? '✅ Set' : '❌ Not Set') . "\n";
        }
        
        if (isset($config['application_id'])) {
            echo "  Application ID: " . ($config['application_id'] ? '✅ Set (' . substr($config['application_id'], 0, 20) . '...)' : '❌ Not Set') . "\n";
        }
        
        if (isset($config['private_key_set'])) {
            echo "  Private Key: " . ($config['private_key_set'] ? '✅ Set' : '❌ Not Set') . "\n";
        }
    }
    
    echo "\n";
}

// Specific Video API issue
$videoSecret = config('services.vonage.video_api_secret') ?: config('services.vonage.api_secret');
$isFilePath = $videoSecret && (file_exists($videoSecret) || 
              (is_string($videoSecret) && (str_contains($videoSecret, '/') || str_contains($videoSecret, '\\'))));

if ($isFilePath) {
    echo "❌ VIDEO API ISSUE DETECTED:\n";
    echo "===========================\n";
    echo "Your VONAGE_VIDEO_API_SECRET is set to a file path:\n";
    echo "  {$videoSecret}\n\n";
    echo "OpenTok Video API requires the ACTUAL SECRET VALUE, not a file path.\n\n";
    echo "🔧 HOW TO FIX:\n";
    echo "1. Go to Vonage Dashboard: https://dashboard.nexmo.com/\n";
    echo "2. Navigate to: Projects → Your Project → Video API\n";
    echo "3. Copy the 'Project Secret' (the actual string value)\n";
    echo "4. Update your .env file:\n";
    echo "   VONAGE_VIDEO_API_SECRET=your_actual_project_secret_here\n";
    echo "   (NOT a file path!)\n\n";
    echo "⚠️  Note: The private key file you have is for:\n";
    echo "   - Messages API (WhatsApp)\n";
    echo "   - Voice API\n";
    echo "   - Conversations API\n";
    echo "   But NOT for Video API (OpenTok)\n\n";
}

echo "✅ Configuration check completed!\n";

