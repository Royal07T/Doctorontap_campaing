<?php

/**
 * Vonage Video Service Diagnostic Script
 * 
 * This script helps diagnose OpenTok/Video API configuration issues
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 Vonage Video Service Diagnostic\n";
echo "===================================\n\n";

// Check configuration
echo "Configuration Check:\n";
echo "-------------------\n";

$videoEnabled = config('vonage.video_enabled', false);
$videoApiKey = config('vonage.video_api_key');
$videoApiSecret = config('vonage.video_api_secret');
$mainApiKey = config('vonage.api_key');
$mainApiSecret = config('vonage.api_secret');

echo "  Video Enabled: " . ($videoEnabled ? '✅ Yes' : '❌ No') . "\n";
echo "  Video API Key: " . ($videoApiKey ? '✅ Set (' . substr($videoApiKey, 0, 8) . '...)' : '❌ Not set') . "\n";
echo "  Video API Secret: " . ($videoApiSecret ? '✅ Set (' . (strlen($videoApiSecret) > 20 ? substr($videoApiSecret, 0, 20) . '...' : '***') . ')' : '❌ Not set') . "\n";
echo "  Main API Key: " . ($mainApiKey ? '✅ Set (' . substr($mainApiKey, 0, 8) . '...)' : '❌ Not set') . "\n";
echo "  Main API Secret: " . ($mainApiSecret ? '✅ Set' : '❌ Not set') . "\n";

echo "\n";

// Check if secret looks like a file path
if ($videoApiSecret) {
    echo "API Secret Analysis:\n";
    echo "-------------------\n";
    $isFilePath = file_exists($videoApiSecret) || str_contains($videoApiSecret, '/') || str_contains($videoApiSecret, '\\');
    echo "  Looks like file path: " . ($isFilePath ? '⚠️  YES (This is the problem!)' : '✅ No') . "\n";
    
    if ($isFilePath) {
        echo "\n  ❌ ERROR: Your VONAGE_VIDEO_API_SECRET is set to a file path.\n";
        echo "     OpenTok requires the actual API secret VALUE, not a file path.\n";
        echo "     Fix: Set VONAGE_VIDEO_API_SECRET to your actual OpenTok API secret value.\n";
    } else {
        echo "  ✅ API Secret appears to be a value (not a file path)\n";
    }
}

echo "\n";

// Try to initialize service
if ($videoEnabled) {
    echo "Service Initialization Test:\n";
    echo "---------------------------\n";
    
    try {
        $videoService = new \App\Services\VonageVideoService();
        $status = $videoService->getStatus();
        
        echo "  Enabled: " . ($status['enabled'] ? '✅ Yes' : '❌ No') . "\n";
        echo "  Initialized: " . ($status['initialized'] ? '✅ Yes' : '❌ No') . "\n";
        echo "  API Key Set: " . ($status['api_key_set'] ? '✅ Yes' : '❌ No') . "\n";
        echo "  API Secret Set: " . ($status['api_secret_set'] ? '✅ Yes' : '❌ No') . "\n";
        
        if ($status['initialized']) {
            echo "\n  ✅ Service is properly initialized!\n";
            
            // Try creating a session
            echo "\n  Testing session creation...\n";
            $result = $videoService->createSession();
            
            if ($result['success']) {
                echo "  ✅ Session created successfully!\n";
                echo "     Session ID: " . ($result['session_id'] ?? 'N/A') . "\n";
            } else {
                echo "  ❌ Failed to create session\n";
                echo "     Error: " . ($result['message'] ?? 'Unknown error') . "\n";
            }
        } else {
            echo "\n  ❌ Service failed to initialize\n";
            echo "     Check the error logs for details\n";
        }
    } catch (\Exception $e) {
        echo "  ❌ Exception: " . $e->getMessage() . "\n";
    }
} else {
    echo "⚠️  Video service is disabled. Set VONAGE_VIDEO_ENABLED=true in .env\n";
}

echo "\n";
echo "✅ Diagnostic completed!\n";

