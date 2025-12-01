#!/usr/bin/env php
<?php

/**
 * SMS Test Script for DoctorOnTap
 * 
 * This script tests the SMS notification functionality using Termii service.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Notifications\ConsultationSmsNotification;
use Illuminate\Support\Facades\Log;

// Configuration
$testPhone = '+2349036498802'; // The phone number to test
$testName = 'Test Patient';
$testReference = 'TEST-' . strtoupper(substr(md5(time()), 0, 8));

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║           DoctorOnTap SMS Test                                 ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "Test Details:\n";
echo "  Phone: {$testPhone}\n";
echo "  Name: {$testName}\n";
echo "  Reference: {$testReference}\n";
echo "\n";

// Check if Termii is enabled
$termiiEnabled = config('services.termii.enabled', false);
$termiiApiKey = config('services.termii.api_key');

echo "Termii Configuration:\n";
echo "  Enabled: " . ($termiiEnabled ? 'Yes' : 'No') . "\n";
echo "  API Key: " . ($termiiApiKey ? substr($termiiApiKey, 0, 10) . '...' : 'Not set') . "\n";
echo "  Sender ID: " . config('services.termii.sender_id') . "\n";
echo "  Channel: " . config('services.termii.channel') . "\n";
echo "\n";

if (!$termiiEnabled) {
    echo "⚠️  Warning: Termii is disabled in configuration!\n";
    echo "To enable it, set TERMII_ENABLED=true in your .env file\n\n";
}

if (!$termiiApiKey) {
    echo "❌ Error: Termii API key is not configured!\n";
    echo "Please set TERMII_API_KEY in your .env file\n\n";
    exit(1);
}

// Test SMS Types Menu
echo "Select SMS type to test:\n";
echo "  1. Consultation Confirmation\n";
echo "  2. Payment Request\n";
echo "  3. Treatment Plan Ready\n";
echo "  4. Status Change (Assigned)\n";
echo "  5. Status Change (Completed)\n";
echo "  6. Custom Test Message\n";
echo "\n";
echo "Enter choice (1-6): ";

$handle = fopen("php://stdin", "r");
$choice = trim(fgets($handle));

$smsNotification = new ConsultationSmsNotification();

try {
    echo "\n";
    echo "Sending SMS...\n";
    echo "─────────────────────────────────────────────────────────────\n";
    
    $result = null;
    
    switch ($choice) {
        case '1':
            // Consultation Confirmation
            $data = [
                'mobile' => $testPhone,
                'first_name' => $testName,
                'consultation_reference' => $testReference
            ];
            $result = $smsNotification->sendConsultationConfirmation($data);
            break;
            
        case '2':
            // Payment Request - Create a mock consultation
            $consultation = new class {
                public $mobile = '+2349036498802';
                public $reference = '';
                public $first_name = 'Test Patient';
                public $id = 999;
                public $doctor;
                
                public function __construct() {
                    $this->reference = 'TEST-' . strtoupper(substr(md5(time()), 0, 8));
                    $this->doctor = new class {
                        public $effective_consultation_fee = 5000;
                    };
                }
            };
            $result = $smsNotification->sendPaymentRequest($consultation);
            break;
            
        case '3':
            // Treatment Plan Ready - Create a mock consultation
            $consultation = new class {
                public $mobile = '+2349036498802';
                public $reference = '';
                public $first_name = 'Test Patient';
                public $id = 999;
                
                public function __construct() {
                    $this->reference = 'TEST-' . strtoupper(substr(md5(time()), 0, 8));
                }
            };
            $result = $smsNotification->sendTreatmentPlanReady($consultation);
            break;
            
        case '4':
            // Status Change - Assigned
            $consultation = new class {
                public $mobile = '+2349036498802';
                public $reference = '';
                public $first_name = 'Test Patient';
                public $id = 999;
                
                public function __construct() {
                    $this->reference = 'TEST-' . strtoupper(substr(md5(time()), 0, 8));
                }
            };
            $result = $smsNotification->sendStatusChange($consultation, 'assigned');
            break;
            
        case '5':
            // Status Change - Completed
            $consultation = new class {
                public $mobile = '+2349036498802';
                public $reference = '';
                public $first_name = 'Test Patient';
                public $id = 999;
                
                public function __construct() {
                    $this->reference = 'TEST-' . strtoupper(substr(md5(time()), 0, 8));
                }
            };
            $result = $smsNotification->sendStatusChange($consultation, 'completed');
            break;
            
        case '6':
            // Custom message using the service directly
            echo "Enter custom message: ";
            $customMessage = trim(fgets($handle));
            
            $termiiService = app(\App\Services\TermiiService::class);
            $result = $termiiService->sendSMS($testPhone, $customMessage);
            break;
            
        default:
            echo "Invalid choice!\n";
            exit(1);
    }
    
    echo "\n";
    
    if ($result && $result['success']) {
        echo "✅ SMS sent successfully!\n\n";
        echo "Response Details:\n";
        if (isset($result['data']['message_id'])) {
            echo "  Message ID: {$result['data']['message_id']}\n";
        }
        if (isset($result['data']['balance'])) {
            echo "  Remaining Balance: {$result['data']['balance']}\n";
        }
        echo "  Message: {$result['message']}\n";
        echo "\n";
        echo "📱 Check the phone {$testPhone} for the SMS!\n";
    } else {
        echo "❌ Failed to send SMS!\n\n";
        echo "Error Details:\n";
        if (isset($result['message'])) {
            echo "  Message: {$result['message']}\n";
        }
        if (isset($result['error'])) {
            echo "  Error: " . json_encode($result['error'], JSON_PRETTY_PRINT) . "\n";
        }
        if (isset($result['status_code'])) {
            echo "  Status Code: {$result['status_code']}\n";
        }
    }
    
    echo "\n";
    echo "Full Response:\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
    
} catch (\Exception $e) {
    echo "❌ Exception occurred!\n\n";
    echo "Error: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}:{$e->getLine()}\n";
    echo "\nStack Trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n";
echo "─────────────────────────────────────────────────────────────\n";
echo "Check logs at: storage/logs/laravel.log\n";
echo "─────────────────────────────────────────────────────────────\n";
echo "\n";

fclose($handle);

