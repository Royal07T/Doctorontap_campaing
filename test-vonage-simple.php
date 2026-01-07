<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\VonageService;

echo "🚀 Testing Vonage SMS\n";
echo "===================\n\n";

// Check configuration
$apiKey = config('services.vonage.api_key');
$apiSecret = config('services.vonage.api_secret');
$enabled = config('services.vonage.enabled');
$brandName = config('services.vonage.brand_name');

echo "Configuration:\n";
echo "  API Key: " . (empty($apiKey) ? "❌ NOT SET" : "✅ " . substr($apiKey, 0, 4) . "..." . substr($apiKey, -4)) . "\n";
echo "  API Secret: " . (empty($apiSecret) ? "❌ NOT SET" : "✅ " . substr($apiSecret, 0, 4) . "..." . substr($apiSecret, -4)) . "\n";
echo "  Brand Name: {$brandName}\n";
echo "  Enabled: " . ($enabled ? "✅ Yes" : "❌ No") . "\n\n";

if (empty($apiKey) || empty($apiSecret)) {
    echo "❌ ERROR: Vonage credentials not configured!\n";
    echo "Please add to .env:\n";
    echo "  VONAGE_API_KEY=your_key\n";
    echo "  VONAGE_API_SECRET=your_secret\n";
    exit(1);
}

if (!$enabled) {
    echo "❌ ERROR: Vonage is disabled!\n";
    echo "Set VONAGE_ENABLED=true in .env\n";
    exit(1);
}

$phone = '07081114942';
$message = 'Hello! This is a test SMS from DoctorOnTap via Vonage. If you receive this, the integration is working! 🎉';

echo "Sending SMS:\n";
echo "  To: {$phone}\n";
echo "  Message: {$message}\n\n";

try {
    $vonage = new VonageService();
    
    echo "⏳ Sending...\n";
    $startTime = microtime(true);
    
    $result = $vonage->sendSMS($phone, $message);
    
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    
    echo "\n⏱️  Duration: {$duration}s\n\n";
    
    if ($result['success']) {
        echo "✅ SUCCESS! SMS sent successfully!\n\n";
        
        if (isset($result['data'])) {
            echo "Response Details:\n";
            foreach ($result['data'] as $key => $value) {
                echo "  {$key}: {$value}\n";
            }
        }
        
        echo "\n📱 Check phone {$phone} for the message!\n";
    } else {
        echo "❌ FAILED!\n\n";
        echo "Error: " . ($result['message'] ?? 'Unknown error') . "\n";
        
        if (isset($result['error'])) {
            echo "\nError Details:\n";
            if (is_array($result['error'])) {
                foreach ($result['error'] as $key => $value) {
                    echo "  {$key}: {$value}\n";
                }
            } else {
                echo "  {$result['error']}\n";
            }
        }
        
        exit(1);
    }
} catch (\Exception $e) {
    echo "\n❌ EXCEPTION!\n\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}








