# Vonage Messages API - CLI Commands Reference

This document provides quick reference commands for creating and managing Vonage Messages API applications using the Vonage CLI.

## Prerequisites

Install the Vonage CLI:

```bash
npm install -g @vonage/cli
```

Or using Homebrew (macOS):

```bash
brew install vonage-cli
```

## Authentication

Before using the CLI, authenticate with your Vonage account:

```bash
vonage config:set api_key YOUR_API_KEY
vonage config:set api_secret YOUR_API_SECRET
```

Or use environment variables:

```bash
export VONAGE_API_KEY=your_api_key
export VONAGE_API_SECRET=your_api_secret
```

## Create Messages API Application

### Basic Application

```bash
vonage apps:create "DoctorOnTap Messages App"
```

### Application with Webhooks

```bash
vonage apps:create "DoctorOnTap Messages App" \
  --messages_inbound_url=https://yourdomain.com/webhooks/inbound-message \
  --messages_status_url=https://yourdomain.com/webhooks/message-status
```

### Application with All Capabilities

```bash
vonage apps:create "DoctorOnTap Full App" \
  --messages_inbound_url=https://yourdomain.com/webhooks/inbound-message \
  --messages_status_url=https://yourdomain.com/webhooks/message-status \
  --voice_answer_url=https://yourdomain.com/vonage/webhook/voice/answer \
  --voice_event_url=https://yourdomain.com/vonage/webhook/voice/event
```

## List Applications

```bash
vonage apps:list
```

## Get Application Details

```bash
vonage apps:get APPLICATION_ID
```

## Update Application Webhooks

```bash
vonage apps:update APPLICATION_ID \
  --messages_inbound_url=https://yourdomain.com/webhooks/inbound-message \
  --messages_status_url=https://yourdomain.com/webhooks/message-status
```

## Delete Application

```bash
vonage apps:delete APPLICATION_ID
```

## Example: Complete Setup for DoctorOnTap

### 1. Create Application

```bash
vonage apps:create "DoctorOnTap Messages App" \
  --messages_inbound_url=https://doctorontap.com/webhooks/inbound-message \
  --messages_status_url=https://doctorontap.com/webhooks/message-status
```

This will:
- Create a new application
- Generate a private key file (e.g., `doctorontap_messages_app.key`)
- Create or update `vonage_app.json` with application details

### 2. Get Application ID

After creation, the CLI will display the Application ID, or you can find it in `vonage_app.json`:

```bash
cat vonage_app.json
```

### 3. Configure Laravel

Add to your `.env`:

```env
VONAGE_APPLICATION_ID=your_application_id_from_cli
VONAGE_PRIVATE_KEY_PATH=/path/to/doctorontap_messages_app.key
VONAGE_API_METHOD=messages
```

### 4. Test the Setup

```bash
php artisan vonage:test-all --service=sms --to=+2347081114942
```

## Local Development with ngrok

### 1. Start ngrok

```bash
ngrok http 8000
```

### 2. Create Application with ngrok URL

```bash
vonage apps:create "DoctorOnTap Dev" \
  --messages_inbound_url=https://your-ngrok-id.ngrok.io/webhooks/inbound-message \
  --messages_status_url=https://your-ngrok-id.ngrok.io/webhooks/message-status
```

**Note**: Update your application webhooks in the Dashboard when your ngrok URL changes.

## Troubleshooting

### "Command not found: vonage"

Install the CLI:
```bash
npm install -g @vonage/cli
```

### "Authentication failed"

Verify your API credentials:
```bash
vonage config:get
```

### "Application not found"

List all applications:
```bash
vonage apps:list
```

## Additional Resources

- [Vonage CLI Documentation](https://developer.vonage.com/en/tools/cli)
- [Vonage Messages API Documentation](https://developer.vonage.com/en/messages/overview)
- [Vonage Dashboard](https://dashboard.nexmo.com/)

