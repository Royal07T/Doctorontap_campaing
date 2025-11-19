<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TermiiService
{
    protected $apiKey;
    protected $secretKey;
    protected $senderId;
    protected $baseUrl;
    protected $channel;
    protected $enabled;
    protected $whatsappDeviceId;

    public function __construct()
    {
        $this->apiKey = config('services.termii.api_key');
        $this->secretKey = config('services.termii.secret_key');
        $this->senderId = config('services.termii.sender_id');
        $this->baseUrl = config('services.termii.base_url');
        $this->channel = config('services.termii.channel');
        $this->enabled = config('services.termii.enabled', true);
        $this->whatsappDeviceId = config('services.termii.whatsapp_device_id');
    }

    /**
     * Send SMS to a single recipient
     *
     * @param string $to Phone number in international format (e.g., +2348012345678)
     * @param string $message The SMS message content
     * @return array Response with success status and data
     */
    public function sendSMS(string $to, string $message): array
    {
        // Check if Termii is enabled
        if (!$this->enabled) {
            Log::info('Termii SMS skipped (disabled in config)', [
                'to' => $to,
                'message' => $message
            ]);
            
            return [
                'success' => true,
                'message' => 'SMS sending disabled',
                'skipped' => true
            ];
        }

        // Validate configuration
        if (empty($this->apiKey)) {
            Log::error('Termii API key not configured');
            
            return [
                'success' => false,
                'message' => 'Termii API key not configured',
                'error' => 'configuration_error'
            ];
        }

        // Format phone number (ensure it starts with +234)
        $formattedPhone = $this->formatPhoneNumber($to);

        try {
            // Termii v3 API uses /api/sms/send endpoint
            $response = Http::timeout(30)
                ->retry(3, 100) // Retry 3 times with 100ms delay
                ->post("{$this->baseUrl}/api/sms/send", [
                    'to' => $formattedPhone,
                    'from' => $this->senderId,
                    'sms' => $message,
                    'type' => 'plain',
                    'channel' => $this->channel,
                    'api_key' => $this->apiKey,
                ]);

            $responseData = $response->json();
            
            if ($response->successful()) {
                Log::info('Termii SMS sent successfully', [
                    'to' => $formattedPhone,
                    'message_id' => $responseData['message_id'] ?? null,
                    'balance' => $responseData['balance'] ?? null,
                    'response' => $responseData
                ]);

                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'data' => $responseData
                ];
            } else {
                Log::error('Termii SMS failed', [
                    'to' => $formattedPhone,
                    'status' => $response->status(),
                    'response_body' => $response->body(),
                    'error' => $responseData
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to send SMS',
                    'error' => $responseData,
                    'status_code' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Termii SMS exception', [
                'to' => $formattedPhone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred while sending SMS',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send bulk SMS to multiple recipients
     *
     * @param array $recipients Array of phone numbers
     * @param string $message The SMS message content
     * @return array Response with success status and results
     */
    public function sendBulkSMS(array $recipients, string $message): array
    {
        $results = [];
        $successCount = 0;
        $failedCount = 0;

        foreach ($recipients as $recipient) {
            $result = $this->sendSMS($recipient, $message);
            
            if ($result['success']) {
                $successCount++;
            } else {
                $failedCount++;
            }
            
            $results[] = [
                'recipient' => $recipient,
                'result' => $result
            ];
        }

        return [
            'success' => $successCount > 0,
            'total' => count($recipients),
            'successful' => $successCount,
            'failed' => $failedCount,
            'results' => $results
        ];
    }

    /**
     * Format phone number to international format
     *
     * @param string $phone Phone number in various formats
     * @return string Formatted phone number
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove spaces, hyphens, and parentheses
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

        // If starts with 0, replace with +234
        if (substr($phone, 0, 1) === '0') {
            $phone = '+234' . substr($phone, 1);
        }

        // If starts with 234 but no +, add it
        if (substr($phone, 0, 3) === '234' && substr($phone, 0, 1) !== '+') {
            $phone = '+' . $phone;
        }

        // If doesn't start with + but has country code, add +
        if (substr($phone, 0, 1) !== '+') {
            $phone = '+234' . $phone;
        }

        return $phone;
    }

    /**
     * Check account balance
     *
     * @return array Response with balance information
     */
    public function checkBalance(): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'Termii API key not configured'
            ];
        }

        try {
            // Termii v3 API uses /api/get-balance endpoint
            $response = Http::timeout(30)
                ->get("{$this->baseUrl}/api/get-balance", [
                    'api_key' => $this->apiKey,
                ]);

            $responseData = $response->json();
            
            Log::info('Termii balance check response', [
                'status' => $response->status(),
                'response' => $responseData,
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'balance' => $responseData['balance'] ?? 0,
                    'currency' => $responseData['currency'] ?? 'NGN',
                    'data' => $responseData
                ];
            } else {
                Log::error('Termii balance check failed', [
                    'status' => $response->status(),
                    'response_body' => $response->body(),
                    'error' => $responseData
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Failed to check balance',
                    'error' => $responseData,
                    'status_code' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Termii balance check exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get delivery status of a message
     *
     * @param string $messageId The message ID returned from send
     * @return array Response with delivery status
     */
    public function getMessageStatus(string $messageId): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'Termii API key not configured'
            ];
        }

        try {
            // Termii v3 API uses /api/sms/inbox endpoint
            $response = Http::get("{$this->baseUrl}/api/sms/inbox", [
                'api_key' => $this->apiKey,
                'message_id' => $messageId,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to get message status',
                    'error' => $response->json()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Termii message status check exception', [
                'message_id' => $messageId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred',
                'error' => $e->getMessage()
            ];
        }
    }

    // ============================================
    // WHATSAPP METHODS
    // ============================================

    /**
     * Send WhatsApp message using approved template
     * Use this to INITIATE conversations with patients/doctors
     *
     * @param string $to Phone number (234XXXXXXXXXX format - no +)
     * @param string $templateId Template ID from Termii dashboard
     * @param array $templateData Variables for template placeholders
     * @return array Response with success status and data
     */
    public function sendWhatsAppTemplate(string $to, string $templateId, array $templateData = []): array
    {
        // Check if WhatsApp is enabled
        if (!$this->enabled || empty($this->whatsappDeviceId)) {
            Log::info('Termii WhatsApp skipped (disabled or no device ID)', [
                'to' => $to,
                'template_id' => $templateId
            ]);
            
            return [
                'success' => true,
                'message' => 'WhatsApp sending disabled',
                'skipped' => true
            ];
        }

        // Validate configuration
        if (empty($this->apiKey)) {
            Log::error('Termii API key not configured');
            
            return [
                'success' => false,
                'message' => 'Termii API key not configured',
                'error' => 'configuration_error'
            ];
        }

        // Format phone number (ensure it's 234XXXXXXXXXX without +)
        $formattedPhone = $this->formatPhoneNumberForWhatsApp($to);

        try {
            // Termii WhatsApp Template API
            $response = Http::timeout(30)
                ->retry(3, 100)
                ->post("{$this->baseUrl}/api/whatsapp/otp/send", [
                    'api_key' => $this->apiKey,
                    'phone_number' => $formattedPhone,
                    'device_id' => $this->whatsappDeviceId,
                    'template_id' => $templateId,
                    'data' => $templateData,
                ]);

            $responseData = $response->json();
            
            if ($response->successful()) {
                Log::info('Termii WhatsApp template sent successfully', [
                    'to' => $formattedPhone,
                    'template_id' => $templateId,
                    'message_id' => $responseData['message_id'] ?? null,
                    'response' => $responseData
                ]);

                return [
                    'success' => true,
                    'message' => 'WhatsApp template sent successfully',
                    'data' => $responseData
                ];
            } else {
                Log::error('Termii WhatsApp template failed', [
                    'to' => $formattedPhone,
                    'template_id' => $templateId,
                    'status' => $response->status(),
                    'response_body' => $response->body(),
                    'error' => $responseData
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to send WhatsApp template',
                    'error' => $responseData,
                    'status_code' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Termii WhatsApp template exception', [
                'to' => $formattedPhone,
                'template_id' => $templateId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred while sending WhatsApp template',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send WhatsApp conversational message (within 24-hour window)
     * Use this to REPLY to patients/doctors after they message you
     *
     * @param string $to Phone number (234XXXXXXXXXX format - no +)
     * @param string $message Plain text or formatted message
     * @param string|null $mediaUrl Optional: URL to image/PDF/document
     * @param string|null $caption Optional: Caption for media
     * @return array Response with success status and data
     */
    public function sendWhatsAppMessage(string $to, string $message, ?string $mediaUrl = null, ?string $caption = null): array
    {
        // Check if WhatsApp is enabled
        if (!$this->enabled || empty($this->whatsappDeviceId)) {
            Log::info('Termii WhatsApp skipped (disabled or no device ID)', [
                'to' => $to,
                'message' => $message
            ]);
            
            return [
                'success' => true,
                'message' => 'WhatsApp sending disabled',
                'skipped' => true
            ];
        }

        // Validate configuration
        if (empty($this->apiKey)) {
            Log::error('Termii API key not configured');
            
            return [
                'success' => false,
                'message' => 'Termii API key not configured',
                'error' => 'configuration_error'
            ];
        }

        // Format phone number
        $formattedPhone = $this->formatPhoneNumberForWhatsApp($to);

        try {
            $payload = [
                'api_key' => $this->apiKey,
                'to' => $formattedPhone,
                'from' => $this->whatsappDeviceId,
                'type' => 'plain',
                'channel' => 'whatsapp',
                'text' => $message,
            ];

            // Add media if provided
            if ($mediaUrl) {
                $payload['media'] = [
                    'url' => $mediaUrl,
                    'caption' => $caption ?? $message
                ];
            }

            // Termii WhatsApp Messaging API
            $response = Http::timeout(30)
                ->retry(3, 100)
                ->post("{$this->baseUrl}/api/send/whatsapp", $payload);

            $responseData = $response->json();
            
            if ($response->successful()) {
                Log::info('Termii WhatsApp message sent successfully', [
                    'to' => $formattedPhone,
                    'has_media' => !empty($mediaUrl),
                    'message_id' => $responseData['message_id'] ?? null,
                    'response' => $responseData
                ]);

                return [
                    'success' => true,
                    'message' => 'WhatsApp message sent successfully',
                    'data' => $responseData
                ];
            } else {
                Log::error('Termii WhatsApp message failed', [
                    'to' => $formattedPhone,
                    'status' => $response->status(),
                    'response_body' => $response->body(),
                    'error' => $responseData
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to send WhatsApp message',
                    'error' => $responseData,
                    'status_code' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Termii WhatsApp message exception', [
                'to' => $formattedPhone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred while sending WhatsApp message',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format phone number for WhatsApp (234XXXXXXXXXX without +)
     *
     * @param string $phone Phone number in various formats
     * @return string Formatted phone number (234XXXXXXXXXX)
     */
    protected function formatPhoneNumberForWhatsApp(string $phone): string
    {
        // Remove spaces, hyphens, parentheses, and +
        $phone = preg_replace('/[\s\-\(\)+]/', '', $phone);

        // If starts with 0, replace with 234
        if (substr($phone, 0, 1) === '0') {
            $phone = '234' . substr($phone, 1);
        }

        // If doesn't start with 234, add it
        if (substr($phone, 0, 3) !== '234') {
            $phone = '234' . $phone;
        }

        return $phone;
    }
}


