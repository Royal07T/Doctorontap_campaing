# Vonage WhatsApp Integration (Production - Non-Sandbox) ✅

## Overview
This guide explains how to use Vonage WhatsApp in production mode (non-sandbox) for sending WhatsApp messages.

---

## Prerequisites

✅ **Package Already Installed**
- `vonage/vonage-laravel` package is already installed
- Vonage config file exists at `config/vonage.php`

---

## Configuration Steps

### **Step 1: Configure .env File**

Add the following to your `.env` file:

```env
# Vonage API Credentials
VONAGE_KEY=YOUR-API-KEY-HERE
VONAGE_SECRET=YOUR-VONAGE-SECRET-HERE

# WhatsApp Phone Number (Your WhatsApp Business Number from Vonage)
WHATSAPP_PHONE_NUMBER=YOUR-WHATSAPP-PHONE-NUMBER

# Disable Sandbox Mode (for production)
VONAGE_WHATSAPP_SANDBOX=false
VONAGE_WHATSAPP_ENABLED=true
```

**Important Notes:**
- `VONAGE_KEY` and `VONAGE_SECRET` are your Vonage API credentials
- `WHATSAPP_PHONE_NUMBER` is your WhatsApp Business Number (e.g., `447123456789` or `+447123456789`)
- Set `VONAGE_WHATSAPP_SANDBOX=false` to use production (non-sandbox) mode

---

### **Step 2: Config File Already Updated**

The `config/services.php` file has been updated with:

```php
'vonage' => [
    // ... existing config ...
    'whatsapp' => [
        'from_phone_number' => env('WHATSAPP_PHONE_NUMBER') ?: env('VONAGE_WHATSAPP_NUMBER'),
    ],
],
```

You can access it with: `config('services.vonage.whatsapp.from_phone_number')`

---

## Usage Examples

### **Method 1: Using WhatsAppService (Recommended)**

A new `WhatsAppService` class has been created for easy WhatsApp messaging:

```php
use App\Services\WhatsAppService;

$whatsappService = new WhatsAppService();

// Send a text message
$result = $whatsappService->sendText(
    toNumber: '447123456789', // Recipient phone number
    message: 'Hello, this is a test message.'
);

if ($result['success']) {
    echo "Message sent! UUID: " . $result['data']['message_uuid'];
} else {
    echo "Error: " . $result['message'];
}
```

### **Method 2: Using Vonage Client Directly**

```php
use Vonage\Client;
use Vonage\Messages\Channel\WhatsApp\WhatsAppText;

$toNumber = '447123456789';
$message = 'Hello, this is a test message.';

$whatsAppMessage = new WhatsAppText(
    to: $toNumber,
    from: config('services.vonage.whatsapp.from_phone_number'),
    text: $message,
);

$response = app(Client::class)
    ->messages()
    ->send($whatsAppMessage);

$messageUuid = $response->getMessageUuid();
```

### **Method 3: Using Existing VonageService**

The existing `VonageService` has been updated to use the new config:

```php
use App\Services\VonageService;

$vonageService = new VonageService();

$result = $vonageService->sendWhatsApp(
    to: '447123456789',
    message: 'Hello from DoctorOnTap!'
);
```

---

## Sending Template Messages (MTM)

Template messages are required to start a new conversation window:

```php
use App\Services\WhatsAppService;

$whatsappService = new WhatsAppService();

$result = $whatsappService->sendTemplate(
    toNumber: '447123456789',
    templateName: 'welcome_message', // Must be approved by WhatsApp
    templateLanguage: 'en',
    templateParameters: ['John', 'DoctorOnTap'] // Parameters for template
);
```

---

## Phone Number Format

Phone numbers should be in **E.164 format**:
- ✅ Correct: `+447123456789` or `447123456789`
- ❌ Wrong: `07123456789` or `+44 7123 456789`

The service automatically formats numbers, but it's best to provide them in E.164 format.

---

## Production vs Sandbox

### **Sandbox Mode (Testing)**
- URL: `https://messages-sandbox.nexmo.com/v1/messages`
- Limited to test numbers
- Free for testing
- Set `VONAGE_WHATSAPP_SANDBOX=true` in `.env`

### **Production Mode (Current Setup)**
- URL: `https://api.nexmo.com/v1/messages` (default)
- Real WhatsApp Business Number
- Requires approved WhatsApp Business Account
- Set `VONAGE_WHATSAPP_SANDBOX=false` in `.env`

**Current Configuration:** Production mode (sandbox disabled)

---

## Verification Checklist

✅ **Package Installed**
```bash
composer show vonage/vonage-laravel
```

✅ **Config Published**
- Config file exists at `config/vonage.php`

✅ **Environment Variables Set**
- `VONAGE_KEY` - Your Vonage API key
- `VONAGE_SECRET` - Your Vonage API secret
- `WHATSAPP_PHONE_NUMBER` - Your WhatsApp Business Number
- `VONAGE_WHATSAPP_SANDBOX=false` - Production mode

✅ **Service Updated**
- `VonageService` updated to use new config structure
- `WhatsAppService` created for easy messaging

---

## Testing

### **Test Sending a Message**

Create a test route or use tinker:

```php
// In routes/web.php (temporary test route)
Route::get('/test-whatsapp', function() {
    $service = new \App\Services\WhatsAppService();
    $result = $service->sendText(
        '447123456789', // Replace with your test number
        'Test message from DoctorOnTap'
    );
    return response()->json($result);
});
```

Or use Laravel Tinker:
```bash
php artisan tinker
```

```php
$service = new \App\Services\WhatsAppService();
$result = $service->sendText('447123456789', 'Test message');
dd($result);
```

---

## Troubleshooting

### **Error: "WhatsApp phone number not configured"**
- Check that `WHATSAPP_PHONE_NUMBER` is set in `.env`
- Run `php artisan config:clear` to refresh config

### **Error: "Authentication failed"**
- Verify `VONAGE_KEY` and `VONAGE_SECRET` are correct
- Check that credentials have WhatsApp API access

### **Error: "Invalid phone number"**
- Ensure phone number is in E.164 format
- Remove spaces, dashes, and parentheses

### **Messages not sending**
- Verify WhatsApp Business Account is approved
- Check that recipient has opted in (for production)
- Ensure `VONAGE_WHATSAPP_SANDBOX=false` for production

---

## Files Modified

1. ✅ `config/services.php` - Added WhatsApp config structure
2. ✅ `app/Services/VonageService.php` - Updated to use new config
3. ✅ `app/Services/WhatsAppService.php` - New service class created

---

## Next Steps

1. **Set Environment Variables**
   - Add `VONAGE_KEY`, `VONAGE_SECRET`, and `WHATSAPP_PHONE_NUMBER` to `.env`
   - Set `VONAGE_WHATSAPP_SANDBOX=false`

2. **Clear Config Cache**
   ```bash
   php artisan config:clear
   ```

3. **Test Sending**
   - Use the test route or tinker to send a test message

4. **Integrate into Application**
   - Use `WhatsAppService` in your controllers, notifications, or jobs

---

## Summary

✅ **Setup Complete**
- Package installed
- Config updated
- Service classes ready
- Production mode configured

**Status:** Ready for production use (non-sandbox)  
**Date:** February 8, 2026

