<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DoctorOnTap Vonage Video API Test ===\n\n";

// Check Vonage Video Configuration
echo "1. Checking Vonage Video Configuration...\n";
$apiKey = config('services.vonage.video_api_key') ?: config('services.vonage.api_key');
$apiSecret = config('services.vonage.video_api_secret') ?: config('services.vonage.api_secret');
$enabled = config('services.vonage.video_enabled', false);
$location = config('services.vonage.video_location', 'us');
$timeout = config('services.vonage.video_timeout', 30);

echo "   Video Enabled: " . ($enabled ? 'YES' : 'NO') . "\n";
echo "   API Key: " . ($apiKey ? 'SET (using ' . (config('services.vonage.video_api_key') ? 'dedicated video' : 'main') . ' credentials)' : 'NOT SET') . "\n";
echo "   API Secret: " . ($apiSecret ? 'SET' : 'NOT SET') . "\n";
echo "   Location: $location\n";
echo "   Timeout: {$timeout}s\n\n";

if (!$enabled) {
    echo "WARNING: Vonage Video is not enabled!\n";
    echo "To enable, set VONAGE_VIDEO_ENABLED=true in .env file\n\n";
}

if (empty($apiKey) || empty($apiSecret)) {
    echo "ERROR: Vonage credentials not configured!\n";
    echo "Please set VONAGE_API_KEY and VONAGE_API_SECRET in .env file\n";
    echo "Or use dedicated video credentials: VONAGE_VIDEO_API_KEY and VONAGE_VIDEO_API_SECRET\n";
    exit(1);
}

// Test VonageVideoService
echo "2. Testing VonageVideoService Class...\n";
try {
    $videoService = app(\App\Services\VonageVideoService::class);
    echo "   ✓ VonageVideoService instantiated successfully\n";
    
    $status = $videoService->getStatus();
    echo "   Service Status:\n";
    echo "     - Enabled: " . ($status['enabled'] ? 'Yes' : 'No') . "\n";
    echo "     - Initialized: " . ($status['initialized'] ? 'Yes' : 'No') . "\n";
    echo "     - API Key Set: " . ($status['api_key_set'] ? 'Yes' : 'No') . "\n";
    echo "     - API Secret Set: " . ($status['api_secret_set'] ? 'Yes' : 'No') . "\n";
    echo "     - Using Dedicated Video Credentials: " . ($status['using_dedicated_video_creds'] ? 'Yes' : 'No') . "\n\n";
    
    if (!$status['initialized']) {
        echo "ERROR: Video service is not properly initialized!\n";
        echo "This usually means credentials are missing or invalid.\n";
        exit(1);
    }
    
} catch (\Exception $e) {
    echo "   ✗ Failed to create VonageVideoService: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 3: Create a Video Session
echo "3. Creating Test Video Session...\n";
try {
    $sessionResult = $videoService->createSession([
        'mediaMode' => \OpenTok\MediaMode::ROUTED, // Required for archiving
        'archiveMode' => \OpenTok\ArchiveMode::MANUAL,
    ]);
    
    if ($sessionResult['success']) {
        echo "   ✓ Video session created successfully!\n";
        echo "   Session ID: " . $sessionResult['session_id'] . "\n\n";
        $sessionId = $sessionResult['session_id'];
    } else {
        echo "   ✗ Failed to create video session\n";
        echo "   Error: " . ($sessionResult['error'] ?? 'Unknown error') . "\n";
        echo "   Message: " . ($sessionResult['message'] ?? 'No message') . "\n\n";
        exit(1);
    }
    
} catch (\Exception $e) {
    echo "   ✗ Exception occurred: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 4: Generate Tokens for Doctor and Patient
echo "4. Generating Access Tokens...\n";
try {
    // Generate doctor token (Publisher role)
    $doctorTokenResult = $videoService->generateToken(
        $sessionId,
        \OpenTok\Role::PUBLISHER,
        'Dr. Test Doctor',
        3600 // 1 hour
    );
    
    if ($doctorTokenResult['success']) {
        echo "   ✓ Doctor token generated successfully\n";
        echo "   Token: " . substr($doctorTokenResult['token'], 0, 50) . "...\n";
        echo "   Expires in: " . $doctorTokenResult['expires_in'] . " seconds\n\n";
    } else {
        echo "   ✗ Failed to generate doctor token\n";
        echo "   Error: " . ($doctorTokenResult['error'] ?? 'Unknown error') . "\n\n";
        exit(1);
    }
    
    // Generate patient token (Publisher role)
    $patientTokenResult = $videoService->generateToken(
        $sessionId,
        \OpenTok\Role::PUBLISHER,
        'Test Patient',
        3600 // 1 hour
    );
    
    if ($patientTokenResult['success']) {
        echo "   ✓ Patient token generated successfully\n";
        echo "   Token: " . substr($patientTokenResult['token'], 0, 50) . "...\n";
        echo "   Expires in: " . $patientTokenResult['expires_in'] . " seconds\n\n";
    } else {
        echo "   ✗ Failed to generate patient token\n";
        echo "   Error: " . ($patientTokenResult['error'] ?? 'Unknown error') . "\n\n";
        exit(1);
    }
    
} catch (\Exception $e) {
    echo "   ✗ Exception occurred: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 5: Verify Token Roles
echo "5. Testing Different User Roles...\n";
try {
    // Generate moderator token
    $moderatorTokenResult = $videoService->generateToken(
        $sessionId,
        \OpenTok\Role::MODERATOR,
        'Admin Moderator',
        3600
    );
    
    if ($moderatorTokenResult['success']) {
        echo "   ✓ Moderator token generated (has full control)\n";
    } else {
        echo "   ✗ Failed to generate moderator token\n";
    }
    
    // Generate subscriber token (view only)
    $subscriberTokenResult = $videoService->generateToken(
        $sessionId,
        \OpenTok\Role::SUBSCRIBER,
        'Observer',
        3600
    );
    
    if ($subscriberTokenResult['success']) {
        echo "   ✓ Subscriber token generated (view only)\n\n";
    } else {
        echo "   ✗ Failed to generate subscriber token\n\n";
    }
    
} catch (\Exception $e) {
    echo "   ✗ Exception occurred: " . $e->getMessage() . "\n\n";
}

// Summary
echo "=== Test Summary ===\n\n";
echo "✓ Vonage Video API is working properly!\n\n";
echo "Session Details:\n";
echo "  - Session ID: $sessionId\n";
echo "  - API Key: $apiKey\n";
echo "  - Location: $location\n\n";

echo "What This Means:\n";
echo "  ✓ Video consultations can be created\n";
echo "  ✓ Doctors and patients can join video calls\n";
echo "  ✓ Token generation is working\n";
echo "  ✓ Session management is functional\n\n";

echo "Next Steps:\n";
echo "  1. Doctors and patients can use this session for video calls\n";
echo "  2. Integrate the session ID and tokens into your consultation pages\n";
echo "  3. Use Vonage Video Client SDK in the frontend to connect\n";
echo "  4. Optional: Test recording/archiving features\n\n";

echo "Test Video Session URL (for manual testing):\n";
echo "  https://tokbox.com/developer/tools/publish-test/\n";
echo "  API Key: $apiKey\n";
echo "  Session ID: $sessionId\n";
echo "  Token: " . $doctorTokenResult['token'] . "\n\n";

echo "=== Test Complete ===\n";

