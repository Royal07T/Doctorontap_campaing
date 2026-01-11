# Reverb WebSocket Server - Quick Fix

## Problem
WebSocket connection to `ws://localhost:8080` is failing because the Reverb server is not running.

## Solution

### Option 1: Run Reverb in a Separate Terminal (Recommended for Development)

Open a new terminal and run:

```bash
cd "/home/royal-t/doctorontap campain"
php artisan reverb:start
```

This will start the Reverb server on port 8080. Keep this terminal open while developing.

### Option 2: Run Reverb in Background

```bash
php artisan reverb:start > /dev/null 2>&1 &
```

### Option 3: Use Supervisor (Production)

For production, set up Supervisor to keep Reverb running:

```ini
[program:reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan reverb:start
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/reverb.log
stopwaitsecs=3600
```

## Verify It's Working

1. Check if port 8080 is listening:
   ```bash
   lsof -i :8080
   # or
   netstat -tuln | grep 8080
   ```

2. Check the browser console - the WebSocket connection should succeed

3. Check Reverb logs in `storage/logs/laravel.log`

## Environment Variables

Make sure these are set in your `.env` file:

```env
REVERB_APP_ID=your_app_id
REVERB_APP_KEY=your_app_key
REVERB_APP_SECRET=your_app_secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# For Vite to pick up
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

## After Starting Reverb

1. **Rebuild your frontend assets** (if you changed env vars):
   ```bash
   npm run build
   # or
   npm run dev
   ```

2. **Refresh your browser** - the WebSocket connection should now work

## Troubleshooting

### Port Already in Use
If port 8080 is already in use:
```bash
# Find what's using it
lsof -i :8080

# Or use a different port
php artisan reverb:start --port=8081
```

Then update `.env`:
```env
REVERB_PORT=8081
VITE_REVERB_PORT=8081
```

### Connection Still Failing
1. Check firewall settings
2. Verify REVERB_* environment variables are set
3. Check browser console for specific error messages
4. Verify the Reverb server is actually running: `ps aux | grep reverb`

### For Production
- Use a process manager like Supervisor or systemd
- Consider using a reverse proxy (nginx) for WebSocket connections
- Use HTTPS/WSS instead of HTTP/WS

