# Vonage WhatsApp Integration Setup

This application supports both **Termii** and **Vonage** as WhatsApp providers. You can switch between them using environment variables.

## Prerequisites

1. **Vonage API Account**: Sign up at [https://dashboard.nexmo.com/](https://dashboard.nexmo.com/)
2. **WhatsApp Business Account (WABA)**: Set up via Vonage Dashboard
   - Navigate to **Messaging > External Accounts > WhatsApp**
   - Click **Continue with Meta** to set up your WhatsApp Business Account
   - Follow the onboarding process
3. **WhatsApp Business Number**: Link your WhatsApp-enabled phone number to Vonage

## Important Notes

⚠️ **WhatsApp requires the Messages API (not Legacy SMS API)**
- WhatsApp messaging uses Vonage's Messages API, which requires:
  - Application ID
  - Private Key (JWT authentication)
- The Legacy SMS API (API Key/Secret) cannot be used for WhatsApp

⚠️ **24-Hour Customer Care Window**
- Regular WhatsApp messages can only be sent within 24 hours after:
  - A user sends a message to your business, OR
  - A user replies to a template message you sent
- Outside this window, you must use **approved message templates**

⚠️ **Message Templates**
- Templates must be approved by WhatsApp before use
- Templates are created in WhatsApp Manager (via Meta)
- Contact your Vonage Account Manager to submit templates for approval
- Only templates in your own namespace will work

## Installation

The Vonage client package is already installed:
```bash
composer require vonage/client
```

## Configuration

### 1. Get Your Vonage Messages API Credentials

1. Go to [Vonage Dashboard](https://dashboard.nexmo.com/)
2. Navigate to **Applications** → **Create Application**
3. Create a new application for Messages API
4. Download the private key file or copy the private key
5. Note your **Application ID**

### 2. Set Up WhatsApp Business Account

1. In Vonage Dashboard, go to **Messaging > External Accounts > WhatsApp**
2. Click **Continue with Meta** to set up WhatsApp Business Account
3. Complete the Meta onboarding process
4. Link your WhatsApp Business Number to Vonage
5. Note your **WhatsApp Business Number** (e.g., +14155552671)

### 3. Configure Environment Variables

Add these to your `.env` file:

```env
# WhatsApp Provider Selection (options: 'termii' or 'vonage')
WHATSAPP_PROVIDER=vonage

# Vonage Messages API Configuration (Required for WhatsApp)
VONAGE_APPLICATION_ID=your_application_id_here
VONAGE_PRIVATE_KEY_PATH=/path/to/private.key  # Path to private key file
# OR use inline private key (with \n for newlines):
# VONAGE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n"

# WhatsApp Configuration
VONAGE_WHATSAPP_ENABLED=true
VONAGE_WHATSAPP_NUMBER=+14155552671  # Your WhatsApp Business Number

# Optional: Legacy SMS API (for SMS only, not WhatsApp)
VONAGE_API_KEY=your_api_key_here
VONAGE_API_SECRET=your_api_secret_here
VONAGE_BRAND_NAME=DoctorOnTap
VONAGE_ENABLED=true
```

**Important Notes:**
- Never commit your private key to version control
- Keep your `.env` file secure and never share it
- The WhatsApp number must be in E.164 format (e.g., +14155552671)
- Private key can be provided as a file path OR inline string

### 4. Create and Approve Message Templates

1. Go to [Meta Business Manager](https://business.facebook.com/)
2. Navigate to **WhatsApp Manager**
3. Create message templates for your use cases:
   - **Utility templates**: Transaction updates, post-purchase notifications
   - **Authentication templates**: OTP codes, verification
   - **Marketing templates**: Promotional messages (requires opt-in)
4. Submit templates for WhatsApp approval
5. Wait for approval (can take 24-48 hours)
6. Note the template names for use in your code

## How It Works

1. The application checks the `WHATSAPP_PROVIDER` config value
2. Based on the provider, it uses either `VonageService` or `TermiiService`
3. Both services have similar interfaces, so switching is seamless
4. All WhatsApp notifications throughout the app will use the selected provider

## Usage Examples

### Sending a Regular WhatsApp Message (Within 24-Hour Window)

```php
use App\Services\VonageService;

$vonage = new VonageService();
$result = $vonage->sendWhatsAppMessage(
    '+2347081114942',
    'Hello! This is a test message.'
);
```

### Sending a WhatsApp Template Message

```php
use App\Services\VonageService;

$vonage = new VonageService();
$result = $vonage->sendWhatsAppTemplate(
    '+2347081114942',
    'consultation_confirmation',  // Template name (must be approved)
    'en',  // Language code
    [
        [
            'type' => 'text',
            'text' => 'John Doe'  // Parameter 1
        ],
        [
            'type' => 'text',
            'text' => 'CON123456'  // Parameter 2
        ]
    ]
);
```

### Using the Notification Class

```php
use App\Notifications\ConsultationWhatsAppNotification;

$whatsapp = new ConsultationWhatsAppNotification();

// Send template (automatically uses configured provider)
$result = $whatsapp->sendConsultationConfirmationTemplate(
    [
        'mobile' => '+2347081114942',
        'consultation_reference' => 'CON123456',
        'first_name' => 'John Doe'
    ],
    'consultation_confirmation'  // Template name (Vonage) or ID (Termii)
);
```

## Testing

### Test WhatsApp Message

```bash
php artisan vonage:test-whatsapp 07081114942 --message="Hello! This is a test."
```

### Test WhatsApp Template

```bash
php artisan vonage:test-whatsapp 07081114942 --template=consultation_confirmation --language=en
```

## Webhooks

Vonage will send webhooks for:
- **Inbound messages**: When users send messages to your WhatsApp number
- **Delivery receipts**: Status updates for sent messages

### Configure Webhooks in Vonage Dashboard

1. Go to **Settings > Webhooks** in Vonage Dashboard
2. Set **Inbound Message URL**: `https://yourdomain.com/vonage/webhook/whatsapp/inbound`
3. Set **Status URL**: `https://yourdomain.com/vonage/webhook/whatsapp/status`

### Webhook Routes (Already Configured)

The application already has webhook routes set up:
- `POST /vonage/webhook/whatsapp/inbound` - Handle inbound messages
- `POST /vonage/webhook/whatsapp/status` - Handle delivery status

## Features

- ✅ **No Hardcoded Secrets**: All credentials come from environment variables
- ✅ **Easy Switching**: Change providers by updating one environment variable
- ✅ **Automatic Phone Formatting**: Handles Nigerian phone numbers (+234 format)
- ✅ **Comprehensive Logging**: All WhatsApp attempts are logged for debugging
- ✅ **Error Handling**: Graceful error handling with detailed error messages
- ✅ **Template Support**: Send approved WhatsApp message templates
- ✅ **24-Hour Window**: Send regular messages within customer care window

## Pricing

Vonage WhatsApp pricing consists of:
1. **Vonage platform fee** - per message
2. **WhatsApp fee** - per template message (varies by category):
   - **Utility templates**: Lower cost
   - **Authentication templates**: Medium cost
   - **Marketing templates**: Higher cost

See [Vonage Messages API Pricing](https://www.vonage.com/communications-apis/messages/pricing/) for details.

## Troubleshooting

### "Messages API credentials not configured"
- Ensure `VONAGE_APPLICATION_ID` is set
- Ensure `VONAGE_PRIVATE_KEY_PATH` or `VONAGE_PRIVATE_KEY` is set
- Verify the private key file exists and is readable

### "WhatsApp number not configured"
- Set `VONAGE_WHATSAPP_NUMBER` in `.env`
- Ensure the number is in E.164 format (e.g., +14155552671)

### "Template not found" or Error 1022
- Ensure the template name matches exactly (case-sensitive)
- Verify the template is in your own namespace
- Check that the template is approved in WhatsApp Manager

### "Message outside 24-hour window"
- Use an approved message template instead
- Or wait for the user to send you a message first

### Regular messages not working
- Regular messages only work within the 24-hour customer care window
- For initial messages, use approved templates
- Ensure the user has opted in to receive messages

## Further Reading

- [Vonage WhatsApp Documentation](https://developer.vonage.com/en/messages/concepts/whatsapp)
- [WhatsApp Template Management](https://developer.vonage.com/en/messages/guides/whatsapp-template-management)
- [WhatsApp Interactive Messages](https://developer.vonage.com/en/messages/guides/whatsapp-interactive-messages)
- [WhatsApp Pricing Updates](https://developers.facebook.com/docs/whatsapp/pricing/updates-to-pricing)

