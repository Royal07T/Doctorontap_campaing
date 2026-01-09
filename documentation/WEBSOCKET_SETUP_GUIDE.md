# WebSocket Setup Guide - Real-Time Notifications

## Overview

This guide explains how to set up Laravel Reverb (WebSockets) to replace polling for real-time notifications. This eliminates the rapid request issues and provides instant notification delivery.

## What Was Changed

### 1. Backend Changes

✅ **Installed Laravel Reverb**
- WebSocket server for real-time communication
- Package: `laravel/reverb`

✅ **Created NotificationCreated Event**
- Location: `app/Events/NotificationCreated.php`
- Broadcasts to user-specific private channels
- Format: `notifications.{userType}.{userId}`

✅ **Updated create_notification() Helper**
- Now broadcasts events when notifications are created
- Location: `app/helpers.php`

✅ **Set Up Private Channels**
- Location: `routes/channels.php`
- Channels for each user type: admin, doctor, patient, nurse, canvasser

### 2. Frontend Changes

✅ **Installed Laravel Echo & Pusher JS**
- Packages: `laravel-echo`, `pusher-js`

✅ **Updated app.js**
- Initialized Laravel Echo with Reverb configuration
- Location: `resources/js/app.js`

✅ **Updated Notification Component**
- Replaced polling with WebSocket listeners
- Fallback polling (2 minutes) if WebSocket fails
- Browser notifications support
- Location: `resources/views/components/notification-icon.blade.php`

## Setup Instructions

### Step 1: Environment Configuration

#### Development (.env)

Add these to your local `.env` file:

```env
BROADCAST_CONNECTION=reverb

# Reverb WebSocket Configuration
REVERB_APP_ID=308890
REVERB_APP_KEY=kz477bclvg9fpkpstrpb
REVERB_APP_SECRET=epd00eppcuxty4y3fjhx
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080

# Frontend (Vite)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

#### Production (.env)

The production `.envprodd` file has been updated with:
- `BROADCAST_CONNECTION=reverb`
- All Reverb configuration variables
- VITE_ variables for frontend

**Important**: Update your production `.env` file with these values.

### Step 2: Generate Reverb Keys (If Needed)

If you need to generate new keys:

```bash
php artisan reverb:key --generate
```

This will generate new `REVERB_APP_KEY`, `REVERB_APP_SECRET`, and `REVERB_APP_ID`.

### Step 3: Build Frontend Assets

```bash
npm install
npm run build
```

### Step 4: Start Reverb Server

#### Development

```bash
php artisan reverb:start
```

Or run it in the background:
```bash
php artisan reverb:start &
```

#### Production

Use a process manager like Supervisor or systemd:

**Supervisor Configuration** (`/etc/supervisor/conf.d/reverb.conf`):

```ini
[program:reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan reverb:start
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/reverb.log
stopwaitsecs=3600
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb:*
```

### Step 5: Configure Web Server (Production)

#### Nginx Configuration

Add this to your Nginx server block:

```nginx
location /app/ {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_read_timeout 86400;
}
```

#### Apache Configuration

Add this to your `.htaccess` or virtual host:

```apache
<LocationMatch "^/app/">
    ProxyPass ws://127.0.0.1:8080/app/
    ProxyPassReverse ws://127.0.0.1:8080/app/
    ProxyPreserveHost On
</LocationMatch>
```

### Step 6: Test the Setup

1. **Start Reverb Server**:
   ```bash
   php artisan reverb:start
   ```

2. **Open Application**:
   - Open your app in browser
   - Open browser console (F12)
   - You should see: "WebSocket connected for notifications"

3. **Create a Test Notification**:
   ```bash
   php artisan tinker
   >>> create_notification('admin', 1, 'Test', 'WebSocket test notification', 'info');
   ```

4. **Verify**:
   - Notification should appear instantly (no polling delay)
   - Check browser console for WebSocket messages
   - Check Reverb server logs

## How It Works

### Before (Polling)
```
Frontend → Polls every 60 seconds → Backend
- Creates many requests
- Delayed notifications (up to 60 seconds)
- Server load from constant polling
```

### After (WebSockets)
```
Backend → Broadcasts event → Reverb Server → Frontend (instant)
- Real-time delivery
- No polling requests
- Lower server load
- Better user experience
```

### Flow

1. **Notification Created**:
   ```php
   create_notification('admin', 1, 'Title', 'Message');
   ```

2. **Event Broadcast**:
   - `NotificationCreated` event is dispatched
   - Broadcasts to channel: `notifications.admin.1`

3. **Frontend Receives**:
   - Laravel Echo listens to private channel
   - Updates UI instantly
   - Shows browser notification

## Benefits

✅ **Eliminates Polling**
- No more `/notifications/unread-count` requests every 60 seconds
- No false security alerts from rapid requests

✅ **Real-Time Delivery**
- Notifications appear instantly
- Better user experience

✅ **Reduced Server Load**
- No constant polling
- Only sends data when needed

✅ **Browser Notifications**
- Desktop notifications when app is in background
- Better engagement

✅ **Fallback Support**
- If WebSocket fails, falls back to polling (2 minutes)
- Ensures notifications always work

## Troubleshooting

### WebSocket Not Connecting

1. **Check Reverb Server**:
   ```bash
   php artisan reverb:start
   # Should see: "Reverb server started on 0.0.0.0:8080"
   ```

2. **Check Environment Variables**:
   ```bash
   php artisan tinker
   >>> config('broadcasting.default') // Should be 'reverb'
   >>> config('reverb.apps.apps.0.key') // Should have value
   ```

3. **Check Browser Console**:
   - Look for WebSocket connection errors
   - Check if Echo is initialized

4. **Check Network Tab**:
   - Look for WebSocket connection (ws:// or wss://)
   - Should see upgrade to WebSocket protocol

### Notifications Not Appearing

1. **Check Channel Authorization**:
   - Verify user is authenticated
   - Check `routes/channels.php` authorization logic

2. **Check Event Broadcasting**:
   ```bash
   php artisan tinker
   >>> $notif = \App\Models\Notification::first();
   >>> \App\Events\NotificationCreated::dispatch($notif);
   ```

3. **Check Reverb Logs**:
   ```bash
   tail -f storage/logs/reverb.log
   ```

### Production Issues

1. **Firewall**:
   - Ensure port 8080 (or your REVERB_SERVER_PORT) is open
   - Or configure reverse proxy (recommended)

2. **SSL/HTTPS**:
   - Use `REVERB_SCHEME=https` in production
   - Configure SSL for WebSocket (wss://)

3. **Process Manager**:
   - Ensure Reverb server is running
   - Use Supervisor or systemd to auto-restart

## Monitoring

### Check Reverb Status

```bash
php artisan reverb:status
```

### View Connections

Reverb provides a dashboard (if enabled) or check logs:
```bash
tail -f storage/logs/reverb.log
```

## Security Considerations

1. **Private Channels**: Only authenticated users can subscribe
2. **Channel Authorization**: Verified in `routes/channels.php`
3. **HTTPS/WSS**: Use secure WebSocket in production
4. **CORS**: Configured in `config/reverb.php`

## Next Steps

1. ✅ Update production `.env` with Reverb configuration
2. ✅ Start Reverb server on production
3. ✅ Configure reverse proxy (Nginx/Apache)
4. ✅ Test notifications in production
5. ✅ Monitor Reverb server logs

## Rollback (If Needed)

If you need to rollback to polling:

1. Set `BROADCAST_CONNECTION=log` in `.env`
2. The notification component will automatically use fallback polling
3. No code changes needed - it's already built-in

## Support

- Laravel Reverb Docs: https://laravel.com/docs/reverb
- Laravel Broadcasting: https://laravel.com/docs/broadcasting

