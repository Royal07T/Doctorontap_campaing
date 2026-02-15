# Pusher WebSocket Setup Guide

## Overview
This application has been configured to use **Pusher** instead of Laravel Reverb for WebSocket connections via Laravel Echo.

## Changes Made

### 1. Frontend Configuration (`resources/js/app.js`)
- ✅ Updated Laravel Echo to use Pusher broadcaster instead of Reverb
- ✅ Changed from Reverb configuration to Pusher configuration
- ✅ Uses Pusher-js library (already installed)

### 2. Backend Configuration (`config/services.php`)
- ✅ Added Pusher configuration section
- ✅ Configured to read from environment variables

## Required Environment Variables

Add these variables to your `.env` file:

```env
# Pusher WebSocket Configuration
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1

# Frontend (Vite) - Must start with VITE_
VITE_PUSHER_APP_KEY=your-app-key
VITE_PUSHER_APP_CLUSTER=mt1

# Broadcasting Driver
BROADCAST_DRIVER=pusher
```

## How to Get Pusher Credentials

1. **Sign up for Pusher** (if you haven't already):
   - Go to https://pusher.com/
   - Create a free account (free tier available)

2. **Create a Channels App**:
   - Go to your Pusher dashboard
   - Click "Create app" or "Channels"
   - Choose a name and cluster (e.g., `mt1` for US East)
   - Select "Laravel" as your framework

3. **Get Your Credentials**:
   - After creating the app, you'll see:
     - **App ID**
     - **Key** (this is your `PUSHER_APP_KEY`)
     - **Secret** (this is your `PUSHER_APP_SECRET`)
     - **Cluster** (e.g., `mt1`, `eu`, `ap-southeast-1`)

4. **Update Your .env File**:
   ```env
   PUSHER_APP_ID=123456
   PUSHER_APP_KEY=abc123def456
   PUSHER_APP_SECRET=xyz789secret
   PUSHER_APP_CLUSTER=mt1
   
   VITE_PUSHER_APP_KEY=abc123def456
   VITE_PUSHER_APP_CLUSTER=mt1
   
   BROADCAST_DRIVER=pusher
   ```

## After Configuration

1. **Rebuild Frontend Assets**:
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

2. **Clear Config Cache** (if in production):
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

3. **Test the Connection**:
   - Open your browser console
   - You should see: `✅ Pusher WebSocket connection initialized`
   - If you see errors, check your credentials

## Features

- ✅ **Real-time Notifications**: WebSocket-based notifications via Pusher
- ✅ **Graceful Degradation**: Falls back to polling if Pusher is not configured
- ✅ **Multi-Guard Support**: Works with all authentication guards (admin, doctor, patient, etc.)
- ✅ **Secure**: Uses encrypted connections (TLS/SSL)

## Troubleshooting

### Connection Fails
- Check that `VITE_PUSHER_APP_KEY` and `VITE_PUSHER_APP_CLUSTER` are set
- Verify your Pusher credentials are correct
- Check browser console for specific error messages
- Ensure `BROADCAST_DRIVER=pusher` is set in `.env`

### Notifications Not Working
- Verify the broadcasting authentication endpoint is accessible: `/broadcasting/auth`
- Check that your user is authenticated
- Verify channels are properly defined in `routes/channels.php`

### WebSocket Errors in Console
- If you see WebSocket connection errors, check:
  1. Pusher credentials are correct
  2. Cluster matches your Pusher app cluster
  3. Frontend assets have been rebuilt after adding environment variables

## Migration from Reverb

If you were previously using Reverb:

1. ✅ **Code Updated**: All Reverb references have been replaced with Pusher
2. ✅ **No Server Required**: Pusher is a hosted service (no need to run a server)
3. ✅ **Remove Reverb Variables**: You can remove `VITE_REVERB_*` variables from `.env`
4. ✅ **Stop Reverb Server**: No need to run `php artisan reverb:start` anymore

## Pusher Free Tier Limits

- **200,000 messages/day**
- **100 concurrent connections**
- **Unlimited channels**

For production with higher traffic, consider upgrading your Pusher plan.

## Support

For Pusher-specific issues:
- Pusher Documentation: https://pusher.com/docs
- Laravel Broadcasting: https://laravel.com/docs/broadcasting

