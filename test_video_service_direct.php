<?php

/**
 * Direct Video Service Test
 * Tests the service initialization and session creation
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🎥 Direct Video Service Test\n";
echo "============================\n\n";

try {
    $videoService = new \App\Services\VonageVideoService();
    
    echo "Service Status:\n";
    echo "---------------\n";
    $status = $videoService->getStatus();
    
    foreach ($status as $key => $value) {
        $displayValue = is_bool($value) ? ($value ? '✅ Yes' : '❌ No') : $value;
        echo "  " . ucwords(str_replace('_', ' ', $key)) . ": {$displayValue}\n";
    }
    
    echo "\n";
    
    if (!$status['enabled']) {
        echo "❌ Service is disabled. Check VONAGE_VIDEO_ENABLED in .env\n";
        exit(1);
    }
    
    if (!$status['initialized']) {
        echo "❌ Service failed to initialize.\n";
        echo "\n";
        echo "Common Issues:\n";
        echo "  1. VONAGE_VIDEO_API_SECRET is set to a file path (should be actual secret value)\n";
        echo "  2. API credentials are invalid\n";
        echo "  3. Network connectivity issues\n";
        echo "\n";
        echo "Check the Laravel logs for detailed error messages:\n";
        echo "  tail -f storage/logs/laravel.log | grep -i video\n";
        exit(1);
    }
    
    echo "✅ Service is properly initialized!\n";
    echo "\n";
    
    // Test session creation
    echo "Testing Session Creation:\n";
    echo "------------------------\n";
    
    $sessionResult = $videoService->createSession();
    
    if ($sessionResult['success']) {
        echo "✅ Session created successfully!\n";
        echo "   Session ID: " . ($sessionResult['session_id'] ?? 'N/A') . "\n";
        
        $sessionId = $sessionResult['session_id'] ?? null;
        
        if ($sessionId) {
            echo "\n";
            echo "Testing Token Generation:\n";
            echo "------------------------\n";
            
            $tokenResult = $videoService->generateToken($sessionId, \OpenTok\Role::PUBLISHER, 'Test User', 3600);
            
            if ($tokenResult['success']) {
                echo "✅ Token generated successfully!\n";
                echo "   Token (first 50 chars): " . substr($tokenResult['token'] ?? '', 0, 50) . "...\n";
                echo "   Expires in: " . ($tokenResult['expires_in'] ?? 'N/A') . " seconds\n";
                
                echo "\n";
                echo "✅✅✅ ALL TESTS PASSED! ✅✅✅\n";
                echo "\n";
                echo "Your Video API is working correctly!\n";
                echo "You can now use the service in your application.\n";
            } else {
                echo "❌ Failed to generate token\n";
                echo "   Error: " . ($tokenResult['message'] ?? 'Unknown error') . "\n";
                exit(1);
            }
        }
    } else {
        echo "❌ Failed to create session\n";
        echo "   Error: " . ($sessionResult['message'] ?? 'Unknown error') . "\n";
        if (isset($sessionResult['error'])) {
            echo "   Details: " . $sessionResult['error'] . "\n";
        }
        exit(1);
    }
    
} catch (\Exception $e) {
    echo "❌ Exception occurred:\n";
    echo "   " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}

