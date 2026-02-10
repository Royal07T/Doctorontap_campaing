# Vonage WhatsApp Integration - Official Documentation Guide ✅

Based on: https://developer.vonage.com/en/blog/send-whatsapp-messages-in-laravel-with-vonages-native-sdk

---

## Overview

This implementation follows the official Vonage WhatsApp documentation for Laravel. It supports both **Message Templates (MTM)** and **free-form messages** within the 24-hour customer care window.

---

## Key Concepts

### **1. Message Templates (MTM) - Required to Start Conversations**

- **Purpose:** Start a new conversation window with a customer
- **Format:** Template name must be in format `namespace:template_name` (e.g., `whatsapp:hugotemplate`)
- **Requirements:** Templates must be approved by WhatsApp before use
- **Usage:** Use `sendTemplate()` method

### **2. 24-Hour Customer Care Window**

After sending a template message, you have a **24-hour window** to send free-form messages:
- ✅ Text messages (`sendText()`)
- ✅ Images (`sendImage()`)
- ✅ Videos (`sendVideo()`)
- ✅ Audio (`sendAudio()`)
- ✅ Files/Documents (`sendFile()`)

**Important:** After 24 hours, you must send another template message to restart the conversation window.

---

## Configuration

### **Your Credentials (Already Configured)**

```env
# Vonage API Credentials
VONAGE_KEY=YOUR-API-KEY-HERE
VONAGE_SECRET=YOUR-API-SECRET-HERE

# Application ID
VONAGE_APPLICATION_ID=250782187688

# WhatsApp Number
WHATSAPP_PHONE_NUMBER=405228299348572
VONAGE_WHATSAPP_NUMBER=405228299348572

# Enable WhatsApp (Production - Non-Sandbox)
VONAGE_WHATSAPP_ENABLED=true
VONAGE_WHATSAPP_SANDBOX=false

# Private Key (required for Messages API)
VONAGE_PRIVATE_KEY_PATH=/path/to/private.key
# OR
VONAGE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----"
```

---

## Usage Examples

### **1. Send Template Message (Start Conversation)**

Template messages are required to initiate a conversation:

```php
use App\Services\WhatsAppService;

$whatsapp = new WhatsAppService();

// Template name format: "namespace:template_name"
$result = $whatsapp->sendTemplate(
    toNumber: '447123456789',
    templateName: 'whatsapp:hugotemplate', // Must be approved by WhatsApp
    templateLanguage: 'en_US', // Language code
    templateParameters: ['John', 'DoctorOnTap'] // Parameters for template body
);

if ($result['success']) {
    echo "Template sent! UUID: " . $result['data']['message_uuid'];
    // 24-hour window now open for free-form messages
}
```

**Template Name Format:**
- ✅ Correct: `whatsapp:hugotemplate`, `myapp:welcome_message`
- ❌ Wrong: `hugotemplate`, `welcome_message`

**Language Codes:**
- `en_US` - English (US)
- `en` - English
- `es` - Spanish
- `fr` - French
- etc.

### **2. Send Text Message (Within 24-Hour Window)**

After sending a template, you can send free-form text messages:

```php
use App\Services\WhatsAppService;

$whatsapp = new WhatsAppService();

$result = $whatsapp->sendText(
    toNumber: '447123456789',
    message: 'Hello! This is a free-form message within the 24-hour window.'
);

if ($result['success']) {
    echo "Message sent! UUID: " . $result['data']['message_uuid'];
}
```

### **3. Send Image Message**

```php
$result = $whatsapp->sendImage(
    toNumber: '447123456789',
    imageUrl: 'https://example.com/image.jpg',
    caption: 'Check out this image!' // Optional
);
```

### **4. Send Video Message**

```php
$result = $whatsapp->sendVideo(
    toNumber: '447123456789',
    videoUrl: 'https://example.com/video.mp4',
    caption: 'Watch this video!' // Optional
);
```

### **5. Send Audio Message**

```php
$result = $whatsapp->sendAudio(
    toNumber: '447123456789',
    audioUrl: 'https://example.com/audio.mp3'
);
```

### **6. Send File/Document**

```php
$result = $whatsapp->sendFile(
    toNumber: '447123456789',
    fileUrl: 'https://example.com/document.pdf',
    caption: 'Please review this document', // Optional
    fileName: 'document.pdf' // Optional
);
```

---

## Using Vonage Client Directly

You can also use the Vonage Client directly as shown in the official documentation:

### **Text Message**

```php
use Vonage\Client;
use Vonage\Messages\Channel\WhatsApp\WhatsAppText;

$whatsAppMessage = new WhatsAppText(
    to: '447123456789',
    from: config('services.vonage.whatsapp.from_phone_number'),
    text: 'Hello, this is a test message.',
);

$response = app(Client::class)
    ->messages()
    ->send($whatsAppMessage);

$messageUuid = $response->getMessageUuid();
```

### **Template Message**

```php
use Vonage\Client;
use Vonage\Messages\Channel\WhatsApp\WhatsAppTemplate;
use Vonage\Messages\MessageObjects\TemplateObject;

$toNumber = '447123456789';
$locale = 'en_US';
$templateName = 'abc_123:sample_issue_resolution';
$templateParams = ['John'];

$whatsAppMessage = new WhatsAppTemplate(
    to: $toNumber,
    from: config('services.vonage.whatsapp.from_phone_number'),
    templateObject: new TemplateObject(
        name: $templateName,
        parameters: $templateParams,
    ),
    locale: $locale,
);

app(Client::class)
    ->messages()
    ->send($whatsAppMessage);
```

**Key Points:**
- `TemplateObject` takes `name` and `parameters` (simple array)
- `WhatsAppTemplate` takes `templateObject` and `locale` (separate parameters)
- Parameters are mapped to `{{1}}`, `{{2}}`, etc. in the template

---

## Phone Number Format

All phone numbers must be in **E.164 format**:
- ✅ Correct: `+447123456789` or `447123456789`
- ❌ Wrong: `07123456789`, `+44 7123 456789`, `(44) 7123-456789`

The service automatically formats numbers, but providing E.164 format is recommended.

---

## 24-Hour Window Management

### **Best Practices**

1. **Track Conversation Windows**
   - Store the last message timestamp for each recipient
   - Check if 24 hours have passed before sending free-form messages
   - Send a template message if the window has expired

2. **Example Implementation**

```php
use App\Services\WhatsAppService;
use Carbon\Carbon;

function sendWhatsAppMessage($toNumber, $message) {
    $whatsapp = new WhatsAppService();
    
    // Check if 24-hour window is still open
    $lastMessage = getLastMessageTime($toNumber);
    
    if (!$lastMessage || Carbon::parse($lastMessage)->addHours(24)->isPast()) {
        // Window expired, send template first
        $whatsapp->sendTemplate(
            $toNumber,
            'whatsapp:notification_template',
            'en_US',
            ['Customer Name']
        );
    }
    
    // Now send free-form message
    return $whatsapp->sendText($toNumber, $message);
}
```

---

## Error Handling

The service returns structured responses:

```php
$result = $whatsapp->sendText('447123456789', 'Hello');

if ($result['success']) {
    // Success
    $messageUuid = $result['data']['message_uuid'];
} else {
    // Error
    $errorMessage = $result['message'];
    $errorCode = $result['error_code'] ?? null;
    $error = $result['error'] ?? null;
}
```

**Common Errors:**
- `configuration_error` - WhatsApp number not configured
- `invalid_phone_number` - Phone number format incorrect
- `template_not_approved` - Template not approved by WhatsApp
- `window_expired` - 24-hour window expired (need to send template)

---

## Template Approval Process

Before using a template:

1. **Create Template in WhatsApp Business Manager**
   - Go to your WhatsApp Business Account
   - Create a message template
   - Submit for approval

2. **Wait for Approval**
   - Usually takes 24-48 hours
   - Templates must follow WhatsApp's guidelines

3. **Use Approved Template**
   - Format: `namespace:template_name`
   - Example: `whatsapp:hugotemplate`

---

## Files Structure

### **Service Class**
- `app/Services/WhatsAppService.php` - Main WhatsApp service (updated to match official docs)

### **Configuration**
- `config/services.php` - WhatsApp configuration
- `config/vonage.php` - Vonage API configuration

### **Documentation**
- `VONAGE_WHATSAPP_SETUP.md` - Setup guide
- `VONAGE_WHATSAPP_CONFIG.md` - Configuration details
- `VONAGE_WHATSAPP_OFFICIAL_GUIDE.md` - This file (official patterns)

---

## Testing

### **Test Template Message**

```php
use App\Services\WhatsAppService;

$whatsapp = new WhatsAppService();

$result = $whatsapp->sendTemplate(
    '447123456789', // Replace with test number
    'whatsapp:hugotemplate', // Replace with your approved template
    'en_US',
    ['Test Parameter']
);

dd($result);
```

### **Test Text Message (After Template)**

```php
$result = $whatsapp->sendText(
    '447123456789',
    'This is a test message within the 24-hour window'
);

dd($result);
```

---

## Summary

✅ **Implementation Complete**
- Follows official Vonage documentation patterns
- Uses `TemplateObject` for template messages
- Supports all message types (text, image, video, audio, file)
- Proper error handling and logging
- E.164 phone number formatting
- Production-ready (non-sandbox)

**Status:** Ready for Production  
**Date:** February 8, 2026  
**Based on:** Official Vonage Laravel SDK Documentation

