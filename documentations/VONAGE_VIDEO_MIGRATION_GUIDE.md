# Vonage Video API Migration Guide - OpenTok to Vonage Video SDK

## Overview

This guide explains how to migrate from OpenTok SDK to Vonage Video SDK using the unified Vonage PHP SDK.

## Key Differences

### 1. Credentials & Authentication

**OpenTok (Legacy):**
- Uses Project API Key + Project Secret
- Example: `$opentok = new OpenTok($apiKey, $apiSecret);`

**Vonage Video SDK (Unified):**
- **Option 1 (Recommended):** Uses Application ID + Private Key (JWT) for session creation
- **Option 2 (Backward Compatible):** Uses API Key + Secret (Basic credentials) via `Vonage\Client\Credentials\Basic`
- **Hybrid Approach:** JWT for session creation + Basic credentials for token generation

### 2. Client Creation

**Before (OpenTok):**
```php
$opentok = new OpenTok\OpenTok($apiKey, $apiSecret);
```

**After (Vonage Video SDK):**
```php
// JWT Authentication (Recommended)
use Vonage\Client;
use Vonage\Client\Credentials\Keypair;

$credentials = new Keypair($privateKey, $applicationId);
$client = new Client($credentials);
$videoClient = $client->video();

// OR Basic Authentication (Backward Compatible)
use Vonage\Client\Credentials\Basic;

$credentials = new Basic($apiKey, $apiSecret);
$client = new Client($credentials);
$videoClient = $client->video();
```

### 3. Method Signatures & Value Objects

**Before (OpenTok):**
```php
// Used arrays for options
$session = $opentok->createSession(['mediaMode' => OpenTok\MediaMode::ROUTED]);
```

**After (Vonage Video SDK):**
```php
// Uses value objects
use Vonage\Video\SessionOptions;
use Vonage\Video\MediaMode;

$sessionOptions = new SessionOptions([
    'mediaMode' => MediaMode::ROUTED
]);
$session = $videoClient->createSession($sessionOptions);
```

### 4. Token Generation

**Important:** Token generation still requires OpenTok SDK, even when using JWT for session creation.

**Current Implementation:**
- Session creation: Uses Vonage Video SDK with JWT ✅
- Token generation: Uses OpenTok SDK with Basic credentials (API Key + Secret) ⚠️

**Why?**
- The Vonage Video SDK doesn't have a `generateToken()` method yet
- OpenTok SDK is still needed for token generation
- You can use Basic credentials (API Key + Secret) for OpenTok SDK while using JWT for session creation

## Current Setup in This Application

### Configuration

Your application uses a **hybrid approach**:

1. **Session Creation (JWT):**
   ```env
   VONAGE_APPLICATION_ID=87592234-e76c-4c4b-b4fe-401b71d15d45
   VONAGE_PRIVATE_KEY_PATH=storage/app/private/private.key
   ```
   - Uses: `Vonage\Client` with `Keypair` credentials
   - Method: `$client->video()->createSession()`

2. **Token Generation (Basic Credentials):**
   ```env
   VONAGE_VIDEO_API_KEY=47592234          # OpenTok API Key (numeric)
   VONAGE_VIDEO_API_SECRET=your_secret    # OpenTok API Secret (string value)
   ```
   - Uses: `OpenTok\OpenTok` with API Key + Secret
   - Method: `$opentok->generateToken()`

### Why This Hybrid Approach?

- ✅ Session creation works with JWT (modern, unified)
- ✅ Token generation works with OpenTok SDK (required for now)
- ✅ Both can coexist using different credential sets
- ⚠️ Requires two sets of credentials (Application ID/Private Key + API Key/Secret)

## Migration Path

### Step 1: Get Your OpenTok Credentials

1. Go to [Vonage Dashboard](https://dashboard.nexmo.com/)
2. Navigate to **Account** → **API Settings**
3. Find **OpenTok API Credentials** section
4. Copy:
   - **OpenTok API Key** (numeric, e.g., `47592234`)
   - **OpenTok API Secret** (string value)

### Step 2: Update Your .env

```env
# JWT Authentication (for session creation) - Already configured ✅
VONAGE_APPLICATION_ID=87592234-e76c-4c4b-b4fe-401b71d15d45
VONAGE_PRIVATE_KEY_PATH=storage/app/private/private.key

# OpenTok API Credentials (for token generation) - Need to add ⚠️
VONAGE_VIDEO_API_KEY=47592234                    # Your OpenTok API Key (numeric)
VONAGE_VIDEO_API_SECRET=your_opentok_secret     # Your OpenTok API Secret (string, NOT file path)
```

### Step 3: Test

```bash
php artisan vonage:test-all --service=video
```

You should see:
- ✅ Video session created successfully
- ✅ Token generated successfully

## Supported Features

The Vonage Video SDK currently supports:
- ✅ Session creation
- ✅ Signaling
- ✅ Force mute
- ✅ Archiving

## Unsupported Features (Still Require OpenTok)

- ❌ Custom S3/Azure buckets
- ❌ SIP interconnect
- ❌ Live streaming broadcast
- ❌ Experience Composer
- ❌ Account management

## Future Migration

When Vonage Video SDK adds token generation support:
1. Remove OpenTok SDK dependency
2. Use Vonage Video SDK for both session creation and token generation
3. Use only JWT credentials (Application ID + Private Key)

## Code Examples

### Creating a Session (Vonage Video SDK)

```php
use Vonage\Client;
use Vonage\Client\Credentials\Keypair;
use Vonage\Video\SessionOptions;
use Vonage\Video\MediaMode;

$credentials = new Keypair($privateKey, $applicationId);
$client = new Client($credentials);
$videoClient = $client->video();

$sessionOptions = new SessionOptions([
    'mediaMode' => MediaMode::ROUTED
]);

$session = $videoClient->createSession($sessionOptions);
$sessionId = $session->getSessionId();
```

### Generating a Token (OpenTok SDK - Still Required)

```php
use OpenTok\OpenTok;
use OpenTok\Role;

$opentok = new OpenTok($apiKey, $apiSecret);

$token = $opentok->generateToken($sessionId, [
    'role' => Role::PUBLISHER,
    'expireTime' => time() + 3600
]);
```

## Summary

- **Session Creation:** ✅ Using Vonage Video SDK with JWT
- **Token Generation:** ⚠️ Still using OpenTok SDK with Basic credentials
- **Migration Status:** Partial - waiting for Vonage Video SDK token generation support
- **Action Required:** Configure `VONAGE_VIDEO_API_KEY` and `VONAGE_VIDEO_API_SECRET` with correct OpenTok credentials

