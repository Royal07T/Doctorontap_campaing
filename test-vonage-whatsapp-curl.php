<?php
/**
 * Test Vonage WhatsApp using Basic Authentication (API Key/Secret)
 * This matches the curl command format you provided
 */

require __DIR__ . '/vendor/autoload.php';

use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\Messages\Channel\WhatsApp\WhatsAppText;

// Configuration - Update these with your credentials
$apiKey = getenv('VONAGE_API_KEY') ?: '210c6b53'; // Your API Key
$apiSecret = getenv('VONAGE_API_SECRET') ?: 'YOUR_API_SECRET'; // Your API Secret
$fromNumber = '14157386102'; // Your WhatsApp Business Number
$toNumber = '2348177777122'; // Recipient number (Nigerian format: 234XXXXXXXXXX)
$message = 'This is a WhatsApp Message sent from the Messages API';

echo "🚀 Testing Vonage WhatsApp with Basic Authentication\n";
echo "==================================================\n\n";

// Validate configuration
if (empty($apiKey) || $apiSecret === 'YOUR_API_SECRET') {
    echo "❌ Error: Please set VONAGE_API_KEY and VONAGE_API_SECRET environment variables\n";
    echo "   Or update the values in this script\n";
    exit(1);
}

try {
    // Create client with Basic authentication
    $credentials = new Basic($apiKey, $apiSecret);
    $client = new Client($credentials);

    echo "📋 Configuration:\n";
    echo "   API Key: " . substr($apiKey, 0, 4) . "..." . substr($apiKey, -4) . "\n";
    echo "   From: {$fromNumber}\n";
    echo "   To: {$toNumber}\n";
    echo "   Message: {$message}\n\n";

    echo "⏳ Sending WhatsApp message...\n";

    // Create WhatsApp text message
    $whatsappMessage = new WhatsAppText(
        $toNumber,
        $fromNumber,
        $message
    );

    // Send the message
    $response = $client->messages()->send($whatsappMessage);
    $messageUuid = $response->getMessageUuid();

    echo "\n✅ Success!\n";
    echo "   Message UUID: {$messageUuid}\n";
    echo "   Status: Sent\n\n";
    echo "📱 Check the recipient's WhatsApp for the message.\n";
    echo "⚠️  Note: This only works within the 24-hour customer care window.\n";
    echo "   For initial messages, use approved templates.\n";

} catch (\Vonage\Client\Exception\Request $e) {
    echo "\n❌ Request Error:\n";
    echo "   Code: {$e->getCode()}\n";
    echo "   Message: {$e->getMessage()}\n";
    
    if ($e->getEntity()) {
        echo "   Details: " . json_encode($e->getEntity(), JSON_PRETTY_PRINT) . "\n";
    }
    
    exit(1);
} catch (\Exception $e) {
    echo "\n❌ Error:\n";
    echo "   " . $e->getMessage() . "\n";
    echo "   Class: " . get_class($e) . "\n";
    exit(1);
}

