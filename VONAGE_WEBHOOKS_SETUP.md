# Vonage Webhooks Setup Guide

This guide explains how to set up and use Vonage webhooks to receive delivery receipts and inbound SMS messages.

## What are Webhooks?

Webhooks allow Vonage to notify your application about:
- **Delivery Status**: When an SMS is delivered, failed, or rejected
- **Inbound Messages**: When someone sends an SMS to your Vonage number

## Prerequisites

1. **Public URL**: Your webhook endpoints must be accessible over the public Internet
2. **HTTPS**: Vonage requires HTTPS for webhooks (except during development with ngrok)
3. **No CSRF Protection**: Webhook routes are excluded from CSRF protection

## Webhook Endpoints

Two webhook endpoints are available:

### 1. Inbound Messages
**URL**: `https://yourdomain.com/vonage/webhook/inbound`  
**Method**: POST  
**Purpose**: Receives inbound SMS messages sent to your Vonage number

### 2. Delivery Status
**URL**: `https://yourdomain.com/vonage/webhook/status`  
**Method**: POST  
**Purpose**: Receives delivery status updates for sent SMS messages

## Configuration in Vonage Dashboard

### For Legacy SMS API:

1. Go to [Vonage Dashboard](https://dashboard.nexmo.com/)
2. Navigate to **Settings** → **API Settings**
3. Set **Callback URL for Inbound Message**:
   ```
   https://yourdomain.com/vonage/webhook/inbound
   ```
4. Set **Delivery Receipts URL**:
   ```
   https://yourdomain.com/vonage/webhook/status
   ```

### For Messages API:

1. Go to [Vonage Dashboard](https://dashboard.nexmo.com/)
2. Navigate to **Applications** → Select your application
3. Under **Webhooks**, set:
   - **Inbound URL**: `https://yourdomain.com/vonage/webhook/inbound`
   - **Status URL**: `https://yourdomain.com/vonage/webhook/status`

## Testing with ngrok (Development)

During development, use ngrok to expose your local server:

1. **Install ngrok**: [https://ngrok.com/](https://ngrok.com/)

2. **Start your Laravel server**:
   ```bash
   php artisan serve
   ```

3. **Start ngrok**:
   ```bash
   ngrok http 8000
   ```

4. **Use ngrok URL in Vonage Dashboard**:
   ```
   https://abc123.ngrok.io/vonage/webhook/inbound
   https://abc123.ngrok.io/vonage/webhook/status
   ```

## Webhook Data Formats

### Inbound Message (Legacy API)
```json
{
  "msisdn": "2347081114942",
  "to": "14155550101",
  "messageId": "0A0000001234567B",
  "text": "Hello from customer",
  "type": "text",
  "keyword": "HELLO",
  "message-timestamp": "2020-01-01 12:00:00"
}
```

### Inbound Message (Messages API)
```json
{
  "from": "2347081114942",
  "to": "14155550101",
  "message": {
    "content": {
      "type": "text",
      "text": "Hello from customer"
    }
  },
  "message_uuid": "abc-123-def",
  "timestamp": "2020-01-01T12:00:00Z"
}
```

### Delivery Status (Legacy API)
```json
{
  "messageId": "0A0000001234567B",
  "status": "delivered",
  "err-code": "0",
  "network": "234",
  "price": "0.03330000",
  "scts": "2001011200",
  "message-timestamp": "2020-01-01 12:00:00"
}
```

### Delivery Status (Messages API)
```json
{
  "message_uuid": "abc-123-def",
  "status": "delivered",
  "timestamp": "2020-01-01T12:00:00Z"
}
```

## Status Mapping

The webhook controller maps Vonage statuses to internal statuses:

| Vonage Status | Internal Status | Description |
|--------------|----------------|-------------|
| `0` or `delivered` | `delivered` | Message successfully delivered |
| `accepted` | `sent` | Message accepted by carrier |
| `1-29` (Legacy errors) | `failed` | Various error conditions |
| `rejected` | `failed` | Message rejected |
| `undelivered` | `failed` | Message not delivered |

## Database Tables

Webhook data is stored in two tables:

### `sms_inbound_logs`
Stores inbound SMS messages:
- `from`: Sender phone number
- `to`: Recipient phone number
- `message`: Message content
- `message_id`: Vonage message ID
- `raw_data`: Complete webhook payload (JSON)

### `sms_status_logs`
Stores delivery status updates:
- `message_id`: Vonage message ID
- `status`: Delivery status
- `error_code`: Error code (if failed)
- `network`: Network code
- `price`: Message cost
- `raw_data`: Complete webhook payload (JSON)

## Integration with Notification Tracking

The webhook controller automatically updates `notification_tracking_logs` when a status update is received:

- Matches by `external_message_id`
- Updates `status`, `delivered_at`, `failed_at`, `error_code`
- Logs the raw response for debugging

## Security Considerations

1. **Verify Webhook Signatures** (Optional but Recommended):
   - Vonage can sign webhooks with a secret
   - Verify signatures to ensure requests are from Vonage
   - See: [Vonage Webhook Signing](https://developer.vonage.com/en/getting-started/concepts/signing-messages)

2. **Rate Limiting**:
   - Consider adding rate limiting to webhook endpoints
   - Vonage may send multiple webhooks for the same message

3. **IP Whitelisting**:
   - Vonage publishes IP ranges for webhooks
   - Consider whitelisting these IPs in your firewall

## Troubleshooting

### Webhooks not received
- ✅ Verify URLs are publicly accessible
- ✅ Check HTTPS is enabled (required for production)
- ✅ Verify URLs in Vonage Dashboard are correct
- ✅ Check Laravel logs: `storage/logs/laravel.log`

### Status updates not updating notifications
- ✅ Verify `external_message_id` matches in `notification_tracking_logs`
- ✅ Check database for entries in `sms_status_logs`
- ✅ Review Laravel logs for errors

### Testing locally
- Use ngrok to expose local server
- Update Vonage Dashboard with ngrok URL
- Test with a real SMS to your Vonage number

## Next Steps

1. Configure webhook URLs in Vonage Dashboard
2. Test with ngrok (development) or deploy to production
3. Monitor `sms_inbound_logs` and `sms_status_logs` tables
4. Implement webhook signature verification (optional)
5. Set up alerts for failed deliveries

## Resources

- [Vonage Webhooks Documentation](https://developer.vonage.com/en/getting-started/concepts/webhooks)
- [Testing with ngrok](https://developer.vonage.com/en/getting-started/tools/ngrok)
- [Webhook Signing](https://developer.vonage.com/en/getting-started/concepts/signing-messages)



