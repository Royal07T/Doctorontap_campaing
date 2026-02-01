<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\VonageVideoService;

class TestVonageVideo extends Command
{
    protected $signature = 'vonage:test-video';
    protected $description = 'Test Vonage Video API (OpenTok) integration';

    public function handle(VonageVideoService $videoService)
    {
        $this->info('🚀 Vonage Video API Test Command');
        
        $status = $videoService->getStatus();
        
        $this->info('📋 Current Configuration:');
        $this->table(['Setting', 'Value'], [
            ['Enabled', $status['enabled'] ? '✅ Yes' : '❌ No'],
            ['Initialized', $status['initialized'] ? '✅ Yes' : '❌ No'],
            ['API Key Set', $status['api_key_set'] ? '✅ Yes' : '❌ No'],
            ['API Secret Set', $status['api_secret_set'] ? '✅ Yes' : '❌ No'],
        ]);

        if (!$videoService->isInitialized()) {
            $this->error('❌ Video service not initialized! Check your credentials in .env');
            return 1;
        }

        $this->info('⏳ Creating test video session...');
        $result = $videoService->createSession();

        if ($result['success']) {
            $sessionId = $result['session_id'];
            $this->info("✅ Session created successfully!");
            $this->info("🆔 Session ID: $sessionId");

            $this->info('⏳ Generating test token...');
            $tokenResult = $videoService->generateToken($sessionId);

            if ($tokenResult['success']) {
                $this->info("✅ Token generated successfully!");
                $this->line("🔑 Token: <fg=gray>" . substr($tokenResult['token'], 0, 50) . "...</>");
                $this->info("🎉 Video integration is working perfectly!");
            } else {
                $this->error("❌ Token generation failed: " . $tokenResult['message']);
            }
        } else {
            $this->error("❌ Session creation failed: " . $result['message']);
        }

        return 0;
    }
}
