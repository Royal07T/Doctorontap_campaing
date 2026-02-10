# Video API Setup Summary

## ⚠️ Important: Credential Types

You provided **RSA keys** (Public + Private), which are used for **JWT authentication** (Application ID + Private Key).

However, **OpenTok Video API** typically requires:
- **Project API Key** (numeric, e.g., `47592234`)
- **Project Secret** (string value)

## What You Have

✅ **Application ID:** `87592234-e76c-4c4b-b4fe-401b71d15d45`  
✅ **Private Key:** RSA key (stored securely)

**These work for:**
- ✅ Messages API (WhatsApp, SMS)
- ✅ Voice API
- ✅ Conversations API
- ❓ Video API (may not work - needs Project API Key + Secret)

## What You Need for Video API

For **OpenTok Video API**, you need:

1. **Project API Key** - Get from Vonage Dashboard:
   - Go to [Vonage Dashboard](https://dashboard.nexmo.com/)
   - Navigate to **Projects** → **Your Project** → **Video API**
   - Copy the **Project API Key** (numeric)

2. **Project Secret** - Get from same location:
   - Copy the **Project Secret** (string value)

## Configuration Steps

### Step 1: Store Private Key (Already Done)

The private key has been stored securely at:
```
storage/app/private/vonage_private_key.pem
```

### Step 2: Update .env for Other Services

```env
# For Messages API, Voice API, Conversations API
VONAGE_APPLICATION_ID=87592234-e76c-4c4b-b4fe-401b71d15d45
VONAGE_PRIVATE_KEY_PATH=storage/app/private/vonage_private_key.pem
```

### Step 3: Get Video API Credentials

1. Go to [Vonage Dashboard](https://dashboard.nexmo.com/)
2. Navigate to **Projects** → **Your Project** → **Video API**
3. Copy **Project API Key** and **Project Secret**

### Step 4: Configure Video API in .env

```env
# Video API Configuration
VONAGE_VIDEO_ENABLED=true
VONAGE_VIDEO_API_KEY=your_project_api_key_here
VONAGE_VIDEO_API_SECRET=your_project_secret_here
```

**Important:** `VONAGE_VIDEO_API_SECRET` must be the **actual secret value**, NOT a file path.

## Testing

After configuring:

```bash
# Clear config cache
php artisan config:clear

# Test Video API
php artisan vonage:test-all --service=video

# Or direct test
php test_video_service_direct.php
```

## Security Reminder ⚠️

**If you shared your real private key publicly:**
1. ✅ Regenerate it in Vonage Dashboard immediately
2. ✅ Update all services using it
3. ✅ Never share private keys in logs or public channels

## Summary

| Service | Credentials Needed | Status |
|---------|-------------------|--------|
| **Messages API** | Application ID + Private Key | ✅ Ready |
| **Voice API** | Application ID + Private Key | ✅ Ready |
| **Conversations API** | Application ID + Private Key | ✅ Ready |
| **Video API** | Project API Key + Project Secret | ⏳ Need to get from dashboard |

## Next Steps

1. ✅ Private key stored securely
2. ⏳ Get Project API Key + Secret from Vonage Dashboard
3. ⏳ Update `.env` with Video API credentials
4. ⏳ Test Video API service

