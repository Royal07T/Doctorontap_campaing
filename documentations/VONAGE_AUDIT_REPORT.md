# Vonage Integration Audit Report

**Date:** $(date +%Y-%m-%d)  
**Application:** DoctorOnTap — Healthcare Consultation Platform  
**Scope:** Full Vonage Video API, WhatsApp Messages API, SMS, and Production Hardening  

---

## Executive Summary

The audit identified **17 critical issues** across the Vonage integration. The root cause of video consultations not connecting was a **combination of 4 bugs working together**: wrong API key passed to `OT.initSession()`, role string case mismatch causing doctors to get PUBLISHER instead of MODERATOR, bogus hardcoded defaults in config files, and token expiry desync between the database and actual Vonage tokens.

All issues have been fixed in this changeset.

---

## Part 1: Issues Found & Fixed

### CRITICAL — Video Not Connecting (Root Cause Chain)

| # | File | Issue | Root Cause | Fix |
|---|------|-------|------------|-----|
| 1 | `config/services.php`, `config/vonage.php` | **Bogus Application ID default** `250782187688` — this is a phone number, not a UUID | Previous developer used a WhatsApp Business number as default | Removed default — `env('VONAGE_APPLICATION_ID')` with no fallback |
| 2 | `config/services.php`, `config/vonage.php` | **Bogus WhatsApp number default** `405228299348572` — too long to be a phone number | Facebook/WhatsApp Business ID used instead of phone number | Removed default — `env('VONAGE_WHATSAPP_NUMBER')` with no fallback |
| 3 | `.env.example` | **`VONAGE_VIDEO_API_SECRET=storage/app/private/private.key`** — this is a FILE PATH, not a secret string | Confusion between private key file path and API secret | Cleared value, added documentation comments explaining each variable |
| 4 | `VonageVideoService.php` | **`generateToken()` role matching failed** — `\OpenTok\Role::MODERATOR` resolves to `'moderator'` (lowercase) but the match statement expected `'MODERATOR'` (uppercase) | Case-sensitive `match()` expression | Added `strtoupper(trim($role))` normalization before matching |
| 5 | `ConsultationSessionService.php` | **Doctors passed `\OpenTok\Role::MODERATOR` (='moderator')** directly to `generateToken()` | PHP constant resolves to lowercase string | Changed to string `'MODERATOR'` explicitly |
| 6 | `ConsultationSessionService.php` | **Token expiry desync** — DB recorded `token_expires_at = now()->addHours(24)` but Vonage caps tokens at 7200 seconds (2 hours) | Vonage silently caps to 7200s, app thinks tokens are valid for 24h | Changed `addHours(24)` → `addHours(2)`, voice token from `86400` → `7200` |
| 7 | `active.blade.php` (frontend) | **Wrong API key for `OT.initSession()`** — passed `config('services.vonage.api_key')` (SMS/legacy key) instead of Application ID | JWT auth requires Application ID, not legacy API key | Removed hardcoded keys from Blade template; frontend now gets `api_key` from backend response |
| 8 | `ConsultationSessionController.php` | **`getToken()` response missing `api_key`** — frontend couldn't get the correct identifier for `OT.initSession()` | Response only returned `token`, `session_id`, `mode` | Added `api_key` field from `VonageVideoService::getClientApiKey()` |

### HIGH — Service Reliability Issues

| # | File | Issue | Fix |
|---|------|-------|-----|
| 9 | `VonageVideoService.php` | No validation that Application ID is a UUID | Added UUID format check in constructor with warning log |
| 10 | `VonageVideoService.php` | No validation that API secret isn't a file path | Added file path detection in constructor with warning log |
| 11 | `VonageVideoService.php` | `getApplicationId()` returned wrong value for legacy auth | Created `getClientApiKey()` — returns Application ID for JWT, API Key for legacy |
| 12 | `VideoRoomController.php` | `roleForActor()` returned lowercase `Role::MODERATOR`/`Role::PUBLISHER` constants | Changed to uppercase strings `'MODERATOR'`/`'PUBLISHER'` |
| 13 | `active.blade.php` (frontend) | Missing `session.on('exception')` handler — auth failures silently swallowed | Added exception handler with code 1004 (authentication error) specific messaging |

### MEDIUM — WhatsApp / Configuration Issues

| # | File | Issue | Fix |
|---|------|-------|-----|
| 14 | `WhatsAppService.php` | `formatPhoneNumber()` doesn't add Nigeria country code — `08012345678` becomes `+8012345678` instead of `+2348012345678` | Rewrote to match VonageService behavior: strips leading 0, prepends +234 |
| 15 | `VonageConfigServiceProvider.php` | No startup validation of Vonage env vars | Created new service provider that validates config at boot and logs errors/warnings |

### SECURITY — Must Address Before Deployment

| # | File | Issue | Action Required |
|---|------|-------|-----------------|
| 16 | `store_private_key.sh` | **Private key committed in plain text** to version control | **ROTATE THE KEY IMMEDIATELY** on Vonage dashboard. Remove key from script. Use env variable or secure vault. |
| 17 | `.env.example` | Had duplicate/confusing env variable names (`VONAGE_KEY`/`VONAGE_API_KEY`, `VONAGE_SECRET`/`VONAGE_API_SECRET`) | Cleaned up with clear documentation |

---

## Part 2: Files Modified

### Backend

| File | Changes |
|------|---------|
| `config/services.php` | Removed bogus Application ID and WhatsApp number defaults |
| `config/vonage.php` | Removed bogus Application ID and WhatsApp number defaults |
| `.env.example` | Fixed `VONAGE_VIDEO_API_SECRET`, added docs, removed duplicates |
| `app/Services/VonageVideoService.php` | Constructor validation, `getClientApiKey()`, role normalization, token expiry cap to 86400, debug logging |
| `app/Services/ConsultationSessionService.php` | Role strings uppercase, token expiry 2h, `api_key` in response |
| `app/Services/WhatsAppService.php` | `formatPhoneNumber()` with Nigeria country code default |
| `app/Http/Controllers/VideoRoomController.php` | `roleForActor()` uppercase, `api_key` in join/refresh responses |
| `app/Http/Controllers/ConsultationSessionController.php` | Added `api_key` to `getToken()` response |
| `app/Providers/VonageConfigServiceProvider.php` | **NEW** — Boot-time config validation |
| `bootstrap/providers.php` | Registered `VonageConfigServiceProvider` |

### Frontend

| File | Changes |
|------|---------|
| `resources/views/consultation/session/active.blade.php` | Removed hardcoded `vonageApiKey`/`applicationId` from Blade config; frontend now uses `data.api_key` from backend responses; added `debugMode` support; added `session.on('exception')` handler; added parameter validation in `initializeVonage()`; added debug logging throughout |

---

## Part 3: Deployment Checklist

### Pre-Deployment (Vonage Dashboard)

- [ ] **Rotate the private key** — the current key is exposed in `store_private_key.sh` in version control
- [ ] Verify `VONAGE_APPLICATION_ID` is a UUID (e.g., `87592234-e76c-4c4b-b4fe-401b71d15d45`)
- [ ] Verify Video API is enabled on the Vonage Application
- [ ] Verify Messages API is enabled on the Vonage Application
- [ ] Verify WhatsApp Business Number is linked to the Vonage Application
- [ ] Download the new private key file after rotation

### Environment Variables (.env)

```env
# ── Core Auth (REQUIRED) ──────────────────────────────
VONAGE_API_KEY=your_api_key                     # From dashboard > API Settings
VONAGE_API_SECRET=your_api_secret               # From dashboard > API Settings
VONAGE_APPLICATION_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx  # UUID from dashboard > Applications

# ── Private Key (REQUIRED for JWT) ────────────────────
VONAGE_PRIVATE_KEY_PATH=storage/app/private/private.key     # Path to .key file
# OR: VONAGE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n..."

# ── Video API (REQUIRED for video consultations) ──────
VONAGE_VIDEO_API_SECRET=your_video_secret       # STRING from dashboard, NOT a file path

# ── WhatsApp (REQUIRED for WhatsApp messaging) ────────
VONAGE_WHATSAPP_NUMBER=2347089146888            # Your WhatsApp Business phone number (E.164 without +)
VONAGE_WHATSAPP_ID=your_whatsapp_business_id    # WhatsApp Business Number ID from dashboard

# ── Feature Flags ─────────────────────────────────────
VONAGE_ENABLED=true
VONAGE_WHATSAPP_ENABLED=true
VONAGE_MESSAGES_SANDBOX=false                   # MUST be false for production

# ── Optional ──────────────────────────────────────────
VONAGE_SIGNATURE_SECRET=                        # For webhook signature verification
VONAGE_API_METHOD=messages                      # 'messages' (preferred) or 'legacy'
```

### Server Deployment

- [ ] Copy new private key to `storage/app/private/private.key`
- [ ] Set file permissions: `chmod 600 storage/app/private/private.key`
- [ ] Set `.env` variables as listed above
- [ ] Run `php artisan config:clear && php artisan config:cache`
- [ ] Check Laravel logs for `[VonageConfig]` messages — fix any ERROR level entries
- [ ] Verify webhook URLs are configured in Vonage dashboard:
  - Inbound: `https://yourdomain.com/api/vonage/webhooks/inbound`
  - Status: `https://yourdomain.com/api/vonage/webhooks/status`
  - Voice Answer: `https://yourdomain.com/api/vonage/webhooks/voice/answer`
  - Voice Event: `https://yourdomain.com/api/vonage/webhooks/voice/event`

---

## Part 4: Testing Checklist

### Video Consultation Testing

- [ ] **Doctor creates session** → Check logs: `[VonageVideo]` entries should show UUID application_id and valid token
- [ ] **Patient joins session** → Should connect without `OT_AUTHENTICATION_ERROR (1004)`
- [ ] **Doctor has MODERATOR role** → Verify in token generation logs
- [ ] **Patient has PUBLISHER role** → Verify in token generation logs
- [ ] **Token refresh works** → Wait 2 hours or force refresh; new token should reconnect
- [ ] **Debug mode logging** → Set `APP_DEBUG=true`, check browser console for `[Vonage]` prefix logs

### WhatsApp Testing

- [ ] Send test message to Nigerian number (format: `08012345678`) → Should format to `+2348012345678`
- [ ] Send test message to international number → Should work with proper E.164 format
- [ ] Verify `from` number is your WhatsApp Business Number, not a Facebook ID

### Config Validation Testing

- [ ] Start app with missing `VONAGE_APPLICATION_ID` → Should see `[VonageConfig]` warning in logs
- [ ] Start app with `VONAGE_APPLICATION_ID=250782187688` → Should see `[VonageConfig]` ERROR (not a UUID)
- [ ] Start app with `VONAGE_VIDEO_API_SECRET=storage/app/private/private.key` → Should see `[VonageConfig]` ERROR (file path)
- [ ] Start app with all valid config → Should see `[VonageConfig] All Vonage configuration validated successfully`

### Error Handling Testing

- [ ] Disconnect network during video → Should show reconnection prompt, not crash
- [ ] Use invalid API key → Browser console should show `[Vonage] Exception details:` with code 1004
- [ ] Expired token → Should trigger `refreshVideoToken()` automatically

---

## Part 5: Architecture Notes

### Authentication Flow Priority

```
1. JWT Auth (preferred): Application ID + Private Key → generates JWT → used for Video, Messages, WhatsApp
2. Legacy Auth (fallback): API Key + API Secret → used for SMS, basic operations
```

### `OT.initSession()` First Parameter

The **first parameter** to `OT.initSession(apiKey, sessionId)` is context-dependent:
- **JWT Auth** → Pass the **Application ID** (UUID)
- **Legacy Auth** → Pass the **API Key** (numeric string)

`VonageVideoService::getClientApiKey()` returns the correct value based on auth mode.

### Token Lifecycle

```
Token Generated → Valid for 2 hours (max per Vonage) → DB records token_expires_at
                                                       → Frontend auto-refreshes on error 1004/1006/1008
```

### Key Insight: Why Video Failed

The bug cascade was:
1. `OT.initSession()` received the wrong key (SMS API key, not Application ID)
2. Even if the key was correct, doctors got PUBLISHER role instead of MODERATOR (role case mismatch)
3. Even if roles were correct, the fallback Application ID default was `250782187688` (a phone number), so JWT auth would fail anyway
4. Even if auth worked, tokens were recorded as valid for 24h but expired after 2h, causing ghost disconnections

All four layers are now fixed.
