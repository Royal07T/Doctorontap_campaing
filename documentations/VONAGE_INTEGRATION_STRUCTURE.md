# Vonage Integration Structure & Organization

This document provides an overview of the Vonage integration architecture in the DoctorOnTap application.

## 📁 File Structure

### Services (`app/Services/`)

All Vonage services are organized in the `app/Services/` directory:

1. **`VonageService.php`** - Main service for SMS, MMS, and WhatsApp messaging
   - Supports both Legacy SMS API and Messages API
   - Handles SMS, MMS, WhatsApp text, images, videos, audio, files
   - Methods: `sendSMS()`, `sendBulkSMS()`, `sendWhatsAppMessage()`, `sendWhatsAppTemplate()`, etc.

2. **`VonageVoiceService.php`** - Voice API service
   - Handles outbound calls, text-to-speech, call recording
   - Methods: `makeCall()`, `sendTextToSpeech()`, `startRecording()`, etc.

3. **`VonageVideoService.php`** - Video API service
   - Handles video consultations (OpenTok/Vonage Video)
   - Methods: `createSession()`, `generateToken()`, `startArchive()`, etc.

4. **`VonageConversationService.php`** - Conversations API service
   - Handles in-app chat consultations
   - Methods: `createConversation()`, `generateToken()`, `addMember()`, etc.

### Controllers (`app/Http/Controllers/`)

1. **`VonageWebhookController.php`** - Handles all messaging webhooks
   - `handleInbound()` - Legacy SMS inbound
   - `handleStatus()` - Legacy SMS status
   - `handleInboundMessage()` - Messages API inbound (all channels)
   - `handleMessageStatus()` - Messages API status (all channels)
   - `handleWhatsAppInbound()` - WhatsApp inbound
   - `handleWhatsAppStatus()` - WhatsApp status

2. **`VonageVoiceWebhookController.php`** - Handles voice webhooks
   - `handleAnswer()` - Call answer webhook
   - `handleEvent()` - Call event webhook
   - `handleRecording()` - Recording webhook

3. **`VonageSessionWebhookController.php`** - Handles session webhooks
   - `handleSessionEvent()` - Video/voice session events

### Routes (`routes/web.php`)

#### Messages API Standard Routes (Recommended)
```php
POST /webhooks/inbound-message      → VonageWebhookController@handleInboundMessage
POST /webhooks/message-status       → VonageWebhookController@handleMessageStatus
```

#### Legacy Routes (Backward Compatible)
```php
POST /vonage/webhook/inbound        → VonageWebhookController@handleInbound
POST /vonage/webhook/status         → VonageWebhookController@handleStatus
POST /vonage/webhook/whatsapp/inbound → VonageWebhookController@handleWhatsAppInbound
POST /vonage/webhook/whatsapp/status  → VonageWebhookController@handleWhatsAppStatus
```

#### Voice Routes
```php
POST /vonage/webhook/voice/answer   → VonageVoiceWebhookController@handleAnswer
POST /vonage/webhook/voice/event    → VonageVoiceWebhookController@handleEvent
POST /vonage/webhook/voice/recording → VonageVoiceWebhookController@handleRecording
```

#### Session Routes
```php
POST /vonage/webhook/session        → VonageSessionWebhookController@handleSessionEvent
```

### Configuration (`config/`)

1. **`config/services.php`** - Main configuration (Recommended)
   - All Vonage settings under `services.vonage.*`
   - Supports both Legacy and Messages API
   - Includes: SMS, WhatsApp, Voice, Video, MMS, Conversations

2. **`config/vonage.php`** - Legacy configuration (from vonage-laravel package)
   - Maintained for backward compatibility
   - Services use `services.vonage.*` with fallback to `vonage.*`

## 🔧 Configuration Access Pattern

All services follow a consistent pattern:

```php
// Primary: services.vonage.*
// Fallback: vonage.* (for backward compatibility)
$value = config('services.vonage.key') ?: config('vonage.key');
```

This ensures:
- ✅ New code uses standardized `services.vonage.*`
- ✅ Backward compatibility with existing `vonage.*` config
- ✅ Smooth migration path

## 📚 Documentation Structure

### Main Documentation (`documentations/`)

1. **`VONAGE_MESSAGES_API_GETTING_STARTED.md`** - Complete Messages API setup guide
2. **`VONAGE_MESSAGES_API_QUICK_START.md`** - Quick 5-minute setup guide
3. **`VONAGE_MESSAGES_API_CLI_COMMANDS.md`** - CLI commands reference
4. **`VONAGE_MESSAGES_API_SETUP.md`** - Original Messages API setup
5. **`VONAGE_SETUP.md`** - General Vonage setup
6. **`VONAGE_WHATSAPP_SETUP.md`** - WhatsApp-specific setup
7. **`VONAGE_VOICE_SETUP.md`** - Voice API setup
8. **`VONAGE_WEBHOOKS_SETUP.md`** - Webhooks setup guide
9. **`VONAGE_INTEGRATION_STRUCTURE.md`** - This file

### Root Documentation (Legacy)

Some documentation files exist in the root directory for historical reference:
- `VONAGE_VIDEO_JWT_SETUP_COMPLETE.md`
- `VONAGE_WHATSAPP_OFFICIAL_GUIDE.md`
- `VONAGE_SERVICES_TEST_RESULTS.md`
- etc.

## 🎯 Service Responsibilities

### VonageService
- ✅ SMS (Legacy & Messages API)
- ✅ MMS (Images, Videos, Audio)
- ✅ WhatsApp (Text, Media, Templates)
- ✅ Bulk messaging
- ✅ Phone number formatting

### VonageVoiceService
- ✅ Outbound calls
- ✅ Text-to-speech
- ✅ Call recording
- ✅ Conference calls
- ✅ NCCO (Nexmo Call Control Object)

### VonageVideoService
- ✅ Video session creation
- ✅ Token generation
- ✅ Session archiving
- ✅ Participant management

### VonageConversationService
- ✅ Chat conversations
- ✅ Member management
- ✅ Token generation

## 🔐 Security

1. **Credentials Storage**
   - All credentials from `.env` (never hardcoded)
   - Private keys stored outside web root
   - File permissions: `600` (readable by owner only)

2. **Webhook Security**
   - Signature verification implemented
   - CSRF protection disabled for webhook routes
   - All webhook routes are public (required by Vonage)

3. **Authentication Methods**
   - JWT (Application ID + Private Key) - Recommended
   - Basic (API Key + Secret) - Legacy support

## 📊 Supported Channels

The Messages API integration supports:

- ✅ **SMS** - Text messages
- ✅ **MMS** - Media messages (images, videos, audio)
- ✅ **WhatsApp** - WhatsApp Business messages
- ✅ **Facebook Messenger** - Facebook messages
- ✅ **Viber** - Viber messages
- ✅ **RCS** - Rich Communication Services

## 🧪 Testing

Test all services:
```bash
php artisan vonage:test-all
```

Test specific service:
```bash
php artisan vonage:test-all --service=sms
php artisan vonage:test-all --service=whatsapp
php artisan vonage:test-all --service=voice
php artisan vonage:test-all --service=video
```

## 🔄 Migration Notes

### From Legacy to Messages API

1. Create a Vonage Application (CLI or Dashboard)
2. Get Application ID and Private Key
3. Update `.env`:
   ```env
   VONAGE_API_METHOD=messages
   VONAGE_APPLICATION_ID=your_app_id
   VONAGE_PRIVATE_KEY_PATH=/path/to/private.key
   ```
4. Update webhook URLs in Vonage Dashboard
5. Test: `php artisan vonage:test-all --service=sms`

### Configuration Migration

- Old: `config('vonage.api_key')`
- New: `config('services.vonage.api_key')`
- Pattern: Services use new with fallback to old

## ✅ Best Practices

1. **Use Messages API** for new integrations
2. **Store private keys** outside web root
3. **Use environment variables** for all credentials
4. **Verify webhook signatures** in production
5. **Log all API calls** for debugging
6. **Handle errors gracefully** with proper error messages
7. **Test webhooks** with ngrok in development
8. **Use standardized routes** (`/webhooks/*`) for Messages API

## 📞 Support Resources

- [Vonage Documentation](https://developer.vonage.com/)
- [Messages API Docs](https://developer.vonage.com/en/messages/overview)
- [Vonage Dashboard](https://dashboard.nexmo.com/)
- [Vonage Support](https://help.nexmo.com/)

## 🔍 Code Quality

- ✅ Consistent naming conventions
- ✅ Comprehensive error handling
- ✅ Detailed logging
- ✅ Type hints and return types
- ✅ PHPDoc comments
- ✅ Backward compatibility maintained

