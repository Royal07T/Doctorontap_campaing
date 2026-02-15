<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PusherBeamsService;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class TestPusherBeams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pusher-beams:test 
                            {--user-type=admin : User type (admin, doctor, patient, nurse, etc.)}
                            {--user-id=1 : User ID to send test notification to}
                            {--title=Test Notification : Notification title}
                            {--message=This is a test push notification from Pusher Beams : Notification message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Pusher Beams push notification integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Testing Pusher Beams Integration');
        $this->newLine();
        $this->line('=====================================');
        $this->newLine();

        $beamsService = app(PusherBeamsService::class);

        // Step 1: Check if Pusher Beams is enabled
        $this->info('✓ Step 1: Checking Pusher Beams configuration...');
        
        if (!$beamsService->isEnabled()) {
            $this->error('  ✗ Pusher Beams is not enabled or configured!');
            $this->newLine();
            $this->warn('Please check your .env file:');
            $this->line('  - PUSHER_BEAMS_INSTANCE_ID');
            $this->line('  - PUSHER_BEAMS_SECRET_KEY');
            $this->line('  - PUSHER_BEAMS_ENABLED=true');
            return Command::FAILURE;
        }
        
        $this->info('  ✓ Pusher Beams is enabled');
        $this->newLine();

        // Step 2: Test token generation
        $this->info('✓ Step 2: Testing token generation...');
        
        $userType = $this->option('user-type');
        $userId = $this->option('user-id');
        $beamsUserId = "{$userType}_{$userId}";
        
        try {
            $token = $beamsService->generateToken($beamsUserId);
            
            if ($token && isset($token['token'])) {
                $this->info("  ✓ Token generated successfully for user: {$beamsUserId}");
                $this->line("  Token (first 50 chars): " . substr($token['token'], 0, 50) . '...');
            } else {
                $this->error('  ✗ Failed to generate token');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("  ✗ Token generation failed: " . $e->getMessage());
            return Command::FAILURE;
        }
        
        $this->newLine();

        // Step 3: Send test notification
        $this->info('✓ Step 3: Sending test push notification...');
        
        $title = $this->option('title');
        $message = $this->option('message');
        
        try {
            $response = $beamsService->publishToUsers(
                [$beamsUserId],
                $title,
                $message,
                [
                    'test' => true,
                    'timestamp' => now()->toIso8601String(),
                ],
                url('/admin/notifications')
            );

            if ($response) {
                $this->info("  ✓ Push notification sent successfully!");
                $this->line("  User ID: {$beamsUserId}");
                $this->line("  Title: {$title}");
                $this->line("  Message: {$message}");
                
                if (isset($response['publishId'])) {
                    $this->line("  Publish ID: {$response['publishId']}");
                }
            } else {
                $this->error('  ✗ Failed to send push notification');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("  ✗ Push notification failed: " . $e->getMessage());
            $this->line("  Error details: " . $e->getTraceAsString());
            return Command::FAILURE;
        }
        
        $this->newLine();

        // Step 4: Create database notification (optional)
        $this->info('✓ Step 4: Creating database notification...');
        
        try {
            $notification = Notification::create([
                'user_type' => $userType,
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => 'info',
                'action_url' => url('/admin/notifications'),
                'data' => [
                    'test' => true,
                    'pusher_beams_test' => true,
                ],
            ]);
            
            $this->info("  ✓ Database notification created (ID: {$notification->id})");
            $this->line("  Note: This will also trigger automatic push notification via Notification model");
        } catch (\Exception $e) {
            $this->warn("  ⚠ Database notification creation failed: " . $e->getMessage());
            $this->line("  Push notification was still sent directly");
        }
        
        $this->newLine();
        $this->line('=====================================');
        $this->info('✅ Pusher Beams test completed successfully!');
        $this->newLine();
        $this->line('Next steps:');
        $this->line('  1. Check your device/browser for the push notification');
        $this->line('  2. Verify the notification appears in the app');
        $this->line('  3. Check logs: storage/logs/laravel.log');
        $this->newLine();

        return Command::SUCCESS;
    }
}

