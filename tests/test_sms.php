<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DoctorOnTap SMS API Test ===\n\n";

// Test 1: Check Vonage Configuration
echo "1. Checking Vonage Configuration...\n";
$apiKey = config('services.vonage.api_key');
$apiSecret = config('services.vonage.api_secret');
$brandName = config('services.vonage.brand_name', 'DoctorOnTap');

echo "   API Key: " . ($apiKey ? 'SET' : 'NOT SET') . "\n";
echo "   API Secret: " . ($apiSecret ? 'SET' : 'NOT SET') . "\n";
echo "   Brand Name: $brandName\n\n";

if (empty($apiKey) || empty($apiSecret)) {
    echo "ERROR: Vonage credentials not configured!\n";
    exit(1);
}

// Test 2: Create Vonage Client
echo "2. Creating Vonage Client...\n";
try {
    $credentials = new \Vonage\Client\Credentials\Basic($apiKey, $apiSecret);
    $client = new \Vonage\Client($credentials);
    echo "   ✓ Client created successfully\n\n";
} catch (Exception $e) {
    echo "   ✗ Failed to create client: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 3: Send Test SMS
echo "3. Sending Test SMS...\n";
$testNumber = '+1234567890'; // Using test number
$testMessage = 'Test SMS from DoctorOnTap API - ' . date('Y-m-d H:i:s');

try {
    $message = new \Vonage\Messages\Channel\SMS\SMSText(
        $testNumber,
        $brandName,
        $testMessage
    );
    
    $result = $client->messages()->send($message);
    
    echo "   ✓ SMS sent successfully!\n";
    echo "   Message ID: " . $result->getMessageId() . "\n";
    echo "   To: $testNumber\n";
    echo "   From: $brandName\n";
    echo "   Text: $testMessage\n\n";
    
} catch (Exception $e) {
    echo "   ✗ Failed to send SMS: " . $e->getMessage() . "\n";
    echo "   Error Code: " . $e->getCode() . "\n\n";
    
    // Check if it's a network/SSL issue
    if (strpos($e->getMessage(), 'SSL') !== false || strpos($e->getMessage(), 'timeout') !== false) {
        echo "   This appears to be a network/SSL connectivity issue.\n";
        echo "   Possible solutions:\n";
        echo "   - Check internet connection\n";
        echo "   - Verify firewall settings\n";
        echo "   - Try with different network\n";
        echo "   - Check if Vonage API is accessible\n\n";
    }
}

// Test 4: Test Template SMS
echo "4. Testing Template SMS...\n";
try {
    $template = 'appointment_reminder';
    $variables = [
        'patient_name' => 'John Doe',
        'doctor_name' => 'Dr. Smith',
        'date' => 'Jan 30, 2026',
        'time' => '2:00 PM'
    ];
    
    $templateMessage = 'Hi {patient_name}, this is a reminder about your appointment with Dr. {doctor_name} on {date} at {time}. Reply CANCEL to reschedule.';
    
    // Replace template variables
    foreach ($variables as $key => $value) {
        $templateMessage = str_replace('{' . $key . '}', $value, $templateMessage);
    }
    
    $templateSms = new \Vonage\Messages\Channel\SMS\SMSText(
        $testNumber,
        $brandName,
        $templateMessage
    );
    
    $templateResult = $client->messages()->send($templateSms);
    
    echo "   ✓ Template SMS sent successfully!\n";
    echo "   Template: $template\n";
    echo "   Message ID: " . $templateResult->getMessageId() . "\n";
    echo "   Final Message: $templateMessage\n\n";
    
} catch (Exception $e) {
    echo "   ✗ Failed to send template SMS: " . $e->getMessage() . "\n\n";
}

echo "=== SMS API Test Complete ===\n";
