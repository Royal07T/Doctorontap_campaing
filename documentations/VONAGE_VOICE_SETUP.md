# Vonage Voice API Integration Setup

This application supports **Vonage Voice API** for making programmatic voice calls, text-to-speech, call recording, and conference calls.

## Prerequisites

1. **Vonage API Account**: Sign up at [https://dashboard.nexmo.com/](https://dashboard.nexmo.com/)
2. **Vonage Phone Number**: Purchase a phone number from Vonage for making outbound calls
3. **Webhook URL**: Your application must be accessible via HTTPS for webhooks (or use ngrok for local development)

## Features

- ✅ **Outbound Calls**: Make calls programmatically
- ✅ **Text-to-Speech**: Convert text to speech in 40+ languages
- ✅ **Call Recording**: Record calls automatically
- ✅ **Conference Calls**: Create multi-party conference calls
- ✅ **Custom NCCO**: Full control over call flow with NCCO (Nexmo Call Control Object)
- ✅ **Webhook Support**: Handle call events and recordings

## Installation

The Vonage client package is already installed:
```bash
composer require vonage/client
```

## Configuration

### 1. Get Your Vonage Credentials

1. Go to [Vonage Dashboard](https://dashboard.nexmo.com/)
2. Navigate to **Settings** → **API Settings**
3. Copy your **API Key** and **API Secret**
4. Or create an **Application** for JWT authentication (recommended)

### 2. Purchase a Phone Number

1. In Vonage Dashboard, go to **Numbers** → **Buy Numbers**
2. Select your country and purchase a phone number
3. Note the phone number (e.g., +14155552671)

### 3. Configure Environment Variables

Add these to your `.env` file:

```env
# Voice API Configuration
VONAGE_VOICE_ENABLED=true
VONAGE_VOICE_NUMBER=+14155552671  # Your Vonage phone number

# Webhook URL (for production, use your domain)
VONAGE_VOICE_WEBHOOK_URL=https://yourdomain.com/vonage/webhook

# Authentication (choose one method)

# Option 1: Basic Authentication (API Key/Secret)
VONAGE_API_KEY=your_api_key_here
VONAGE_API_SECRET=your_api_secret_here

# Option 2: JWT Authentication (Application ID + Private Key) - Recommended
VONAGE_APPLICATION_ID=your_application_id_here
VONAGE_PRIVATE_KEY_PATH=/path/to/private.key
# OR inline:
# VONAGE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----"
```

### 4. Configure Webhooks in Vonage Dashboard

1. Go to **Settings** → **Webhooks** in Vonage Dashboard
2. Set **Answer URL**: `https://yourdomain.com/vonage/webhook/voice/answer`
3. Set **Event URL**: `https://yourdomain.com/vonage/webhook/voice/event`
4. Set **Recording URL**: `https://yourdomain.com/vonage/webhook/voice/recording`

**For Local Development:**
- Use [ngrok](https://ngrok.com/) to expose your local server:
  ```bash
  ngrok http 8000
  ```
- Use the ngrok URL in webhook configuration (e.g., `https://abc123.ngrok.io/vonage/webhook/voice/answer`)

## Usage Examples

### Making a Simple Call with Text-to-Speech

```php
use App\Services\VonageVoiceService;

$voiceService = new VonageVoiceService();

$result = $voiceService->makeCall(
    '+2347081114942',
    'Hello! This is a call from DoctorOnTap. Your consultation is ready.',
    [
        'language' => 'en-US',
        'voice' => 'female',
        'style' => 0
    ]
);

if ($result['success']) {
    echo "Call UUID: " . $result['data']['call_uuid'];
}
```

### Making a Call with Recording

```php
$result = $voiceService->makeCall(
    '+2347081114942',
    'This call will be recorded for quality assurance.',
    [
        'record' => true,
        'language' => 'en-US'
    ]
);
```

### Creating a Conference Call

```php
$participants = [
    '+2347081114942',
    '+2347081114943',
    '+2347081114944'
];

$result = $voiceService->createConference(
    $participants,
    'Doctor Consultation Conference'
);
```

### Using Custom NCCO

```php
use App\Services\VonageVoiceService;
use Vonage\Voice\NCCO\NCCO;
use Vonage\Voice\NCCO\Action\Talk;
use Vonage\Voice\NCCO\Action\Input;

$ncco = new NCCO();

// First, ask a question
$talk = new Talk('Please press 1 for English, or 2 for Spanish.');
$ncco->addAction($talk);

// Get user input
$input = new Input();
$input->setMaxDigits(1);
$input->setTimeOut(5);
$ncco->addAction($input);

// Make the call with custom NCCO
$result = $voiceService->makeCallWithNCCO('+2347081114942', $ncco);
```

### Getting Call Information

```php
$callUuid = 'abc123-def456-ghi789';
$result = $voiceService->getCall($callUuid);

if ($result['success']) {
    $call = $result['data'];
    echo "Status: " . $call['status'];
    echo "Duration: " . $call['duration'] . " seconds";
    echo "Price: $" . $call['price'];
}
```

### Hanging Up a Call

```php
$callUuid = 'abc123-def456-ghi789';
$result = $voiceService->hangupCall($callUuid);
```

## Testing

### Test a Voice Call

```bash
php artisan vonage:test-voice 07081114942 --message="Hello! This is a test call." --language=en-US --voice=female
```

### Test with Different Options

```bash
# Test with custom message
php artisan vonage:test-voice 07081114942 --message="Your appointment is confirmed for tomorrow at 2 PM."

# Test with different language
php artisan vonage:test-voice 07081114942 --message="Hola! Esta es una llamada de prueba." --language=es-ES

# Test with male voice
php artisan vonage:test-voice 07081114942 --message="Hello!" --voice=male
```

## NCCO (Nexmo Call Control Object)

NCCO is a JSON structure that defines what happens during a call. Common actions include:

### Talk Action (Text-to-Speech)
```php
use Vonage\Voice\NCCO\NCCO;
use Vonage\Voice\NCCO\Action\Talk;

$ncco = new NCCO();
$talk = new Talk('Hello, this is a test call.');
$talk->setLanguage('en-US');
$talk->setVoiceName('female');
$talk->setStyle(0);
$ncco->addAction($talk);
```

### Stream Action (Play Audio File)
```php
use Vonage\Voice\NCCO\Action\Stream;

$stream = new Stream('https://example.com/audio.mp3');
$stream->setLevel(1.0); // Volume (0-1)
$ncco->addAction($stream);
```

### Record Action
```php
use Vonage\Voice\NCCO\Action\Record;

$record = new Record();
$record->setEventUrl(['https://yourdomain.com/vonage/webhook/voice/recording']);
$record->setSplit('conversation'); // Record both sides
$ncco->addAction($record);
```

### Input Action (DTMF Input)
```php
use Vonage\Voice\NCCO\Action\Input;

$input = new Input();
$input->setMaxDigits(4);
$input->setTimeOut(10);
$input->setSubmitOnHash(true);
$ncco->addAction($input);
```

### Conversation Action (Conference)
```php
use Vonage\Voice\NCCO\Action\Conversation;

$conversation = new Conversation('conference-room-123');
$conversation->setStartOnEnter(true);
$conversation->setEndOnExit(false);
$ncco->addAction($conversation);
```

## Webhooks

The application handles three types of voice webhooks:

### 1. Answer Webhook (`/vonage/webhook/voice/answer`)
- Called when a call is answered
- Must return NCCO instructions
- Used to control what happens when the call connects

### 2. Event Webhook (`/vonage/webhook/voice/event`)
- Called for call status updates (ringing, answered, completed, etc.)
- Used to track call progress and update your database

### 3. Recording Webhook (`/vonage/webhook/voice/recording`)
- Called when a recording is complete
- Provides URL to download the recording
- Used to store recording information

## Text-to-Speech Options

### Languages
Supported languages include: `en-US`, `en-GB`, `es-ES`, `fr-FR`, `de-DE`, `it-IT`, `pt-BR`, `ja-JP`, `ko-KR`, `zh-CN`, and many more.

### Voices
- **Female voices**: `female`, `Amy`, `Emma`, `Joanna`, `Kendra`, `Kimberly`, `Salli`
- **Male voices**: `male`, `Brian`, `Geraint`, `Russell`, `Matthew`

### Styles (for some languages)
- `0` - Neutral
- `1` - Formal
- `2` - Informal

## Call Flow

1. **Application initiates call** → `makeCall()` or `makeCallWithNCCO()`
2. **Vonage dials recipient** → Call is placed
3. **Recipient answers** → Answer webhook is called
4. **NCCO is returned** → Defines what happens (talk, stream, record, etc.)
5. **Call progresses** → Event webhooks are sent for status updates
6. **Call ends** → Final event webhook with duration and cost

## Database Tables

The webhook handlers automatically log to these tables (create migrations if needed):

- `voice_call_logs` - Call events and status
- `voice_recordings` - Recording information

## Pricing

Vonage Voice API pricing:
- **Per-minute rates** vary by country
- **Text-to-speech** included in call cost
- **Recording** may have additional storage costs
- Check [Vonage Pricing](https://www.vonage.com/communications-apis/pricing/) for details

## Troubleshooting

### "Voice number not configured"
- Set `VONAGE_VOICE_NUMBER` in `.env`
- Ensure the number is in E.164 format (e.g., +14155552671)

### "Authentication credentials not configured"
- Set either Basic (API Key/Secret) or JWT (Application ID/Private Key) credentials
- Verify credentials are correct in Vonage Dashboard

### "Call not connecting"
- Verify recipient number is in correct format
- Check Vonage account balance
- Ensure webhooks are configured correctly
- Check Laravel logs for errors

### "Webhook not receiving events"
- Ensure webhook URL is publicly accessible (use ngrok for local dev)
- Verify webhook URLs in Vonage Dashboard
- Check that routes are not protected by CSRF middleware

### "NCCO not working"
- Verify NCCO structure is valid JSON
- Check that actions are in correct order
- Ensure webhook returns valid NCCO format

## Further Reading

- [Vonage Voice API Documentation](https://developer.vonage.com/en/voice/voice-api/overview)
- [NCCO Reference](https://developer.vonage.com/en/voice/voice-api/ncco-reference)
- [Voice API Reference](https://developer.vonage.com/en/api/voice)
- [Getting Started with Voice API](https://developer.vonage.com/en/voice/voice-api/getting-started)

