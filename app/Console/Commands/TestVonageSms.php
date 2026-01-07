<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\VonageService;

class TestVonageSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vonage:test-sms {phone?} {--message=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Vonage SMS integration by sending a test message';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $vonage = new VonageService();

        $this->info('🚀 Vonage SMS Test Command');
        $this->newLine();

        // Check if Vonage is enabled
        if (!config('services.vonage.enabled')) {
            $this->warn('⚠️  Vonage is currently DISABLED in your configuration.');
            $this->info('Set VONAGE_ENABLED=true in your .env file to enable SMS sending.');
            return Command::FAILURE;
        }

        // Check if API credentials are configured
        if (empty(config('services.vonage.api_key')) || empty(config('services.vonage.api_secret'))) {
            $this->error('❌ Vonage API credentials are not configured!');
            $this->info('Add VONAGE_API_KEY and VONAGE_API_SECRET to your .env file.');
            return Command::FAILURE;
        }

        // Display configuration
        $this->info('📋 Current Configuration:');
        $apiKey = config('services.vonage.api_key');
        $apiSecret = config('services.vonage.api_secret');
        $this->table(
            ['Setting', 'Value'],
            [
                ['API Key', str_repeat('*', 20) . substr($apiKey, -4)],
                ['API Secret', str_repeat('*', 20) . substr($apiSecret, -4)],
                ['Brand Name', config('services.vonage.brand_name')],
                ['Enabled', config('services.vonage.enabled') ? '✅ Yes' : '❌ No'],
            ]
        );
        $this->newLine();

        // Get phone number
        $phone = $this->argument('phone') ?: $this->ask('Enter phone number to test', '07081114942');
        
        // Get message
        $message = $this->option('message') ?: $this->ask('Enter test message', 'Hello! This is a test SMS from DoctorOnTap via Vonage. If you receive this, the integration is working! 🎉');

        $this->newLine();
        $this->info("📱 Sending SMS to: {$phone}");
        $this->info("💬 Message: {$message}");
        $this->newLine();

        // Send SMS
        $this->info('⏳ Sending SMS...');
        $result = $vonage->sendSMS($phone, $message);

        $this->newLine();

        if ($result['success']) {
            $this->info('✅ SMS sent successfully!');
            $this->newLine();
            
            if (isset($result['data'])) {
                $this->info('📊 Response Details:');
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['Message ID', $result['data']['message_id'] ?? 'N/A'],
                        ['Status', $result['data']['status'] ?? 'N/A'],
                        ['To', $result['data']['to'] ?? 'N/A'],
                        ['Remaining Balance', $result['data']['remaining_balance'] ?? 'N/A'],
                        ['Message Price', $result['data']['message_price'] ?? 'N/A'],
                        ['Network', $result['data']['network'] ?? 'N/A'],
                    ]
                );
            }
            
            $this->newLine();
            $this->info('🎉 Test completed successfully! Check the recipient phone for the message.');
            
            return Command::SUCCESS;
        } else {
            $this->error('❌ SMS sending failed!');
            $this->newLine();
            
            if (isset($result['error'])) {
                $this->error('Error Details:');
                if (is_array($result['error'])) {
                    foreach ($result['error'] as $key => $value) {
                        $this->line("  {$key}: {$value}");
                    }
                } else {
                    $this->line("  {$result['error']}");
                }
            }
            
            $this->newLine();
            $this->warn('💡 Troubleshooting Tips:');
            $this->line('  1. Verify your API credentials in .env file');
            $this->line('  2. Check your Vonage account balance');
            $this->line('  3. Ensure the phone number is in correct format');
            $this->line('  4. Check Laravel logs: storage/logs/laravel.log');
            $this->line('  5. Verify your brand name is approved in Vonage dashboard');
            
            return Command::FAILURE;
        }
    }
}





