<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Consultation;
use App\Notifications\ConsultationWhatsAppNotification;

class WhatsAppWebhookController extends Controller
{
    /**
     * Handle incoming webhook from Termii WhatsApp
     * 
     * This endpoint receives:
     * 1. Incoming messages from patients/doctors
     * 2. Delivery status updates
     * 3. Read receipts
     * 4. Message failures
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)
    {
        // Log all incoming webhooks for debugging
        Log::info('Termii WhatsApp Webhook Received', [
            'event_type' => $request->input('event'),
            'timestamp' => now()->toDateTimeString(),
            'ip' => $request->ip(),
            'full_payload' => $request->all()
        ]);

        try {
            $event = $request->input('event');
            $data = $request->input('data', []);

            // Route to appropriate handler based on event type
            switch ($event) {
                case 'message.received':
                case 'whatsapp.incoming':
                    return $this->handleIncomingMessage($data);

                case 'message.sent':
                case 'whatsapp.sent':
                    return $this->handleMessageSent($data);

                case 'message.delivered':
                case 'whatsapp.delivered':
                    return $this->handleMessageDelivered($data);

                case 'message.read':
                case 'whatsapp.read':
                    return $this->handleMessageRead($data);

                case 'message.failed':
                case 'whatsapp.failed':
                    return $this->handleMessageFailed($data);

                default:
                    Log::warning('Unknown WhatsApp webhook event type', [
                        'event' => $event,
                        'data' => $data
                    ]);
                    return response()->json([
                        'status' => 'ok',
                        'message' => 'Event type not handled'
                    ], 200);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp Webhook Processing Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all()
            ]);

            // Always return 200 to prevent Termii from retrying
            return response()->json([
                'status' => 'error',
                'message' => 'Processing error'
            ], 200);
        }
    }

    /**
     * Handle incoming message from patient/doctor
     * 
     * IMPORTANT: This opens the 24-hour FREE messaging window!
     *
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function handleIncomingMessage(array $data): \Illuminate\Http\JsonResponse
    {
        $from = $data['from'] ?? null;
        $message = $data['text'] ?? $data['message'] ?? '';
        $messageId = $data['message_id'] ?? null;
        $timestamp = $data['timestamp'] ?? now()->toDateTimeString();

        Log::info('Incoming WhatsApp Message', [
            'from' => $from,
            'message' => $message,
            'message_id' => $messageId,
            'timestamp' => $timestamp
        ]);

        // Store message in database (optional - for conversation history)
        $this->storeIncomingMessage($from, $message, $messageId, $timestamp);

        // Find consultation by phone number
        $consultation = $this->findConsultationByPhone($from);

        if ($consultation) {
            // 🎉 24-HOUR FREE MESSAGING WINDOW IS NOW OPEN!
            Log::info('WhatsApp conversation window opened', [
                'consultation_id' => $consultation->id,
                'reference' => $consultation->reference,
                'from' => $from,
                'window_expires_at' => now()->addHours(24)->toDateTimeString()
            ]);

            // Mark consultation as having active WhatsApp conversation
            $consultation->update([
                'whatsapp_last_message_at' => now(),
                'whatsapp_window_open' => true,
                'whatsapp_window_expires_at' => now()->addHours(24)
            ]);

            // Auto-reply logic (optional)
            $this->sendAutoReply($consultation, $message);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Message received and processed'
        ], 200);
    }

    /**
     * Handle message sent confirmation
     *
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function handleMessageSent(array $data): \Illuminate\Http\JsonResponse
    {
        $messageId = $data['message_id'] ?? null;
        $to = $data['to'] ?? null;

        Log::info('WhatsApp Message Sent', [
            'message_id' => $messageId,
            'to' => $to,
            'timestamp' => now()->toDateTimeString()
        ]);

        // Update message status in database
        $this->updateMessageStatus($messageId, 'sent');

        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Handle message delivered confirmation
     *
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function handleMessageDelivered(array $data): \Illuminate\Http\JsonResponse
    {
        $messageId = $data['message_id'] ?? null;
        $to = $data['to'] ?? null;

        Log::info('WhatsApp Message Delivered', [
            'message_id' => $messageId,
            'to' => $to,
            'timestamp' => now()->toDateTimeString()
        ]);

        // Update message status in database
        $this->updateMessageStatus($messageId, 'delivered');

        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Handle message read confirmation (blue ticks!)
     *
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function handleMessageRead(array $data): \Illuminate\Http\JsonResponse
    {
        $messageId = $data['message_id'] ?? null;
        $to = $data['to'] ?? null;

        Log::info('WhatsApp Message Read', [
            'message_id' => $messageId,
            'to' => $to,
            'timestamp' => now()->toDateTimeString()
        ]);

        // Update message status in database
        $this->updateMessageStatus($messageId, 'read');

        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Handle message failure
     *
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function handleMessageFailed(array $data): \Illuminate\Http\JsonResponse
    {
        $messageId = $data['message_id'] ?? null;
        $to = $data['to'] ?? null;
        $error = $data['error'] ?? 'Unknown error';

        Log::error('WhatsApp Message Failed', [
            'message_id' => $messageId,
            'to' => $to,
            'error' => $error,
            'timestamp' => now()->toDateTimeString()
        ]);

        // Update message status in database
        $this->updateMessageStatus($messageId, 'failed', $error);

        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Find consultation by phone number
     *
     * @param string $phone
     * @return Consultation|null
     */
    protected function findConsultationByPhone(string $phone): ?Consultation
    {
        // Normalize phone number (remove +, spaces, etc.)
        $normalizedPhone = preg_replace('/[\s\-\(\)+]/', '', $phone);

        // Try to find consultation by mobile number
        // Check multiple formats: 234XXXXXXXXXX, 0XXXXXXXXXX, etc.
        $consultation = Consultation::where(function ($query) use ($normalizedPhone) {
            $query->where('mobile', 'like', '%' . substr($normalizedPhone, -10) . '%')
                  ->orWhere('mobile', 'like', '%' . $normalizedPhone . '%');
        })
        ->orderBy('created_at', 'desc')
        ->first();

        return $consultation;
    }

    /**
     * Store incoming message in database
     * (Optional - for conversation history)
     *
     * @param string|null $from
     * @param string $message
     * @param string|null $messageId
     * @param string $timestamp
     * @return void
     */
    protected function storeIncomingMessage(?string $from, string $message, ?string $messageId, string $timestamp): void
    {
        // TODO: Create WhatsAppMessage model and store messages
        // For now, just log it
        Log::info('Storing WhatsApp message', [
            'from' => $from,
            'message' => $message,
            'message_id' => $messageId,
            'timestamp' => $timestamp
        ]);

        // Example implementation:
        // WhatsAppMessage::create([
        //     'from' => $from,
        //     'message' => $message,
        //     'message_id' => $messageId,
        //     'direction' => 'incoming',
        //     'status' => 'received',
        //     'received_at' => $timestamp
        // ]);
    }

    /**
     * Update message status in database
     *
     * @param string|null $messageId
     * @param string $status
     * @param string|null $error
     * @return void
     */
    protected function updateMessageStatus(?string $messageId, string $status, ?string $error = null): void
    {
        if (!$messageId) {
            return;
        }

        // TODO: Update WhatsAppMessage model
        Log::info('Updating WhatsApp message status', [
            'message_id' => $messageId,
            'status' => $status,
            'error' => $error
        ]);

        // Example implementation:
        // WhatsAppMessage::where('message_id', $messageId)->update([
        //     'status' => $status,
        //     'error_message' => $error,
        //     'updated_at' => now()
        // ]);
    }

    /**
     * Send auto-reply based on incoming message
     * (Optional - for automated responses)
     *
     * @param Consultation $consultation
     * @param string $message
     * @return void
     */
    protected function sendAutoReply(Consultation $consultation, string $message): void
    {
        // Convert message to lowercase for matching
        $messageLower = strtolower(trim($message));

        // Simple keyword matching for auto-replies
        $autoReplyMessage = null;

        if (str_contains($messageLower, 'status') || str_contains($messageLower, 'update')) {
            $autoReplyMessage = "Hi {$consultation->first_name}! 👋\n\n";
            $autoReplyMessage .= "Your consultation status: *{$consultation->status}*\n";
            $autoReplyMessage .= "Reference: {$consultation->reference}\n\n";
            $autoReplyMessage .= "Need more info? Just ask! 😊";
        } 
        elseif (str_contains($messageLower, 'payment') || str_contains($messageLower, 'pay')) {
            if ($consultation->payment_status === 'unpaid') {
                $autoReplyMessage = "Hi {$consultation->first_name}! 💳\n\n";
                $autoReplyMessage .= "Your treatment plan is ready!\n";
                $autoReplyMessage .= "Amount: NGN 5,000\n\n";
                $autoReplyMessage .= "Pay here: " . route('payment.request', ['reference' => $consultation->reference]) . "\n\n";
                $autoReplyMessage .= "Questions? Reply to this message! 😊";
            } else {
                $autoReplyMessage = "Hi {$consultation->first_name}! ✅\n\n";
                $autoReplyMessage .= "Your payment is already confirmed!\n";
                $autoReplyMessage .= "Treatment plan unlocked. 🎉";
            }
        }
        elseif (str_contains($messageLower, 'doctor') || str_contains($messageLower, 'appointment')) {
            $autoReplyMessage = "Hi {$consultation->first_name}! 👨‍⚕️\n\n";
            $autoReplyMessage .= "Your doctor will contact you shortly via WhatsApp.\n";
            $autoReplyMessage .= "Reference: {$consultation->reference}\n\n";
            $autoReplyMessage .= "For urgent matters, call: 0817 777 7122 📞";
        }

        // Send auto-reply if matched
        if ($autoReplyMessage) {
            try {
                $whatsapp = new ConsultationWhatsAppNotification();
                $result = $whatsapp->sendWhatsAppMessage(
                    $consultation->mobile,
                    $autoReplyMessage,
                    null,
                    null,
                    'auto_reply',
                    ['consultation_id' => $consultation->id]
                );

                if ($result['success']) {
                    Log::info('Auto-reply sent successfully', [
                        'consultation_id' => $consultation->id,
                        'reference' => $consultation->reference
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send auto-reply', [
                    'consultation_id' => $consultation->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}

