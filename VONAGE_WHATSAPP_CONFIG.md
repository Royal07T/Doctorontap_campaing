# Vonage WhatsApp Configuration for DoctorOnTap LLC

## Your Vonage WhatsApp Credentials

**WhatsApp Number:** `405228299348572`  
**Application ID:** `250782187688`

---

## Required .env Configuration

Add these to your `.env` file:

```env
# Vonage API Credentials (get from https://dashboard.nexmo.com)
VONAGE_KEY=YOUR-API-KEY-HERE
VONAGE_SECRET=YOUR-API-SECRET-HERE

# Vonage Application ID (for Messages API)
VONAGE_APPLICATION_ID=250782187688

# WhatsApp Configuration
WHATSAPP_PHONE_NUMBER=405228299348572
VONAGE_WHATSAPP_NUMBER=405228299348572
VONAGE_WHATSAPP_ENABLED=true
VONAGE_WHATSAPP_SANDBOX=false

# Private Key (required for Messages API with JWT authentication)
# You need to download this from your Vonage dashboard
VONAGE_PRIVATE_KEY_PATH=/path/to/your/private.key
# OR use inline private key (replace \n with actual newlines)
# VONAGE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----"
```

---

## Important Notes

1. **Application ID:** `250782187688` - This is your Vonage Application ID for the Messages API
2. **WhatsApp Number:** `405228299348572` - This is your WhatsApp Business Number
3. **Private Key:** You need to download the private key from your Vonage dashboard and either:
   - Save it to a file and set `VONAGE_PRIVATE_KEY_PATH`
   - Or paste it in `.env` as `VONAGE_PRIVATE_KEY` (with `\n` for newlines)

---

## Configuration Status

✅ WhatsApp number configured: `405228299348572`  
✅ Application ID configured: `250782187688`  
⚠️ You still need to set:
   - `VONAGE_KEY` and `VONAGE_SECRET` (from your Vonage dashboard)
   - `VONAGE_PRIVATE_KEY` or `VONAGE_PRIVATE_KEY_PATH` (download from dashboard)

---

## Testing

After setting all credentials, test with:

```php
use App\Services\WhatsAppService;

$service = new WhatsAppService();
$result = $service->sendText(
    '447123456789', // Replace with test number
    'Test message from DoctorOnTap'
);
```

---

## Next Steps

1. Get your API Key and Secret from: https://dashboard.nexmo.com/settings
2. Download your private key from: https://dashboard.nexmo.com/applications/250782187688
3. Add all credentials to `.env`
4. Run: `php artisan config:clear`
5. Test sending a WhatsApp message

