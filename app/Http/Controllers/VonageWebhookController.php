<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class VonageWebhookController extends Controller
{
    /**
     * Handle inbound SMS messages from Vonage
     * POST /vonage/webhook/inbound
     */
    public function handleInbound(Request $request)
    {
        Log::info('Vonage inbound webhook received', [
            'data' => $request->all()
        ]);

        // Vonage sends different formats for Legacy API vs Messages API
        // Legacy API: msisdn, to, messageId, text, type, keyword, message-timestamp
        // Messages API: from, to, message, timestamp, message_uuid, etc.

        $data = $request->all();

        // Store inbound message (you can customize this based on your needs)
        try {
            DB::table('sms_inbound_logs')->insert([
                'from' => $data['msisdn'] ?? $data['from'] ?? null,
                'to' => $data['to'] ?? null,
                'message' => $data['text'] ?? $data['message'] ?? null,
                'message_id' => $data['messageId'] ?? $data['message_uuid'] ?? null,
                'timestamp' => $data['message-timestamp'] ?? $data['timestamp'] ?? now(),
                'type' => $data['type'] ?? 'text',
                'raw_data' => json_encode($data),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Vonage inbound message logged', [
                'from' => $data['msisdn'] ?? $data['from'] ?? null,
                'message_id' => $data['messageId'] ?? $data['message_uuid'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log Vonage inbound message', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }

        // Return 200 OK to acknowledge receipt
        return response('OK', 200);
    }

    /**
     * Handle delivery status updates from Vonage
     * POST /vonage/webhook/status
     */
    public function handleStatus(Request $request)
    {
        Log::info('Vonage status webhook received', [
            'data' => $request->all()
        ]);

        $data = $request->all();

        // Legacy API format: messageId, status, err-code, network, price, scts, message-timestamp
        // Messages API format: message_uuid, status, timestamp, etc.

        $messageId = $data['messageId'] ?? $data['message_uuid'] ?? null;
        $status = $data['status'] ?? null;
        $errorCode = $data['err-code'] ?? $data['error_code'] ?? null;

        // Map Vonage status to our internal status
        $internalStatus = $this->mapStatus($status);

        // Update notification tracking if message ID exists
        if ($messageId) {
            try {
                $updated = DB::table('notification_tracking_logs')
                    ->where('external_message_id', $messageId)
                    ->update([
                        'status' => $internalStatus,
                        'delivered_at' => $internalStatus === 'delivered' ? now() : null,
                        'failed_at' => $internalStatus === 'failed' ? now() : null,
                        'error_code' => $errorCode,
                        'raw_response' => json_encode($data),
                        'updated_at' => now(),
                    ]);

                if ($updated) {
                    Log::info('Updated notification tracking from Vonage webhook', [
                        'message_id' => $messageId,
                        'status' => $internalStatus,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to update notification tracking from webhook', [
                    'error' => $e->getMessage(),
                    'message_id' => $messageId,
                ]);
            }
        }

        // Store status update in logs
        try {
            DB::table('sms_status_logs')->insert([
                'message_id' => $messageId,
                'status' => $status,
                'error_code' => $errorCode,
                'network' => $data['network'] ?? null,
                'price' => $data['price'] ?? null,
                'raw_data' => json_encode($data),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log Vonage status update', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }

        // Return 200 OK to acknowledge receipt
        return response('OK', 200);
    }

    /**
     * Map Vonage status to internal status
     *
     * @param string|null $vonageStatus
     * @return string
     */
    protected function mapStatus(?string $vonageStatus): string
    {
        if (!$vonageStatus) {
            return 'pending';
        }

        // Legacy API status codes: 0 = success, others = various errors
        // Messages API status: delivered, rejected, etc.
        
        $statusMap = [
            '0' => 'delivered',           // Legacy API success
            'delivered' => 'delivered',    // Messages API
            'accepted' => 'sent',         // Messages API
            '1' => 'failed',              // Legacy API: Throttled
            '2' => 'failed',              // Legacy API: Missing params
            '3' => 'failed',              // Legacy API: Invalid params
            '4' => 'failed',              // Legacy API: Invalid credentials
            '5' => 'failed',              // Legacy API: Internal error
            '6' => 'failed',              // Legacy API: Invalid message
            '7' => 'failed',              // Legacy API: Number barred
            '8' => 'failed',              // Legacy API: Partner account barred
            '9' => 'failed',              // Legacy API: Partner quota exceeded
            '11' => 'failed',             // Legacy API: Account not enabled
            '12' => 'failed',             // Legacy API: Message too long
            '13' => 'failed',             // Legacy API: Communication failed
            '14' => 'failed',             // Legacy API: Invalid signature
            '15' => 'failed',             // Legacy API: Invalid sender address
            '22' => 'failed',             // Legacy API: Invalid network code
            '23' => 'failed',             // Legacy API: Invalid callback URL
            '29' => 'failed',             // Legacy API: Non-whitelisted destination
            'rejected' => 'failed',       // Messages API
            'undelivered' => 'failed',    // Messages API
        ];

        return $statusMap[strtolower($vonageStatus)] ?? 'pending';
    }

    /**
     * Handle inbound WhatsApp messages from Vonage
     * POST /vonage/webhook/whatsapp/inbound
     */
    public function handleWhatsAppInbound(Request $request)
    {
        Log::info('Vonage WhatsApp inbound webhook received', [
            'data' => $request->all()
        ]);

        $data = $request->all();

        // WhatsApp Messages API format: from, to, message, timestamp, message_uuid, channel, etc.
        $from = $data['from'] ?? $data['from']['number'] ?? null;
        $to = $data['to'] ?? $data['to']['number'] ?? null;
        $message = $data['message'] ?? null;
        $messageUuid = $data['message_uuid'] ?? null;
        $timestamp = $data['timestamp'] ?? now();

        // Extract message content based on type
        $messageText = null;
        $messageType = $data['message']['type'] ?? 'text';
        
        if ($messageType === 'text') {
            $messageText = $message['text']['body'] ?? null;
        } elseif ($messageType === 'image') {
            $messageText = $message['image']['caption'] ?? '[Image]';
        } elseif ($messageType === 'video') {
            $messageText = $message['video']['caption'] ?? '[Video]';
        } elseif ($messageType === 'document') {
            $messageText = $message['document']['caption'] ?? '[Document]';
        }

        // Store inbound WhatsApp message
        try {
            DB::table('whatsapp_inbound_logs')->insert([
                'from' => $from,
                'to' => $to,
                'message' => $messageText,
                'message_type' => $messageType,
                'message_uuid' => $messageUuid,
                'timestamp' => $timestamp,
                'raw_data' => json_encode($data),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Vonage WhatsApp inbound message logged', [
                'from' => $from,
                'message_uuid' => $messageUuid,
                'type' => $messageType
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log Vonage WhatsApp inbound message', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }

        // Return 200 OK to acknowledge receipt
        return response('OK', 200);
    }

    /**
     * Handle WhatsApp delivery status updates from Vonage
     * POST /vonage/webhook/whatsapp/status
     */
    public function handleWhatsAppStatus(Request $request)
    {
        Log::info('Vonage WhatsApp status webhook received', [
            'data' => $request->all()
        ]);

        $data = $request->all();

        // Messages API format: message_uuid, status, timestamp, channel, etc.
        $messageUuid = $data['message_uuid'] ?? null;
        $status = $data['status'] ?? null;
        $errorCode = $data['error_code'] ?? null;
        $channel = $data['channel'] ?? 'whatsapp';

        // Map Vonage status to our internal status
        $internalStatus = $this->mapWhatsAppStatus($status);

        // Update notification tracking if message UUID exists
        if ($messageUuid) {
            try {
                $updated = DB::table('notification_tracking_logs')
                    ->where('external_message_id', $messageUuid)
                    ->update([
                        'status' => $internalStatus,
                        'delivered_at' => $internalStatus === 'delivered' ? now() : null,
                        'failed_at' => $internalStatus === 'failed' ? now() : null,
                        'error_code' => $errorCode,
                        'raw_response' => json_encode($data),
                        'updated_at' => now(),
                    ]);

                if ($updated) {
                    Log::info('Updated notification tracking from Vonage WhatsApp webhook', [
                        'message_uuid' => $messageUuid,
                        'status' => $internalStatus,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to update notification tracking from WhatsApp webhook', [
                    'error' => $e->getMessage(),
                    'message_uuid' => $messageUuid,
                ]);
            }
        }

        // Store status update in logs
        try {
            DB::table('whatsapp_status_logs')->insert([
                'message_uuid' => $messageUuid,
                'status' => $status,
                'error_code' => $errorCode,
                'channel' => $channel,
                'raw_data' => json_encode($data),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log Vonage WhatsApp status update', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }

        // Return 200 OK to acknowledge receipt
        return response('OK', 200);
    }

    /**
     * Map Vonage WhatsApp status to internal status
     *
     * @param string|null $vonageStatus
     * @return string
     */
    protected function mapWhatsAppStatus(?string $vonageStatus): string
    {
        if (!$vonageStatus) {
            return 'pending';
        }

        $statusMap = [
            'submitted' => 'sent',
            'delivered' => 'delivered',
            'read' => 'read',
            'rejected' => 'failed',
            'undelivered' => 'failed',
            'failed' => 'failed',
        ];

        return $statusMap[strtolower($vonageStatus)] ?? 'pending';
    }
}







