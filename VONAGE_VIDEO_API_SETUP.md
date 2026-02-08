# Vonage Video API Setup Guide

## Overview

Vonage Video API (OpenTok) supports two authentication environments:

1. **OpenTok Environment** (Legacy) - Uses Project API Key and Project Secret
2. **Unified Environment** (New) - Uses Application ID and Private Key

This application currently uses the **OpenTok Environment** with API Key and API Secret.

---

## Configuration

### Step 1: Get Your Video API Credentials

1. Go to [Vonage Dashboard](https://dashboard.nexmo.com/)
2. Navigate to **Projects** → **Your Project** → **Video API**
3. Copy your **Project API Key** and **Project Secret**

**Important:** These are different from:
- Your main Vonage API Key/Secret (used for SMS/WhatsApp)
- Your Application ID/Private Key (used for Messages API)

### Step 2: Configure Environment Variables

Add to your `.env` file:

```env
# Video API Configuration (OpenTok Environment)
VONAGE_VIDEO_ENABLED=true
VONAGE_VIDEO_API_KEY=your_project_api_key_here
VONAGE_VIDEO_API_SECRET=your_project_secret_here

# Optional: Video API Settings
VONAGE_VIDEO_LOCATION=us  # Options: us, eu, ap (data center location)
VONAGE_VIDEO_TIMEOUT=30   # Timeout in seconds
```

**⚠️ CRITICAL:** 
- `VONAGE_VIDEO_API_SECRET` must be the **actual secret value**, NOT a file path
- Example: `VONAGE_VIDEO_API_SECRET=abc123def456...` ✅
- Wrong: `VONAGE_VIDEO_API_SECRET=storage/app/private/secret.txt` ❌

---

## How It Works

### Backend (Laravel)

The backend is responsible for:

1. **Creating Sessions** - Using `VonageVideoService::createSession()`
2. **Generating Tokens** - Using `VonageVideoService::generateToken()`
3. **Returning Credentials** - Via API endpoints

#### Example: Creating a Session

```php
use App\Services\VonageVideoService;

$videoService = new VonageVideoService();

// Create a new video session
$result = $videoService->createSession([
    'mediaMode' => \OpenTok\MediaMode::ROUTED,  // Required for archiving
    'archiveMode' => \OpenTok\ArchiveMode::MANUAL,
]);

if ($result['success']) {
    $sessionId = $result['session_id'];
    // Store sessionId in database
}
```

#### Example: Generating a Token

```php
// Generate token for a user to join the session
$tokenResult = $videoService->generateToken(
    sessionId: $sessionId,
    role: \OpenTok\Role::PUBLISHER,  // or MODERATOR, SUBSCRIBER
    userName: 'John Doe',
    expiresIn: 3600  // 1 hour
);

if ($tokenResult['success']) {
    $token = $tokenResult['token'];
    // Return to frontend
}
```

### API Endpoints

The application provides these endpoints:

#### 1. Create Video Room
```
POST /consultations/{consultation}/video/room
```

**Response:**
```json
{
  "success": true,
  "room": {
    "id": 1,
    "uuid": "room-uuid",
    "status": "pending",
    "vonage_session_id": "2_MX40NzU5MjIzNC..."
  }
}
```

#### 2. Join Video Room (Get Credentials)
```
POST /consultations/{consultation}/video/join
```

**Response:**
```json
{
  "success": true,
  "api_key": "47592234",
  "session_id": "2_MX40NzU5MjIzNC...",
  "token": "T1==cGFydG5lcl9pZD00NzU5MjIzNC...",
  "room": {
    "id": 1,
    "uuid": "room-uuid",
    "status": "active"
  }
}
```

#### 3. Refresh Token
```
POST /consultations/{consultation}/video/refresh-token
```

**Response:**
```json
{
  "success": true,
  "api_key": "47592234",
  "session_id": "2_MX40NzU5MjIzNC...",
  "token": "T1==cGFydG5lcl9pZD00NzU5MjIzNC..."
}
```

---

## Frontend Integration

### Step 1: Load OpenTok.js SDK

Add to your Blade template or HTML:

```html
<script src="https://static.opentok.com/v2/js/opentok.min.js"></script>
```

### Step 2: Fetch Credentials from Backend

```javascript
// Fetch session credentials from Laravel backend
async function initializeVideo() {
    try {
        const response = await fetch('/consultations/123/video/join', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            const { api_key, session_id, token } = data;
            connectToSession(api_key, session_id, token);
        }
    } catch (error) {
        console.error('Failed to get video credentials:', error);
    }
}
```

### Step 3: Initialize and Connect

```javascript
function connectToSession(apiKey, sessionId, token) {
    // Initialize OpenTok session
    const session = OT.initSession(apiKey, sessionId);
    
    // Handle session events
    session.on('sessionConnected', () => {
        console.log('Connected to video session');
    });
    
    session.on('sessionDisconnected', () => {
        console.log('Disconnected from video session');
    });
    
    session.on('streamCreated', (event) => {
        // Subscribe to remote stream
        session.subscribe(event.stream, 'subscriber', {
            insertMode: 'append',
            width: '100%',
            height: '100%'
        }, (error) => {
            if (error) {
                console.error('Error subscribing:', error);
            }
        });
    });
    
    // Initialize publisher (local video)
    const publisher = OT.initPublisher('publisher', {
        insertMode: 'append',
        width: '100%',
        height: '100%',
        videoSource: null  // Use default camera
    }, (error) => {
        if (error) {
            console.error('Error initializing publisher:', error);
        }
    });
    
    // Connect to session
    session.connect(token, (error) => {
        if (error) {
            console.error('Error connecting:', error);
        } else {
            // Publish local stream
            session.publish(publisher, (error) => {
                if (error) {
                    console.error('Error publishing:', error);
                }
            });
        }
    });
}
```

### Complete Example

```html
<!DOCTYPE html>
<html>
<head>
    <title>Video Consultation</title>
    <script src="https://static.opentok.com/v2/js/opentok.min.js"></script>
</head>
<body>
    <div id="publisher"></div>
    <div id="subscriber"></div>
    
    <script>
        async function startVideo() {
            // 1. Get credentials from backend
            const response = await fetch('/consultations/123/video/join', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const { api_key, session_id, token } = await response.json();
            
            // 2. Initialize session
            const session = OT.initSession(api_key, session_id);
            
            // 3. Handle remote streams
            session.on('streamCreated', (event) => {
                session.subscribe(event.stream, 'subscriber', {
                    insertMode: 'append',
                    width: '100%',
                    height: '100%'
                });
            });
            
            // 4. Initialize publisher
            const publisher = OT.initPublisher('publisher', {
                insertMode: 'append',
                width: '100%',
                height: '100%'
            });
            
            // 5. Connect and publish
            session.connect(token, (error) => {
                if (error) {
                    console.error('Connection error:', error);
                } else {
                    session.publish(publisher);
                }
            });
        }
        
        // Start video when page loads
        startVideo();
    </script>
</body>
</html>
```

---

## Testing

### Test Video Service

```bash
# Test video service configuration
php artisan vonage:test-all --service=video

# Or use the diagnostic script
php test_vonage_video_diagnostic.php
```

### Test Session Creation

```php
use App\Services\VonageVideoService;

$videoService = new VonageVideoService();

// Test session creation
$result = $videoService->createSession();

if ($result['success']) {
    echo "Session ID: " . $result['session_id'] . "\n";
    
    // Test token generation
    $tokenResult = $videoService->generateToken($result['session_id']);
    if ($tokenResult['success']) {
        echo "Token: " . $tokenResult['token'] . "\n";
    }
}
```

---

## Troubleshooting

### Error: "Private key file does not exist or is not readable"

**Problem:** `VONAGE_VIDEO_API_SECRET` is set to a file path instead of the actual secret value.

**Solution:** Update `.env`:
```env
# Wrong ❌
VONAGE_VIDEO_API_SECRET=storage/app/private/secret.txt

# Correct ✅
VONAGE_VIDEO_API_SECRET=your_actual_secret_value_here
```

### Error: "OpenTok credentials not configured"

**Problem:** Video API credentials are missing or incorrect.

**Solution:**
1. Verify `VONAGE_VIDEO_ENABLED=true` in `.env`
2. Verify `VONAGE_VIDEO_API_KEY` and `VONAGE_VIDEO_API_SECRET` are set
3. Clear config cache: `php artisan config:clear`

### Error: "Failed to create video session"

**Problem:** API credentials are invalid or network issue.

**Solution:**
1. Verify credentials in Vonage Dashboard
2. Check network connectivity
3. Verify API key and secret are correct (not Application ID/Private Key)

---

## Key Differences

| Environment | Credentials | Use Case |
|------------|-------------|----------|
| **OpenTok** | Project API Key + Project Secret | Video API only |
| **Unified** | Application ID + Private Key | Messages API, Video API, etc. |

**Current Implementation:** Uses OpenTok environment (API Key + Secret)

---

## Additional Resources

- [Vonage Video API Documentation](https://developer.vonage.com/video/overview)
- [OpenTok PHP SDK](https://github.com/opentok/Opentok-PHP-SDK)
- [OpenTok.js Client SDK](https://tokbox.com/developer/sdks/js/)
- [Video API Getting Started](https://developer.vonage.com/video/getting-started)

---

## Summary

1. ✅ Get Project API Key and Secret from Vonage Dashboard
2. ✅ Set `VONAGE_VIDEO_API_KEY` and `VONAGE_VIDEO_API_SECRET` in `.env` (actual values, not file paths)
3. ✅ Backend creates sessions and generates tokens
4. ✅ Frontend fetches credentials and uses OpenTok.js to connect
5. ✅ Test with `php artisan vonage:test-all --service=video`

