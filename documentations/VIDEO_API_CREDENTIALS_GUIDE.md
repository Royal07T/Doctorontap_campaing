# Video API Credentials Guide

## Important: Two Different Authentication Methods

Vonage Video API (OpenTok) has **two different authentication methods**:

### 1. OpenTok Environment (Traditional)
- **Credentials:** Project API Key + Project Secret
- **Format:** 
  - API Key: Numeric string (e.g., `47592234`)
  - API Secret: String value (e.g., `abc123def456...`)
- **Where to get:** Vonage Dashboard → Projects → Your Project → Video API

### 2. Unified Environment (Newer - if supported)
- **Credentials:** Application ID + Private Key (RSA)
- **Format:**
  - Application ID: UUID (e.g., `87592234-e76c-4c4b-b4fe-401b71d15d45`)
  - Private Key: RSA private key in PEM format

## What You Provided

You provided:
- ✅ Public Key (RSA)
- ✅ Private Key (RSA)

These are **JWT authentication credentials** (Application ID + Private Key), which are used for:
- ✅ Messages API (WhatsApp, SMS via Messages API)
- ✅ Voice API
- ✅ Conversations API
- ❓ Video API (depends on SDK version)

## For OpenTok Video API

The **OpenTok PHP SDK** typically requires:
- **Project API Key** (numeric, from Video API dashboard)
- **Project Secret** (string value, from Video API dashboard)

**NOT** Application ID + Private Key.

## How to Get OpenTok Credentials

1. Go to [Vonage Dashboard](https://dashboard.nexmo.com/)
2. Navigate to **Projects** → **Your Project**
3. Click on **Video API** section
4. You'll see:
   - **Project API Key** (numeric, e.g., `47592234`)
   - **Project Secret** (string, e.g., `abc123def456...`)

## Configuration Options

### Option 1: Use OpenTok Credentials (Recommended)

```env
# Video API - OpenTok Environment
VONAGE_VIDEO_ENABLED=true
VONAGE_VIDEO_API_KEY=47592234
VONAGE_VIDEO_API_SECRET=your_project_secret_here
```

### Option 2: Store Private Key for Other Services

If you want to use the private key for other Vonage services (Voice, Messages, etc.):

**Option A: Store in .env (multiline)**
```env
VONAGE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDqHGgipH19aso1\n...\n-----END PRIVATE KEY-----"
```

**Option B: Store in file**
```env
VONAGE_PRIVATE_KEY_PATH=/path/to/private_key.pem
```

Then save the private key to a file:
```bash
# Create secure directory
mkdir -p storage/app/private
chmod 700 storage/app/private

# Save private key
cat > storage/app/private/vonage_private_key.pem << 'EOF'
-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDqHGgipH19aso1
...
-----END PRIVATE KEY-----
EOF

chmod 600 storage/app/private/vonage_private_key.pem
```

## Security Warning ⚠️

**If you shared your real private key publicly, you MUST:**
1. Regenerate it immediately in Vonage Dashboard
2. Update all services using it
3. Never share private keys in logs, code, or public channels

## Testing

Once you have the correct credentials:

```bash
# Test Video API
php artisan vonage:test-all --service=video

# Or use direct test
php test_video_service_direct.php
```

## Summary

- **For Video API:** You need **Project API Key + Project Secret** (not RSA keys)
- **For Other APIs:** You can use **Application ID + Private Key** (RSA keys you provided)
- **Get Video credentials:** Vonage Dashboard → Projects → Video API

