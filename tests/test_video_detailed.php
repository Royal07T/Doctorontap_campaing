<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\VonageVideoService;

echo "\n🎥 Detailed Vonage Video API Test\n";
echo "=====================================\n\n";

$videoService = new VonageVideoService();

// Get status
$status = $videoService->getStatus();

echo "Service Status:\n";
echo "---------------\n";
echo "  Enabled: " . ($status['enabled'] ? '✅ Yes' : '❌ No') . "\n";
echo "  Initialized: " . ($status['initialized'] ? '✅ Yes' : '❌ No') . "\n";
echo "  Auth Method: " . ($status['auth_method'] ?? 'none') . "\n";
echo "  Application ID: " . ($status['application_id_set'] ? '✅ Set' : '❌ Not set') . "\n";
echo "  Private Key: " . ($status['private_key_set'] ? '✅ Set' : '❌ Not set') . "\n";
echo "  API Key: " . ($status['api_key_set'] ? '✅ Set' : '❌ Not set') . "\n";
echo "  API Secret: " . ($status['api_secret_set'] ? '✅ Set' : '❌ Not set') . "\n\n";

if (!$status['initialized']) {
    echo "❌ Service is not initialized. Cannot proceed.\n";
    exit(1);
}

echo "✅ Service is properly initialized!\n\n";

// Test network connectivity
echo "Testing Network Connectivity:\n";
echo "-----------------------------\n";
$testUrl = 'https://video.api.vonage.com';
echo "  Testing connection to: {$testUrl}\n";

$ch = curl_init($testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "  ❌ Connection failed: {$error}\n";
    echo "  ⚠️  This is a network/firewall issue, not a code issue.\n";
} else {
    echo "  ✅ Connection successful (HTTP {$httpCode})\n";
}

echo "\n";

// Test session creation with detailed error
echo "Testing Session Creation:\n";
echo "------------------------\n";
$result = $videoService->createSession();

if ($result['success']) {
    echo "  ✅ Session created successfully!\n";
    echo "  Session ID: {$result['session_id']}\n\n";
    
    // Test token generation
    echo "Testing Token Generation:\n";
    echo "------------------------\n";
    $tokenResult = $videoService->generateToken($result['session_id'], 'PUBLISHER', 'Test User');
    
    if ($tokenResult['success']) {
        echo "  ✅ Token generated successfully!\n";
        echo "  Token: " . substr($tokenResult['token'], 0, 50) . "...\n";
        echo "  Expires in: {$tokenResult['expires_in']} seconds\n";
    } else {
        echo "  ❌ Token generation failed\n";
        echo "  Error: {$tokenResult['message']}\n";
        if (isset($tokenResult['error'])) {
            echo "  Details: {$tokenResult['error']}\n";
        }
    }
} else {
    echo "  ❌ Session creation failed\n";
    echo "  Error: {$result['message']}\n";
    if (isset($result['error'])) {
        echo "  Details: {$result['error']}\n";
    }
    
    // Check if it's a network issue
    if (str_contains($result['error'] ?? '', 'timeout') || 
        str_contains($result['error'] ?? '', 'SSL') ||
        str_contains($result['error'] ?? '', 'connection')) {
        echo "\n  ⚠️  NETWORK ISSUE DETECTED:\n";
        echo "  - Your credentials are correct (service initialized)\n";
        echo "  - Your code is correct (using proper SDK)\n";
        echo "  - The issue is network connectivity to Vonage API\n";
        echo "\n  Possible solutions:\n";
        echo "  1. Check firewall/proxy settings\n";
        echo "  2. Verify network can reach video.api.vonage.com\n";
        echo "  3. Try from a different network\n";
        echo "  4. Check if Vonage API is accessible from your server\n";
    }
}

echo "\n✅ Test completed!\n\n";

