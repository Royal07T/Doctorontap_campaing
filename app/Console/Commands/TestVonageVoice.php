<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\VonageVoiceService;

class TestVonageVoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vonage:test-voice {phone?} {--message=} {--language=en-US} {--voice=female}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Vonage Voice API by making a test call';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $voiceService = new VonageVoiceService();

        $this->info('🚀 Vonage Voice API Test Command');
        $this->newLine();

        // Check if Voice is enabled
        if (!config('services.vonage.voice_enabled')) {
            $this->warn('⚠️  Vonage Voice is currently DISABLED in your configuration.');
            $this->info('Set VONAGE_VOICE_ENABLED=true in your .env file to enable voice calling.');
            return Command::FAILURE;
        }

        // Check authentication credentials
        $hasJWT = !empty(config('services.vonage.application_id')) && !empty(config('services.vonage.private_key'));
        $hasBasic = !empty(config('services.vonage.api_key')) && !empty(config('services.vonage.api_secret'));
        
        if (!$hasJWT && !$hasBasic) {
            $this->error('❌ Vonage authentication credentials are not configured!');
            $this->info('Voice API requires either:');
            $this->info('  Option 1 (JWT): VONAGE_APPLICATION_ID and VONAGE_PRIVATE_KEY_PATH or VONAGE_PRIVATE_KEY');
            $this->info('  Option 2 (Basic): VONAGE_API_KEY and VONAGE_API_SECRET');
            return Command::FAILURE;
        }

        // Check if voice number is configured
        if (empty(config('services.vonage.voice_number'))) {
            $this->error('❌ Vonage Voice number is not configured!');
            $this->info('Add VONAGE_VOICE_NUMBER to your .env file.');
            return Command::FAILURE;
        }

        // Display configuration
        $this->info('📋 Current Configuration:');
        $appId = config('services.vonage.application_id');
        $apiKey = config('services.vonage.api_key');
        $voiceNumber = config('services.vonage.voice_number');
        $authMethod = $hasJWT ? 'JWT (Application ID + Private Key)' : 'Basic (API Key + Secret)';
        
        $configRows = [
            ['Authentication', $authMethod],
            ['Voice Number', $voiceNumber],
            ['Enabled', config('services.vonage.voice_enabled') ? '✅ Yes' : '❌ No'],
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
        $phone = $this->argument('phone') ?: $this->ask('Enter phone number to call', '07081114942');
        
        // Get message
        $message = $this->option('message') ?: $this->ask('Enter message to speak', 'Hello! This is a test call from DoctorOnTap via Vonage Voice API. If you can hear this, the integration is working!');
        
        $language = $this->option('language');
        $voice = $this->option('voice');

        $this->newLine();
        $this->info("📞 Making call to: {$phone}");
        $this->info("💬 Message: {$message}");
        $this->info("🌐 Language: {$language}");
        $this->info("🎤 Voice: {$voice}");
        $this->newLine();

        // Make the call
        $this->info('⏳ Initiating call...');
        $result = $voiceService->makeCall($phone, $message, [
            'language' => $language,
            'voice' => $voice
        ]);

        $this->newLine();

        if ($result['success']) {
            if (isset($result['skipped']) && $result['skipped']) {
                $this->warn('⚠️  ' . $result['message']);
                return Command::SUCCESS;
            }

            $this->info('✅ Call initiated successfully!');
            $this->newLine();
            
            if (isset($result['data'])) {
                $this->info('📊 Call Details:');
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['Call UUID', $result['data']['call_uuid'] ?? 'N/A'],
                        ['To', $result['data']['to'] ?? 'N/A'],
                        ['From', $result['data']['from'] ?? 'N/A'],
                        ['Status', $result['data']['status'] ?? 'N/A'],
                    ]
                );
            }
            
            $this->newLine();
            $this->info('📱 The recipient should receive the call shortly.');
            $this->warn('⚠️  Note: Make sure webhooks are configured to handle call events.');
            
            return Command::SUCCESS;
        } else {
            $this->error('❌ Call initiation failed!');
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
            $this->line('  1. Verify your authentication credentials in .env file');
            $this->line('  2. Ensure your Vonage phone number is configured');
            $this->line('  3. Check that the recipient number is in correct format');
            $this->line('  4. Verify you have sufficient balance in your Vonage account');
            $this->line('  5. Check Laravel logs: storage/logs/laravel.log');
            $this->line('  6. Ensure webhooks are configured in Vonage Dashboard');
            
            return Command::FAILURE;
        }
    }
}

