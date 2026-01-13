# In-App Consultation Architecture

## Overview

This document describes the implementation of in-app consultation support using Vonage API (Video, Voice, and Chat) while maintaining backward compatibility with existing WhatsApp consultations.

## Architecture Principles

1. **Backward Compatibility**: All existing WhatsApp consultations continue to work unchanged
2. **Additive Design**: New functionality is added without modifying existing flows
3. **Security First**: All tokens are encrypted at rest, authorization is enforced at every step
4. **Service Layer Abstraction**: All Vonage API calls go through service classes, never directly from controllers

---

## Phase 1: Data Model Extension

### Database Changes

#### 1. Extended `consultations` Table

**Migration**: `2026_01_13_010944_extend_consultations_table_for_in_app_consultations.php`

**New Fields**:
- `consultation_mode` (ENUM): `whatsapp`, `voice`, `video`, `chat` (default: `whatsapp`)
- `session_status` (ENUM): `scheduled`, `waiting`, `active`, `completed`, `cancelled`
- `started_at` (timestamp): When session started
- `ended_at` (timestamp): When session ended

**Backward Compatibility**:
- Existing consultations with `consult_mode` values (`voice`, `video`, `chat`) are automatically mapped to `consultation_mode`
- Consultations with WhatsApp fields are set to `consultation_mode = 'whatsapp'`
- Legacy `consult_mode` field is preserved for compatibility

#### 2. New `consultation_sessions` Table

**Migration**: `2026_01_13_010945_create_consultation_sessions_table.php`

**Purpose**: Stores Vonage session information for in-app consultations

**Key Fields**:
- `consultation_id` (FK): Links to consultation
- `vonage_session_id`: Vonage Video/Conversation session ID
- `vonage_token_doctor` (encrypted): JWT token for doctor
- `vonage_token_patient` (encrypted): JWT token for patient
- `mode`: `voice`, `video`, or `chat`
- `status`: `pending`, `active`, `ended`, `failed`, `cancelled`
- `token_expires_at`: Token expiration timestamp

**Security**: Tokens are encrypted using Laravel's `Crypt` facade before storage

---

## Phase 2: Service Layer

### Service Classes

#### 1. `VonageVideoService`

**Location**: `app/Services/VonageVideoService.php`

**Responsibilities**:
- Create Vonage Video sessions
- Generate JWT tokens for video participants
- Disconnect participants from sessions

**Configuration**:
- Requires `VONAGE_APPLICATION_ID` and `VONAGE_PRIVATE_KEY_PATH` or `VONAGE_PRIVATE_KEY`
- Enable with `VONAGE_VIDEO_ENABLED=true` in `.env`

**Methods**:
- `createSession()`: Creates a new Vonage Video session
- `generateToken($sessionId, $role, $userName, $expiresIn)`: Generates JWT token for user
- `disconnectParticipant($sessionId, $connectionId)`: Force disconnect a participant

#### 2. `VonageConversationService`

**Location**: `app/Services/VonageConversationService.php`

**Responsibilities**:
- Create Vonage Conversations (for chat)
- Generate JWT tokens for conversation participants
- Add members to conversations

**Configuration**:
- Requires `VONAGE_APPLICATION_ID` and `VONAGE_PRIVATE_KEY_PATH` or `VONAGE_PRIVATE_KEY`
- Enable with `VONAGE_CONVERSATION_ENABLED=true` in `.env`

**Methods**:
- `createConversation($name)`: Creates a new conversation
- `generateToken($conversationId, $userName, $userId, $expiresIn)`: Generates JWT token
- `addMember($conversationId, $userId, $userName)`: Adds member to conversation

#### 3. `ConsultationSessionService`

**Location**: `app/Services/ConsultationSessionService.php`

**Responsibilities**:
- Orchestrate session creation based on consultation mode
- Manage session lifecycle (start, end)
- Handle token generation and storage
- Enforce authorization

**Key Methods**:
- `createSession($consultation)`: Creates session based on mode (voice/video/chat)
- `getSessionToken($session, $userType, $userId)`: Returns token if user is authorized
- `startSession($session)`: Marks session as active
- `endSession($session)`: Marks session as ended

**Security**:
- Verifies doctor assignment before creating session
- Verifies user authorization before returning tokens
- Tokens are encrypted before storage

---

## Phase 3: Controller Extension

### ConsultationSessionController

**Location**: `app/Http/Controllers/ConsultationSessionController.php`

**Endpoints**:

1. **GET `/doctor/consultations/{consultation}/session/token`**
   - Returns Vonage token for doctor to join session
   - **Authorization**: Only assigned doctor can access

2. **GET `/patient/consultations/{consultation}/session/token`**
   - Returns Vonage token for patient to join session
   - **Authorization**: Only consultation owner can access

3. **POST `/doctor|patient/consultations/{consultation}/session/start`**
   - Marks session as active
   - Updates consultation `session_status` to `active`

4. **POST `/doctor|patient/consultations/{consultation}/session/end`**
   - Marks session as ended
   - Updates consultation `session_status` to `completed`

5. **GET `/doctor|patient/consultations/{consultation}/session/status`**
   - Returns current session status

### ConsultationService Extension

**Location**: `app/Services/ConsultationService.php`

**Changes**:
- When creating consultation with `consultation_mode` in `['voice', 'video', 'chat']`:
  - Automatically creates Vonage session if doctor is assigned
  - Session creation failure does not block consultation creation (logged as warning)

---

## Phase 4: Security & Access Control

### Authorization Rules

1. **Doctor Access**:
   - Only doctor assigned to consultation (`consultation.doctor_id === doctor.id`)
   - Verified in `ConsultationSessionController` before token generation

2. **Patient Access**:
   - Only consultation owner (`consultation.patient_id === patient.id` OR `consultation.email === patient.email`)
   - Verified in `ConsultationSessionController` before token generation

3. **Token Security**:
   - Tokens are encrypted using Laravel `Crypt` before storage
   - Tokens are decrypted only when authorized user requests them
   - Tokens expire after 24 hours (configurable)

4. **Session Expiration**:
   - Sessions are marked as ended when consultation completes
   - Expired tokens cannot be used

### Model Updates

#### Consultation Model

**New Methods**:
- `isWhatsAppMode()`: Returns true if consultation uses WhatsApp
- `isInAppMode()`: Returns true if consultation uses voice/video/chat
- `hasActiveSession()`: Returns true if session is active
- `sessions()`: HasMany relationship to ConsultationSession
- `activeSession()`: Returns the active session

#### ConsultationSession Model

**Security Features**:
- `getDoctorToken()`: Decrypts and returns doctor token (only if authorized)
- `getPatientToken()`: Decrypts and returns patient token (only if authorized)
- `setDoctorToken($token)`: Encrypts and stores doctor token
- `setPatientToken($token)`: Encrypts and stores patient token
- `areTokensExpired()`: Checks if tokens have expired

---

## Phase 5: UI Hooks (Placeholder Views)

### Views Created

1. **Waiting Room**: `resources/views/consultation/session/waiting-room.blade.php`
   - Placeholder for waiting room UI
   - Auto-refreshes to check session status
   - Redirects to active consultation when session starts

2. **Active Consultation**: `resources/views/consultation/session/active.blade.php`
   - Placeholder for active consultation UI
   - Contains hooks for Vonage SDK integration
   - Includes end session functionality

**Note**: These are placeholder views. Full UI implementation with Vonage SDK will be done in a separate phase.

---

## Configuration

### Environment Variables

Add to `.env`:

```env
# Vonage Application Credentials (Required for Video/Chat)
VONAGE_APPLICATION_ID=your_application_id
VONAGE_PRIVATE_KEY_PATH=/path/to/private.key
# OR
VONAGE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----"

# Enable Video API
VONAGE_VIDEO_ENABLED=true
VONAGE_VIDEO_LOCATION=us  # Options: us, eu, ap, etc.

# Enable Conversations API (for Chat)
VONAGE_CONVERSATION_ENABLED=true
```

---

## Migration Guide

### Running Migrations

```bash
php artisan migrate
```

**Safe Migration**:
- Existing consultations are automatically migrated
- No data loss
- WhatsApp consultations continue to work

### Backward Compatibility

1. **Existing WhatsApp Consultations**:
   - Continue to work unchanged
   - `consultation_mode` is set to `whatsapp`
   - No Vonage session is created

2. **Legacy `consult_mode` Field**:
   - Preserved for compatibility
   - New `consultation_mode` field is the source of truth
   - Both fields are kept in sync during creation

---

## API Flow

### Consultation Creation Flow

```
1. Patient/Canvasser creates consultation with consultation_mode = 'video'/'voice'/'chat'
   ↓
2. ConsultationService.createConsultation() creates consultation record
   ↓
3. If consultation_mode is in-app AND doctor is assigned:
   ↓
4. ConsultationSessionService.createSession() is called
   ↓
5. VonageVideoService or VonageConversationService creates session
   ↓
6. Tokens are generated for doctor and patient
   ↓
7. ConsultationSession record is created with encrypted tokens
   ↓
8. Consultation.session_status is set to 'scheduled'
```

### Joining Session Flow

```
1. Doctor/Patient requests token: GET /consultations/{id}/session/token
   ↓
2. ConsultationSessionController verifies authorization
   ↓
3. ConsultationSessionService.getSessionToken() is called
   ↓
4. Token is decrypted and returned (if authorized)
   ↓
5. Frontend uses token to initialize Vonage SDK
   ↓
6. User joins session
```

---

## Security Considerations

1. **Token Encryption**: All tokens are encrypted at rest using Laravel's encryption
2. **Authorization**: Every token request verifies user authorization
3. **Token Expiration**: Tokens expire after 24 hours (configurable)
4. **Session Isolation**: Each consultation has its own session
5. **Access Control**: Only assigned doctor and consultation owner can access

---

## Next Steps (Future Implementation)

1. **Frontend Integration**:
   - Integrate Vonage Video SDK for video consultations
   - Integrate Vonage Conversations SDK for chat
   - Implement real-time UI updates

2. **Webhook Handlers**:
   - Handle Vonage session events (participant joined/left)
   - Handle session errors
   - Update session status automatically

3. **Recording**:
   - Optional session recording for video/voice
   - Store recordings securely
   - Patient/doctor access controls

4. **Analytics**:
   - Track session duration
   - Monitor connection quality
   - Usage statistics

---

## Files Created/Modified

### Migrations
- `database/migrations/2026_01_13_010944_extend_consultations_table_for_in_app_consultations.php`
- `database/migrations/2026_01_13_010945_create_consultation_sessions_table.php`

### Models
- `app/Models/ConsultationSession.php` (new)
- `app/Models/Consultation.php` (extended)

### Services
- `app/Services/VonageVideoService.php` (new)
- `app/Services/VonageConversationService.php` (new)
- `app/Services/ConsultationSessionService.php` (new)
- `app/Services/ConsultationService.php` (extended)

### Controllers
- `app/Http/Controllers/ConsultationSessionController.php` (new)

### Routes
- `routes/web.php` (extended with session routes)

### Views
- `resources/views/consultation/session/waiting-room.blade.php` (new)
- `resources/views/consultation/session/active.blade.php` (new)

### Configuration
- `config/services.php` (extended with video/conversation config)

---

## Testing Checklist

- [ ] Run migrations successfully
- [ ] Create consultation with `consultation_mode = 'whatsapp'` (should work as before)
- [ ] Create consultation with `consultation_mode = 'video'` (should create session)
- [ ] Create consultation with `consultation_mode = 'chat'` (should create session)
- [ ] Create consultation with `consultation_mode = 'voice'` (should create session)
- [ ] Doctor can get token for assigned consultation
- [ ] Patient can get token for own consultation
- [ ] Unauthorized users cannot get tokens
- [ ] Tokens are encrypted in database
- [ ] Session can be started
- [ ] Session can be ended
- [ ] Session status is tracked correctly

---

## Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Verify Vonage credentials in `.env`
3. Ensure migrations have run
4. Check authorization in `ConsultationSessionController`

---

**Last Updated**: 2026-01-13
**Version**: 1.0.0

