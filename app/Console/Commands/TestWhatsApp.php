<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WhatsAppService;
use Vonage\Client;
use Vonage\Messages\Channel\WhatsApp\WhatsAppText;

class TestWhatsApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:test 
                            {--to= : Phone number to send test message to (E.164 format)}
                            {--type=text : Message type: text, template, image, video, audio, file, location}
                            {--message= : Message text (for text messages)}
                            {--template= : Template name (for template messages, format: namespace:template_name)}
                            {--locale=en_US : Template locale (for template messages)}
                            {--params= : Template parameters (comma-separated, for template messages)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Vonage WhatsApp integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Testing Vonage WhatsApp Integration');
        $this->newLine();

        // Check configuration
        $this->checkConfiguration();

        // Get phone number
        $toNumber = $this->option('to');
        if (!$toNumber) {
            $toNumber = $this->ask('Enter phone number to test (E.164 format, e.g., 447123456789)');
        }

        if (empty($toNumber)) {
            $this->error('❌ Phone number is required');
            return 1;
        }

        $type = $this->option('type');
        $whatsapp = new WhatsAppService();

        $this->info("📱 Sending {$type} message to: {$toNumber}");
        $this->newLine();

        try {
            switch ($type) {
                case 'text':
                    $this->testTextMessage($whatsapp, $toNumber);
                    break;
                case 'template':
                    $this->testTemplateMessage($whatsapp, $toNumber);
                    break;
                case 'image':
                    $this->testImageMessage($whatsapp, $toNumber);
                    break;
                case 'video':
                    $this->testVideoMessage($whatsapp, $toNumber);
                    break;
                case 'audio':
                    $this->testAudioMessage($whatsapp, $toNumber);
                    break;
                case 'file':
                    $this->testFileMessage($whatsapp, $toNumber);
                    break;
                case 'location':
                    $this->testLocationMessage($whatsapp, $toNumber);
                    break;
                default:
                    $this->error("❌ Unknown message type: {$type}");
                    return 1;
            }
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }

    protected function checkConfiguration()
    {
        $this->info('🔍 Checking configuration...');

        $apiKey = config('vonage.api_key');
        $apiSecret = config('vonage.api_secret');
        $whatsappNumber = config('services.vonage.whatsapp.from_phone_number');
        $applicationId = config('vonage.application_id');

        $checks = [
            'API Key' => !empty($apiKey),
            'API Secret' => !empty($apiSecret),
            'WhatsApp Number' => !empty($whatsappNumber),
            'Application ID' => !empty($applicationId),
        ];

        foreach ($checks as $check => $status) {
            if ($status) {
                $this->line("  ✅ {$check}: Configured");
            } else {
                $this->line("  ❌ {$check}: Not configured");
            }
        }

        $this->newLine();

        if (!$whatsappNumber) {
            $this->warn('⚠️  WhatsApp number not configured. Set WHATSAPP_PHONE_NUMBER in .env');
        }
    }

    protected function testTextMessage(WhatsAppService $whatsapp, string $toNumber)
    {
        $message = $this->option('message') ?: 'Hello! This is a test message from DoctorOnTap WhatsApp integration.';

        $this->info("📝 Message: {$message}");
        $this->newLine();

        $result = $whatsapp->sendText($toNumber, $message);

        if ($result['success']) {
            $this->info('✅ Message sent successfully!');
            $this->line("   Message UUID: " . ($result['data']['message_uuid'] ?? 'N/A'));
            $this->line("   To: " . ($result['data']['to'] ?? $toNumber));
        } else {
            $this->error('❌ Failed to send message');
            $this->error("   Error: " . ($result['message'] ?? 'Unknown error'));
            $this->error("   Details: " . ($result['error'] ?? 'No details'));
        }
    }

    protected function testTemplateMessage(WhatsAppService $whatsapp, string $toNumber)
    {
        $templateName = $this->option('template');
        if (!$templateName) {
            $templateName = $this->ask('Enter template name (format: namespace:template_name)', 'whatsapp:hugotemplate');
        }

        $locale = $this->option('locale') ?: 'en_US';
        $paramsInput = $this->option('params');
        $params = $paramsInput ? explode(',', $paramsInput) : ['Test User'];

        $this->info("📋 Template: {$templateName}");
        $this->info("🌐 Locale: {$locale}");
        $this->info("📝 Parameters: " . implode(', ', $params));
        $this->newLine();

        $result = $whatsapp->sendTemplate($toNumber, $templateName, $locale, $params);

        if ($result['success']) {
            $this->info('✅ Template message sent successfully!');
            $this->line("   Message UUID: " . ($result['data']['message_uuid'] ?? 'N/A'));
            $this->line("   Template: " . ($result['data']['template_name'] ?? $templateName));
        } else {
            $this->error('❌ Failed to send template message');
            $this->error("   Error: " . ($result['message'] ?? 'Unknown error'));
            $this->error("   Details: " . ($result['error'] ?? 'No details'));
        }
    }

    protected function testImageMessage(WhatsAppService $whatsapp, string $toNumber)
    {
        $imageUrl = $this->ask('Enter image URL', 'https://via.placeholder.com/300');
        $caption = $this->ask('Enter caption (optional)', 'Test image from DoctorOnTap');

        $this->info("🖼️  Image URL: {$imageUrl}");
        $this->info("📝 Caption: {$caption}");
        $this->newLine();

        $result = $whatsapp->sendImage($toNumber, $imageUrl, $caption);

        if ($result['success']) {
            $this->info('✅ Image sent successfully!');
            $this->line("   Message UUID: " . ($result['data']['message_uuid'] ?? 'N/A'));
        } else {
            $this->error('❌ Failed to send image');
            $this->error("   Error: " . ($result['message'] ?? 'Unknown error'));
        }
    }

    protected function testVideoMessage(WhatsAppService $whatsapp, string $toNumber)
    {
        $videoUrl = $this->ask('Enter video URL', 'https://example.com/video.mp4');
        $caption = $this->ask('Enter caption (optional)');

        $result = $whatsapp->sendVideo($toNumber, $videoUrl, $caption);

        if ($result['success']) {
            $this->info('✅ Video sent successfully!');
        } else {
            $this->error('❌ Failed to send video');
            $this->error("   Error: " . ($result['message'] ?? 'Unknown error'));
        }
    }

    protected function testAudioMessage(WhatsAppService $whatsapp, string $toNumber)
    {
        $audioUrl = $this->ask('Enter audio URL', 'https://example.com/audio.mp3');

        $result = $whatsapp->sendAudio($toNumber, $audioUrl);

        if ($result['success']) {
            $this->info('✅ Audio sent successfully!');
        } else {
            $this->error('❌ Failed to send audio');
            $this->error("   Error: " . ($result['message'] ?? 'Unknown error'));
        }
    }

    protected function testFileMessage(WhatsAppService $whatsapp, string $toNumber)
    {
        $fileUrl = $this->ask('Enter file URL', 'https://example.com/document.pdf');
        $caption = $this->ask('Enter caption (optional)', 'Test document');
        $fileName = $this->ask('Enter file name (optional)', 'document.pdf');

        $result = $whatsapp->sendFile($toNumber, $fileUrl, $caption, $fileName);

        if ($result['success']) {
            $this->info('✅ File sent successfully!');
        } else {
            $this->error('❌ Failed to send file');
            $this->error("   Error: " . ($result['message'] ?? 'Unknown error'));
        }
    }

    protected function testLocationMessage(WhatsAppService $whatsapp, string $toNumber)
    {
        $longitude = (float) $this->ask('Enter longitude', '-40.34764');
        $latitude = (float) $this->ask('Enter latitude', '-74.18875');
        $name = $this->ask('Enter location name (optional)', 'DoctorOnTap Office');
        $address = $this->ask('Enter address (optional)', '123 Main Street, City, Country');

        $result = $whatsapp->sendLocation($toNumber, $longitude, $latitude, $name, $address);

        if ($result['success']) {
            $this->info('✅ Location sent successfully!');
        } else {
            $this->error('❌ Failed to send location');
            $this->error("   Error: " . ($result['message'] ?? 'Unknown error'));
        }
    }
}
