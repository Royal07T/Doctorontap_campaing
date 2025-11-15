<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TermiiService;

class TestTermiiSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'termii:test-sms {phone?} {--message=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Termii SMS integration by sending a test message';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $termii = new TermiiService();

        $this->info('🚀 Termii SMS Test Command');
        $this->newLine();

        // Check if Termii is enabled
        if (!config('services.termii.enabled')) {
            $this->warn('⚠️  Termii is currently DISABLED in your configuration.');
            $this->info('Set TERMII_ENABLED=true in your .env file to enable SMS sending.');
            return Command::FAILURE;
        }

        // Check if API key is configured
        if (empty(config('services.termii.api_key'))) {
            $this->error('❌ Termii API key is not configured!');
            $this->info('Add TERMII_API_KEY to your .env file.');
            return Command::FAILURE;
        }

        // Display configuration
        $this->info('📋 Current Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['API Key', str_repeat('*', 20) . substr(config('services.termii.api_key'), -4)],
                ['Sender ID', config('services.termii.sender_id')],
                ['Base URL', config('services.termii.base_url')],
                ['Channel', config('services.termii.channel')],
                ['Enabled', config('services.termii.enabled') ? '✅ Yes' : '❌ No'],
            ]
        );
        $this->newLine();

        // Check balance
        $this->info('💰 Checking Termii Account Balance...');
        $balanceResult = $termii->checkBalance();
        
        if ($balanceResult['success']) {
            $balance = $balanceResult['balance'] ?? 0;
            $currency = $balanceResult['currency'] ?? 'NGN';
            $this->info("Balance: {$currency} {$balance}");
            
            if ($balance < 10) {
                $this->warn('⚠️  Low balance! Please top up your Termii account.');
            }
        } else {
            $this->error('❌ Failed to check balance: ' . ($balanceResult['message'] ?? 'Unknown error'));
            return Command::FAILURE;
        }

        $this->newLine();

        // Ask if user wants to send a test SMS
        if (!$this->confirm('Do you want to send a test SMS?', true)) {
            $this->info('Test cancelled.');
            return Command::SUCCESS;
        }

        // Get phone number
        $phone = $this->argument('phone');
        if (!$phone) {
            $phone = $this->ask('Enter phone number (e.g., +2348012345678 or 08012345678)');
        }

        if (empty($phone)) {
            $this->error('❌ Phone number is required!');
            return Command::FAILURE;
        }

        // Get message
        $message = $this->option('message');
        if (!$message) {
            $message = $this->ask(
                'Enter message (or press enter for default)',
                'This is a test SMS from DoctorOnTap. Your Termii integration is working! 🎉'
            );
        }

        // Send SMS
        $this->info('📤 Sending SMS...');
        $this->newLine();

        $result = $termii->sendSMS($phone, $message);

        if ($result['success']) {
            $this->info('✅ SMS sent successfully!');
            
            if (isset($result['data']['message_id'])) {
                $this->info('Message ID: ' . $result['data']['message_id']);
            }
            
            if (isset($result['data']['balance'])) {
                $this->info('Remaining Balance: ' . $result['data']['balance']);
            }
            
            $this->newLine();
            $this->info('Check your phone for the SMS! 📱');
            
            return Command::SUCCESS;
        } else {
            $this->error('❌ Failed to send SMS!');
            $this->error('Error: ' . ($result['message'] ?? 'Unknown error'));
            
            if (isset($result['error'])) {
                $this->newLine();
                $this->warn('Details:');
                $this->line(json_encode($result['error'], JSON_PRETTY_PRINT));
            }
            
            return Command::FAILURE;
        }
    }
}

