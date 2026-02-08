<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DoctorOnTap Vonage SMS Test ===\n\n";

// Check Vonage Configuration
echo "1. Checking Vonage Configuration...\n";
$apiKey = config('services.vonage.api_key');
$apiSecret = config('services.vonage.api_secret');
$brandName = config('services.vonage.brand_name', 'DoctorOnTap');
$enabled = config('services.vonage.enabled', false);
$apiMethod = config('services.vonage.api_method', 'legacy');
$currentProvider = config('services.sms_provider', 'termii');

echo "   Current SMS Provider: " . strtoupper($currentProvider) . "\n";
echo "   Vonage Enabled: " . ($enabled ? 'YES' : 'NO') . "\n";
echo "   API Key: " . ($apiKey ? 'SET' : 'NOT SET') . "\n";
echo "   API Secret: " . ($apiSecret ? 'SET' : 'NOT SET') . "\n";
echo "   Brand Name: $brandName\n";
echo "   API Method: $apiMethod\n\n";

if (empty($apiKey) || empty($apiSecret)) {
    echo "ERROR: Vonage credentials not configured!\n";
    echo "Please set VONAGE_API_KEY and VONAGE_API_SECRET in .env file\n";
    exit(1);
}

// Test phone number
$testPhone = '07081114942'; // Nigerian format
$testMessage = 'Test SMS from DoctorOnTap via Vonage - ' . date('Y-m-d H:i:s');

echo "2. Testing Vonage Service Class...\n";
try {
    $vonageService = app(\App\Services\VonageService::class);
    echo "   ✓ VonageService instantiated successfully\n\n";
} catch (\Exception $e) {
    echo "   ✗ Failed to create VonageService: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "3. Sending Test SMS...\n";
echo "   To: $testPhone\n";
echo "   Message: $testMessage\n";
echo "   Method: VonageService class\n\n";

try {
    $result = $vonageService->sendSMS($testPhone, $testMessage);
    
    echo "=== RESULT ===\n";
    if ($result['success']) {
        echo "✓ SUCCESS! SMS sent via Vonage\n\n";
        echo "Response Details:\n";
        echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "✗ FAILED to send SMS\n\n";
        echo "Error Details:\n";
        echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
    }
    
} catch (\Exception $e) {
    echo "✗ EXCEPTION occurred\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n\n";
    
    if (strpos($e->getMessage(), 'authentication') !== false || strpos($e->getMessage(), 'credentials') !== false) {
        echo "This appears to be an authentication error.\n";
        echo "Please verify your Vonage API credentials are correct.\n\n";
    }
    
    if (strpos($e->getMessage(), 'SSL') !== false || strpos($e->getMessage(), 'curl') !== false) {
        echo "This appears to be a network/SSL issue.\n";
        echo "Please check your internet connection and SSL certificates.\n\n";
    }
    
    echo "Stack Trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";

