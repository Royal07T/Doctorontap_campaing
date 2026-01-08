# Vonage Messages API Setup Guide

This guide explains how to set up and use Vonage's **Messages API** (the newer API) instead of the legacy SMS API.

## Prerequisites

1. **Vonage Account**: Sign up at [https://dashboard.nexmo.com/](https://dashboard.nexmo.com/)
2. **Set SMS Settings**: In your dashboard, go to **Settings** → Set SMS settings to **"Messages API"**
3. **Create a Vonage Application**: You need to create an application to get credentials

## Step 1: Create a Vonage Application

1. Go to [Vonage Dashboard](https://dashboard.nexmo.com/)
2. Navigate to **Applications** → **Create a new application**
3. Give it a name (e.g., "DoctorOnTap SMS")
4. **Generate Private Key** - Download the private key file (`.key` extension)
5. Copy the **Application ID**

## Step 2: Configure Webhooks (Optional but Recommended)

In your Vonage Application settings, configure webhooks:
- **Inbound URL**: `https://yourdomain.com/vonage/webhook/inbound`
- **Status URL**: `https://yourdomain.com/vonage/webhook/status`

These allow you to receive delivery receipts and inbound messages.

## Step 3: Configure Environment Variables

Add these to your `.env` file:

```env
# Choose API method: 'legacy' (simpler) or 'messages' (newer)
VONAGE_API_METHOD=messages

# Messages API Configuration (required for Messages API)
VONAGE_APPLICATION_ID=your_application_id_here

# Option 1: Private key file path (recommended)
VONAGE_PRIVATE_KEY_PATH=/path/to/your/private.key

# Option 2: Private key as inline string (alternative)
# VONAGE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----"

# Legacy API Configuration (only needed if using legacy method)
# VONAGE_API_KEY=your_api_key
# VONAGE_API_SECRET=your_api_secret

# Common settings
VONAGE_BRAND_NAME=DoctorOnTap
VONAGE_ENABLED=true
```

### Private Key Options

**Option 1: File Path (Recommended)**
```env
VONAGE_PRIVATE_KEY_PATH=/home/user/doctorontap/vonage_private.key
```

**Option 2: Inline Key**
```env
VONAGE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC...\n-----END PRIVATE KEY-----"
```
Note: Use `\n` for newlines when using inline key.

## Step 4: Store Private Key Securely

**Recommended**: Store the private key file outside your web root:
```
/home/user/doctorontap/
├── storage/
├── app/
└── vonage_private.key  ← Store here (outside web root)
```

Set proper permissions:
```bash
chmod 600 vonage_private.key
chown www-data:www-data vonage_private.key
```

## Step 5: Test the Integration

```bash
php artisan vonage:test-sms 07081114942
```

## Differences: Legacy API vs Messages API

### Legacy SMS API (Simpler)
- ✅ Uses API Key + Secret (simpler setup)
- ✅ No application needed
- ✅ Works immediately
- ❌ Older API (may be deprecated in future)

### Messages API (Newer)
- ✅ Modern API
- ✅ Better webhook support
- ✅ More features (WhatsApp, Viber, etc.)
- ✅ Future-proof
- ❌ Requires Application setup
- ❌ Requires JWT authentication

## Switching Between APIs

You can switch between APIs by changing one environment variable:

```env
# Use Legacy API
VONAGE_API_METHOD=legacy
VONAGE_API_KEY=your_key
VONAGE_API_SECRET=your_secret

# Use Messages API
VONAGE_API_METHOD=messages
VONAGE_APPLICATION_ID=your_app_id
VONAGE_PRIVATE_KEY_PATH=/path/to/key.key
```

## Troubleshooting

### "Messages API classes not found"
- Ensure you have the latest Vonage SDK: `composer require vonage/client`
- Check SDK version: `composer show vonage/client`

### "Private key not found"
- Verify the file path is correct and absolute
- Check file permissions (should be readable by web server)
- Ensure the file exists

### "Invalid credentials"
- Verify your Application ID is correct
- Ensure the private key matches the application
- Check that you've set SMS settings to "Messages API" in dashboard

### "JWT generation failed"
- Verify private key format is correct
- Check that private key hasn't been corrupted
- Ensure private key matches the application

## Security Best Practices

1. ✅ **Never commit private keys** to version control
2. ✅ Store private keys **outside web root**
3. ✅ Use **file path** method instead of inline key when possible
4. ✅ Set **restrictive file permissions** (600)
5. ✅ Use **different applications** for development and production
6. ✅ **Rotate keys** periodically

## Support

- [Vonage Messages API Documentation](https://developer.vonage.com/en/messages/overview)
- [Creating a Vonage Application](https://developer.vonage.com/en/messages/code-snippets/create-an-application)
- [Configuring Webhooks](https://developer.vonage.com/en/messages/code-snippets/configure-webhooks)









