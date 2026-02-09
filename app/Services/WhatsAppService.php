<?php

namespace App\Services;

use Vonage\Client;
use Vonage\Messages\Channel\WhatsApp\WhatsAppText;
use Vonage\Messages\Channel\WhatsApp\WhatsAppTemplate;
use Vonage\Messages\MessageObjects\TemplateObject;
use Vonage\Messages\Channel\WhatsApp\WhatsAppImage;
use Vonage\Messages\Channel\WhatsApp\WhatsAppVideo;
use Vonage\Messages\Channel\WhatsApp\WhatsAppAudio;
use Vonage\Messages\Channel\WhatsApp\WhatsAppFile;
use Vonage\Messages\Channel\WhatsApp\WhatsAppCustom;
use Vonage\Messages\MessageObjects\ImageObject;
use Vonage\Messages\MessageObjects\VideoObject;
use Vonage\Messages\MessageObjects\AudioObject;
use Vonage\Messages\MessageObjects\FileObject;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Service using Vonage Laravel Package
 * Production-ready (non-sandbox) WhatsApp messaging
 */
class WhatsAppService
{
    /**
     * Send a WhatsApp text message
     * 
     * @param string $toNumber Phone number in E.164 format (e.g., 447123456789 or +447123456789)
     * @param string $message Message text
     * @return array Response with success status and data
     */
    public function sendText(string $toNumber, string $message): array
    {
        try {
            // Format phone number (ensure E.164 format)
            $formattedTo = $this->formatPhoneNumber($toNumber);
            
            // For WhatsApp Messages API, use WhatsApp Business Number ID (required)
            // The Business Number ID is what the API expects in the 'from' parameter
            $fromNumber = config('services.vonage.whatsapp.business_number_id') 
                ?: config('services.vonage.whatsapp_id');
            
            // If Business Number ID not available, fallback to phone number (remove +)
            if (empty($fromNumber)) {
                $fromNumber = config('services.vonage.whatsapp.from_phone_number') 
                    ?: config('services.vonage.whatsapp_number');
                if ($fromNumber) {
                    $fromNumber = str_replace('+', '', $fromNumber);
                }
            }

            if (empty($fromNumber)) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp Business Number ID or phone number not configured. Please set VONAGE_WHATSAPP_ID or VONAGE_WHATSAPP_NUMBER in .env',
                    'error' => 'configuration_error'
                ];
            }

            // Create WhatsApp text message
            $whatsAppMessage = new WhatsAppText(
                to: $formattedTo,
                from: $fromNumber,
                text: $message,
            );

            // Send message using Vonage Client
            $response = app(Client::class)
                ->messages()
                ->send($whatsAppMessage);

            // Handle response
            $messageUuid = is_object($response) 
                ? $response->getMessageUuid() 
                : ($response['message_uuid'] ?? null);

            Log::info('Vonage WhatsApp message sent successfully', [
                'to' => $formattedTo,
                'from' => $fromNumber,
                'message_uuid' => $messageUuid
            ]);

            return [
                'success' => true,
                'message' => 'WhatsApp message sent successfully',
                'data' => [
                    'message_uuid' => $messageUuid,
                    'to' => $formattedTo,
                    'from' => $fromNumber
                ]
            ];

        } catch (\Vonage\Client\Exception\Request $e) {
            Log::error('Vonage WhatsApp request exception', [
                'to' => $toNumber,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send WhatsApp message',
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        } catch (\Exception $e) {
            Log::error('Vonage WhatsApp exception', [
                'to' => $toNumber,
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
     * Send a WhatsApp template message (MTM - Message Template)
     * Use this to start a new conversation window (24-hour window starts after this)
     * 
     * Template name format: "namespace:template_name" (e.g., "whatsapp:hugotemplate")
     * 
     * @param string $toNumber Phone number in E.164 format
     * @param string $templateName Template name in format "namespace:template_name" (must be approved by WhatsApp)
     * @param string $templateLanguage Template language code (e.g., 'en_US', 'en')
     * @param array $templateParameters Template parameters for body components
     * @return array Response with success status and data
     */
    public function sendTemplate(
        string $toNumber, 
        string $templateName, 
        string $templateLanguage = 'en_US',
        array $templateParameters = []
    ): array {
        try {
            $formattedTo = $this->formatPhoneNumber($toNumber);
            
            // Use WhatsApp Business Number ID if available (preferred for Messages API)
            $fromNumber = config('services.vonage.whatsapp.business_number_id') 
                ?: config('services.vonage.whatsapp_id')
                ?: config('services.vonage.whatsapp.from_phone_number');

            if (empty($fromNumber)) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp Business Number ID or phone number not configured',
                    'error' => 'configuration_error'
                ];
            }

            // Create TemplateObject as per official documentation
            // TemplateObject takes: name and parameters (not components/language)
            $templateObject = new TemplateObject(
                name: $templateName,
                parameters: $templateParameters
            );

            // Create WhatsApp template message using templateObject and locale
            // As per official docs: https://developer.vonage.com/en/blog/send-whatsapp-messages-in-laravel-with-vonages-native-sdk
            $whatsAppTemplate = new WhatsAppTemplate(
                to: $formattedTo,
                from: $fromNumber,
                templateObject: $templateObject,
                locale: $templateLanguage
            );

            $response = app(Client::class)
                ->messages()
                ->send($whatsAppTemplate);

            $messageUuid = is_object($response) 
                ? $response->getMessageUuid() 
                : ($response['message_uuid'] ?? null);

            Log::info('Vonage WhatsApp template sent successfully', [
                'to' => $formattedTo,
                'template_name' => $templateName,
                'template_language' => $templateLanguage,
                'message_uuid' => $messageUuid
            ]);

            return [
                'success' => true,
                'message' => 'WhatsApp template sent successfully',
                'data' => [
                    'message_uuid' => $messageUuid,
                    'to' => $formattedTo,
                    'template_name' => $templateName
                ]
            ];

        } catch (\Vonage\Client\Exception\Request $e) {
            Log::error('Vonage WhatsApp template request exception', [
                'to' => $toNumber,
                'template_name' => $templateName,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send WhatsApp template',
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        } catch (\Exception $e) {
            Log::error('Vonage WhatsApp template exception', [
                'to' => $toNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred while sending WhatsApp template',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format phone number to E.164 format
     * 
     * @param string $phone Phone number in any format
     * @return string Phone number in E.164 format (e.g., +447123456789)
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // If doesn't start with +, add it
        if (!str_starts_with($phone, '+')) {
            // If starts with 0, remove it (assuming local format)
            if (str_starts_with($phone, '0')) {
                $phone = substr($phone, 1);
            }
            // Add country code if not present (default to 44 for UK, adjust as needed)
            // You may want to make this configurable
            $phone = '+' . $phone;
        }

        return $phone;
    }

    /**
     * Send a WhatsApp image message
     * Can only be sent within 24-hour customer care window
     * 
     * @param string $toNumber Phone number in E.164 format
     * @param string $imageUrl Public URL to the image
     * @param string|null $caption Optional caption for the image
     * @return array Response with success status and data
     */
    public function sendImage(string $toNumber, string $imageUrl, ?string $caption = null): array
    {
        try {
            $formattedTo = $this->formatPhoneNumber($toNumber);
            
            // Use WhatsApp Business Number ID if available (preferred for Messages API)
            $fromNumber = config('services.vonage.whatsapp.business_number_id') 
                ?: config('services.vonage.whatsapp_id')
                ?: config('services.vonage.whatsapp.from_phone_number');

            if (empty($fromNumber)) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp Business Number ID or phone number not configured',
                    'error' => 'configuration_error'
                ];
            }

            $imageObject = new ImageObject($imageUrl, $caption);
            $whatsAppImage = new WhatsAppImage($formattedTo, $fromNumber, $imageObject);

            $response = app(Client::class)
                ->messages()
                ->send($whatsAppImage);

            $messageUuid = is_object($response) 
                ? $response->getMessageUuid() 
                : ($response['message_uuid'] ?? null);

            Log::info('Vonage WhatsApp image sent successfully', [
                'to' => $formattedTo,
                'image_url' => $imageUrl,
                'message_uuid' => $messageUuid
            ]);

            return [
                'success' => true,
                'message' => 'WhatsApp image sent successfully',
                'data' => [
                    'message_uuid' => $messageUuid,
                    'to' => $formattedTo
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Vonage WhatsApp image exception', [
                'to' => $toNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send WhatsApp image',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send a WhatsApp video message
     * Can only be sent within 24-hour customer care window
     * 
     * @param string $toNumber Phone number in E.164 format
     * @param string $videoUrl Public URL to the video
     * @param string|null $caption Optional caption for the video
     * @return array Response with success status and data
     */
    public function sendVideo(string $toNumber, string $videoUrl, ?string $caption = null): array
    {
        try {
            $formattedTo = $this->formatPhoneNumber($toNumber);
            
            // Use WhatsApp Business Number ID if available (preferred for Messages API)
            $fromNumber = config('services.vonage.whatsapp.business_number_id') 
                ?: config('services.vonage.whatsapp_id')
                ?: config('services.vonage.whatsapp.from_phone_number');

            if (empty($fromNumber)) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp Business Number ID or phone number not configured',
                    'error' => 'configuration_error'
                ];
            }

            $videoObject = new VideoObject($videoUrl, $caption);
            $whatsAppVideo = new WhatsAppVideo($formattedTo, $fromNumber, $videoObject);

            $response = app(Client::class)
                ->messages()
                ->send($whatsAppVideo);

            $messageUuid = is_object($response) 
                ? $response->getMessageUuid() 
                : ($response['message_uuid'] ?? null);

            return [
                'success' => true,
                'message' => 'WhatsApp video sent successfully',
                'data' => [
                    'message_uuid' => $messageUuid,
                    'to' => $formattedTo
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Vonage WhatsApp video exception', [
                'to' => $toNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send WhatsApp video',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send a WhatsApp audio message
     * Can only be sent within 24-hour customer care window
     * 
     * @param string $toNumber Phone number in E.164 format
     * @param string $audioUrl Public URL to the audio file
     * @return array Response with success status and data
     */
    public function sendAudio(string $toNumber, string $audioUrl): array
    {
        try {
            $formattedTo = $this->formatPhoneNumber($toNumber);
            
            // Use WhatsApp Business Number ID if available (preferred for Messages API)
            $fromNumber = config('services.vonage.whatsapp.business_number_id') 
                ?: config('services.vonage.whatsapp_id')
                ?: config('services.vonage.whatsapp.from_phone_number');

            if (empty($fromNumber)) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp Business Number ID or phone number not configured',
                    'error' => 'configuration_error'
                ];
            }

            $audioObject = new AudioObject($audioUrl);
            $whatsAppAudio = new WhatsAppAudio($formattedTo, $fromNumber, $audioObject);

            $response = app(Client::class)
                ->messages()
                ->send($whatsAppAudio);

            $messageUuid = is_object($response) 
                ? $response->getMessageUuid() 
                : ($response['message_uuid'] ?? null);

            return [
                'success' => true,
                'message' => 'WhatsApp audio sent successfully',
                'data' => [
                    'message_uuid' => $messageUuid,
                    'to' => $formattedTo
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Vonage WhatsApp audio exception', [
                'to' => $toNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send WhatsApp audio',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send a WhatsApp file/document message
     * Can only be sent within 24-hour customer care window
     * 
     * @param string $toNumber Phone number in E.164 format
     * @param string $fileUrl Public URL to the file
     * @param string|null $caption Optional caption for the file
     * @param string|null $fileName Optional file name
     * @return array Response with success status and data
     */
    public function sendFile(string $toNumber, string $fileUrl, ?string $caption = null, ?string $fileName = null): array
    {
        try {
            $formattedTo = $this->formatPhoneNumber($toNumber);
            
            // Use WhatsApp Business Number ID if available (preferred for Messages API)
            $fromNumber = config('services.vonage.whatsapp.business_number_id') 
                ?: config('services.vonage.whatsapp_id')
                ?: config('services.vonage.whatsapp.from_phone_number');

            if (empty($fromNumber)) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp Business Number ID or phone number not configured',
                    'error' => 'configuration_error'
                ];
            }

            $fileObject = new FileObject($fileUrl, $caption, $fileName);
            $whatsAppFile = new WhatsAppFile($formattedTo, $fromNumber, $fileObject);

            $response = app(Client::class)
                ->messages()
                ->send($whatsAppFile);

            $messageUuid = is_object($response) 
                ? $response->getMessageUuid() 
                : ($response['message_uuid'] ?? null);

            return [
                'success' => true,
                'message' => 'WhatsApp file sent successfully',
                'data' => [
                    'message_uuid' => $messageUuid,
                    'to' => $formattedTo
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Vonage WhatsApp file exception', [
                'to' => $toNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send WhatsApp file',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send a WhatsApp location message
     * Can only be sent within 24-hour customer care window
     * 
     * @param string $toNumber Phone number in E.164 format
     * @param float $longitude Longitude coordinate
     * @param float $latitude Latitude coordinate
     * @param string|null $name Optional location name
     * @param string|null $address Optional location address
     * @return array Response with success status and data
     */
    public function sendLocation(
        string $toNumber, 
        float $longitude, 
        float $latitude, 
        ?string $name = null, 
        ?string $address = null
    ): array {
        try {
            $formattedTo = $this->formatPhoneNumber($toNumber);
            
            // Use WhatsApp Business Number ID if available (preferred for Messages API)
            $fromNumber = config('services.vonage.whatsapp.business_number_id') 
                ?: config('services.vonage.whatsapp_id')
                ?: config('services.vonage.whatsapp.from_phone_number');

            if (empty($fromNumber)) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp Business Number ID or phone number not configured',
                    'error' => 'configuration_error'
                ];
            }

            // Build location data as per official documentation
            $locationData = [
                'longitude' => $longitude,
                'latitude' => $latitude,
            ];

            if ($name) {
                $locationData['name'] = $name;
            }

            if ($address) {
                $locationData['address'] = $address;
            }

            // Create WhatsApp custom message with location type
            $whatsAppMessage = new WhatsAppCustom(
                to: $formattedTo,
                from: $fromNumber,
                custom: [
                    'type' => 'location',
                    'location' => $locationData,
                ],
            );

            $response = app(Client::class)
                ->messages()
                ->send($whatsAppMessage);

            $messageUuid = is_object($response) 
                ? $response->getMessageUuid() 
                : ($response['message_uuid'] ?? null);

            Log::info('Vonage WhatsApp location sent successfully', [
                'to' => $formattedTo,
                'longitude' => $longitude,
                'latitude' => $latitude,
                'message_uuid' => $messageUuid
            ]);

            return [
                'success' => true,
                'message' => 'WhatsApp location sent successfully',
                'data' => [
                    'message_uuid' => $messageUuid,
                    'to' => $formattedTo
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Vonage WhatsApp location exception', [
                'to' => $toNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send WhatsApp location',
                'error' => $e->getMessage()
            ];
        }
    }
}

