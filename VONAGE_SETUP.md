# Vonage SMS Integration Setup

This application supports both **Termii** and **Vonage** (formerly Nexmo) as SMS providers. You can switch between them using environment variables.

## Installation

The Vonage client package is already installed:
```bash
composer require vonage/client
```

## Configuration

### 1. Get Your Vonage Credentials

1. Sign up for a Vonage account at [https://dashboard.nexmo.com/](https://dashboard.nexmo.com/)
2. Navigate to **Settings** → **API Settings** in your dashboard
3. Copy your **API Key** and **API Secret**

### 2. Configure Environment Variables

Add these to your `.env` file:

```env
# SMS Provider Selection (options: 'termii' or 'vonage')
SMS_PROVIDER=vonage

# Vonage Configuration
VONAGE_API_KEY=your_api_key_here
VONAGE_API_SECRET=your_api_secret_here
VONAGE_BRAND_NAME=DoctorOnTap
VONAGE_ENABLED=true
```

**Important Notes:**
- Never commit your API secret to version control
- Keep your `.env` file secure and never share it
- The `VONAGE_BRAND_NAME` is the sender name that appears in SMS (must be approved by Vonage)
- Set `VONAGE_ENABLED=true` to activate Vonage SMS sending

### 3. Switch Back to Termii

If you want to use Termii instead:

```env
SMS_PROVIDER=termii
TERMII_ENABLED=true
VONAGE_ENABLED=false
```

## How It Works

1. The application checks the `SMS_PROVIDER` config value
2. Based on the provider, it uses either `VonageService` or `TermiiService`
3. Both services have the same interface (`sendSMS()` method), so switching is seamless
4. All SMS notifications throughout the app will use the selected provider

## Features

- ✅ **No Hardcoded Secrets**: All credentials come from environment variables
- ✅ **Easy Switching**: Change providers by updating one environment variable
- ✅ **Automatic Phone Formatting**: Handles Nigerian phone numbers (+234 format)
- ✅ **Comprehensive Logging**: All SMS attempts are logged for debugging
- ✅ **Error Handling**: Graceful error handling with detailed error messages
- ✅ **Bulk SMS Support**: Send SMS to multiple recipients

## Testing

You can test the Vonage integration by:

1. Creating a consultation (which triggers SMS)
2. Using the admin panel to send SMS reminders
3. Checking the Laravel logs for SMS sending status

## Vonage Status Codes

- `0` = Success
- `1` = Throttled
- `2` = Missing parameters
- `3` = Invalid parameters
- `4` = Invalid credentials
- `5` = Internal error
- And more... (see `VonageService::getStatusErrorMessage()` for full list)

## Security Best Practices

1. ✅ Store credentials in `.env` file (never in code)
2. ✅ Use different API keys for development and production
3. ✅ Regularly rotate your API secrets
4. ✅ Monitor your Vonage dashboard for unusual activity
5. ✅ Set up webhooks for delivery receipts (optional)

## Troubleshooting

### SMS Not Sending

1. Check that `VONAGE_ENABLED=true` in `.env`
2. Verify `SMS_PROVIDER=vonage` is set
3. Ensure API key and secret are correct
4. Check Laravel logs: `storage/logs/laravel.log`
5. Verify your Vonage account has sufficient balance

### Invalid Credentials Error

- Double-check your API key and secret in `.env`
- Make sure there are no extra spaces or quotes
- Run `php artisan config:clear` after changing `.env`

### Phone Number Format Issues

- Vonage requires international format: `+2348012345678`
- The service automatically formats Nigerian numbers
- Numbers starting with `0` are converted to `+234`

## Support

For Vonage-specific issues:
- Vonage Documentation: [https://developer.vonage.com/](https://developer.vonage.com/)
- Vonage Support: [https://help.nexmo.com/](https://help.nexmo.com/)




