# Pusher Beams Push Notifications Setup

This document explains how to set up and use Pusher Beams for push notifications in the DoctorOnTap application.

## Overview

Pusher Beams is a push notification service that allows you to send push notifications to:
- **Android** devices (via FCM - Firebase Cloud Messaging)
- **iOS** devices (via APNS - Apple Push Notification Service)
- **Web browsers** (via Web Push API)

Unlike Reverb (which is for WebSocket real-time connections), Pusher Beams is specifically designed for push notifications that work even when the app is closed.

## Installation

The Pusher Beams PHP SDK has been installed via Composer:

```bash
composer require pusher/pusher-push-notifications
```

## Configuration

### 1. Get Pusher Beams Credentials

1. Sign up for a Pusher account at [https://pusher.com](https://pusher.com)
2. Create a new Beams instance in the Pusher dashboard
3. Get your **Instance ID** and **Secret Key** from the dashboard under "Credentials"

### 2. Add Environment Variables

Add the following to your `.env` file:

```env
# Pusher Beams Configuration
PUSHER_BEAMS_INSTANCE_ID=your_instance_id_here
PUSHER_BEAMS_SECRET_KEY=your_secret_key_here
PUSHER_BEAMS_ENABLED=true
```

### 3. Configuration File

The configuration is already set up in `config/services.php`:

```php
'pusher_beams' => [
    'instance_id' => env('PUSHER_BEAMS_INSTANCE_ID'),
    'secret_key' => env('PUSHER_BEAMS_SECRET_KEY'),
    'enabled' => env('PUSHER_BEAMS_ENABLED', false),
],
```

## How It Works

### Automatic Push Notifications

When a `Notification` model is created, the system automatically:

1. **Sends WebSocket notification** via Reverb (for in-app real-time updates)
2. **Sends push notification** via Pusher Beams (for mobile/web push notifications)

The user ID format used is: `{user_type}_{user_id}` (e.g., `patient_123`, `doctor_456`)

### Manual Push Notifications

You can also send push notifications manually using the `PusherBeamsService`:

```php
use App\Services\PusherBeamsService;

$beamsService = app(PusherBeamsService::class);

// Send to specific users
$beamsService->publishToUsers(
    ['patient_123', 'doctor_456'],
    'Notification Title',
    'Notification message body',
    ['custom' => 'data'],
    'https://example.com/action-url'
);

// Send to device interests
$beamsService->publishToInterests(
    ['all-doctors', 'urgent-notifications'],
    'Notification Title',
    'Notification message body',
    ['custom' => 'data'],
    'https://example.com/action-url'
);
```

## Frontend Integration

### 1. Install Pusher Beams Client SDK

For **Web**:
```bash
npm install @pusher/push-notifications-web
```

For **React Native**:
```bash
npm install @pusher/push-notifications-react-native
```

For **iOS** (Swift):
Add via CocoaPods or Swift Package Manager

For **Android** (Kotlin/Java):
Add via Gradle

### 2. Get Authentication Token

Before registering a device, you need to get an authentication token from your backend:

```javascript
// Example: Get token from backend
const response = await fetch('/admin/pusher-beams/token', {
    headers: {
        'Authorization': `Bearer ${yourAuthToken}`,
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
});

const { token } = await response.json();
```

### 3. Register Device (Web Example)

```javascript
import * as PusherPushNotifications from "@pusher/push-notifications-web";

// Initialize Beams client
const beamsClient = new PusherPushNotifications.Client({
    instanceId: 'YOUR_INSTANCE_ID' // Get from .env or config
});

// Start Beams
beamsClient.start()
    .then(() => {
        // Get token from your backend
        return fetch('/admin/pusher-beams/token');
    })
    .then(response => response.json())
    .then(data => {
        // Set user ID
        return beamsClient.setUserId(data.token, {
            url: '/pusher-beams/auth', // Your auth endpoint
            headers: {
                'Authorization': `Bearer ${yourAuthToken}`
            }
        });
    })
    .then(() => {
        // Add device to interests (optional)
        return beamsClient.addDeviceInterest('all-users');
    })
    .then(() => {
        console.log('Device registered successfully');
    })
    .catch(err => {
        console.error('Failed to register device:', err);
    });
```

### 4. Handle Incoming Notifications

```javascript
// Listen for notifications
beamsClient.on('notification', (notification) => {
    console.log('Received notification:', notification);
    
    // Handle notification click
    if (notification.action_url) {
        window.location.href = notification.action_url;
    }
});
```

## API Endpoints

### Generate Authentication Token

**Endpoint:** `GET /{guard}/pusher-beams/token`

**Authentication:** Required (any authenticated user)

**Response:**
```json
{
    "success": true,
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

**Available for all user types:**
- `/admin/pusher-beams/token`
- `/doctor/pusher-beams/token`
- `/patient/pusher-beams/token`
- `/nurse/pusher-beams/token`
- `/canvasser/pusher-beams/token`
- `/customer-care/pusher-beams/token`

## User ID Format

The system uses the following format for user IDs in Pusher Beams:
- Format: `{user_type}_{user_id}`
- Examples:
  - `patient_123`
  - `doctor_456`
  - `admin_789`
  - `nurse_101`

This allows you to send notifications to specific users across different user types.

## Notification Data Structure

When a notification is sent via Pusher Beams, it includes:

```json
{
    "notification_id": 123,
    "type": "info",
    "user_type": "patient",
    "user_id": 123,
    "action_url": "https://example.com/notifications/123",
    // ... any additional data from notification.data
}
```

## Service Methods

### `PusherBeamsService`

#### `publishToUsers(array $userIds, string $title, string $body, array $data = [], ?string $actionUrl = null)`

Send push notification to specific users (max 1000 users per request).

#### `publishToInterests(array $interests, string $title, string $body, array $data = [], ?string $actionUrl = null)`

Send push notification to device interests (max 100 interests per request).

#### `generateToken(string $userId)`

Generate authentication token for a user to associate their device.

#### `deleteUser(string $userId)`

Remove a user and all their devices from Pusher Beams.

#### `isEnabled()`

Check if Pusher Beams is enabled and configured.

## Testing

### Test Push Notification

You can test push notifications by creating a notification in your application:

```php
use App\Models\Notification;

Notification::create([
    'user_type' => 'patient',
    'user_id' => 123,
    'title' => 'Test Notification',
    'message' => 'This is a test push notification',
    'type' => 'info',
    'action_url' => route('notifications.index'),
]);
```

This will automatically:
1. Create the notification in the database
2. Broadcast via WebSocket (Reverb)
3. Send push notification via Pusher Beams

## Troubleshooting

### Notifications Not Being Sent

1. **Check if Pusher Beams is enabled:**
   ```php
   $beamsService = app(PusherBeamsService::class);
   if (!$beamsService->isEnabled()) {
       // Check .env configuration
   }
   ```

2. **Check logs:**
   ```bash
   tail -f storage/logs/laravel.log | grep "Pusher Beams"
   ```

3. **Verify credentials:**
   - Ensure `PUSHER_BEAMS_INSTANCE_ID` is correct
   - Ensure `PUSHER_BEAMS_SECRET_KEY` is correct
   - Ensure `PUSHER_BEAMS_ENABLED=true`

### Device Not Receiving Notifications

1. **Check device registration:**
   - Ensure the device is properly registered with Pusher Beams
   - Verify the user ID matches the format: `{user_type}_{user_id}`

2. **Check browser/device permissions:**
   - Web: Ensure notification permissions are granted
   - Mobile: Ensure push notifications are enabled in app settings

3. **Check FCM/APNS configuration:**
   - For Android: Ensure FCM is properly configured
   - For iOS: Ensure APNS certificates are valid

## Differences: Reverb vs Pusher Beams

| Feature | Reverb | Pusher Beams |
|---------|--------|--------------|
| **Purpose** | WebSocket real-time connections | Push notifications |
| **Works when app is closed** | ❌ No | ✅ Yes |
| **Mobile support** | Limited | ✅ Full support |
| **Web support** | ✅ Yes | ✅ Yes |
| **Use case** | In-app real-time updates | Mobile/web push notifications |
| **Connection** | Persistent WebSocket | One-time push delivery |

## Best Practices

1. **Use both services:**
   - Use **Reverb** for in-app real-time notifications (when app is open)
   - Use **Pusher Beams** for push notifications (when app is closed)

2. **User ID consistency:**
   - Always use the format: `{user_type}_{user_id}`
   - This ensures notifications reach the correct user

3. **Error handling:**
   - Push notifications are sent asynchronously
   - Failures are logged but don't block notification creation
   - Check logs for delivery issues

4. **Testing:**
   - Test on actual devices, not just browsers
   - Test with app in background/closed state
   - Verify notification permissions are granted

## References

- [Pusher Beams Documentation](https://pusher.com/docs/beams/)
- [Pusher Beams PHP SDK Reference](https://pusher.com/docs/beams/reference/server-sdk-php/)
- [Pusher Beams Web SDK](https://pusher.com/docs/beams/getting-started/web/sdk-integration/)
- [Pusher Beams React Native SDK](https://pusher.com/docs/beams/getting-started/react-native/sdk-integration/)

