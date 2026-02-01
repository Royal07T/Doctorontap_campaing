<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\Client\Credentials\Keypair;
use Vonage\Voice\NCCO\NCCO;
use Vonage\Voice\NCCO\Action\Talk;
use Vonage\Voice\NCCO\Action\Stream;
use Vonage\Voice\NCCO\Action\Record;
use Vonage\Voice\NCCO\Action\Connect;
use Vonage\Voice\NCCO\Action\Input;
use Vonage\Voice\NCCO\Action\Conversation;
use Vonage\Voice\OutboundCall;

class VonageVoiceService
{
    protected $apiKey;
    protected $apiSecret;
    protected $applicationId;
    protected $privateKey;
    protected $fromNumber;
    protected $enabled;
    protected $webhookUrl;

    public function __construct()
    {
        $this->apiKey = config('vonage.api_key');
        $this->apiSecret = config('vonage.api_secret');
        $this->applicationId = config('vonage.application_id');
        $this->privateKey = $this->getPrivateKey();
        $this->fromNumber = config('vonage.voice_number');
        $this->enabled = config('vonage.voice_enabled', false);
        $this->webhookUrl = config('services.vonage.voice_webhook_url');
    }

    /**
     * Get private key from file path or inline
     */
    protected function getPrivateKey()
    {
        $privateKeyPath = config('services.vonage.private_key_path');
        $privateKey = config('vonage.private_key');

        if ($privateKeyPath && file_exists($privateKeyPath)) {
            return file_get_contents($privateKeyPath);
        }

        if ($privateKey) {
            return str_replace('\\n', "\n", $privateKey);
        }

        return null;
    }

    /**
     * Get Vonage client with appropriate authentication
     */
    protected function getClient(): Client
    {
        $useJWT = !empty($this->applicationId) && !empty($this->privateKey);
        $useBasic = !empty($this->apiKey) && !empty($this->apiSecret);

        if ($useJWT) {
            $credentials = new Keypair($this->privateKey, $this->applicationId);
            Log::debug('Using JWT authentication for Voice API');
        } elseif ($useBasic) {
            $credentials = new Basic($this->apiKey, $this->apiSecret);
            Log::debug('Using Basic authentication for Voice API');
        } else {
            throw new \Exception('Vonage Voice API requires either JWT (VONAGE_APPLICATION_ID + VONAGE_PRIVATE_KEY) or Basic (VONAGE_API_KEY + VONAGE_API_SECRET) credentials');
        }

        return new Client($credentials);
    }

    /**
     * Make an outbound call with text-to-speech
     *
     * @param string $to Phone number in international format (e.g., +2348012345678)
     * @param string $message Text to speak to the recipient
     * @param array $options Additional options (language, voice, etc.)
     * @return array Response with success status and call UUID
     */
    public function makeCall(string $to, string $message, array $options = []): array
    {
        if (!$this->enabled) {
            Log::info('Vonage Voice skipped (disabled in config)', [
                'to' => $to,
                'message' => $message
            ]);
            
            return [
                'success' => true,
                'message' => 'Voice calling disabled',
                'skipped' => true
            ];
        }

        if (empty($this->fromNumber)) {
            Log::error('Vonage Voice number not configured');
            
            return [
                'success' => false,
                'message' => 'Voice number not configured. Set VONAGE_VOICE_NUMBER in .env',
                'error' => 'configuration_error'
            ];
        }

        // Format phone number
        $formattedPhone = $this->formatPhoneNumber($to);

        try {
            set_time_limit(60); // Calls may take longer
            
            $client = $this->getClient();

            // Create NCCO (Nexmo Call Control Object)
            $ncco = new NCCO();
            
            // Add talk action (text-to-speech)
            $talk = new Talk($message);
            
            // Set language and style (voice) if provided
            if (isset($options['language'])) {
                $style = (int) ($options['voice_style'] ?? 0);
                $talk->setLanguage($options['language'], $style);
            }
            if (isset($options['style'])) {
                $talk->setStyle($options['style']);
            }
            
            $ncco->addAction($talk);

            // Add recording if requested
            if (isset($options['record']) && $options['record']) {
                $record = new Record();
                if ($this->webhookUrl) {
                    $record->setEventUrl([$this->webhookUrl . '/voice/recording']);
                }
                $ncco->addAction($record);
            }

            // Create outbound call
            $call = new OutboundCall(
                new \Vonage\Voice\Endpoint\Phone($formattedPhone),
                new \Vonage\Voice\Endpoint\Phone($this->fromNumber)
            );
            
            $call->setNCCO($ncco);
            
            // Set webhook URLs if configured
            if ($this->webhookUrl) {
                $call->setAnswerWebhook(new \Vonage\Voice\Webhook($this->webhookUrl . '/voice/answer'));
                $call->setEventWebhook(new \Vonage\Voice\Webhook($this->webhookUrl . '/voice/event'));
            }

            // Make the call
            $response = $client->voice()->createOutboundCall($call);
            $callUuid = $response->getUuid();
            
            Log::info('Vonage Voice call initiated successfully', [
                'to' => $formattedPhone,
                'from' => $this->fromNumber,
                'call_uuid' => $callUuid
            ]);

            return [
                'success' => true,
                'message' => 'Call initiated successfully',
                'data' => [
                    'call_uuid' => $callUuid,
                    'to' => $formattedPhone,
                    'from' => $this->fromNumber,
                    'status' => $response->getStatus()
                ]
            ];
        } catch (\Vonage\Client\Exception\Request $e) {
            Log::error('Vonage Voice call request exception', [
                'to' => $formattedPhone,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            
            return [
                'success' => false,
                'message' => 'Vonage Voice API request failed',
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        } catch (\Exception $e) {
            Log::error('Vonage Voice call exception', [
                'to' => $formattedPhone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred while making call',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Make a call with custom NCCO actions
     *
     * @param string $to Phone number
     * @param NCCO $ncco Custom NCCO object
     * @return array Response with success status and call UUID
     */
    public function makeCallWithNCCO(string $to, NCCO $ncco): array
    {
        if (!$this->enabled) {
            return [
                'success' => true,
                'message' => 'Voice calling disabled',
                'skipped' => true
            ];
        }

        if (empty($this->fromNumber)) {
            return [
                'success' => false,
                'message' => 'Voice number not configured',
                'error' => 'configuration_error'
            ];
        }

        $formattedPhone = $this->formatPhoneNumber($to);

        try {
            set_time_limit(60);
            
            $client = $this->getClient();

            $call = new OutboundCall(
                new \Vonage\Voice\Endpoint\Phone($formattedPhone),
                new \Vonage\Voice\Endpoint\Phone($this->fromNumber)
            );
            
            $call->setNCCO($ncco);
            
            if ($this->webhookUrl) {
                $call->setAnswerWebhook(new \Vonage\Voice\Webhook($this->webhookUrl . '/voice/answer'));
                $call->setEventWebhook(new \Vonage\Voice\Webhook($this->webhookUrl . '/voice/event'));
            }

            $response = $client->voice()->createOutboundCall($call);
            $callUuid = $response->getUuid();
            
            Log::info('Vonage Voice call with custom NCCO initiated', [
                'to' => $formattedPhone,
                'call_uuid' => $callUuid
            ]);

            return [
                'success' => true,
                'message' => 'Call initiated successfully',
                'data' => [
                    'call_uuid' => $callUuid,
                    'to' => $formattedPhone
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Vonage Voice call with NCCO exception', [
                'to' => $formattedPhone,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred while making call',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create a conference call
     *
     * @param array $participants Array of phone numbers
     * @param string $conversationName Name for the conference
     * @return array Response with success status
     */
    public function createConference(array $participants, string $conversationName = 'Conference'): array
    {
        if (!$this->enabled) {
            return [
                'success' => true,
                'message' => 'Voice calling disabled',
                'skipped' => true
            ];
        }

        $results = [];
        
        foreach ($participants as $participant) {
            $formattedPhone = $this->formatPhoneNumber($participant);
            
            // Create NCCO to join conversation
            $ncco = new NCCO();
            $conversation = new Conversation($conversationName);
            $ncco->addAction($conversation);
            
            $result = $this->makeCallWithNCCO($formattedPhone, $ncco);
            $results[] = $result;
        }

        $successCount = count(array_filter($results, fn($r) => $r['success']));
        
        return [
            'success' => $successCount > 0,
            'message' => "Conference call initiated. {$successCount}/" . count($participants) . " participants called",
            'data' => [
                'participants' => $results,
                'conversation_name' => $conversationName
            ]
        ];
    }

    /**
     * Get call information
     *
     * @param string $callUuid Call UUID
     * @return array Call information
     */
    public function getCall(string $callUuid): array
    {
        try {
            $client = $this->getClient();
            $call = $client->voice()->get($callUuid);
            
            return [
                'success' => true,
                'data' => [
                    'uuid' => $call->getUuid(),
                    'status' => $call->getStatus(),
                    'direction' => $call->getDirection(),
                    'rate' => $call->getRate(),
                    'price' => $call->getPrice(),
                    'duration' => $call->getDuration(),
                    'start_time' => $call->getStartTime(),
                    'end_time' => $call->getEndTime(),
                    'network' => $call->getNetwork(),
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get call information', [
                'call_uuid' => $callUuid,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to get call information',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Hang up a call
     *
     * @param string $callUuid Call UUID
     * @return array Response
     */
    public function hangupCall(string $callUuid): array
    {
        try {
            $client = $this->getClient();
            $client->voice()->put($callUuid, new \Vonage\Voice\Endpoint\Phone(''), 'hangup');
            
            Log::info('Call hung up', ['call_uuid' => $callUuid]);
            
            return [
                'success' => true,
                'message' => 'Call hung up successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to hang up call', [
                'call_uuid' => $callUuid,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to hang up call',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format phone number to E.164 format
     *
     * @param string $phone Phone number in any format
     * @return string Phone number in E.164 format
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove spaces, hyphens, parentheses
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

        // If starts with 0, replace with +234 (Nigeria)
        if (substr($phone, 0, 1) === '0') {
            $phone = '+234' . substr($phone, 1);
        }

        // If doesn't start with +, add it
        if (substr($phone, 0, 1) !== '+') {
            if (substr($phone, 0, 3) === '234') {
                $phone = '+' . $phone;
            } else {
                // Assume Nigeria if no country code
                $phone = '+234' . $phone;
            }
        }

        return $phone;
    }

    /**
     * Create NCCO for text-to-speech
     *
     * @param string $text Text to speak
     * @param array $options Options (language, voice, style)
     * @return NCCO
     */
    public static function createTalkNCCO(string $text, array $options = []): NCCO
    {
        $ncco = new NCCO();
        $talk = new Talk($text);
        
        if (isset($options['language'])) {
            $talk->setLanguage($options['language']);
        }
        if (isset($options['voice'])) {
            $talk->setVoiceName($options['voice']);
        }
        if (isset($options['style'])) {
            $talk->setStyle($options['style']);
        }
        
        $ncco->addAction($talk);
        return $ncco;
    }

    /**
     * Create NCCO for playing audio file
     *
     * @param string $audioUrl URL to audio file
     * @param int $level Volume level (0-1)
     * @return NCCO
     */
    public static function createStreamNCCO(string $audioUrl, float $level = 1.0): NCCO
    {
        $ncco = new NCCO();
        $stream = new Stream($audioUrl);
        $stream->setLevel($level);
        $ncco->addAction($stream);
        return $ncco;
    }
}

