<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\VonageService;

class TestVonageWhatsApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vonage:test-whatsapp {phone?} {--message=} {--template=} {--language=en}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Vonage WhatsApp integration by sending a test message or template';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $vonage = new VonageService();

        $this->info('🚀 Vonage WhatsApp Test Command');
        $this->newLine();

        // Check if WhatsApp is enabled
        if (!config('services.vonage.whatsapp_enabled')) {
            $this->warn('⚠️  Vonage WhatsApp is currently DISABLED in your configuration.');
            $this->info('Set VONAGE_WHATSAPP_ENABLED=true in your .env file to enable WhatsApp sending.');
            return Command::FAILURE;
        }

        // Check if authentication credentials are configured
        // WhatsApp supports both JWT (Application ID + Private Key) and Basic (API Key + Secret)
        $hasJWT = !empty(config('services.vonage.application_id')) && !empty(config('services.vonage.private_key'));
        $hasBasic = !empty(config('services.vonage.api_key')) && !empty(config('services.vonage.api_secret'));
        
        if (!$hasJWT && !$hasBasic) {
            $this->error('❌ Vonage authentication credentials are not configured!');
            $this->info('WhatsApp requires either:');
            $this->info('  Option 1 (JWT): VONAGE_APPLICATION_ID and VONAGE_PRIVATE_KEY_PATH or VONAGE_PRIVATE_KEY');
            $this->info('  Option 2 (Basic): VONAGE_API_KEY and VONAGE_API_SECRET');
            return Command::FAILURE;
        }

        // Check if WhatsApp number is configured
        if (empty(config('services.vonage.whatsapp_number'))) {
            $this->error('❌ Vonage WhatsApp number is not configured!');
            $this->info('Add VONAGE_WHATSAPP_NUMBER to your .env file.');
            return Command::FAILURE;
        }

        // Display configuration
        $this->info('📋 Current Configuration:');
        $appId = config('services.vonage.application_id');
        $apiKey = config('services.vonage.api_key');
        $whatsappNumber = config('services.vonage.whatsapp_number');
        $authMethod = $hasJWT ? 'JWT (Application ID + Private Key)' : 'Basic (API Key + Secret)';
        
        $configRows = [
            ['Authentication', $authMethod],
            ['WhatsApp Number', $whatsappNumber],
            ['Enabled', config('services.vonage.whatsapp_enabled') ? '✅ Yes' : '❌ No'],
        ];
        
        if ($hasJWT && $appId) {
            $configRows[] = ['Application ID', str_repeat('*', 20) . substr($appId, -4)];
        }
        if ($hasBasic && $apiKey) {
            $configRows[] = ['API Key', str_repeat('*', 20) . substr($apiKey, -4)];
        }
        
        $this->table(
            ['Setting', 'Value'],
            $configRows
        );
        $this->newLine();

        // Get phone number
        $phone = $this->argument('phone') ?: $this->ask('Enter phone number to test', '07081114942');
        
        // Check if sending template or regular message
        $templateName = $this->option('template');
        
        if ($templateName) {
            // Send template message
            $language = $this->option('language');
            
            $this->newLine();
            $this->info("📱 Sending WhatsApp Template to: {$phone}");
            $this->info("📝 Template: {$templateName}");
            $this->info("🌐 Language: {$language}");
            $this->newLine();

            $this->info('⏳ Sending WhatsApp template...');
            $result = $vonage->sendWhatsAppTemplate($phone, $templateName, $language, [
                [
                    'type' => 'text',
                    'text' => 'Test Parameter 1'
                ],
                [
                    'type' => 'text',
                    'text' => 'Test Parameter 2'
                ]
            ]);
        } else {
            // Send regular message (within 24-hour window)
            $message = $this->option('message') ?: $this->ask('Enter test message', 'Hello! This is a test WhatsApp message from DoctorOnTap via Vonage. If you receive this, the integration is working! 🎉');

            $this->newLine();
            $this->info("📱 Sending WhatsApp Message to: {$phone}");
            $this->info("💬 Message: {$message}");
            $this->newLine();

            $this->info('⏳ Sending WhatsApp message...');
            $result = $vonage->sendWhatsAppMessage($phone, $message);
        }

        $this->newLine();

        if ($result['success']) {
            $this->info('✅ WhatsApp message sent successfully!');
            $this->newLine();
            
            if (isset($result['data'])) {
                $this->info('📊 Response Details:');
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['Message UUID', $result['data']['message_uuid'] ?? 'N/A'],
                        ['To', $result['data']['to'] ?? 'N/A'],
                        ['Template Name', $result['data']['template_name'] ?? 'N/A (Regular Message)'],
                    ]
                );
            }
            
            $this->newLine();
            $this->info('🎉 Test completed successfully! Check the recipient phone for the message.');
            $this->warn('⚠️  Note: Regular messages only work within the 24-hour customer care window.');
            $this->info('   For initial messages, use approved templates with --template option.');
            
            return Command::SUCCESS;
        } else {
            $this->error('❌ WhatsApp sending failed!');
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
            $this->line('  1. Verify your Messages API credentials in .env file');
            $this->line('  2. Ensure your WhatsApp Business Account is set up');
            $this->line('  3. Check that your WhatsApp number is linked to Vonage');
            $this->line('  4. For templates, ensure they are approved in WhatsApp Manager');
            $this->line('  5. Regular messages only work within 24-hour customer care window');
            $this->line('  6. Check Laravel logs: storage/logs/laravel.log');
            
            return Command::FAILURE;
        }
    }
}

