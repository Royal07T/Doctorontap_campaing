<?php

/**
 * Direct Vonage SMS Test Script
 * Based on Vonage PHP SDK documentation
 */

require __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Get credentials from environment
$apiKey = $_ENV['VONAGE_API_KEY'] ?? getenv('VONAGE_API_KEY');
$apiSecret = $_ENV['VONAGE_API_SECRET'] ?? getenv('VONAGE_API_SECRET');
$brandName = $_ENV['VONAGE_BRAND_NAME'] ?? getenv('VONAGE_BRAND_NAME') ?? 'DoctorOnTap';

// Test phone number
$toNumber = '+2347081114942';
$message = 'Hello! This is a test SMS from DoctorOnTap via Vonage. If you receive this, the integration is working! 🎉';

echo "🚀 Vonage SMS Direct Test\n";
echo "========================\n\n";

// Validate credentials
if (empty($apiKey) || empty($apiSecret)) {
    echo "❌ Error: VONAGE_API_KEY and VONAGE_API_SECRET must be set in .env file\n";
    exit(1);
}

echo "📋 Configuration:\n";
echo "   API Key: " . substr($apiKey, 0, 8) . "..." . substr($apiKey, -4) . "\n";
echo "   Brand Name: {$brandName}\n";
echo "   To: {$toNumber}\n";
echo "   Message: {$message}\n\n";

try {
    // Initialize Vonage client with Basic credentials (API Key/Secret)
    $credentials = new \Vonage\Client\Credentials\Basic($apiKey, $apiSecret);
    $client = new \Vonage\Client($credentials);

    echo "⏳ Sending SMS...\n\n";

    // Send SMS
    $response = $client->sms()->send(
        new \Vonage\SMS\Message\SMS($toNumber, $brandName, $message)
    );

    $messageObj = $response->current();

    if ($messageObj->getStatus() == 0) {
        echo "✅ SMS sent successfully!\n\n";
        echo "📊 Response Details:\n";
        echo "   Message ID: " . $messageObj->getMessageId() . "\n";
        echo "   Status: " . $messageObj->getStatus() . " (0 = Success)\n";
        echo "   To: " . $messageObj->getTo() . "\n";
        echo "   Remaining Balance: " . $messageObj->getRemainingBalance() . "\n";
        echo "   Message Price: " . $messageObj->getMessagePrice() . "\n";
        echo "   Network: " . $messageObj->getNetwork() . "\n";
        echo "\n🎉 Test completed successfully! Check your phone for the message.\n";
        exit(0);
    } else {
        echo "❌ SMS sending failed!\n\n";
        echo "   Status: " . $messageObj->getStatus() . "\n";
        echo "   Error: " . $messageObj->getErrorText() . "\n";
        exit(1);
    }
} catch (\Vonage\Client\Exception\Request $e) {
    echo "❌ Vonage API Request Error:\n";
    echo "   Message: " . $e->getMessage() . "\n";
    echo "   Code: " . $e->getCode() . "\n";
    if ($e->getEntity()) {
        echo "   Entity: " . json_encode($e->getEntity(), JSON_PRETTY_PRINT) . "\n";
    }
    exit(1);
} catch (\Exception $e) {
    echo "❌ General Error:\n";
    echo "   Message: " . $e->getMessage() . "\n";
    echo "   Type: " . get_class($e) . "\n";
    exit(1);
}

