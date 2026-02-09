# Vonage Messages API - Quick Start Guide

This is a quick reference guide for setting up the Vonage Messages API in your DoctorOnTap application.

## 🚀 Quick Setup (5 Minutes)

### Step 1: Create Vonage Account
1. Go to [Vonage Dashboard](https://ui.idp.vonage.com/ui/auth/registration)
2. Sign up and verify your phone number
3. You'll receive €2 free test credit

### Step 2: Create Application

**Option A: Using CLI (Recommended)**
```bash
# Install CLI
npm install -g @vonage/cli

# Authenticate
vonage config:set api_key YOUR_API_KEY
vonage config:set api_secret YOUR_API_SECRET

# Create application
vonage apps:create "DoctorOnTap Messages App" \
  --messages_inbound_url=https://yourdomain.com/webhooks/inbound-message \
  --messages_status_url=https://yourdomain.com/webhooks/message-status
```

**Option B: Using Dashboard**
1. Go to [Applications](https://dashboard.nexmo.com/applications)
2. Click "Create a new application"
3. Name it "DoctorOnTap Messages App"
4. Select "Messages" capability
5. Set webhook URLs:
   - Inbound: `https://yourdomain.com/webhooks/inbound-message`
   - Status: `https://yourdomain.com/webhooks/message-status`
6. Generate and download private key

### Step 3: Configure Laravel

Add to `.env`:
```env
VONAGE_API_METHOD=messages
VONAGE_APPLICATION_ID=your_application_id
VONAGE_PRIVATE_KEY_PATH=/path/to/private.key
VONAGE_ENABLED=true
VONAGE_BRAND_NAME=DoctorOnTap
```

### Step 4: Test

```bash
php artisan vonage:test-all --service=sms --to=+2347081114942
```

## 📋 Webhook Endpoints

Your application supports these webhook endpoints:

### Messages API Standard Routes (Recommended)
- **Inbound Messages**: `POST /webhooks/inbound-message`
- **Message Status**: `POST /webhooks/message-status`

### Legacy Routes (Backward Compatible)
- **Inbound Messages**: `POST /vonage/webhook/inbound`
- **Message Status**: `POST /vonage/webhook/status`

## 🔧 Local Development

For local development, use ngrok:

```bash
# Terminal 1: Start Laravel
php artisan serve

# Terminal 2: Start ngrok
ngrok http 8000

# Use ngrok URL in your Vonage application webhooks
# Example: https://abc123.ngrok.io/webhooks/inbound-message
```

## 📚 Documentation

- **[Getting Started Guide](VONAGE_MESSAGES_API_GETTING_STARTED.md)** - Complete setup guide
- **[CLI Commands](VONAGE_MESSAGES_API_CLI_COMMANDS.md)** - CLI reference
- **[Original Setup](VONAGE_MESSAGES_API_SETUP.md)** - Original setup documentation

## ✅ Supported Channels

The Messages API supports multiple channels:

- ✅ **SMS** - Text messages
- ✅ **MMS** - Media messages (images, videos, audio)
- ✅ **WhatsApp** - WhatsApp Business messages
- ✅ **Facebook Messenger** - Facebook messages
- ✅ **Viber** - Viber messages
- ✅ **RCS** - Rich Communication Services

## 🔐 Security

- Private keys are stored outside web root
- Webhook signature verification is implemented
- All credentials come from environment variables

## 🆘 Troubleshooting

### Application not found
```bash
vonage apps:list
```

### Webhook not receiving messages
1. Verify webhook URLs are accessible
2. Check Laravel logs: `storage/logs/laravel.log`
3. For local dev, ensure ngrok is running

### Invalid credentials
1. Verify Application ID matches
2. Ensure private key file is readable
3. Check file permissions: `chmod 600 private.key`

## 📞 Support

- [Vonage Documentation](https://developer.vonage.com/en/messages/overview)
- [Vonage Dashboard](https://dashboard.nexmo.com/)
- [Vonage Support](https://help.nexmo.com/)

