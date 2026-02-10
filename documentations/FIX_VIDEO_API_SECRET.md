# Fix Video API Secret Configuration

## Current Issue

Your `.env` file has:
```env
VONAGE_VIDEO_API_SECRET=storage/app/private/private.key
```

**This is wrong!** OpenTok Video API needs the **actual secret value**, not a file path.

## The Problem

OpenTok Video API uses:
- **Project API Key** (numeric, e.g., `87592234`)
- **Project Secret** (string value, e.g., `abc123def456...`)

It does **NOT** use:
- ❌ File paths
- ❌ Private key files (RSA keys)
- ❌ Application ID + Private Key (that's for other APIs)

## How to Fix

### Option 1: Get Project Secret from Vonage Dashboard (Recommended)

1. Go to [Vonage Dashboard](https://dashboard.nexmo.com/)
2. Navigate to **Projects** → **Your Project** → **Video API**
3. Find **Project Secret** (it's a string value, not a file)
4. Copy the entire secret value
5. Update your `.env`:

```env
# Change from:
VONAGE_VIDEO_API_SECRET=storage/app/private/private.key

# To:
VONAGE_VIDEO_API_SECRET=your_actual_project_secret_string_here
```

**Example:**
```env
VONAGE_VIDEO_API_SECRET=abc123def456ghi789jkl012mno345pqr678stu901vwx234yz
```

### Option 2: Check if File Contains the Secret

If `storage/app/private/private.key` actually contains the secret value (not a private key), you can:

```bash
# Read the file content
cat storage/app/private/private.key

# Then copy that value to .env
# VONAGE_VIDEO_API_SECRET=<paste_the_value_here>
```

## Important Notes

### Different Credentials for Different Services

| Service | Credentials Type | What You Have |
|---------|-----------------|---------------|
| **SMS** | API Key + Secret | ✅ Working |
| **WhatsApp** | Application ID + Private Key | ✅ Working |
| **Voice** | Application ID + Private Key | ✅ Working |
| **Video** | Project API Key + **Project Secret** | ❌ Needs actual secret value |

### Your Current Setup

✅ **Working:**
- SMS: Using API Key + Secret
- WhatsApp: Using Application ID + Private Key file
- Voice: Using Application ID + Private Key file

❌ **Not Working:**
- Video: API Secret is set to file path (needs actual value)

## After Fixing

1. Update `.env` with actual secret value
2. Clear config cache:
   ```bash
   php artisan config:clear
   ```
3. Test Video API:
   ```bash
   php artisan vonage:test-all --service=video
   ```

## Summary

- ✅ All your other services are configured correctly
- ❌ Video API secret needs to be the actual value, not a file path
- 🔧 Get Project Secret from Vonage Dashboard → Projects → Video API
- 📝 Update `VONAGE_VIDEO_API_SECRET` in `.env` with the actual value

