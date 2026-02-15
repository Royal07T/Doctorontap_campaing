<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PusherBeamsWebhookController extends Controller
{
    /**
     * Handle Pusher Beams webhook events
     * POST /webhooks/pusher-beams
     * 
     * Handles the following event types:
     * - v1.PublishToUsersAttempt
     * - v1.UserNotificationAcknowledgement
     * - v1.UserNotificationOpen
     */
    public function handle(Request $request)
    {
        Log::info('Pusher Beams webhook received', [
            'data' => $request->all(),
            'ip' => $request->ip(),
        ]);

        // Verify Basic Auth if configured
        if (!$this->verifyBasicAuth($request)) {
            Log::warning('Pusher Beams webhook authentication failed', [
                'ip' => $request->ip(),
            ]);
            return response('Unauthorized', 401);
        }

        $data = $request->all();
        $eventType = $data['metadata']['event_type'] ?? null;

        try {
            switch ($eventType) {
                case 'v1.PublishToUsersAttempt':
                    $this->handlePublishToUsersAttempt($data);
                    break;

                case 'v1.UserNotificationAcknowledgement':
                    $this->handleUserNotificationAcknowledgement($data);
                    break;

                case 'v1.UserNotificationOpen':
                    $this->handleUserNotificationOpen($data);
                    break;

                default:
                    Log::warning('Unknown Pusher Beams webhook event type', [
                        'event_type' => $eventType,
                        'data' => $data,
                    ]);
            }

            // Store webhook event in logs
            $this->storeWebhookLog($eventType, $data);

        } catch (\Exception $e) {
            Log::error('Failed to process Pusher Beams webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'event_type' => $eventType,
                'data' => $data,
            ]);
        }

        // Return 200 OK to acknowledge receipt
        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Handle v1.PublishToUsersAttempt webhook
     * Contains summary of what happened during a publish to Authenticated Users
     */
    protected function handlePublishToUsersAttempt(array $data): void
    {
        $payload = $data['payload'] ?? [];
        $publishId = $payload['publish_id'] ?? null;
        $instanceId = $payload['instance_id'] ?? null;
        $usersDelivered = $payload['users_delivered_to_gateway'] ?? [];
        $usersNoDevices = $payload['users_no_devices'] ?? [];
        $usersGatewayFailed = $payload['users_gateway_failed'] ?? [];

        Log::info('Pusher Beams publish attempt processed', [
            'publish_id' => $publishId,
            'instance_id' => $instanceId,
            'users_delivered' => count($usersDelivered),
            'users_no_devices' => count($usersNoDevices),
            'users_failed' => count($usersGatewayFailed),
        ]);

        // Store publish attempt results
        try {
            DB::table('pusher_beams_publish_logs')->insert([
                'publish_id' => $publishId,
                'instance_id' => $instanceId,
                'event_type' => 'v1.PublishToUsersAttempt',
                'users_delivered' => json_encode($usersDelivered),
                'users_no_devices' => json_encode($usersNoDevices),
                'users_gateway_failed' => json_encode($usersGatewayFailed),
                'users_delivered_count' => count($usersDelivered),
                'users_no_devices_count' => count($usersNoDevices),
                'users_failed_count' => count($usersGatewayFailed),
                'raw_data' => json_encode($data),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store Pusher Beams publish log', [
                'error' => $e->getMessage(),
                'publish_id' => $publishId,
            ]);
        }

        // Update notification tracking if publish_id can be linked to a notification
        // You may need to store publish_id when sending notifications to link them
        if ($publishId) {
            $this->updateNotificationTracking($publishId, [
                'delivered_count' => count($usersDelivered),
                'no_devices_count' => count($usersNoDevices),
                'failed_count' => count($usersGatewayFailed),
            ]);
        }
    }

    /**
     * Handle v1.UserNotificationAcknowledgement webhook
     * Sent when a notification is acknowledged as delivered by a user's device
     */
    protected function handleUserNotificationAcknowledgement(array $data): void
    {
        $payload = $data['payload'] ?? [];
        $publishId = $payload['publish_id'] ?? null;
        $userId = $payload['user_id'] ?? null;
        $instanceId = $payload['instance_id'] ?? null;

        Log::info('Pusher Beams notification acknowledged', [
            'publish_id' => $publishId,
            'user_id' => $userId,
            'instance_id' => $instanceId,
        ]);

        // Store acknowledgement
        try {
            DB::table('pusher_beams_acknowledgements')->insert([
                'publish_id' => $publishId,
                'user_id' => $userId,
                'instance_id' => $instanceId,
                'event_type' => 'v1.UserNotificationAcknowledgement',
                'raw_data' => json_encode($data),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store Pusher Beams acknowledgement', [
                'error' => $e->getMessage(),
                'publish_id' => $publishId,
                'user_id' => $userId,
            ]);
        }

        // Update notification tracking
        if ($publishId && $userId) {
            $this->updateNotificationTracking($publishId, [
                'acknowledged' => true,
                'acknowledged_user_id' => $userId,
                'acknowledged_at' => now(),
            ]);
        }
    }

    /**
     * Handle v1.UserNotificationOpen webhook
     * Sent when a notification is opened by a user on one of their devices
     */
    protected function handleUserNotificationOpen(array $data): void
    {
        $payload = $data['payload'] ?? [];
        $publishId = $payload['publish_id'] ?? null;
        $userId = $payload['user_id'] ?? null;
        $instanceId = $payload['instance_id'] ?? null;

        Log::info('Pusher Beams notification opened', [
            'publish_id' => $publishId,
            'user_id' => $userId,
            'instance_id' => $instanceId,
        ]);

        // Store open event
        try {
            DB::table('pusher_beams_opens')->insert([
                'publish_id' => $publishId,
                'user_id' => $userId,
                'instance_id' => $instanceId,
                'event_type' => 'v1.UserNotificationOpen',
                'raw_data' => json_encode($data),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store Pusher Beams open event', [
                'error' => $e->getMessage(),
                'publish_id' => $publishId,
                'user_id' => $userId,
            ]);
        }

        // Update notification tracking
        if ($publishId && $userId) {
            $this->updateNotificationTracking($publishId, [
                'opened' => true,
                'opened_user_id' => $userId,
                'opened_at' => now(),
            ]);
        }

        // If you want to mark notification as read when opened
        // You can extract notification_id from custom_data if stored
        $customData = $data['custom_data'] ?? [];
        if (isset($customData['notification_id'])) {
            $this->markNotificationAsRead($customData['notification_id'], $userId);
        }
    }

    /**
     * Verify Basic Auth credentials
     * Pusher Beams supports Basic Auth in webhook URLs: https://username:password@yourwebsite.com/webhook
     */
    protected function verifyBasicAuth(Request $request): bool
    {
        $webhookUsername = config('services.pusher_beams.webhook_username');
        $webhookPassword = config('services.pusher_beams.webhook_password');

        // If no credentials configured, allow (for development)
        if (!$webhookUsername || !$webhookPassword) {
            if (app()->environment('production')) {
                Log::warning('Pusher Beams webhook credentials not configured in production');
                return false;
            }
            return true; // Allow in non-production if not configured
        }

        // Get Basic Auth credentials from request
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Basic ')) {
            return false;
        }

        // Decode Basic Auth
        $encoded = substr($authHeader, 6);
        $decoded = base64_decode($encoded, true);
        
        if ($decoded === false) {
            return false;
        }

        [$username, $password] = explode(':', $decoded, 2) + [null, null];

        // Verify credentials
        return hash_equals($webhookUsername, $username ?? '') 
            && hash_equals($webhookPassword, $password ?? '');
    }

    /**
     * Store webhook event log
     */
    protected function storeWebhookLog(string $eventType, array $data): void
    {
        try {
            DB::table('pusher_beams_webhook_logs')->insert([
                'event_type' => $eventType,
                'event_id' => $data['metadata']['event_id'] ?? null,
                'instance_id' => $data['payload']['instance_id'] ?? null,
                'publish_id' => $data['payload']['publish_id'] ?? null,
                'user_id' => $data['payload']['user_id'] ?? null,
                'raw_data' => json_encode($data),
                'created_at' => $data['metadata']['created_at'] ?? now(),
                'processed_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store Pusher Beams webhook log', [
                'error' => $e->getMessage(),
                'event_type' => $eventType,
            ]);
        }
    }

    /**
     * Update notification tracking based on webhook data
     * This assumes you store publish_id when sending notifications
     */
    protected function updateNotificationTracking(string $publishId, array $updates): void
    {
        try {
            // Try to find notification by publish_id
            // You may need to store publish_id in notifications table or a separate tracking table
            $updated = DB::table('notification_tracking_logs')
                ->where('external_message_id', $publishId)
                ->orWhere('publish_id', $publishId)
                ->update(array_merge($updates, [
                    'updated_at' => now(),
                ]));

            if ($updated) {
                Log::info('Updated notification tracking from Pusher Beams webhook', [
                    'publish_id' => $publishId,
                    'updates' => $updates,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update notification tracking', [
                'error' => $e->getMessage(),
                'publish_id' => $publishId,
            ]);
        }
    }

    /**
     * Mark notification as read when opened
     */
    protected function markNotificationAsRead(int $notificationId, string $userId): void
    {
        try {
            // Extract user_type and user_id from userId format: {user_type}_{user_id}
            [$userType, $userIdNumber] = explode('_', $userId, 2) + [null, null];

            if ($userType && $userIdNumber) {
                DB::table('notifications')
                    ->where('id', $notificationId)
                    ->where('user_type', $userType)
                    ->where('user_id', $userIdNumber)
                    ->whereNull('read_at')
                    ->update([
                        'read_at' => now(),
                        'updated_at' => now(),
                    ]);

                Log::info('Notification marked as read from Pusher Beams open event', [
                    'notification_id' => $notificationId,
                    'user_id' => $userId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'error' => $e->getMessage(),
                'notification_id' => $notificationId,
                'user_id' => $userId,
            ]);
        }
    }
}

