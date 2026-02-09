# Getting Started with the Vonage Messages API

This page will walk you through all of the necessary steps to get up and running with the Vonage Messages API. The Messages API provides a simple way to send messages to your customers through multiple popular channels - SMS, MMS, RCS, WhatsApp, Facebook, and Viber. Instead of integrating with each platform separately, you can connect once and manage all conversations in one place.

To begin, follow this guide to create your account and application. Then, navigate to the getting started guide for your [chosen channels](#channel-specific-getting-started-sections) to complete your onboarding.

## Prerequisites

Before sending your first message, you'll need to create a Vonage account and application:

### Create a Vonage account

To create your free Vonage account:

1. In your browser, navigate to [Dashboard](https://ui.idp.vonage.com/ui/auth/registration?icid=tryitfree_adpdocs_nexmodashbdfreetrialsignup_inpagelink).
2. Add your company information and click **Sign up**. Vonage sends a PIN to your phone as a text message or automated phone call. The timeout for each verification attempt is 5 minutes.  
   **Note**: you can associate a phone number with one account only. If your phone number is already associated with a Vonage account you should [remove that phone number](#manage-your-profile) from the existing account.
3. In **Phone number verification**, enter the PIN sent to you by Vonage and click **Verify**. You are logged into Dashboard and shown how to start developing with Vonage. This page is displayed each time you login until you have made your first successful call with Vonage APIs.

When you create your Vonage account you are given €2 free test credit and your account is set in DEMO mode. You can use our products to send messages to up to 10 destination numbers, a message stating that the account is in demo mode is added to all the SMS you send. To move out of the demo mode [add credit to your account](https://developer.vonage.com/en/numbers/guides/payments#add-a-payment-method).

For very few countries Vonage cannot create an account for you automatically. This is because of payment restrictions or legal trading restrictions for a US registered company.

### Create a Vonage API Application

There are two alternative methods for creating a Messages application:

1. Using the Vonage CLI
2. Using the Dashboard

Each of these methods is described in the following sections.

#### How to create a Messages application using the Vonage CLI

To create your application using the Vonage CLI, enter the following command into the shell:

```bash
$ vonage apps:create "My Messages App" --messages_inbound_url=https://example.com/webhooks/inbound-message --messages_status_url=https://example.com/webhooks/message-status
```

This creates a Vonage API application with a messages [capability](https://developer.vonage.com/en/application/technical-details#capabilities), with the webhook URLs configured as specified, and generate a private key file `my_messages_app.key` and creates or updates the `vonage_app.json` file.

**For DoctorOnTap Application:**

```bash
# Replace with your actual domain
vonage apps:create "DoctorOnTap Messages App" \
  --messages_inbound_url=https://yourdomain.com/webhooks/inbound-message \
  --messages_status_url=https://yourdomain.com/webhooks/message-status
```

**Note**: The webhook URLs should point to your Laravel application's webhook endpoints. See [Webhook Configuration](#webhook-configuration) below.

#### How to create a Messages application using the Dashboard

You can create Messages application in the [Dashboard](https://dashboard.nexmo.com/applications).

To create your application using the Dashboard:

1. Under [Applications](https://dashboard.nexmo.com/applications) in the Dashboard, click the **Create a new application** button.
   
2. Under **Name**, enter the Application name. Choose a name for ease of future reference.
   
3. Click the button **Generate public and private key**. This will create a public/private key pair and the private key will be downloaded by your browser.
   
4. Under **Capabilities** select the **Messages** button.
   
5. In the **Inbound URL** box, enter the URL for your inbound message webhook, for example, `https://yourdomain.com/webhooks/inbound-message`.
   
6. In the **Status URL** box, enter the URL for your message status webhook, for example, `https://yourdomain.com/webhooks/message-status`.
   
7. Click the **Generate new application** button. You are now taken to the next step of the Create Application procedure where you can link a Vonage API number to the application, and link external accounts such as Facebook to this application.
   
8. If there is an external account you want to link this application to, click the **Linked external accounts** tab, and then click the corresponding **Link** button for the account you want to link to.
   

You have now created your application.

> **NOTE**: Before testing your application ensure that your webhooks are configured and your webhook server is running.

## Webhook Configuration

This Laravel application has webhook endpoints configured for the Messages API. The standard Messages API webhook paths are:

- **Inbound Messages**: `https://yourdomain.com/webhooks/inbound-message`
- **Message Status**: `https://yourdomain.com/webhooks/message-status`

These routes are also available for backward compatibility:
- `https://yourdomain.com/vonage/webhook/inbound`
- `https://yourdomain.com/vonage/webhook/status`

### Local Development with ngrok

For local development, use ngrok to expose your local server:

1. **Install ngrok**: [https://ngrok.com/](https://ngrok.com/)

2. **Start your Laravel server**:
   ```bash
   php artisan serve
   ```

3. **Start ngrok**:
   ```bash
   ngrok http 8000
   ```

4. **Use the ngrok URL** in your Vonage application webhook settings:
   ```
   https://your-ngrok-url.ngrok.io/webhooks/inbound-message
   https://your-ngrok-url.ngrok.io/webhooks/message-status
   ```

## Environment Configuration

After creating your application, configure your Laravel application:

### 1. Get Your Credentials

From your Vonage Dashboard or CLI output, you'll need:
- **Application ID**: Found in the Dashboard under your application
- **Private Key**: The `.key` file downloaded when creating the application

### 2. Configure Environment Variables

Add these to your `.env` file:

```env
# Messages API Configuration
VONAGE_API_METHOD=messages
VONAGE_APPLICATION_ID=your_application_id_here

# Option 1: Private key file path (recommended)
VONAGE_PRIVATE_KEY_PATH=/path/to/your/private.key

# Option 2: Private key as inline string (alternative)
# VONAGE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----"

# Common settings
VONAGE_BRAND_NAME=DoctorOnTap
VONAGE_ENABLED=true

# Webhook base URL (for production)
VONAGE_WEBHOOK_BASE_URL=https://yourdomain.com
```

### 3. Store Private Key Securely

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

## Testing Your Setup

### Test SMS via Messages API

```bash
php artisan vonage:test-all --service=sms --to=+2347081114942
```

### Test WhatsApp (if configured)

```bash
php artisan vonage:test-all --service=whatsapp --to=+2347081114942
```

## Channel specific Getting started sections

*   [Getting started with RCS messaging](https://developer.vonage.com/en/messages/concepts/getting-started/getting-started-rcs.md)
*   [Getting started with WhatsApp messaging](https://developer.vonage.com/en/messages/concepts/getting-started/getting-started-whatsapp.md)
*   [Getting started with SMS](https://developer.vonage.com/en/messages/concepts/getting-started/getting-started-sms.md)
*   [Getting started with Facebook](https://developer.vonage.com/en/messages/concepts/getting-started/getting-started-fb.md)
*   [Getting started with Viber](https://developer.vonage.com/en/messages/concepts/getting-started/getting-started-viber.md)

## Manage Your Profile

To remove a phone number from your Vonage account:

1. Go to [Vonage Dashboard](https://dashboard.nexmo.com/)
2. Navigate to **Settings** → **Profile**
3. Under **Phone Number**, click **Remove** next to the number you want to remove
4. Confirm the removal

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

### Webhook not receiving messages
- Verify webhook URLs are accessible (use ngrok for local development)
- Check that webhook URLs are correctly configured in your Vonage application
- Ensure your server is running and accessible
- Check Laravel logs: `storage/logs/laravel.log`

## Security Best Practices

1. ✅ **Never commit private keys** to version control
2. ✅ Store private keys **outside web root**
3. ✅ Use **file path** method instead of inline key when possible
4. ✅ Set **restrictive file permissions** (600)
5. ✅ Use **different applications** for development and production
6. ✅ **Rotate keys** periodically
7. ✅ **Verify webhook signatures** (implemented in this application)

## Additional Resources

- [Vonage Messages API Documentation](https://developer.vonage.com/en/messages/overview)
- [Creating a Vonage Application](https://developer.vonage.com/en/messages/code-snippets/create-an-application)
- [Configuring Webhooks](https://developer.vonage.com/en/messages/code-snippets/configure-webhooks)
- [Vonage PHP SDK](https://github.com/Vonage/vonage-php-sdk)

