# WebSocket Quick Start Guide

## ✅ What's Done

1. ✅ Laravel Reverb installed and configured
2. ✅ NotificationCreated event created and broadcasts
3. ✅ Frontend updated to use WebSockets
4. ✅ Fallback polling (2 min) if WebSocket fails
5. ✅ Production .env configured

## 🚀 Quick Start

### Development

1. **Update local `.env`**:
```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=308890
REVERB_APP_KEY=kz477bclvg9fpkpstrpb
REVERB_APP_SECRET=epd00eppcuxty4y3fjhx
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

2. **Build frontend**:
```bash
npm install
npm run build
```

3. **Start Reverb server**:
```bash
php artisan reverb:start
```

4. **Test**:
- Open app in browser
- Check console: "WebSocket connected for notifications"
- Create notification: `php artisan tinker` → `create_notification('admin', 1, 'Test', 'Message');`

### Production

1. **Update production `.env`** (already done in `.envprodd`)

2. **Start Reverb server** (use Supervisor/systemd):
```bash
# With Supervisor (recommended)
sudo supervisorctl start reverb:*
```

3. **Configure reverse proxy** (Nginx example):
```nginx
location /app/ {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
}
```

## 📊 Benefits

- ✅ **No more polling** - Eliminates rapid request security alerts
- ✅ **Real-time** - Notifications appear instantly
- ✅ **Lower server load** - Only sends when needed
- ✅ **Better UX** - Browser notifications support

## 🔄 How It Works

**Before**: Frontend polls every 60 seconds → Many requests → Security alerts

**After**: Backend broadcasts → Reverb → Frontend (instant) → No polling needed

## 📝 Files Changed

- `app/Events/NotificationCreated.php` - Broadcasts notifications
- `app/helpers.php` - Broadcasts when creating notifications
- `routes/channels.php` - Private channel authorization
- `resources/js/app.js` - Laravel Echo initialization
- `resources/views/components/notification-icon.blade.php` - WebSocket listener
- `.envprodd` - Reverb configuration

## 🛠️ Troubleshooting

**WebSocket not connecting?**
- Check Reverb server is running: `php artisan reverb:start`
- Check browser console for errors
- Verify environment variables are set

**Notifications not appearing?**
- Check channel authorization in `routes/channels.php`
- Verify user is authenticated
- Check Reverb logs: `tail -f storage/logs/reverb.log`

**Fallback to polling?**
- If WebSocket fails, component automatically uses 2-minute polling
- Check console for WebSocket connection errors

See `WEBSOCKET_SETUP_GUIDE.md` for detailed instructions.

