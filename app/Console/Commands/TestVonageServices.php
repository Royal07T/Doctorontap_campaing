<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\VonageService;
use App\Services\WhatsAppService;
use App\Services\VonageVoiceService;
use App\Services\VonageVideoService;
use App\Services\VonageConversationService;

class TestVonageServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vonage:test-all 
                            {--service=all : Service to test: all, sms, whatsapp, voice, video, conversation}
                            {--to= : Phone number for SMS/WhatsApp/Voice tests (E.164 format)}
                            {--message= : Message text for SMS/WhatsApp tests}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test all Vonage services (SMS, WhatsApp, Voice, Video, Conversations)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Testing All Vonage Services');
        $this->newLine();
        $this->line('=====================================');
        $this->newLine();

        $service = $this->option('service');

        if ($service === 'all' || $service === 'sms') {
            $this->testSMSService();
            $this->newLine();
        }

        if ($service === 'all' || $service === 'whatsapp') {
            $this->testWhatsAppService();
            $this->newLine();
        }

        if ($service === 'all' || $service === 'voice') {
            $this->testVoiceService();
            $this->newLine();
        }

        if ($service === 'all' || $service === 'video') {
            $this->testVideoService();
            $this->newLine();
        }

        if ($service === 'all' || $service === 'conversation') {
            $this->testConversationService();
            $this->newLine();
        }

        $this->info('✅ All tests completed!');
        return 0;
    }

    protected function testSMSService()
    {
        $this->info('📱 Testing SMS Service');
        $this->line('---------------------');

        $vonageService = new VonageService();

        // Check configuration
        $apiKey = config('vonage.api_key');
        $apiSecret = config('vonage.api_secret');
        $enabled = config('services.vonage.enabled', false);
        $apiMethod = config('services.vonage.api_method', 'legacy');

        $this->line("  API Method: {$apiMethod}");
        $this->line("  Enabled: " . ($enabled ? '✅ Yes' : '❌ No'));
        $this->line("  API Key: " . ($apiKey ? '✅ Configured' : '❌ Not configured'));
        $this->line("  API Secret: " . ($apiSecret ? '✅ Configured' : '❌ Not configured'));

        if (!$enabled) {
            $this->warn('  ⚠️  SMS service is disabled. Set VONAGE_ENABLED=true in .env');
            return;
        }

        if (!$apiKey || !$apiSecret) {
            $this->error('  ❌ SMS credentials not configured');
            return;
        }

        // Test SMS sending
        $toNumber = $this->option('to');
        if (!$toNumber) {
            $toNumber = $this->ask('  Enter phone number to test SMS (E.164 format, e.g., 447123456789)', '');
        }

        if (empty($toNumber)) {
            $this->warn('  ⚠️  Skipping SMS test - no phone number provided');
            return;
        }

        $message = $this->option('message') ?: 'Test SMS from DoctorOnTap - Vonage integration test';

        $this->line("  Sending SMS to: {$toNumber}");
        $this->line("  Message: {$message}");

        $result = $vonageService->sendSMS($toNumber, $message);

        if ($result['success'] && !($result['skipped'] ?? false)) {
            $this->info('  ✅ SMS sent successfully!');
            if (isset($result['data']['message_id'])) {
                $this->line("     Message ID: " . $result['data']['message_id']);
            }
        } elseif ($result['skipped'] ?? false) {
            $this->warn('  ⚠️  SMS sending skipped (disabled)');
        } else {
            $this->error('  ❌ Failed to send SMS');
            $this->error("     Error: " . ($result['message'] ?? 'Unknown error'));
        }
    }

    protected function testWhatsAppService()
    {
        $this->info('💬 Testing WhatsApp Service');
        $this->line('----------------------------');

        $whatsappService = new WhatsAppService();

        // Check configuration
        $whatsappNumber = config('services.vonage.whatsapp.from_phone_number');
        $whatsappEnabled = config('services.vonage.whatsapp_enabled', false);
        $applicationId = config('services.vonage.application_id');

        $this->line("  Enabled: " . ($whatsappEnabled ? '✅ Yes' : '❌ No'));
        $this->line("  WhatsApp Number: " . ($whatsappNumber ? "✅ {$whatsappNumber}" : '❌ Not configured'));
        $this->line("  Application ID: " . ($applicationId ? "✅ {$applicationId}" : '❌ Not configured'));

        if (!$whatsappEnabled) {
            $this->warn('  ⚠️  WhatsApp service is disabled. Set VONAGE_WHATSAPP_ENABLED=true in .env');
            return;
        }

        if (!$whatsappNumber) {
            $this->error('  ❌ WhatsApp number not configured');
            return;
        }

        // Test WhatsApp sending
        $toNumber = $this->option('to');
        if (!$toNumber) {
            $toNumber = $this->ask('  Enter phone number to test WhatsApp (E.164 format, e.g., 447123456789)', '');
        }

        if (empty($toNumber)) {
            $this->warn('  ⚠️  Skipping WhatsApp test - no phone number provided');
            return;
        }

        $message = $this->option('message') ?: 'Test WhatsApp message from DoctorOnTap - Vonage integration test';

        $this->line("  Sending WhatsApp message to: {$toNumber}");
        $this->line("  Message: {$message}");

        $result = $whatsappService->sendText($toNumber, $message);

        if ($result['success']) {
            $this->info('  ✅ WhatsApp message sent successfully!');
            if (isset($result['data']['message_uuid'])) {
                $this->line("     Message UUID: " . $result['data']['message_uuid']);
            }
        } else {
            $this->error('  ❌ Failed to send WhatsApp message');
            $this->error("     Error: " . ($result['message'] ?? 'Unknown error'));
            if (isset($result['error'])) {
                $this->error("     Details: " . $result['error']);
            }
        }
    }

    protected function testVoiceService()
    {
        $this->info('📞 Testing Voice Service');
        $this->line('------------------------');

        $voiceService = new VonageVoiceService();

        // Check configuration
        $voiceEnabled = config('services.vonage.voice_enabled', false);
        $voiceNumber = config('services.vonage.voice_number');
        $applicationId = config('services.vonage.application_id');
        $privateKey = config('services.vonage.private_key_path') ?: config('services.vonage.private_key');

        $this->line("  Enabled: " . ($voiceEnabled ? '✅ Yes' : '❌ No'));
        $this->line("  Voice Number: " . ($voiceNumber ? "✅ {$voiceNumber}" : '❌ Not configured'));
        $this->line("  Application ID: " . ($applicationId ? "✅ {$applicationId}" : '❌ Not configured'));
        $this->line("  Private Key: " . ($privateKey ? '✅ Configured' : '❌ Not configured'));

        if (!$voiceEnabled) {
            $this->warn('  ⚠️  Voice service is disabled. Set VONAGE_VOICE_ENABLED=true in .env');
            return;
        }

        if (!$voiceNumber || !$applicationId || !$privateKey) {
            $this->error('  ❌ Voice service credentials not fully configured');
            return;
        }

        // Test voice call
        $toNumber = $this->option('to');
        if (!$toNumber) {
            $toNumber = $this->ask('  Enter phone number to test voice call (E.164 format, e.g., 447123456789)', '');
        }

        if (empty($toNumber)) {
            $this->warn('  ⚠️  Skipping voice test - no phone number provided');
            return;
        }

        $message = 'Hello! This is a test call from DoctorOnTap. Thank you for testing our voice integration.';

        $this->line("  Making voice call to: {$toNumber}");
        $this->line("  Message: {$message}");

        if (!$this->confirm('  Do you want to make an actual call? (This will charge your Vonage account)', false)) {
            $this->warn('  ⚠️  Voice call test skipped');
            return;
        }

        $result = $voiceService->makeCall($toNumber, $message);

        if ($result['success'] && !($result['skipped'] ?? false)) {
            $this->info('  ✅ Voice call initiated successfully!');
            if (isset($result['data']['call_uuid'])) {
                $this->line("     Call UUID: " . $result['data']['call_uuid']);
            }
        } elseif ($result['skipped'] ?? false) {
            $this->warn('  ⚠️  Voice call skipped (disabled)');
        } else {
            $this->error('  ❌ Failed to initiate voice call');
            $this->error("     Error: " . ($result['message'] ?? 'Unknown error'));
        }
    }

    protected function testVideoService()
    {
        $this->info('🎥 Testing Video Service (OpenTok)');
        $this->line('----------------------------------');

        $videoService = new VonageVideoService();

        // Check status
        $status = $videoService->getStatus();

        $this->line("  Enabled: " . ($status['enabled'] ? '✅ Yes' : '❌ No'));
        $this->line("  Initialized: " . ($status['initialized'] ? '✅ Yes' : '❌ No'));
        $this->line("  Auth Method: " . ($status['auth_method'] ?? 'none'));
        
        if (($status['auth_method'] ?? '') === 'jwt') {
            $this->line("  Application ID: " . ($status['application_id_set'] ? '✅ Configured' : '❌ Not configured'));
            $this->line("  Private Key: " . ($status['private_key_set'] ? '✅ Configured' : '❌ Not configured'));
        } else {
            $this->line("  API Key: " . ($status['api_key_set'] ? '✅ Configured' : '❌ Not configured'));
            $this->line("  API Secret: " . ($status['api_secret_set'] ? '✅ Configured' : '❌ Not configured'));
        }

        if (!$status['enabled']) {
            $this->warn('  ⚠️  Video service is disabled. Set VONAGE_VIDEO_ENABLED=true in .env');
            return;
        }

        if (!$status['initialized']) {
            $this->error('  ❌ Video service not properly initialized');
            return;
        }

        // Test creating a session
        $this->line("  Testing session creation...");

        $result = $videoService->createSession();

        if ($result['success']) {
            $this->info('  ✅ Video session created successfully!');
            $this->line("     Session ID: " . ($result['data']['session_id'] ?? 'N/A'));

            // Test generating token
            $sessionId = $result['data']['session_id'] ?? null;
            if ($sessionId) {
                $this->line("  Testing token generation...");
                $tokenResult = $videoService->generateToken($sessionId, 'publisher');

                if ($tokenResult['success']) {
                    $this->info('  ✅ Token generated successfully!');
                    $this->line("     Token: " . substr($tokenResult['data']['token'] ?? '', 0, 50) . '...');
                } else {
                    $this->error('  ❌ Failed to generate token');
                    $this->error("     Error: " . ($tokenResult['message'] ?? 'Unknown error'));
                }
            }
        } else {
            $this->error('  ❌ Failed to create video session');
            $this->error("     Error: " . ($result['message'] ?? 'Unknown error'));
        }
    }

    protected function testConversationService()
    {
        $this->info('💭 Testing Conversation Service');
        $this->line('--------------------------------');

        $conversationService = new VonageConversationService();

        // Check configuration
        $conversationEnabled = config('services.vonage.conversation_enabled', false);
        $applicationId = config('services.vonage.application_id');
        $privateKey = config('services.vonage.private_key_path') ?: config('services.vonage.private_key');

        $this->line("  Enabled: " . ($conversationEnabled ? '✅ Yes' : '❌ No'));
        $this->line("  Application ID: " . ($applicationId ? "✅ {$applicationId}" : '❌ Not configured'));
        $this->line("  Private Key: " . ($privateKey ? '✅ Configured' : '❌ Not configured'));

        if (!$conversationEnabled) {
            $this->warn('  ⚠️  Conversation service is disabled. Set VONAGE_CONVERSATION_ENABLED=true in .env');
            return;
        }

        if (!$applicationId || !$privateKey) {
            $this->error('  ❌ Conversation service credentials not fully configured');
            return;
        }

        // Test creating a conversation
        $this->line("  Testing conversation creation...");

        $result = $conversationService->createConversation('Test Consultation Chat');

        if ($result['success']) {
            $this->info('  ✅ Conversation created successfully!');
            $this->line("     Conversation ID: " . ($result['data']['conversation_id'] ?? 'N/A'));
        } else {
            $this->error('  ❌ Failed to create conversation');
            $this->error("     Error: " . ($result['message'] ?? 'Unknown error'));
        }
    }
}

