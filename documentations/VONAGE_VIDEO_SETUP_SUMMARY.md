# Vonage Video Setup - Current Status & Next Steps

## ✅ What's Working

1. **Session Creation**: Successfully using JWT (Application ID + Private Key)
   - Application ID: `87592234-e76c-4c4b-b4fe-401b71d15d45` ✅
   - Private Key: `storage/app/private/private.key` ✅
   - Status: **Working perfectly**

## ⚠️ What Needs Configuration

2. **Token Generation**: Requires OpenTok API Key + Secret
   - Current `VONAGE_API_KEY`: `210c6b53` (likely Vonage SMS API Key, not OpenTok)
   - Current `VONAGE_API_SECRET`: `D6t(Hk%6yn)cCzHq0I` (likely Vonage SMS API Secret, not OpenTok)
   - Status: **Not working** - These appear to be SMS credentials, not Video credentials

## The Problem

Your current `VONAGE_API_KEY` and `VONAGE_API_SECRET` are likely **Vonage SMS/Messages API credentials**, not **OpenTok Video API credentials**. These are different services with different credential sets.

### OpenTok API Key Format
- **Length**: Typically 8-10 digits
- **Format**: Numeric only (e.g., `47592234`, `12345678`)
- **Location**: Vonage Dashboard → Account → API Settings → OpenTok API Credentials

### Your Current API Key
- **Value**: `210c6b53`
- **Length**: 8 characters
- **Format**: Alphanumeric (contains letters)
- **Likely**: Vonage SMS API Key, not OpenTok API Key

## Solution

You need to get your **OpenTok API credentials** from the Vonage Dashboard:

### Step 1: Get OpenTok Credentials

1. Go to [Vonage Dashboard](https://dashboard.nexmo.com/)
2. Navigate to **Account** → **API Settings**
3. Scroll to **OpenTok API Credentials** section
4. Copy:
   - **OpenTok API Key** (numeric, e.g., `47592234`)
   - **OpenTok API Secret** (string value, e.g., `a1b2c3d4e5f6...`)

### Step 2: Update Your .env

Add or update these lines:

```env
# OpenTok Video API Credentials (for token generation)
VONAGE_VIDEO_API_KEY=47592234                    # Your numeric OpenTok API Key
VONAGE_VIDEO_API_SECRET=your_opentok_secret_here # Your OpenTok API Secret (string value)
```

**Important**: 
- `VONAGE_VIDEO_API_KEY` should be **numeric** (not alphanumeric like `210c6b53`)
- `VONAGE_VIDEO_API_SECRET` should be a **string value** (not a file path)

### Step 3: Test

```bash
php artisan vonage:test-all --service=video
```

You should see:
- ✅ Video session created successfully
- ✅ Token generated successfully

## Current Configuration Summary

| Credential | Current Value | Status | Purpose |
|------------|---------------|--------|---------|
| `VONAGE_APPLICATION_ID` | `87592234-e76c-4c4b-b4fe-401b71d15d45` | ✅ Valid | Session creation (JWT) |
| `VONAGE_PRIVATE_KEY_PATH` | `storage/app/private/private.key` | ✅ Valid | Session creation (JWT) |
| `VONAGE_API_KEY` | `210c6b53` | ⚠️ SMS Key | SMS/Messages API (not Video) |
| `VONAGE_API_SECRET` | `D6t(Hk%6yn)cCzHq0I` | ⚠️ SMS Secret | SMS/Messages API (not Video) |
| `VONAGE_VIDEO_API_KEY` | `87592234-e76c-4c4b-b4fe-401b71d15d45` | ❌ Wrong (UUID) | Should be numeric |
| `VONAGE_VIDEO_API_SECRET` | `storage/app/private/private.key` | ❌ Wrong (file path) | Should be string value |

## Why You Need Separate Credentials

- **SMS/Messages API**: Uses `VONAGE_API_KEY` + `VONAGE_API_SECRET` (for sending SMS/WhatsApp)
- **Video API (Sessions)**: Uses `VONAGE_APPLICATION_ID` + `VONAGE_PRIVATE_KEY_PATH` (JWT) ✅
- **Video API (Tokens)**: Needs `VONAGE_VIDEO_API_KEY` + `VONAGE_VIDEO_API_SECRET` (OpenTok) ⚠️

## Next Steps

1. ✅ Session creation is working (using JWT)
2. ⚠️ Get OpenTok API Key and Secret from Vonage Dashboard
3. ⚠️ Update `VONAGE_VIDEO_API_KEY` and `VONAGE_VIDEO_API_SECRET` in `.env`
4. ✅ Test token generation

Once you have the correct OpenTok credentials, video calls will be fully functional!

## Need Help?

- **Vonage Dashboard**: https://dashboard.nexmo.com/
- **OpenTok Credentials**: Account → API Settings → OpenTok API Credentials
- **Documentation**: See `VONAGE_VIDEO_CREDENTIALS_EXPLAINED.md` for detailed explanation

