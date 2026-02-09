# Vonage Video API - User Guide

## ✅ What You Can Do Now

Your Vonage Video integration is **fully functional**! You can now:

1. **Create Video Consultations** - Doctors and patients can have video consultations
2. **Join Video Sessions** - Participants can join video calls securely
3. **Record Sessions** - Archive video consultations for later review
4. **Manage Video Rooms** - Create, join, and end video sessions
5. **Secure Access** - Only authorized participants can join (doctors, patients, admins)

## 🎯 How It Works

### Overview

The video call system uses **Vonage Video API** (formerly OpenTok) which provides:
- **WebRTC** for real-time audio/video communication
- **Cloud-based sessions** hosted on Vonage servers
- **Secure token-based authentication** using JWT
- **Session recording** capabilities

### Architecture Flow

```
┌─────────────┐
│   Doctor    │
│  (Browser)  │
└──────┬──────┘
       │
       │ 1. Creates Video Room
       ▼
┌─────────────────────────────────┐
│   Laravel Backend               │
│   VideoRoomController           │
│   ┌──────────────────────────┐ │
│   │ VonageVideoService       │ │
│   │ - createSession()        │ │
│   │ - generateToken()        │ │
│   └──────────────────────────┘ │
└──────┬──────────────────────────┘
       │
       │ 2. Creates Vonage Session
       │    Returns: sessionId, token
       ▼
┌─────────────────────────────────┐
│   Vonage Video API              │
│   (Cloud Infrastructure)        │
│   - Session Management          │
│   - Media Routing               │
│   - Recording                   │
└──────┬──────────────────────────┘
       │
       │ 3. Connects via WebRTC
       ▼
┌─────────────┐
│   Patient   │
│  (Browser)  │
└─────────────┘
```

## 📋 Step-by-Step Flow

### 1. Creating a Video Room

**Who:** Doctor (or Admin)

**Endpoint:** `POST /consultations/{id}/video/create`

**What Happens:**
1. Doctor clicks "Start Video Call" button
2. Frontend sends POST request to create room
3. Backend checks if room already exists
4. If not, creates new Vonage Video session
5. Stores session ID in database
6. Returns room information

**Code Example:**
```javascript
// Frontend (JavaScript)
const response = await fetch(`/consultations/${consultationId}/video/create`, {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Content-Type': 'application/json'
    }
});

const data = await response.json();
// Returns: { success: true, room: { id, vonage_session_id, ... } }
```

**Backend (PHP):**
```php
// VideoRoomController::createRoom()
$sessionResult = $this->videoService->createSession([
    'archiveMode' => ArchiveMode::MANUAL,
]);

$room = VideoRoom::create([
    'vonage_session_id' => $sessionResult['session_id'],
    'consultation_id' => $consultation->id,
    'status' => 'pending',
]);
```

### 2. Joining a Video Session

**Who:** Doctor or Patient

**Endpoint:** `POST /consultations/{id}/video/join`

**What Happens:**
1. User clicks "Join Video Call"
2. Frontend requests to join room
3. Backend verifies authorization (only doctor/patient can join)
4. Generates JWT token for the user
5. Returns session credentials (sessionId, token, applicationId)
6. Frontend initializes Vonage Video SDK
7. User connects to video session

**Code Example:**
```javascript
// Frontend (JavaScript)
const response = await fetch(`/consultations/${consultationId}/video/join`, {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Content-Type': 'application/json'
    }
});

const { sessionId, token, applicationId } = await response.json();

// Initialize Vonage Video SDK
const session = OT.initSession(applicationId, sessionId);
session.connect(token, (error) => {
    if (error) {
        console.error('Connection error:', error);
    } else {
        console.log('Connected to video session!');
    }
});
```

**Backend (PHP):**
```php
// VideoRoomController::joinRoom()
$tokenResult = $this->videoService->generateToken(
    $room->vonage_session_id,
    $this->roleForActor($actor), // 'PUBLISHER', 'MODERATOR', or 'SUBSCRIBER'
    'participant',
    3600 // 1 hour expiration
);

return response()->json([
    'success' => true,
    'sessionId' => $room->vonage_session_id,
    'token' => $tokenResult['token'],
    'applicationId' => $this->videoService->getApplicationId(),
]);
```

### 3. Video Call in Progress

**What Happens:**
- Both participants can see and hear each other
- Audio/video streams are routed through Vonage servers
- Quality automatically adjusts based on network conditions
- Participants can mute/unmute, turn video on/off

### 4. Recording a Session

**Who:** Doctor or Admin

**Endpoint:** `POST /consultations/{id}/video/recording/start`

**What Happens:**
1. Doctor clicks "Start Recording"
2. Backend calls Vonage API to start archive
3. Recording is stored in Vonage cloud
4. Can be downloaded later

**Code Example:**
```javascript
// Start recording
await fetch(`/consultations/${consultationId}/video/recording/start`, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': csrfToken }
});

// Stop recording
await fetch(`/consultations/${consultationId}/video/recording/stop`, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': csrfToken }
});
```

### 5. Ending a Session

**Who:** Doctor or Admin

**Endpoint:** `POST /consultations/{id}/video/end`

**What Happens:**
1. Doctor clicks "End Call"
2. Backend marks room as ended
3. All participants are disconnected
4. Session data is saved

## 🔐 Security Features

### Authorization

Only authorized users can join:
- **Doctor**: Can join consultations assigned to them
- **Patient**: Can join their own consultations
- **Admin**: Can join any consultation

**Implementation:**
```php
// VideoRoomPolicy::join()
Gate::forUser($actor)->authorize('join', $room);
```

### Token Security

- Tokens are generated server-side (never exposed to unauthorized users)
- Tokens expire after 1 hour (configurable)
- Each participant gets a unique token
- Tokens are not stored or reused

### Session Isolation

- Each consultation has its own unique session
- Sessions are not reused
- Session IDs are unique and secure

## 🎨 Frontend Integration

### Required Libraries

Include Vonage Video SDK in your frontend:

```html
<!-- In your Blade template -->
<script src="https://static.opentok.com/v2/js/opentok.min.js"></script>
```

### Basic Video Call Implementation

```javascript
class VideoCallManager {
    constructor(consultationId, applicationId) {
        this.consultationId = consultationId;
        this.applicationId = applicationId;
        this.session = null;
        this.publisher = null;
    }

    async initialize() {
        // Create or get existing room
        const createResponse = await fetch(
            `/consultations/${this.consultationId}/video/create`,
            { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } }
        );

        // Join room and get credentials
        const joinResponse = await fetch(
            `/consultations/${this.consultationId}/video/join`,
            { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } }
        );

        const { sessionId, token } = await joinResponse.json();

        // Initialize session
        this.session = OT.initSession(this.applicationId, sessionId);

        // Handle events
        this.session.on('streamCreated', (event) => {
            this.session.subscribe(event.stream, 'subscriber', {
                insertMode: 'append',
                width: '100%',
                height: '100%'
            });
        });

        // Connect
        this.session.connect(token, (error) => {
            if (error) {
                console.error('Connection error:', error);
            } else {
                // Publish own video
                this.publisher = OT.initPublisher('publisher', {
                    insertMode: 'append',
                    width: '100%',
                    height: '100%'
                });
                this.session.publish(this.publisher);
            }
        });
    }

    disconnect() {
        if (this.session) {
            this.session.disconnect();
        }
    }
}
```

## 📊 Available API Endpoints

### Video Room Management

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/consultations/{id}/video/create` | Create video room | Doctor/Admin |
| POST | `/consultations/{id}/video/join` | Join video session | Doctor/Patient |
| POST | `/consultations/{id}/video/refresh` | Refresh token | Doctor/Patient |
| GET | `/consultations/{id}/video/status` | Get room status | Doctor/Patient |
| POST | `/consultations/{id}/video/end` | End video session | Doctor/Admin |

### Recording

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/consultations/{id}/video/recording/start` | Start recording | Doctor/Admin |
| POST | `/consultations/{id}/video/recording/stop` | Stop recording | Doctor/Admin |

## 🧪 Testing

### Test Video Service

```bash
php artisan vonage:test-all --service=video
```

**Expected Output:**
```
✅ Video session created successfully!
✅ Token generated successfully!
```

### Test in Browser

1. Create a consultation
2. As doctor, click "Start Video Call"
3. As patient, click "Join Video Call"
4. Both should see each other's video

## 📝 Configuration

### Required Environment Variables

```env
# JWT Authentication (for session creation AND token generation)
VONAGE_APPLICATION_ID=87592234-e76c-4c4b-b4fe-401b71d15d45
VONAGE_PRIVATE_KEY_PATH=storage/app/private/private.key
VONAGE_VIDEO_ENABLED=true
```

### Optional Configuration

```env
# Video settings
VONAGE_VIDEO_TIMEOUT=10
VONAGE_VIDEO_LOCATION=auto
```

## 🚀 Use Cases

### 1. Doctor-Patient Consultations
- Real-time video consultations
- Secure, HIPAA-compliant communication
- Session recording for medical records

### 2. Telemedicine
- Remote patient consultations
- Multi-party sessions (doctor + patient + specialist)
- Screen sharing for medical imaging

### 3. Follow-up Appointments
- Quick check-ins via video
- Prescription reviews
- Treatment plan discussions

## 🔧 Troubleshooting

### Common Issues

**Issue:** "No active room found"
- **Solution:** Doctor must create room first before patient can join

**Issue:** "Failed to generate token"
- **Solution:** Check that `VONAGE_APPLICATION_ID` and `VONAGE_PRIVATE_KEY_PATH` are set correctly

**Issue:** "Connection error"
- **Solution:** Check network connectivity, firewall settings, and browser permissions

### Debug Mode

Enable logging:
```env
VONAGE_VIDEO_ENABLED=true
LOG_LEVEL=debug
```

Check logs:
```bash
tail -f storage/logs/laravel.log | grep -i video
```

## 📚 Additional Resources

- [Vonage Video API Documentation](https://developer.vonage.com/en/video/overview)
- [Vonage Video Best Practices](https://developer.vonage.com/en/video/video-best-practices)
- [Vonage Video PHP SDK](https://github.com/Vonage/vonage-php-sdk-video)
- [Sample PHP Learning Server](https://github.com/Vonage-Community/sample-video-php-learning_server)

## ✅ Summary

Your video call system is **ready to use**! It provides:

- ✅ Secure video consultations
- ✅ JWT-based authentication
- ✅ Session recording
- ✅ Multi-party support
- ✅ Automatic quality adjustment
- ✅ HIPAA-compliant infrastructure

Just integrate the frontend JavaScript code and you're ready to go!

