<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Vonage\Laravel\Facade\Vonage;
use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\Client\Credentials\Keypair;
use Vonage\SMS\Message\SMS;
use Vonage\Messages\MessageObjects\TextObject;
use Vonage\Messages\Channel\SMS\SMSText;
use Vonage\Messages\Channel\WhatsApp\WhatsAppText;
use Vonage\Messages\Channel\WhatsApp\WhatsAppTemplate;
use Vonage\Messages\Channel\WhatsApp\WhatsAppImage;
use Vonage\Messages\Channel\WhatsApp\WhatsAppVideo;
use Vonage\Messages\Channel\WhatsApp\WhatsAppAudio;
use Vonage\Messages\Channel\WhatsApp\WhatsAppFile;
use Vonage\Messages\Channel\MMS\MMSImage;
use Vonage\Messages\Channel\MMS\MMSVideo;
use Vonage\Messages\Channel\MMS\MMSAudio;
use Vonage\Messages\MessageObjects\ImageObject;
use Vonage\Messages\MessageObjects\VideoObject;
use Vonage\Messages\MessageObjects\AudioObject;
use Vonage\Messages\MessageObjects\FileObject;

class VonageService
{
    protected $apiKey;
    protected $apiSecret;
    protected $applicationId;
    protected $privateKey;
    protected $brandName;
    protected $apiMethod;
    protected $enabled;
    protected $whatsappEnabled;
    protected $whatsappNumber;
    protected $whatsappBusinessId; // WhatsApp Business Number ID (for Messages API)
    protected $messagesSandbox;

    public function __construct()
    {
        // Use services.vonage config (standardized)
        $this->apiKey = config('services.vonage.api_key') ?: config('vonage.api_key');
        $this->apiSecret = config('services.vonage.api_secret') ?: config('vonage.api_secret');
        $this->applicationId = config('services.vonage.application_id') ?: config('vonage.application_id');
        $this->privateKey = $this->getPrivateKey();
        $this->brandName = config('services.vonage.brand_name') ?: config('vonage.brand_name', 'DoctorOnTap');
        $this->apiMethod = config('services.vonage.api_method', 'legacy');
        $this->enabled = config('services.vonage.enabled', true);
        $this->whatsappEnabled = config('services.vonage.whatsapp_enabled', false);
        // Disable sandbox for production WhatsApp
        $this->messagesSandbox = config('services.vonage.messages_sandbox', false);
        
        // Get WhatsApp number from new config structure (production)
        $rawNumber = config('services.vonage.whatsapp.from_phone_number') 
            ?: config('services.vonage.whatsapp_number') 
            ?: config('vonage.whatsapp_number', '');
            
        $this->whatsappNumber = $rawNumber ? $this->formatPhoneNumber($rawNumber) : '';
        
        // Get WhatsApp Business Number ID (preferred for Messages API 'from' parameter)
        // This is the ID shown in the dashboard (e.g., 2347089146888 or 250782187688)
        $this->whatsappBusinessId = config('services.vonage.whatsapp.business_number_id') 
            ?: config('services.vonage.whatsapp_id') 
            ?: config('vonage.whatsapp_id');
    }

    /**
     * Get private key from file path or inline
     */
    protected function getPrivateKey()
    {
        $privateKeyPath = config('services.vonage.private_key_path');
        $privateKey = config('services.vonage.private_key') ?: config('vonage.private_key');

        if ($privateKeyPath && file_exists($privateKeyPath)) {
            return file_get_contents($privateKeyPath);
        }

        if ($privateKey) {
            // Replace \n with actual newlines if provided as string
            return str_replace('\\n', "\n", $privateKey);
        }

        return null;
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
        // Check if Vonage is enabled
        if (!$this->enabled) {
            Log::info('Vonage SMS skipped (disabled in config)', [
                'to' => $to,
                'message' => $message
            ]);
            
            return [
                'success' => true,
                'message' => 'SMS sending disabled',
                'skipped' => true
            ];
        }

        // Validate configuration based on API method
        if ($this->apiMethod === 'messages') {
            // Messages API requires Application ID and Private Key
            if (empty($this->applicationId) || empty($this->privateKey)) {
                Log::error('Vonage Messages API credentials not configured', [
                    'has_application_id' => !empty($this->applicationId),
                    'has_private_key' => !empty($this->privateKey)
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Vonage Messages API credentials not configured. Need VONAGE_APPLICATION_ID and VONAGE_PRIVATE_KEY_PATH or VONAGE_PRIVATE_KEY',
                    'error' => 'configuration_error'
                ];
            }
        } else {
            // Legacy API requires API Key and Secret
            if (empty($this->apiKey) || empty($this->apiSecret)) {
                Log::error('Vonage Legacy API credentials not configured', [
                    'has_api_key' => !empty($this->apiKey),
                    'has_api_secret' => !empty($this->apiSecret)
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Vonage API credentials not configured',
                    'error' => 'configuration_error'
                ];
            }
        }

        // Format phone number (ensure it's in international format)
        $formattedPhone = $this->formatPhoneNumber($to);

        try {
            set_time_limit(30);
            
            // Use Messages API or Legacy API based on configuration
            if ($this->apiMethod === 'messages') {
                return $this->sendViaMessagesAPI($formattedPhone, $message);
            } else {
                return $this->sendViaLegacyAPI($formattedPhone, $message);
            }
        } catch (\Exception $e) {
            Log::error('Vonage SMS exception', [
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
     * Send SMS via Legacy SMS API (using API Key/Secret)
     */
    protected function sendViaLegacyAPI(string $formattedPhone, string $message): array
    {
        try {
            $credentials = new Basic($this->apiKey, $this->apiSecret);
            $client = new Client($credentials);
            
            // Configure HTTP client timeout (if supported by SDK version)
            // Note: The Vonage SDK uses Guzzle HTTP client internally
            // Timeout configuration may need to be set via environment or SDK config

            $response = $client->sms()->send(
                new SMS($formattedPhone, $this->brandName, $message)
            );

            $messageObj = $response->current();

            if ($messageObj->getStatus() == 0) {
                Log::info('Vonage Legacy API SMS sent successfully', [
                    'to' => $formattedPhone,
                    'message_id' => $messageObj->getMessageId(),
                    'remaining_balance' => $messageObj->getRemainingBalance(),
                    'message_price' => $messageObj->getMessagePrice(),
                    'network' => $messageObj->getNetwork()
                ]);

                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'data' => [
                        'message_id' => $messageObj->getMessageId(),
                        'status' => $messageObj->getStatus(),
                        'remaining_balance' => $messageObj->getRemainingBalance(),
                        'message_price' => $messageObj->getMessagePrice(),
                        'network' => $messageObj->getNetwork(),
                        'to' => $messageObj->getTo()
                    ]
                ];
            } else {
                $errorMessage = $this->getStatusErrorMessage($messageObj->getStatus());
                
                Log::error('Vonage Legacy API SMS failed', [
                    'to' => $formattedPhone,
                    'status' => $messageObj->getStatus(),
                    'error_text' => $errorMessage,
                    'error_label' => $messageObj->getErrorText()
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to send SMS',
                    'error' => [
                        'status' => $messageObj->getStatus(),
                        'error_text' => $errorMessage,
                        'error_label' => $messageObj->getErrorText()
                    ],
                    'status_code' => $messageObj->getStatus()
                ];
            }
        } catch (\Vonage\Client\Exception\Request $e) {
            Log::error('Vonage Legacy API request exception', [
                'to' => $formattedPhone,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            
            return [
                'success' => false,
                'message' => 'Vonage API request failed',
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        } catch (\Exception $e) {
            Log::error('Vonage Legacy API exception', [
                'to' => $formattedPhone,
                'error' => $e->getMessage(),
                'class' => get_class($e)
            ]);
            
            return [
                'success' => false,
                'message' => 'Exception occurred while calling Vonage API',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send SMS via Messages API (using Application with JWT)
     * Note: This requires a Vonage Application with private key
     */
    protected function sendViaMessagesAPI(string $formattedPhone, string $message): array
    {
        try {
            // Check if Messages API classes are available
            if (!class_exists('Vonage\Messages\Channel\SMS\SMSText')) {
                Log::error('Vonage Messages API classes not found. Please ensure you have the latest Vonage SDK.');
                return [
                    'success' => false,
                    'message' => 'Messages API classes not available. Please use legacy API or update SDK.',
                    'error' => 'sdk_version_error'
                ];
            }

            $credentials = new Keypair($this->privateKey, $this->applicationId);
            // Use the Vonage Facade
            $client = Vonage::getFacadeRoot();

            // Create SMS message using Messages API
            // Constructor: SMSText(to, from, message)
            $smsMessage = new \Vonage\Messages\Channel\SMS\SMSText(
                $formattedPhone,
                $this->brandName,
                $message
            );

            // Handle sandbox mode
            if ($this->messagesSandbox) {
                $client->messages()->getAPIResource()->setBaseUrl('https://messages-sandbox.nexmo.com/v1/messages');
                Log::debug('Using Vonage Messages Sandbox for SMS');
            }

            $response = $client->messages()->send($smsMessage);

            // Handle both object and array responses
            $messageUuid = is_object($response) ? $response->getMessageUuid() : ($response['message_uuid'] ?? null);
            
            Log::info('Vonage Messages API SMS sent successfully', [
                'to' => $formattedPhone,
                'message_uuid' => $messageUuid
            ]);

            return [
                'success' => true,
                'message' => 'SMS sent successfully',
                'data' => [
                    'message_uuid' => $messageUuid,
                    'to' => $formattedPhone
                ]
            ];
        } catch (\Vonage\Client\Exception\Request $e) {
            Log::error('Vonage Messages API request exception', [
                'to' => $formattedPhone,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            
            return [
                'success' => false,
                'message' => 'Vonage Messages API request failed',
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        } catch (\Exception $e) {
            Log::error('Vonage Messages API exception', [
                'to' => $formattedPhone,
                'error' => $e->getMessage(),
                'class' => get_class($e)
            ]);
            
            return [
                'success' => false,
                'message' => 'Exception occurred while calling Vonage Messages API',
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
     * @return string Formatted phone number (e.g., +2348012345678)
     */
    /**
     * Format phone number to E.164 format (without + sign, as required by Vonage)
     * E.164 format: country code + number, no special characters, no leading +
     * Examples: 14155550101 (US), 447700900123 (UK), 2347081114942 (Nigeria)
     *
     * @param string $phone Phone number in any format
     * @return string Phone number in E.164 format (without +)
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove spaces, hyphens, parentheses, and plus signs
        $phone = preg_replace('/[\s\-\(\)\+]/', '', $phone);

        // If starts with 0, replace with 234 (Nigeria country code)
        if (substr($phone, 0, 1) === '0') {
            $phone = '234' . substr($phone, 1);
        }

        // If doesn't start with country code, assume Nigeria (234)
        // This is a fallback - ideally numbers should already have country code
        if (strlen($phone) < 10 || (strlen($phone) === 10 && substr($phone, 0, 1) !== '2')) {
            // If it's a 10-digit number without country code, assume Nigeria
            if (strlen($phone) === 10) {
                $phone = '234' . $phone;
            }
        }

        // Ensure no leading + (Vonage E.164 format omits the +)
        $phone = ltrim($phone, '+');

        return $phone;
    }

    /**
     * Get human-readable error message for status code
     *
     * @param int $status Status code from Vonage
     * @return string Error message
     */
    protected function getStatusErrorMessage(int $status): string
    {
        $errorMessages = [
            0 => 'Success',
            1 => 'Throttled',
            2 => 'Missing parameters',
            3 => 'Invalid parameters',
            4 => 'Invalid credentials',
            5 => 'Internal error',
            6 => 'Invalid message',
            7 => 'Number barred',
            8 => 'Partner account barred',
            9 => 'Partner quota exceeded',
            10 => 'Too many existing binds',
            11 => 'Account not enabled for HTTP',
            12 => 'Message too long',
            13 => 'Communication failed',
            14 => 'Invalid signature',
            15 => 'Invalid sender address',
            16 => 'Invalid TTL',
            19 => 'Facility not allowed',
            20 => 'Invalid message class',
            23 => 'Bad callback URL',
            29 => 'Non-whitelisted destination',
        ];

        return $errorMessages[$status] ?? "Unknown error (Status: {$status})";
    }

    /**
     * Check account balance (Vonage doesn't have a direct balance API, but we can check account status)
     *
     * @return array Response with account information
     */
    /**
     * Check account balance
     *
     * @return array Response with account information
     */
    public function checkBalance(): array
    {
        if (empty($this->apiKey) || empty($this->apiSecret)) {
            return [
                'success' => false,
                'message' => 'Vonage API credentials not configured'
            ];
        }

        try {
            $credentials = new Basic($this->apiKey, $this->apiSecret);
            $client = new Client($credentials);

            $response = $client->account()->getBalance();
            $balance = $response->getBalance();
            $autoReload = $response->getAutoReload();

            Log::info('Vonage balance check successful', [
                'balance' => $balance,
                'auto_reload' => $autoReload ? 'yes' : 'no'
            ]);

            return [
                'success' => true,
                'message' => 'Vonage balance retrieved successfully',
                'data' => [
                    'balance' => $balance,
                    'currency' => 'EUR', // Vonage balance is typically in EUR
                    'auto_reload' => $autoReload
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Vonage balance check exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred while checking balance',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify webhook signature
     *
     * @param array $params Request parameters (usually $_GET or query string)
     * @param string $signatureSecret Signature secret from config
     * @param string $signatureMethod Signature method (default: md5hash)
     * @return bool True if signature is valid
     */
    public function verifySignature(array $params, string $signatureSecret): bool
    {
        if (empty($signatureSecret)) {
            Log::warning('Vonage signature validation skipped: No signature secret configured');
            return true; // Skip validation if not configured (backwards compatibility)
        }

        try {
            // Check if signature exists in params
            if (!isset($params['sig'])) {
                return false;
            }

            $signature = new \Vonage\Client\Signature($params, $signatureSecret, 'md5hash');
            return $signature->check($params['sig']);
        } catch (\Exception $e) {
            Log::error('Vonage signature verification error', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    // ==================== WHATSAPP METHODS ====================

    /**
     * Send WhatsApp text message (within 24-hour customer care window)
     * Supports both JWT (Application ID + Private Key) and Basic (API Key + Secret) authentication
     *
     * @param string $to Phone number in international format (e.g., +2348012345678)
     * @param string $message The WhatsApp message content
     * @return array Response with success status and data
     */
    public function sendWhatsAppMessage(string $to, string $message): array
    {
        // Check if WhatsApp is enabled
        if (!$this->whatsappEnabled) {
            Log::info('Vonage WhatsApp skipped (disabled in config)', [
                'to' => $to,
                'message' => $message
            ]);
            
            return [
                'success' => true,
                'message' => 'WhatsApp sending disabled',
                'skipped' => true
            ];
        }

        if (empty($this->whatsappNumber)) {
            Log::error('Vonage WhatsApp number not configured');
            
            return [
                'success' => false,
                'message' => 'WhatsApp number not configured. Set VONAGE_WHATSAPP_NUMBER in .env',
                'error' => 'configuration_error'
            ];
        }

        // Check authentication method - prefer JWT, fallback to Basic
        $useJWT = !empty($this->applicationId) && !empty($this->privateKey);
        $useBasic = !empty($this->apiKey) && !empty($this->apiSecret);

        if (!$useJWT && !$useBasic) {
            Log::error('Vonage WhatsApp requires authentication credentials', [
                'has_jwt' => $useJWT,
                'has_basic' => $useBasic
            ]);
            
            return [
                'success' => false,
                'message' => 'Vonage WhatsApp requires either JWT (VONAGE_APPLICATION_ID + VONAGE_PRIVATE_KEY) or Basic (VONAGE_API_KEY + VONAGE_API_SECRET) credentials',
                'error' => 'configuration_error'
            ];
        }

        // Format phone number (E.164 format with +)
        $formattedPhone = $this->formatPhoneNumberForWhatsApp($to);

        try {
            set_time_limit(30);
            
            // Use the Vonage Facade
            $client = Vonage::getFacadeRoot();

            // For WhatsApp Messages API, use WhatsApp Business Number ID (not phone number)
            // The Business Number ID is what the API expects in the 'from' parameter
            // Format: Use the ID as-is (e.g., 2347089146888 or 250782187688)
            $fromNumber = $this->whatsappBusinessId;
            
            // If Business Number ID not available, fallback to phone number (remove +)
            if (empty($fromNumber)) {
                $fromNumber = str_replace('+', '', $this->whatsappNumber);
            }
            
            Log::debug('Vonage WhatsApp using from parameter', [
                'from_number' => $fromNumber,
                'whatsapp_business_id' => $this->whatsappBusinessId,
                'whatsapp_number' => $this->whatsappNumber,
                'using_business_id' => !empty($this->whatsappBusinessId)
            ]);
            
            // Create WhatsApp text message
            $whatsappMessage = new WhatsAppText(
                $formattedPhone,
                $fromNumber,
                $message
            );

            // Handle sandbox mode
            if ($this->messagesSandbox) {
                $client->messages()->getAPIResource()->setBaseUrl('https://messages-sandbox.nexmo.com/v1/messages');
                Log::debug('Using Vonage Messages Sandbox for WhatsApp');
            }

            $response = $client->messages()->send($whatsappMessage);
            
            // Handle both object and array responses (depending on SDK/Package version)
            $messageUuid = is_object($response) ? $response->getMessageUuid() : ($response['message_uuid'] ?? null);
            
            Log::info('Vonage WhatsApp message sent successfully', [
                'to' => $formattedPhone,
                'message_uuid' => $messageUuid
            ]);

            return [
                'success' => true,
                'message' => 'WhatsApp message sent successfully',
                'data' => [
                    'message_uuid' => $messageUuid,
                    'to' => $formattedPhone
                ]
            ];
        } catch (\Vonage\Client\Exception\Request $e) {
            Log::error('Vonage WhatsApp request exception', [
                'to' => $formattedPhone,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'auth_method' => $useJWT ? 'JWT' : 'Basic'
            ]);
            
            return [
                'success' => false,
                'message' => 'Vonage WhatsApp API request failed',
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        } catch (\Exception $e) {
            Log::error('Vonage WhatsApp exception', [
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
     * Send WhatsApp template message (outside 24-hour window)
     * Supports both JWT (Application ID + Private Key) and Basic (API Key + Secret) authentication
     *
     * @param string $to Phone number in international format
     * @param string $templateName Template name (must be approved in WhatsApp Manager)
     * @param string $templateLanguage Language code (e.g., 'en', 'en_US')
     * @param array $templateParameters Template parameters/components
     * @return array Response with success status and data
     */
    public function sendWhatsAppTemplate(
        string $to,
        string $templateName,
        string $templateLanguage = 'en',
        array $templateParameters = []
    ): array {
        // Check if WhatsApp is enabled
        if (!$this->whatsappEnabled) {
            Log::info('Vonage WhatsApp template skipped (disabled in config)', [
                'to' => $to,
                'template_name' => $templateName
            ]);
            
            return [
                'success' => true,
                'message' => 'WhatsApp sending disabled',
                'skipped' => true
            ];
        }

        if (empty($this->whatsappNumber)) {
            Log::error('Vonage WhatsApp number not configured');
            
            return [
                'success' => false,
                'message' => 'WhatsApp number not configured',
                'error' => 'configuration_error'
            ];
        }

        // Check authentication method - prefer JWT, fallback to Basic
        $useJWT = !empty($this->applicationId) && !empty($this->privateKey);
        $useBasic = !empty($this->apiKey) && !empty($this->apiSecret);

        if (!$useJWT && !$useBasic) {
            Log::error('Vonage WhatsApp requires authentication credentials');
            
            return [
                'success' => false,
                'message' => 'Vonage WhatsApp requires either JWT (VONAGE_APPLICATION_ID + VONAGE_PRIVATE_KEY) or Basic (VONAGE_API_KEY + VONAGE_API_SECRET) credentials',
                'error' => 'configuration_error'
            ];
        }

        // Format phone number
        $formattedPhone = $this->formatPhoneNumberForWhatsApp($to);

        try {
            set_time_limit(30);
            
            // Choose authentication method
            // Use the Vonage Facade
            $client = Vonage::getFacadeRoot();

            // Use WhatsApp Business Number ID if available (preferred for Messages API)
            $fromNumber = $this->whatsappBusinessId ?: $this->whatsappNumber;
            
            // Create WhatsApp template message
            $whatsappTemplate = new WhatsAppTemplate(
                $formattedPhone,
                $fromNumber,
                $templateName,
                $templateLanguage,
                $templateParameters
            );

            // Handle sandbox mode
            if ($this->messagesSandbox) {
                $client->messages()->getAPIResource()->setBaseUrl('https://messages-sandbox.nexmo.com/v1/messages');
                Log::debug('Using Vonage Messages Sandbox for WhatsApp template');
            }

            $response = $client->messages()->send($whatsappTemplate);
            
            // Handle both object and array responses
            $messageUuid = is_object($response) ? $response->getMessageUuid() : ($response['message_uuid'] ?? null);
            
            Log::info('Vonage WhatsApp template sent successfully', [
                'to' => $formattedPhone,
                'template_name' => $templateName,
                'message_uuid' => $messageUuid
            ]);

            return [
                'success' => true,
                'message' => 'WhatsApp template sent successfully',
                'data' => [
                    'message_uuid' => $messageUuid,
                    'to' => $formattedPhone,
                    'template_name' => $templateName
                ]
            ];
        } catch (\Vonage\Client\Exception\Request $e) {
            Log::error('Vonage WhatsApp template request exception', [
                'to' => $formattedPhone,
                'template_name' => $templateName,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'auth_method' => $useJWT ? 'JWT' : 'Basic'
            ]);
            
            return [
                'success' => false,
                'message' => 'Vonage WhatsApp template API request failed',
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        } catch (\Exception $e) {
            Log::error('Vonage WhatsApp template exception', [
                'to' => $formattedPhone,
                'template_name' => $templateName,
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
     * Send WhatsApp image message
     * 
     * @param string $to Phone number in international format
     * @param string $imageUrl Publicly accessible URL to the image
     * @param string $caption Optional caption for the image
     * @return array Response with success status and data
     */
    public function sendWhatsAppImage(string $to, string $imageUrl, string $caption = ''): array
    {
        if (!$this->whatsappEnabled) {
            return [
                'success' => false,
                'message' => 'WhatsApp sending disabled',
                'skipped' => true
            ];
        }

        if (empty($this->whatsappNumber)) {
            return [
                'success' => false,
                'message' => 'WhatsApp number not configured',
                'error' => 'configuration_error'
            ];
        }

        $useJWT = !empty($this->applicationId) && !empty($this->privateKey);
        $useBasic = !empty($this->apiKey) && !empty($this->apiSecret);

        if (!$useJWT && !$useBasic) {
            return [
                'success' => false,
                'message' => 'Vonage WhatsApp requires authentication credentials',
                'error' => 'configuration_error'
            ];
        }

        $formattedPhone = $this->formatPhoneNumberForWhatsApp($to);

        try {
            // Use the Vonage Facade
            $client = Vonage::getFacadeRoot();

            // Handle sandbox mode
            if ($this->messagesSandbox) {
                $client->messages()->getAPIResource()->setBaseUrl('https://messages-sandbox.nexmo.com/v1/messages');
            }

            $imageObject = new ImageObject($imageUrl, $caption);
                $fromNumber = $this->whatsappBusinessId ?: $this->whatsappNumber;
                $whatsappImage = new WhatsAppImage($formattedPhone, $fromNumber, $imageObject);

            $response = $client->messages()->send($whatsappImage);
            
            // Handle both object and array responses
            $messageUuid = is_object($response) ? $response->getMessageUuid() : ($response['message_uuid'] ?? null);

            Log::info('Vonage WhatsApp image sent successfully', [
                'to' => $formattedPhone,
                'image_url' => $imageUrl,
                'message_uuid' => $messageUuid
            ]);

            return [
                'success' => true,
                'message' => 'WhatsApp image sent successfully',
                'data' => [
                    'message_uuid' => $messageUuid,
                    'to' => $formattedPhone
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Vonage WhatsApp image exception', [
                'to' => $formattedPhone,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred while sending WhatsApp image',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send WhatsApp video message
     * 
     * @param string $to Phone number in international format
     * @param string $videoUrl Publicly accessible URL to the video
     * @param string $caption Optional caption for the video
     * @return array Response with success status and data
     */
    public function sendWhatsAppVideo(string $to, string $videoUrl, string $caption = ''): array
    {
        if (!$this->whatsappEnabled) {
            return [
                'success' => false,
                'message' => 'WhatsApp sending disabled',
                'skipped' => true
            ];
        }

        if (empty($this->whatsappNumber)) {
            return [
                'success' => false,
                'message' => 'WhatsApp number not configured',
                'error' => 'configuration_error'
            ];
        }

        $useJWT = !empty($this->applicationId) && !empty($this->privateKey);
        $useBasic = !empty($this->apiKey) && !empty($this->apiSecret);

        if (!$useJWT && !$useBasic) {
            return [
                'success' => false,
                'message' => 'Vonage WhatsApp requires authentication credentials',
                'error' => 'configuration_error'
            ];
        }

        $formattedPhone = $this->formatPhoneNumberForWhatsApp($to);

        try {
            // Use the Vonage Facade
            $client = Vonage::getFacadeRoot();

            // Handle sandbox mode
            if ($this->messagesSandbox) {
                $client->messages()->getAPIResource()->setBaseUrl('https://messages-sandbox.nexmo.com/v1/messages');
            }

            $videoObject = new VideoObject($videoUrl, $caption);
                $fromNumber = $this->whatsappBusinessId ?: $this->whatsappNumber;
                $whatsappVideo = new WhatsAppVideo($formattedPhone, $fromNumber, $videoObject);

            $response = $client->messages()->send($whatsappVideo);
            
            // Handle both object and array responses
            $messageUuid = is_object($response) ? $response->getMessageUuid() : ($response['message_uuid'] ?? null);

            Log::info('Vonage WhatsApp video sent successfully', [
                'to' => $formattedPhone,
                'video_url' => $videoUrl,
                'message_uuid' => $messageUuid
            ]);

            return [
                'success' => true,
                'message' => 'WhatsApp video sent successfully',
                'data' => [
                    'message_uuid' => $messageUuid,
                    'to' => $formattedPhone
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Vonage WhatsApp video exception', [
                'to' => $formattedPhone,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred while sending WhatsApp video',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send WhatsApp audio message
     * 
     * @param string $to Phone number in international format
     * @param string $audioUrl Publicly accessible URL to the audio file
     * @return array Response with success status and data
     */
    public function sendWhatsAppAudio(string $to, string $audioUrl): array
    {
        if (!$this->whatsappEnabled) {
            return [
                'success' => false,
                'message' => 'WhatsApp sending disabled',
                'skipped' => true
            ];
        }

        if (empty($this->whatsappNumber)) {
            return [
                'success' => false,
                'message' => 'WhatsApp number not configured',
                'error' => 'configuration_error'
            ];
        }

        $useJWT = !empty($this->applicationId) && !empty($this->privateKey);
        $useBasic = !empty($this->apiKey) && !empty($this->apiSecret);

        if (!$useJWT && !$useBasic) {
            return [
                'success' => false,
                'message' => 'Vonage WhatsApp requires authentication credentials',
                'error' => 'configuration_error'
            ];
        }

        $formattedPhone = $this->formatPhoneNumberForWhatsApp($to);

        try {
            // Use the Vonage Facade
            $client = Vonage::getFacadeRoot();

            // Handle sandbox mode
            if ($this->messagesSandbox) {
                $client->messages()->getAPIResource()->setBaseUrl('https://messages-sandbox.nexmo.com/v1/messages');
            }

            $audioObject = new AudioObject($audioUrl);
                $fromNumber = $this->whatsappBusinessId ?: $this->whatsappNumber;
                $whatsappAudio = new WhatsAppAudio($formattedPhone, $fromNumber, $audioObject);

            $response = $client->messages()->send($whatsappAudio);
            
            // Handle both object and array responses
            $messageUuid = is_object($response) ? $response->getMessageUuid() : ($response['message_uuid'] ?? null);

            Log::info('Vonage WhatsApp audio sent successfully', [
                'to' => $formattedPhone,
                'audio_url' => $audioUrl,
                'message_uuid' => $messageUuid
            ]);

            return [
                'success' => true,
                'message' => 'WhatsApp audio sent successfully',
                'data' => [
                    'message_uuid' => $messageUuid,
                    'to' => $formattedPhone
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Vonage WhatsApp audio exception', [
                'to' => $formattedPhone,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred while sending WhatsApp audio',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send WhatsApp file/document message
     * 
     * @param string $to Phone number in international format
     * @param string $fileUrl Publicly accessible URL to the file
     * @param string $caption Optional caption for the file
     * @param string|null $fileName Optional filename
     * @return array Response with success status and data
     */
    public function sendWhatsAppFile(string $to, string $fileUrl, string $caption = '', ?string $fileName = null): array
    {
        if (!$this->whatsappEnabled) {
            return [
                'success' => false,
                'message' => 'WhatsApp sending disabled',
                'skipped' => true
            ];
        }

        if (empty($this->whatsappNumber)) {
            return [
                'success' => false,
                'message' => 'WhatsApp number not configured',
                'error' => 'configuration_error'
            ];
        }

        $useJWT = !empty($this->applicationId) && !empty($this->privateKey);
        $useBasic = !empty($this->apiKey) && !empty($this->apiSecret);

        if (!$useJWT && !$useBasic) {
            return [
                'success' => false,
                'message' => 'Vonage WhatsApp requires authentication credentials',
                'error' => 'configuration_error'
            ];
        }

        $formattedPhone = $this->formatPhoneNumberForWhatsApp($to);

        try {
            // Use the Vonage Facade
            $client = Vonage::getFacadeRoot();

            // Handle sandbox mode
            if ($this->messagesSandbox) {
                $client->messages()->getAPIResource()->setBaseUrl('https://messages-sandbox.nexmo.com/v1/messages');
            }

            $fileObject = new FileObject($fileUrl, $caption, $fileName);
                $fromNumber = $this->whatsappBusinessId ?: $this->whatsappNumber;
                $whatsappFile = new WhatsAppFile($formattedPhone, $fromNumber, $fileObject);

            $response = $client->messages()->send($whatsappFile);
            
            // Handle both object and array responses
            $messageUuid = is_object($response) ? $response->getMessageUuid() : ($response['message_uuid'] ?? null);

            Log::info('Vonage WhatsApp file sent successfully', [
                'to' => $formattedPhone,
                'file_url' => $fileUrl,
                'file_name' => $fileName,
                'message_uuid' => $messageUuid
            ]);

            return [
                'success' => true,
                'message' => 'WhatsApp file sent successfully',
                'data' => [
                    'message_uuid' => $messageUuid,
                    'to' => $formattedPhone
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Vonage WhatsApp file exception', [
                'to' => $formattedPhone,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred while sending WhatsApp file',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format phone number for WhatsApp (E.164 format with +)
     *
     * @param string $phone Phone number in any format
     * @return string Phone number in E.164 format (e.g., +2347081114942)
     */
    protected function formatPhoneNumberForWhatsApp(string $phone): string
    {
        // Remove spaces, hyphens, parentheses
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

        // If starts with 0, replace with +234 (Nigeria country code)
        if (substr($phone, 0, 1) === '0') {
            $phone = '+234' . substr($phone, 1);
        }

        // If doesn't start with +, add it
        if (substr($phone, 0, 1) !== '+') {
            // If starts with 234, add +
            if (substr($phone, 0, 3) === '234') {
                $phone = '+' . $phone;
            } else {
                // Assume Nigeria if no country code
                $phone = '+234' . $phone;
            }
        }

        return $phone;
    }

    // ==================== MMS METHODS ====================

    /**
     * Send MMS image message
     * 
     * @param string $to Phone number in international format
     * @param string $imageUrl Publicly accessible URL to the image
     * @param string $caption Optional caption for the image
     * @param string|null $fromNumber Your Vonage phone number (required for MMS)
     * @return array Response with success status and data
     */
    public function sendMMSImage(string $to, string $imageUrl, string $caption = '', ?string $fromNumber = null): array
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'message' => 'Vonage service disabled',
                'skipped' => true
            ];
        }

        $fromNumber = $fromNumber ?? config('services.vonage.mms_number') ?? config('services.vonage.voice_number');
        if (empty($fromNumber)) {
            return [
                'success' => false,
                'message' => 'MMS requires a phone number. Set VONAGE_MMS_NUMBER or VONAGE_VOICE_NUMBER in .env',
                'error' => 'configuration_error'
            ];
        }

        $useJWT = !empty($this->applicationId) && !empty($this->privateKey);
        $useBasic = !empty($this->apiKey) && !empty($this->apiSecret);

        if (!$useJWT && !$useBasic) {
            return [
                'success' => false,
                'message' => 'Vonage MMS requires authentication credentials',
                'error' => 'configuration_error'
            ];
        }

        $formattedPhone = $this->formatPhoneNumber($to);
        $formattedFrom = $this->formatPhoneNumber($fromNumber);

        try {
            $credentials = $useJWT 
                ? new Keypair($this->privateKey, $this->applicationId)
                : new Basic($this->apiKey, $this->apiSecret);
            
            $client = new Client($credentials);

            $imageObject = new ImageObject($imageUrl, $caption);
            $mmsImage = new MMSImage($formattedPhone, $formattedFrom, $imageObject);

            $response = $client->messages()->send($mmsImage);
            $messageUuid = $response->getMessageUuid();

            Log::info('Vonage MMS image sent successfully', [
                'to' => $formattedPhone,
                'from' => $formattedFrom,
                'image_url' => $imageUrl,
                'message_uuid' => $messageUuid
            ]);

            return [
                'success' => true,
                'message' => 'MMS image sent successfully',
                'data' => [
                    'message_uuid' => $messageUuid,
                    'to' => $formattedPhone
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Vonage MMS image exception', [
                'to' => $formattedPhone,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred while sending MMS image',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send MMS video message
     * 
     * @param string $to Phone number in international format
     * @param string $videoUrl Publicly accessible URL to the video
     * @param string $caption Optional caption for the video
     * @param string|null $fromNumber Your Vonage phone number (required for MMS)
     * @return array Response with success status and data
     */
    public function sendMMSVideo(string $to, string $videoUrl, string $caption = '', ?string $fromNumber = null): array
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'message' => 'Vonage service disabled',
                'skipped' => true
            ];
        }

        $fromNumber = $fromNumber ?? config('services.vonage.mms_number') ?? config('services.vonage.voice_number');
        if (empty($fromNumber)) {
            return [
                'success' => false,
                'message' => 'MMS requires a phone number. Set VONAGE_MMS_NUMBER or VONAGE_VOICE_NUMBER in .env',
                'error' => 'configuration_error'
            ];
        }

        $useJWT = !empty($this->applicationId) && !empty($this->privateKey);
        $useBasic = !empty($this->apiKey) && !empty($this->apiSecret);

        if (!$useJWT && !$useBasic) {
            return [
                'success' => false,
                'message' => 'Vonage MMS requires authentication credentials',
                'error' => 'configuration_error'
            ];
        }

        $formattedPhone = $this->formatPhoneNumber($to);
        $formattedFrom = $this->formatPhoneNumber($fromNumber);

        try {
            $credentials = $useJWT 
                ? new Keypair($this->privateKey, $this->applicationId)
                : new Basic($this->apiKey, $this->apiSecret);
            
            $client = new Client($credentials);

            $videoObject = new VideoObject($videoUrl, $caption);
            $mmsVideo = new MMSVideo($formattedPhone, $formattedFrom, $videoObject);

            $response = $client->messages()->send($mmsVideo);
            $messageUuid = $response->getMessageUuid();

            Log::info('Vonage MMS video sent successfully', [
                'to' => $formattedPhone,
                'from' => $formattedFrom,
                'video_url' => $videoUrl,
                'message_uuid' => $messageUuid
            ]);

            return [
                'success' => true,
                'message' => 'MMS video sent successfully',
                'data' => [
                    'message_uuid' => $messageUuid,
                    'to' => $formattedPhone
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Vonage MMS video exception', [
                'to' => $formattedPhone,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred while sending MMS video',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send MMS audio message
     * 
     * @param string $to Phone number in international format
     * @param string $audioUrl Publicly accessible URL to the audio file
     * @param string|null $fromNumber Your Vonage phone number (required for MMS)
     * @return array Response with success status and data
     */
    public function sendMMSAudio(string $to, string $audioUrl, ?string $fromNumber = null): array
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'message' => 'Vonage service disabled',
                'skipped' => true
            ];
        }

        $fromNumber = $fromNumber ?? config('services.vonage.mms_number') ?? config('services.vonage.voice_number');
        if (empty($fromNumber)) {
            return [
                'success' => false,
                'message' => 'MMS requires a phone number. Set VONAGE_MMS_NUMBER or VONAGE_VOICE_NUMBER in .env',
                'error' => 'configuration_error'
            ];
        }

        $useJWT = !empty($this->applicationId) && !empty($this->privateKey);
        $useBasic = !empty($this->apiKey) && !empty($this->apiSecret);

        if (!$useJWT && !$useBasic) {
            return [
                'success' => false,
                'message' => 'Vonage MMS requires authentication credentials',
                'error' => 'configuration_error'
            ];
        }

        $formattedPhone = $this->formatPhoneNumber($to);
        $formattedFrom = $this->formatPhoneNumber($fromNumber);

        try {
            $credentials = $useJWT 
                ? new Keypair($this->privateKey, $this->applicationId)
                : new Basic($this->apiKey, $this->apiSecret);
            
            $client = new Client($credentials);

            $audioObject = new AudioObject($audioUrl);
            $mmsAudio = new MMSAudio($formattedPhone, $formattedFrom, $audioObject);

            $response = $client->messages()->send($mmsAudio);
            $messageUuid = $response->getMessageUuid();

            Log::info('Vonage MMS audio sent successfully', [
                'to' => $formattedPhone,
                'from' => $formattedFrom,
                'audio_url' => $audioUrl,
                'message_uuid' => $messageUuid
            ]);

            return [
                'success' => true,
                'message' => 'MMS audio sent successfully',
                'data' => [
                    'message_uuid' => $messageUuid,
                    'to' => $formattedPhone
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Vonage MMS audio exception', [
                'to' => $formattedPhone,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred while sending MMS audio',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send WhatsApp message
     */
    public function sendWhatsApp($to, $message, $options = [])
    {
        if (!$this->whatsappEnabled) {
            return [
                'success' => false,
                'message' => 'WhatsApp is not enabled',
                'error' => 'service_disabled'
            ];
        }

        $fromNumber = $options['from'] ?? $this->whatsappNumber;
        if (empty($fromNumber)) {
            return [
                'success' => false,
                'message' => 'WhatsApp requires a phone number. Set VONAGE_WHATSAPP_NUMBER in .env',
                'error' => 'configuration_error'
            ];
        }

        $useJWT = !empty($this->applicationId) && !empty($this->privateKey);
        $useBasic = !empty($this->apiKey) && !empty($this->apiSecret);

        if (!$useJWT && !$useBasic) {
            return [
                'success' => false,
                'message' => 'Vonage WhatsApp requires authentication credentials',
                'error' => 'configuration_error'
            ];
        }

        $formattedPhone = $this->formatPhoneNumber($to);
        $formattedFrom = $this->formatPhoneNumber($fromNumber);

        try {
            $credentials = $useJWT 
                ? new Keypair($this->privateKey, $this->applicationId)
                : new Basic($this->apiKey, $this->apiSecret);
            
            $client = new Client($credentials);

            $whatsappText = new WhatsAppText($formattedPhone, $formattedFrom, $message);
            $response = $client->messages()->send($whatsappText);
            $messageUuid = $response->getMessageUuid();

            Log::info('Vonage WhatsApp message sent successfully', [
                'to' => $formattedPhone,
                'from' => $formattedFrom,
                'message' => $message,
                'message_uuid' => $messageUuid
            ]);

            return [
                'success' => true,
                'message' => 'WhatsApp message sent successfully',
                'data' => [
                    'message_uuid' => $messageUuid,
                    'to' => $formattedPhone,
                    'from' => $formattedFrom
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Vonage WhatsApp exception', [
                'to' => $formattedPhone,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred while sending WhatsApp message',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Initiate voice call
     */
    public function initiateCall($to, $options = [])
    {
        $fromNumber = $options['from'] ?? config('services.vonage.voice_number');
        if (empty($fromNumber)) {
            return [
                'success' => false,
                'message' => 'Voice calls require a phone number. Set VONAGE_VOICE_NUMBER in .env',
                'error' => 'configuration_error'
            ];
        }

        $useJWT = !empty($this->applicationId) && !empty($this->privateKey);
        $useBasic = !empty($this->apiKey) && !empty($this->apiSecret);

        if (!$useJWT && !$useBasic) {
            return [
                'success' => false,
                'message' => 'Vonage Voice requires authentication credentials',
                'error' => 'configuration_error'
            ];
        }

        $formattedPhone = $this->formatPhoneNumber($to);
        $formattedFrom = $this->formatPhoneNumber($fromNumber);

        try {
            $credentials = $useJWT 
                ? new Keypair($this->privateKey, $this->applicationId)
                : new Basic($this->apiKey, $this->apiSecret);
            
            $client = new Client($credentials);

            $callOptions = [
                'to' => [['type' => 'phone', 'number' => $formattedPhone]],
                'from' => ['type' => 'phone', 'number' => $formattedFrom],
                'answer_url' => $options['answer_url'] ?? route('webhooks.vonage.voice.answer'),
                'event_url' => $options['event_url'] ?? route('webhooks.vonage.voice.event'),
            ];

            if (isset($options['machine_detection'])) {
                $callOptions['machine_detection'] = $options['machine_detection'];
            }

            $call = $client->voice()->createOutboundCall($callOptions);
            $callUuid = $call->getUuid();

            Log::info('Vonage voice call initiated successfully', [
                'to' => $formattedPhone,
                'from' => $formattedFrom,
                'call_uuid' => $callUuid
            ]);

            return [
                'success' => true,
                'message' => 'Voice call initiated successfully',
                'data' => [
                    'uuid' => $callUuid,
                    'to' => $formattedPhone,
                    'from' => $formattedFrom
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Vonage voice call exception', [
                'to' => $formattedPhone,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred while initiating voice call',
                'error' => $e->getMessage()
            ];
        }
    }
}

