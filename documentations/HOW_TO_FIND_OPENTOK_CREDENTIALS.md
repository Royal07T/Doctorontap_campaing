# How to Find Your OpenTok API Credentials

## Important: OpenTok Credentials Are Different from Vonage API Credentials

The credentials you're seeing in **Settings → API key** are for:
- ✅ SMS/Messages API
- ✅ Voice API  
- ✅ Other Vonage services

**These are NOT the same as OpenTok Video API credentials.**

## Where to Find OpenTok Credentials

### Method 1: Vonage Dashboard - Account Settings

1. Go to [Vonage Dashboard](https://dashboard.nexmo.com/)
2. Navigate to **Account** → **Settings** (or **API Settings**)
3. Look for a section called:
   - **"OpenTok API Credentials"**
   - **"Video API Credentials"**
   - **"OpenTok Project Credentials"**
4. You should see:
   - **OpenTok API Key**: Numeric value (e.g., `47592234`)
   - **OpenTok API Secret**: String value (e.g., `a1b2c3d4e5f6...`)

### Method 2: Vonage Dashboard - Getting Started

1. Go to [Vonage Dashboard](https://dashboard.nexmo.com/)
2. Navigate to **Getting Started** → **Video**
3. Follow the setup wizard
4. You'll receive OpenTok API Key and Secret

### Method 3: Legacy OpenTok Dashboard

If you have an old OpenTok account (before Vonage acquisition):
1. Login to your OpenTok dashboard
2. Go to **Projects** → Select your project
3. Copy the **API Key** and **API Secret**

### Method 4: Check Your Application

If you created a Vonage Application for Video:
1. Go to **Applications** in Vonage Dashboard
2. Select your Video application
3. Check if there are OpenTok credentials listed

## What You're Currently Seeing

In **Settings → API key**, you have:
- **API Key**: `210c6b53` (Vonage Account API Key - for SMS/Messages)
- **API Secret**: Hidden (Vonage Account API Secret - for SMS/Messages)
- **Signature Secret**: `kZddAYnaDqUDX4EeUXOfIHMHrwzWpAtMXXPIgH9wKKEqVLmagq` (for webhooks)

**These are NOT OpenTok credentials.**

## OpenTok Credentials Format

OpenTok credentials have a different format:

| Credential Type | Format | Example |
|----------------|--------|---------|
| **OpenTok API Key** | Numeric (8-10 digits) | `47592234` |
| **OpenTok API Secret** | Alphanumeric string | `a1b2c3d4e5f6g7h8i9j0` |

## If You Don't Have OpenTok Credentials

If you can't find OpenTok credentials in your dashboard, you may need to:

1. **Create a Video Project**:
   - Go to Vonage Dashboard
   - Navigate to **Video** section
   - Create a new Video project
   - You'll receive OpenTok API Key and Secret

2. **Contact Vonage Support**:
   - They can help you locate or create OpenTok credentials
   - Support: https://support.vonage.com/

3. **Check if Video is Enabled**:
   - Some accounts may need Video API enabled
   - Contact your account manager if needed

## Quick Check: Do You Have OpenTok Credentials?

Look for these in your dashboard:
- ✅ Numeric API Key (8-10 digits, not alphanumeric like `210c6b53`)
- ✅ Separate "OpenTok" or "Video" section
- ✅ "Video API Credentials" or "OpenTok Project" section

If you don't see these, you may need to:
1. Enable Video API on your account
2. Create a Video project
3. Contact Vonage support

## Current Status

- ✅ **Session Creation**: Working (using JWT with Application ID + Private Key)
- ⚠️ **Token Generation**: Needs OpenTok API Key + Secret (different from Account API Key)

## Next Steps

1. Search your Vonage Dashboard for "OpenTok" or "Video API"
2. If found, copy the numeric API Key and API Secret
3. Update your `.env`:
   ```env
   VONAGE_VIDEO_API_KEY=47592234                    # Your numeric OpenTok API Key
   VONAGE_VIDEO_API_SECRET=your_opentok_secret_here # Your OpenTok API Secret
   ```
4. Test: `php artisan vonage:test-all --service=video`

## Still Can't Find Them?

If you've searched everywhere and can't find OpenTok credentials:
- They might not be set up for your account yet
- You may need to enable Video API
- Contact Vonage support for assistance

