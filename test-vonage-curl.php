<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$apiKey = config('services.vonage.api_key');
$apiSecret = config('services.vonage.api_secret');
$brandName = config('services.vonage.brand_name', 'DoctorOnTap');

if (empty($apiKey) || empty($apiSecret)) {
    echo "❌ ERROR: Vonage credentials not configured!\n";
    exit(1);
}

$phone = '2347081114942'; // E.164 format (no +, no leading 0)
$message = 'Hello! This is a test SMS from DoctorOnTap via Vonage. If you receive this, the integration is working! 🎉';

echo "🚀 Testing Vonage SMS via cURL\n";
echo "==============================\n\n";
echo "API Key: {$apiKey}\n";
echo "Phone: {$phone}\n";
echo "From: {$brandName}\n";
echo "Message: {$message}\n\n";

$url = 'https://rest.nexmo.com/sms/json';

$data = [
    'api_key' => $apiKey,
    'api_secret' => $apiSecret,
    'to' => $phone,
    'from' => $brandName,
    'text' => $message
];

echo "⏳ Sending SMS via cURL...\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ cURL Error: {$error}\n";
    exit(1);
}

echo "HTTP Status: {$httpCode}\n\n";

$result = json_decode($response, true);

if ($result) {
    echo "Response:\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
    
    if (isset($result['messages']) && count($result['messages']) > 0) {
        $message = $result['messages'][0];
        $status = $message['status'] ?? 'unknown';
        
        if ($status == '0') {
            echo "✅ SUCCESS! SMS sent successfully!\n";
            echo "Message ID: " . ($message['message-id'] ?? 'N/A') . "\n";
            echo "Remaining Balance: " . ($message['remaining-balance'] ?? 'N/A') . "\n";
            echo "Message Price: " . ($message['message-price'] ?? 'N/A') . "\n";
            echo "\n📱 Check phone {$phone} for the message!\n";
        } else {
            echo "❌ FAILED!\n";
            echo "Status: {$status}\n";
            echo "Error Text: " . ($message['error-text'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "❌ No messages in response\n";
    }
} else {
    echo "❌ Failed to parse response\n";
    echo "Raw response: {$response}\n";
}

