# Vonage Video Best Practices - Implementation Alignment

## Overview

This document aligns our Vonage Video implementation with the [official Best Practices guide](https://developer.vonage.com/en/video/video-best-practices).

## ✅ What We're Doing Correctly

### 1. Credential Management (Best Practice: Application ID + Private Key)

**Best Practice:**
> "Vonage Video does not use the account API Key and Secret, but relies on using an Vonage Application."

**Our Implementation:**
- ✅ Using Application ID + Private Key for session creation
- ✅ Private key stored securely in `storage/app/private/private.key` (not in public repos)
- ✅ Application ID is public (safe to expose)
- ✅ Private key is never exposed to client applications

**Configuration:**
```env
VONAGE_APPLICATION_ID=87592234-e76c-4c4b-b4fe-401b71d15d45
VONAGE_PRIVATE_KEY_PATH=storage/app/private/private.key
```

### 2. Session Creation (Best Practice: Always Generate New Sessions)

**Best Practice:**
> "Always generate a new `sessionId` for every new session created."

**Our Implementation:**
- ✅ `VideoRoomController::createRoom()` creates a new session for each consultation
- ✅ Sessions are not reused
- ✅ Each consultation gets a unique session ID

**Code:**
```php
$sessionResult = $this->videoService->createSession([
    'archiveMode' => ArchiveMode::MANUAL,
]);
```

### 3. Token Generation (Best Practice: Server-Side, Secured Endpoint)

**Best Practice:**
> "Your server that generates tokens must be placed behind a secured/authenticated endpoint. Always generate new tokens for each participant. Do not store or reuse tokens."

**Our Implementation:**
- ✅ Tokens generated server-side in `VonageVideoService::generateToken()`
- ✅ Endpoint protected by authentication (`VideoRoomController::joinRoom()`)
- ✅ New token generated for each participant
- ✅ Tokens not stored or reused
- ✅ Default expiration: 24 hours (configurable)

**Code:**
```php
$tokenResult = $this->videoService->generateToken(
    $room->vonage_session_id,
    $this->roleForActor($actor),
    'participant',
    3600
);
```

### 4. Media Mode (Best Practice: Routed Mode for Multi-Party)

**Best Practice:**
> "Routed Mode: This media mode uses Vonage media servers. Use when your video sessions will have three or more participants, may have a need to archive, needs media quality control."

**Our Implementation:**
- ✅ Using Routed Mode (default) for consultations
- ✅ Supports archiving (ArchiveMode::MANUAL)
- ✅ Supports multiple participants
- ✅ Media quality control enabled

**Code:**
```php
$sessionOptions = new SessionOptions([
    'mediaMode' => MediaMode::ROUTED  // Default for multi-party
]);
```

## ⚠️ Current Limitation: Token Generation

### The Issue

**Best Practice:**
> "Vonage Video does not use the account API Key and Secret, but relies on using an Vonage Application."

**Current Reality:**
- ✅ Session creation: Uses JWT (Application ID + Private Key) - **Following best practice**
- ⚠️ Token generation: Still requires OpenTok SDK with API Key + Secret - **Temporary limitation**

### Why This Limitation Exists

1. **Vonage Video SDK doesn't have `generateToken()` method yet**
   - The `vonage/video` PHP SDK supports session creation but not token generation
   - Token generation still requires OpenTok SDK

2. **Migration Status**
   - We're in a transition period
   - OpenTok SDK is in maintenance mode
   - Vonage is working on full JWT support for tokens

3. **Workaround**
   - Using Basic credentials (API Key + Secret) for OpenTok SDK
   - This works alongside JWT for session creation
   - According to migration guide: "PHP SDK can still be used with your Vonage API key/secret via Basic credentials for backward-compatible video usage"

### What We Need

To fully align with best practices, we need **OpenTok API credentials** (different from Account API Key):

```env
# OpenTok Video API Credentials (for token generation)
VONAGE_VIDEO_API_KEY=47592234                    # Numeric OpenTok API Key
VONAGE_VIDEO_API_SECRET=your_opentok_secret_here # OpenTok API Secret (string)
```

**Note:** These are different from:
- ❌ Account API Key (`210c6b53`) - For SMS/Messages API
- ❌ Application ID (`87592234-e76c-4c4b-b4fe-401b71d15d45`) - For JWT session creation

## Best Practices We're Following

### ✅ Security
- Private key stored securely (not in public repos)
- Tokens generated server-side
- Endpoints authenticated
- No credentials exposed to clients

### ✅ Session Management
- New session for each consultation
- Sessions not reused
- Proper session lifecycle management

### ✅ Token Management
- New token for each participant
- Tokens not stored or reused
- Configurable expiration
- Server-side generation

### ✅ Media Configuration
- Routed mode for multi-party
- Archiving support
- Media quality control

## Best Practices to Implement (Future)

### 1. Precall Test
**Best Practice:** "Add a precall test where users' device and connection will be subject to network and hardware test prior to joining a session."

**Status:** ⚠️ Not yet implemented
**Resources:**
- [Vonage Precall Test Tool](https://tools.vonage.com/video/precall)
- [Javascript Network Test Package](https://github.com/Vonage/vonage-video-js-api-network-test)

### 2. Audio Fallback UI
**Best Practice:** "It is recommended that such event is displayed on the UI alerting impacted users that the quality of their connection dropped, switching to audio only."

**Status:** ⚠️ Not yet implemented
**Implementation:** Listen for `videoDisableWarning` event

### 3. Reconnection UI
**Best Practice:** "It is recommended that such events are captured and properly displayed to the UI letting the user know that it is attempting to reconnect back to the session."

**Status:** ⚠️ Not yet implemented
**Implementation:** Handle reconnection events

### 4. Active Speaker Detection
**Best Practice:** "For audio only session, try adding an audio level meter so that participants can have a visual of who the current active speaker/s is/are."

**Status:** ⚠️ Not yet implemented
**Implementation:** Use `audioLevelUpdated` event

### 5. Session Monitoring
**Best Practice:** "Session monitoring allows you to register a webhook URL. Use this feature to monitor sessions and streams."

**Status:** ⚠️ Not yet implemented
**Implementation:** Set up webhook for session events

## Summary

| Best Practice | Status | Notes |
|--------------|--------|-------|
| Use Application ID + Private Key | ✅ Implemented | For session creation |
| Generate new sessions | ✅ Implemented | Each consultation gets new session |
| Server-side token generation | ✅ Implemented | Secured endpoint |
| Routed mode for multi-party | ✅ Implemented | Default configuration |
| Secure credential storage | ✅ Implemented | Private key in secure location |
| Token generation with JWT | ⚠️ Pending | Requires OpenTok credentials for now |
| Precall test | ⚠️ Future | Not yet implemented |
| Audio fallback UI | ⚠️ Future | Not yet implemented |
| Reconnection UI | ⚠️ Future | Not yet implemented |

## Next Steps

1. **Immediate:** Get OpenTok API credentials to enable token generation
2. **Short-term:** Implement precall test for better UX
3. **Short-term:** Add audio fallback and reconnection UI indicators
4. **Long-term:** Migrate to full JWT when Vonage Video SDK supports token generation

## References

- [Vonage Video Best Practices](https://developer.vonage.com/en/video/video-best-practices)
- [Token Creation Overview](https://developer.vonage.com/en/video/guides/create-token)
- [Session Creation Overview](https://developer.vonage.com/en/video/guides/create-session)
- [Vonage Video Transition Guide](VONAGE_VIDEO_MIGRATION_GUIDE.md)

