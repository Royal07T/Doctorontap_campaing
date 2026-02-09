# Vonage Video API Credentials Guide

## Understanding Video API Credentials

The Vonage Video API uses **two different credential systems**:

### 1. JWT Authentication (Application ID + Private Key)
- **Used for**: Creating video sessions
- **Configuration**:
  - `VONAGE_APPLICATION_ID` - Your Vonage Application ID
  - `VONAGE_PRIVATE_KEY_PATH` or `VONAGE_PRIVATE_KEY` - Private key file or inline key
- **Status**: ✅ Working in your setup

### 2. OpenTok API Key + API Secret
- **Used for**: Generating tokens for participants to join sessions
- **Configuration**:
  - `VONAGE_VIDEO_API_KEY` - OpenTok API Key (numeric, e.g., `47592234`)
  - `VONAGE_VIDEO_API_SECRET` - OpenTok API Secret (string value, NOT a file path)
- **Status**: ⚠️ Needs correct values

## Important: These Are Different!

- **Application ID** ≠ **OpenTok API Key**
- **Private Key** ≠ **OpenTok API Secret**

Even though you're using JWT for session creation, token generation still requires the OpenTok API Key/Secret.

## How to Get Your OpenTok Credentials

### Option 1: From Vonage Dashboard

1. Go to [Vonage Dashboard](https://dashboard.nexmo.com/)
2. Navigate to **Getting Started** → **Video**
3. Or go to **Account** → **API Settings**
4. Look for **OpenTok API Key** and **OpenTok API Secret**
5. These are separate from your Application ID and Private Key

### Option 2: From Your Existing OpenTok Account

If you had an OpenTok account before Vonage acquired it:
- Your OpenTok API Key and Secret are still valid
- They're different from your Vonage Application credentials

## Current Configuration Issue

Your `.env` currently has:
```env
VONAGE_VIDEO_API_KEY=87592234-e76c-4c4b-b4fe-401b71d15d45  # ❌ This is an Application ID, not an API Key
VONAGE_VIDEO_API_SECRET=storage/app/private/private.key    # ❌ This is a file path, not an API Secret
```

## Correct Configuration

Update your `.env` to:
```env
# JWT Authentication (for session creation) - Already working ✅
VONAGE_APPLICATION_ID=87592234-e76c-4c4b-b4fe-401b71d15d45
VONAGE_PRIVATE_KEY_PATH=storage/app/private/private.key

# OpenTok API Credentials (for token generation) - Needs correct values ⚠️
VONAGE_VIDEO_API_KEY=47592234                    # OpenTok API Key (numeric)
VONAGE_VIDEO_API_SECRET=your_opentok_secret_here # OpenTok API Secret (string value)
```

## Finding Your OpenTok API Key/Secret

### Method 1: Vonage Dashboard
1. Login to [Vonage Dashboard](https://dashboard.nexmo.com/)
2. Go to **Account** → **API Settings**
3. Scroll to **OpenTok API Credentials**
4. Copy the **API Key** (numeric) and **API Secret** (string)

### Method 2: Check Your Old OpenTok Account
- If you have an old OpenTok account, the credentials are still there
- Login to your OpenTok dashboard to retrieve them

### Method 3: Create New OpenTok Project
1. In Vonage Dashboard, go to **Video** section
2. Create a new Video project
3. You'll get OpenTok API Key and Secret

## Testing After Configuration

Once you've updated your `.env`:

```bash
php artisan vonage:test-all --service=video
```

You should see:
- ✅ Video session created successfully
- ✅ Token generated successfully

## Alternative: Use JWT for Tokens (Future)

Vonage is working on JWT-based token generation, but currently the OpenTok SDK is required for token generation even when using JWT for session creation.

## Summary

| Credential Type | Purpose | Current Status |
|----------------|---------|----------------|
| Application ID + Private Key | Session Creation | ✅ Working |
| OpenTok API Key + Secret | Token Generation | ⚠️ Needs correct values |

**Action Required**: Get your OpenTok API Key and Secret from Vonage Dashboard and update your `.env` file.

