# Vonage Video API Credentials - Complete Explanation

## Understanding the Two Credential Systems

Vonage Video API uses **two different credential systems** that serve different purposes:

### 1. JWT Authentication (Application ID + Private Key)
- **Purpose**: Creating video sessions
- **Used by**: Vonage Video SDK (`vonage/video`)
- **Format**:
  - Application ID: UUID format (e.g., `87592234-e76c-4c4b-b4fe-401b71d15d45`)
  - Private Key: RSA private key file or inline string
- **Status in your setup**: ✅ Working correctly

### 2. Basic Authentication (API Key + API Secret)
- **Purpose**: Generating tokens for participants to join sessions
- **Used by**: OpenTok SDK (still required for token generation)
- **Format**:
  - API Key: Numeric (e.g., `47592234`)
  - API Secret: String value (e.g., `a1b2c3d4e5f6g7h8i9j0`)
- **Status in your setup**: ⚠️ Needs correct values

## Why Both Are Needed

According to the [Vonage Video Transition Guide](VONAGE_VIDEO_MIGRATION_GUIDE.md):

> "The PHP SDK can still be used with your Vonage API key/secret via Basic credentials for backward-compatible video usage."

**Current Implementation:**
- **Session Creation**: Uses Vonage Video SDK with JWT (Application ID + Private Key) ✅
- **Token Generation**: Uses OpenTok SDK with Basic credentials (API Key + Secret) ⚠️

**Why?**
- The Vonage Video SDK doesn't have a `generateToken()` method yet
- Token generation still requires OpenTok SDK
- You can use Basic credentials (API Key + Secret) for OpenTok SDK while using JWT for session creation

## Your Current Configuration

### What You Have (from .env):
```env
# JWT Credentials (for session creation) - ✅ Correct
VONAGE_APPLICATION_ID=87592234-e76c-4c4b-b4fe-401b71d15d45
VONAGE_PRIVATE_KEY_PATH=storage/app/private/private.key

# OpenTok Credentials (for token generation) - ❌ Incorrect
VONAGE_VIDEO_API_KEY=87592234-e76c-4c4b-b4fe-401b71d15d45  # This is an Application ID (UUID), not an API Key
VONAGE_VIDEO_API_SECRET=storage/app/private/private.key      # This is a file path, not an API Secret
```

### What You Need:

```env
# JWT Credentials (for session creation) - ✅ Keep as is
VONAGE_APPLICATION_ID=87592234-e76c-4c4b-b4fe-401b71d15d45
VONAGE_PRIVATE_KEY_PATH=storage/app/private/private.key

# OpenTok Credentials (for token generation) - ⚠️ Update these
VONAGE_VIDEO_API_KEY=47592234                    # OpenTok API Key (numeric, NOT UUID)
VONAGE_VIDEO_API_SECRET=your_opentok_secret_here # OpenTok API Secret (string value, NOT file path)
```

## How to Get Your OpenTok Credentials

### Method 1: Vonage Dashboard (Recommended)

1. Go to [Vonage Dashboard](https://dashboard.nexmo.com/)
2. Navigate to **Account** → **API Settings**
3. Scroll to **OpenTok API Credentials** section
4. You'll see:
   - **OpenTok API Key**: Numeric value (e.g., `47592234`)
   - **OpenTok API Secret**: String value (e.g., `a1b2c3d4e5f6...`)

### Method 2: Legacy OpenTok Dashboard

If you have an old OpenTok account:
1. Login to your OpenTok dashboard
2. Go to **Projects** → Select your project
3. Copy the **API Key** and **API Secret**

### Method 3: Vonage Getting Started

1. Go to [Vonage Dashboard](https://dashboard.nexmo.com/)
2. Navigate to **Getting Started** → **Video**
3. Follow the setup wizard
4. You'll receive OpenTok API Key and Secret

## Key Differences

| Credential Type | Format | Example | Purpose |
|----------------|--------|---------|---------|
| **Application ID** | UUID | `87592234-e76c-4c4b-b4fe-401b71d15d45` | Session creation (JWT) |
| **Private Key** | RSA Key | `-----BEGIN PRIVATE KEY-----...` | Session creation (JWT) |
| **OpenTok API Key** | Numeric | `47592234` | Token generation (Basic) |
| **OpenTok API Secret** | String | `a1b2c3d4e5f6g7h8` | Token generation (Basic) |

## Common Mistakes

### ❌ Mistake 1: Using Application ID as API Key
```env
VONAGE_VIDEO_API_KEY=87592234-e76c-4c4b-b4fe-401b71d15d45  # Wrong - this is a UUID
```
**Fix**: Use numeric OpenTok API Key (e.g., `47592234`)

### ❌ Mistake 2: Using Private Key File Path as API Secret
```env
VONAGE_VIDEO_API_SECRET=storage/app/private/private.key  # Wrong - this is a file path
```
**Fix**: Use actual OpenTok API Secret string value

### ❌ Mistake 3: Using Private Key Content as API Secret
```env
VONAGE_VIDEO_API_SECRET="-----BEGIN PRIVATE KEY-----..."  # Wrong - this is a private key
```
**Fix**: Use OpenTok API Secret (different from private key)

## Verification

After updating your `.env`, test with:

```bash
php artisan vonage:test-all --service=video
```

You should see:
- ✅ Video session created successfully
- ✅ Token generated successfully

## Migration Status

| Component | Status | Credentials Used |
|-----------|--------|------------------|
| Session Creation | ✅ Working | JWT (Application ID + Private Key) |
| Token Generation | ⚠️ Needs Fix | Basic (API Key + Secret) |

## Summary

- **You have**: Application ID + Private Key (for JWT) ✅
- **You need**: OpenTok API Key (numeric) + OpenTok API Secret (string) ⚠️
- **Get them from**: Vonage Dashboard → Account → API Settings → OpenTok API Credentials
- **Update**: `VONAGE_VIDEO_API_KEY` and `VONAGE_VIDEO_API_SECRET` in your `.env`

Once you have the correct OpenTok credentials, video calls will be fully functional!

