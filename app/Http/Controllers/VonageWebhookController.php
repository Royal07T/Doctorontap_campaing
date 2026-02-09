<?php

namespace App\Http\Controllers;

use App\Models\InboundMessage;
use App\Models\Consultation;
use App\Models\Patient;
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

        // Security: Verify signature if configured
        $vonageService = app(\App\Services\VonageService::class);
        $signatureSecret = config('services.vonage.signature_secret');
        
        if ($signatureSecret && !$vonageService->verifySignature($request->all(), $signatureSecret)) {
            Log::warning('Vonage signature verification failed for inbound SMS webhook');
            return response('Unauthorized', 401);
        }

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

        // Security: Verify signature if configured
        $vonageService = app(\App\Services\VonageService::class);
        $signatureSecret = config('services.vonage.signature_secret');
        
        if ($signatureSecret && !$vonageService->verifySignature($request->all(), $signatureSecret)) {
            Log::warning('Vonage signature verification failed for status webhook');
            return response('Unauthorized', 401);
        }

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
     * Handle inbound messages from Vonage Messages API
     * POST /webhooks/inbound-message
     * 
     * This is the standard Messages API webhook endpoint that handles all channels:
     * SMS, MMS, WhatsApp, Facebook, Viber, RCS
     */
    public function handleInboundMessage(Request $request)
    {
        Log::info('Vonage Messages API inbound webhook received', [
            'data' => $request->all()
        ]);

        $data = $request->all();

        // Messages API format: from, to, message, timestamp, message_uuid, channel, etc.
        $from = $data['from']['number'] ?? $data['from'] ?? null;
        $to = $data['to']['number'] ?? $data['to'] ?? null;
        $messageUuid = $data['message_uuid'] ?? null;
        $timestamp = $data['timestamp'] ?? now();
        $channel = $data['channel'] ?? 'sms'; // sms, whatsapp, messenger, viber, rcs

        // Extract message content based on channel
        $messageData = $data['message'] ?? [];
        $messageType = $messageData['type'] ?? 'text';
        $messageText = null;
        $mediaUrl = null;
        $mediaType = null;
        $mediaCaption = null;

        // Handle different message types
        switch ($messageType) {
            case 'text':
                $messageText = $messageData['text'] ?? null;
                break;
            case 'image':
                $messageText = $messageData['caption'] ?? null;
                $mediaUrl = $messageData['image']['url'] ?? null;
                $mediaType = 'image';
                break;
            case 'video':
                $messageText = $messageData['caption'] ?? null;
                $mediaUrl = $messageData['video']['url'] ?? null;
                $mediaType = 'video';
                break;
            case 'audio':
                $mediaUrl = $messageData['audio']['url'] ?? null;
                $mediaType = 'audio';
                break;
            case 'file':
                $messageText = $messageData['caption'] ?? null;
                $mediaUrl = $messageData['file']['url'] ?? null;
                $mediaType = 'file';
                break;
        }

        // Store inbound message
        try {
            // Try to find associated patient/consultation
            $patient = null;
            $consultation = null;

            if ($from) {
                $patient = Patient::where('phone', 'like', '%' . substr($from, -10) . '%')
                    ->orWhere('phone', $from)
                    ->first();

                if ($patient) {
                    $consultation = Consultation::where('patient_id', $patient->id)
                        ->where('status', '!=', 'completed')
                        ->latest()
                        ->first();
                }
            }

            // Create inbound message record
            $inboundMessage = InboundMessage::create([
                'message_uuid' => $messageUuid,
                'channel' => $channel,
                'message_type' => $messageType,
                'from_number' => $from,
                'to_number' => $to,
                'message_text' => $messageText,
                'media_url' => $mediaUrl,
                'media_type' => $mediaType,
                'media_caption' => $mediaCaption,
                'status' => 'received',
                'received_at' => $timestamp,
                'raw_data' => $data,
                'consultation_id' => $consultation?->id,
                'patient_id' => $patient?->id,
            ]);

            Log::info('Vonage Messages API inbound message stored', [
                'message_uuid' => $messageUuid,
                'channel' => $channel,
                'from' => $from,
                'type' => $messageType,
            ]);

            // Process the message
            $this->processInboundMessage($inboundMessage);

        } catch (\Exception $e) {
            Log::error('Failed to process Vonage Messages API inbound message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
        }

        // Return 200 OK to acknowledge receipt
        return response('OK', 200);
    }

    /**
     * Handle message status updates from Vonage Messages API
     * POST /webhooks/message-status
     * 
     * This is the standard Messages API status webhook endpoint
     */
    public function handleMessageStatus(Request $request)
    {
        Log::info('Vonage Messages API status webhook received', [
            'data' => $request->all()
        ]);

        $data = $request->all();

        // Messages API format: message_uuid, status, timestamp, channel, etc.
        $messageUuid = $data['message_uuid'] ?? null;
        $status = $data['status'] ?? null;
        $timestamp = $data['timestamp'] ?? now();
        $channel = $data['channel'] ?? 'sms';
        $errorCode = $data['error_code'] ?? null;
        $errorText = $data['error_text'] ?? null;

        // Map Vonage status to our internal status
        $internalStatus = $this->mapStatus($status);

        // Update notification tracking if message UUID exists
        if ($messageUuid) {
            try {
                DB::table('sms_inbound_logs')
                    ->where('message_id', $messageUuid)
                    ->orWhere('message_uuid', $messageUuid)
                    ->update([
                        'status' => $internalStatus,
                        'error_code' => $errorCode,
                        'error_text' => $errorText,
                        'updated_at' => now(),
                    ]);

                // Also update InboundMessage if exists
                InboundMessage::where('message_uuid', $messageUuid)
                    ->update([
                        'status' => $internalStatus,
                        'updated_at' => now(),
                    ]);

                Log::info('Vonage Messages API status updated', [
                    'message_uuid' => $messageUuid,
                    'status' => $status,
                    'internal_status' => $internalStatus,
                    'channel' => $channel,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to update message status', [
                    'message_uuid' => $messageUuid,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Return 200 OK to acknowledge receipt
        return response('OK', 200);
    }

    /**
     * Handle inbound WhatsApp messages from Vonage
     * POST /vonage/webhook/whatsapp/inbound
     * 
     * Handles all WhatsApp message types: text, image, video, audio, file, location, contact
     */
    public function handleWhatsAppInbound(Request $request)
    {
        Log::info('Vonage WhatsApp inbound webhook received', [
            'data' => $request->all()
        ]);

        $data = $request->all();

        try {
            // WhatsApp Messages API format
            $from = $data['from']['number'] ?? $data['from'] ?? null;
            $to = $data['to']['number'] ?? $data['to'] ?? null;
            $messageUuid = $data['message_uuid'] ?? null;
            $timestamp = $data['timestamp'] ?? now();
            
            // Extract message content
            $messageData = $data['message'] ?? [];
            $messageType = $messageData['type'] ?? 'text';
            $messageText = null;
            $mediaUrl = null;
            $mediaType = null;
            $mediaCaption = null;
            $mediaName = null;
            $latitude = null;
            $longitude = null;
            $locationName = null;
            $locationAddress = null;
            $contactData = null;
            
            switch ($messageType) {
                case 'text':
                    $messageText = $messageData['text']['body'] ?? null;
                    break;
                case 'image':
                    $messageText = $messageData['image']['caption'] ?? '[Image]';
                    $mediaUrl = $messageData['image']['url'] ?? null;
                    $mediaType = $messageData['image']['content_type'] ?? 'image/jpeg';
                    $mediaCaption = $messageData['image']['caption'] ?? null;
                    break;
                case 'video':
                    $messageText = $messageData['video']['caption'] ?? '[Video]';
                    $mediaUrl = $messageData['video']['url'] ?? null;
                    $mediaType = $messageData['video']['content_type'] ?? 'video/mp4';
                    $mediaCaption = $messageData['video']['caption'] ?? null;
                    break;
                case 'audio':
                    $messageText = '[Audio]';
                    $mediaUrl = $messageData['audio']['url'] ?? null;
                    $mediaType = $messageData['audio']['content_type'] ?? 'audio/mpeg';
                    break;
                case 'file':
                case 'document':
                    $messageText = $messageData['file']['caption'] ?? $messageData['document']['caption'] ?? '[File]';
                    $mediaUrl = $messageData['file']['url'] ?? $messageData['document']['url'] ?? null;
                    $mediaType = $messageData['file']['content_type'] ?? $messageData['document']['content_type'] ?? 'application/octet-stream';
                    $mediaCaption = $messageData['file']['caption'] ?? $messageData['document']['caption'] ?? null;
                    $mediaName = $messageData['file']['name'] ?? $messageData['document']['filename'] ?? null;
                    break;
                case 'location':
                    $messageText = '[Location]';
                    $latitude = $messageData['location']['latitude'] ?? null;
                    $longitude = $messageData['location']['longitude'] ?? null;
                    $locationName = $messageData['location']['name'] ?? null;
                    $locationAddress = $messageData['location']['address'] ?? null;
                    break;
                case 'contact':
                    $messageText = '[Contact Card]';
                    $contactData = $messageData['contact'] ?? null;
                    break;
                case 'sticker':
                    $messageText = '[Sticker]';
                    $mediaUrl = $messageData['sticker']['url'] ?? null;
                    $mediaType = 'image/webp';
                    break;
            }
            
            // Try to link to patient/consultation
            $patient = $this->findPatientByPhone($from);
            $consultation = $patient ? $this->findActiveConsultation($patient->id) : null;
            
            // Create inbound message record
            $inboundMessage = InboundMessage::create([
                'message_uuid' => $messageUuid,
                'channel' => 'whatsapp',
                'message_type' => $messageType,
                'from_number' => $from,
                'to_number' => $to,
                'message_text' => $messageText,
                'media_url' => $mediaUrl ?? null,
                'media_type' => $mediaType ?? null,
                'media_caption' => $mediaCaption ?? null,
                'media_name' => $mediaName ?? null,
                'latitude' => $latitude ?? null,
                'longitude' => $longitude ?? null,
                'location_name' => $locationName ?? null,
                'location_address' => $locationAddress ?? null,
                'contact_data' => $contactData ?? null,
                'status' => 'received',
                'received_at' => $timestamp,
                'raw_data' => $data,
                'consultation_id' => $consultation?->id,
                'patient_id' => $patient?->id,
            ]);
            
            Log::info('Vonage WhatsApp inbound message stored', [
                'message_uuid' => $messageUuid,
                'from' => $from,
                'type' => $messageType,
                'has_media' => !empty($mediaUrl)
            ]);
            
            // Process the message
            $this->processInboundMessage($inboundMessage);
            
        } catch (\Exception $e) {
            Log::error('Failed to process Vonage WhatsApp inbound message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
    
    /**
     * Process inbound message (customize based on your needs)
     */
    protected function processInboundMessage(InboundMessage $message): void
    {
        // Example: Auto-reply logic, notification triggers, etc.
        // This is based on the Vonage Blog "How Does It Do That?" section
        
        if ($message->channel === 'whatsapp' && $message->message_type === 'text') {
            $text = trim($message->message_text);
            
            // If the message is a number, multiply it and reply
            if (is_numeric($text)) {
                $number = (int) $text;
                $randomNumber = random_int(1, 10);
                $respondNumber = $number * $randomNumber;
                
                $replyText = "The answer is {$respondNumber}! We multiplied your number ({$number}) by {$randomNumber}. This is an automated reply from DoctorOnTap via Vonage Messages API. 🤖";
                
                $vonageService = app(\App\Services\VonageService::class);
                $vonageService->sendWhatsAppMessage($message->from_number, $replyText);
                
                Log::info('WhatsApp auto-reply sent', [
                    'original' => $number,
                    'multiplier' => $randomNumber,
                    'result' => $respondNumber,
                    'to' => $message->from_number
                ]);
            }
        }
        
        // Mark as processed
        $message->markAsProcessed();
    }
    
    /**
     * Find patient by phone number
     */
    protected function findPatientByPhone(?string $phone): ?Patient
    {
        if (!$phone) {
            return null;
        }
        
        // Normalize phone number
        $normalized = preg_replace('/[\s\-\(\)\+]/', '', $phone);
        
        // Try to find patient by mobile number
        return Patient::where('mobile', 'like', "%{$normalized}%")
            ->orWhere('mobile', 'like', "%{$phone}%")
            ->first();
    }
    
    /**
     * Find active consultation for patient
     */
    protected function findActiveConsultation(int $patientId): ?Consultation
    {
        return Consultation::where('patient_id', $patientId)
            ->whereIn('status', ['pending', 'scheduled', 'active'])
            ->latest()
            ->first();
    }
}







