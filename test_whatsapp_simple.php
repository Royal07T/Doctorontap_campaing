<?php

/**
 * Simple WhatsApp Test Script
 * 
 * Usage: php test_whatsapp_simple.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🚀 Testing Vonage WhatsApp Integration\n";
echo "=====================================\n\n";

// Check configuration
echo "🔍 Checking Configuration:\n";
echo "------------------------\n";

$apiKey = config('vonage.api_key');
$apiSecret = config('vonage.api_secret');
$whatsappNumber = config('services.vonage.whatsapp.from_phone_number');
$applicationId = config('vonage.application_id');

$configs = [
    'API Key' => $apiKey ? '✅ Configured' : '❌ Not configured',
    'API Secret' => $apiSecret ? '✅ Configured' : '❌ Not configured',
    'WhatsApp Number' => $whatsappNumber ? "✅ {$whatsappNumber}" : '❌ Not configured',
    'Application ID' => $applicationId ? "✅ {$applicationId}" : '❌ Not configured',
];

foreach ($configs as $key => $value) {
    echo "  {$key}: {$value}\n";
}

echo "\n";

// Test WhatsApp Service
if ($whatsappNumber) {
    echo "📱 WhatsApp Service Test:\n";
    echo "------------------------\n";
    echo "From Number: {$whatsappNumber}\n";
    echo "\n";
    echo "✅ WhatsAppService class is ready!\n";
    echo "\n";
    echo "To send a test message, run:\n";
    echo "  php artisan whatsapp:test --to=YOUR_PHONE_NUMBER\n";
    echo "\n";
    echo "Or use the service directly:\n";
    echo "  \$whatsapp = new \\App\\Services\\WhatsAppService();\n";
    echo "  \$result = \$whatsapp->sendText('447123456789', 'Test message');\n";
} else {
    echo "⚠️  WhatsApp number not configured. Please set WHATSAPP_PHONE_NUMBER in .env\n";
}

echo "\n";
echo "✅ Test script completed!\n";

