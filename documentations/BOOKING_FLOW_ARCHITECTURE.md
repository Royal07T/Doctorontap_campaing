# Booking Flow Architecture - Patient & Doctor Consultation Booking

## Overview

This document describes the enhanced booking flow for authenticated patients and doctors, including doctor availability management, time conflict prevention, and in-app consultation support.

---

## Booking Flow for Authenticated Patients

### Flow Diagram

```
1. Patient browses available doctors
   ↓
2. Patient clicks "Book Appointment" on a doctor
   ↓
3. Booking modal opens with:
   - Date selection (respects doctor availability schedule)
   - Time slot selection (30-minute intervals)
   - Medical information form
   - Consultation mode selection (voice/video/chat)
   ↓
4. Patient selects date → System loads available time slots
   ↓
5. Patient selects time → System checks slot availability in real-time
   ↓
6. Patient fills medical information and selects consultation mode
   ↓
7. Patient submits booking
   ↓
8. System performs conflict check with database locking
   ↓
9. If no conflict:
   - Creates consultation with consultation_mode
   - Creates Vonage session (if in-app mode)
   - Sends notifications to patient and doctor
   - Returns success
   ↓
10. If conflict detected:
   - Returns 409 Conflict error
   - UI refreshes availability
   - Patient selects different time
```

---

## Doctor Availability System

### Availability Schedule Structure

Doctors have an `availability_schedule` JSON field with the following structure:

```json
{
  "monday": {
    "enabled": true,
    "start": "09:00",
    "end": "17:00"
  },
  "tuesday": {
    "enabled": true,
    "start": "09:00",
    "end": "17:00"
  },
  // ... other days
}
```

### Availability Checks

1. **Doctor-Level Availability**:
   - `is_available` = true
   - `is_approved` = true

2. **Day-Level Availability**:
   - Day must be enabled in `availability_schedule`
   - Selected date must match an enabled day

3. **Time-Level Availability**:
   - Selected time must be within doctor's availability window
   - Time slot must not conflict with existing bookings

---

## Time Conflict Prevention

### Multi-Layer Protection

#### 1. **UI-Level Prevention**
- Time slots are marked as "Booked" based on existing consultations
- Real-time availability check when slot is selected
- Visual indicators (red "Booked" badge, disabled buttons)

#### 2. **API-Level Prevention**
- `checkTimeSlotAvailability()` endpoint validates slot before booking
- Uses database transaction with `lockForUpdate()` to prevent race conditions
- Returns 409 Conflict if slot is taken

#### 3. **Database-Level Prevention**
- `createScheduledConsultation()` uses `lockForUpdate()` on consultations
- Checks for conflicts within 30-minute buffer window
- Transaction ensures atomicity

### Conflict Detection Logic

```php
// 30-minute buffer window
$conflict = Consultation::where('doctor_id', $doctor->id)
    ->whereIn('status', ['pending', 'scheduled'])
    ->whereNotNull('scheduled_at')
    ->where('scheduled_at', '>=', now())
    ->whereBetween('scheduled_at', [
        $scheduledAt->copy()->subMinutes(29),
        $scheduledAt->copy()->addMinutes(29)
    ])
    ->lockForUpdate() // Database-level lock
    ->exists();
```

**Why 30-minute buffer?**
- Prevents overlapping consultations
- Allows time for consultation completion
- Ensures doctor has time between appointments

---

## Consultation Mode Handling

### Mode Mapping

When a patient books a consultation:

1. **Input**: `consult_mode` (voice, video, chat)
2. **Mapping**: 
   - `voice` → `consultation_mode = 'voice'` (uses Vonage Video API, audio-only)
   - `video` → `consultation_mode = 'video'` (uses Vonage Video API)
   - `chat` → `consultation_mode = 'chat'` (uses Vonage Conversations API)
3. **Legacy Field**: `consult_mode` is preserved for backward compatibility

### Session Creation

If `consultation_mode` is in-app (voice/video/chat):
- Vonage session is automatically created
- Tokens are generated for doctor and patient
- Session is stored with encrypted tokens
- Session creation failure does not block booking

---

## Database-Level Concurrency Protection

### Locking Strategy

1. **Distributed Lock (Redis/Cache)**:
   - Lock key: `consultation_session_lock:{consultation_id}`
   - Timeout: 10 seconds
   - Prevents multiple session creation attempts

2. **Database Lock (`SELECT ... FOR UPDATE`)**:
   - Locks consultations during conflict check
   - Prevents concurrent bookings for same time slot
   - Released automatically after transaction

### Example Flow with Locking

```
Request 1: Patient A selects 2:00 PM
  ↓
  Acquires distributed lock
  ↓
  Checks database with lockForUpdate()
  ↓
  No conflict found
  ↓
  Creates consultation
  ↓
  Releases lock

Request 2: Patient B selects 2:00 PM (simultaneously)
  ↓
  Tries to acquire distributed lock
  ↓
  Lock already held by Request 1
  ↓
  Waits 100ms, checks for existing session
  ↓
  Finds Request 1's consultation
  ↓
  Returns existing session OR timeout error
```

---

## UI Enhancements

### Real-Time Availability Updates

1. **Refresh Button**: 
   - Allows manual refresh of availability
   - Fetches latest booked slots from server
   - Updates time slot display

2. **Visual Indicators**:
   - ✅ Green dot for selected slot
   - ❌ Red "Booked" badge for unavailable slots
   - 🔄 Refresh button for manual updates
   - ⚠️ Info message about real-time conflict prevention

3. **Conflict Handling**:
   - On 409 Conflict: Shows error message
   - Automatically refreshes availability
   - Clears selected time
   - Prompts user to select different time

### Consultation Mode Selection

- Visual icons for each mode (voice, video, chat)
- Clear indication that all modes use in-app consultation
- No WhatsApp requirement for in-app modes

---

## API Endpoints

### Patient Booking Endpoints

1. **GET `/patient/doctors/{id}/availability`**
   - Returns doctor availability schedule
   - Returns booked time slots
   - Used to populate time slot selector

2. **POST `/patient/doctors/check-slot`**
   - Validates time slot availability
   - Uses database locking
   - Returns availability status

3. **POST `/patient/doctors/book`**
   - Creates scheduled consultation
   - Uses database locking for conflict prevention
   - Creates Vonage session if in-app mode
   - Returns consultation details

---

## Error Handling

### Conflict Scenarios

1. **Time Slot Conflict (409 Conflict)**:
   ```json
   {
     "success": false,
     "message": "This time slot was just booked by another patient. Please select a different time.",
     "error": "time_slot_conflict"
   }
   ```

2. **Doctor Unavailable**:
   ```json
   {
     "success": false,
     "message": "Doctor is not available for booking"
   }
   ```

3. **Day Not Available**:
   ```json
   {
     "success": false,
     "message": "Doctor is not available on this day"
   }
   ```

### UI Response to Errors

- **409 Conflict**: Refresh availability, show error, clear selection
- **400 Bad Request**: Show validation errors
- **500 Server Error**: Show generic error, suggest retry

---

## Structured Logging

All booking events are logged with structured data:

### Log Events

1. **`consultation_scheduled`**: Successful booking
2. **`consultation_booking_failed`**: Booking failure
3. **`time_slot_conflict`**: Conflict detected
4. **`time_slot_conflict_check`**: Conflict check performed
5. **`session_creation_failed_booking`**: Session creation failure during booking

### Log Structure

```json
{
  "event_type": "consultation_scheduled",
  "consultation_id": 123,
  "consultation_reference": "CONS-ABC123",
  "doctor_id": 45,
  "patient_id": 67,
  "scheduled_at": "2026-01-15T14:00:00Z",
  "consultation_mode": "video",
  "timestamp": "2026-01-13T10:30:00Z"
}
```

---

## Multi-Patient Booking Flow

### BookingService Integration

Multi-patient bookings also support in-app consultation modes:

1. **Consultation Mode Mapping**:
   - Maps `consult_mode` to `consultation_mode` enum
   - Sets both legacy and new fields

2. **Session Creation**:
   - Creates Vonage session for each consultation if in-app mode
   - Handles failures gracefully (doesn't block booking)

3. **Conflict Prevention**:
   - Each consultation in booking follows same conflict rules
   - Database locking prevents concurrent modifications

---

## Security & Data Integrity

### Protection Mechanisms

1. **Authorization**: Only authenticated patients can book
2. **Validation**: All inputs validated before processing
3. **Database Transactions**: Atomic operations prevent partial states
4. **Locking**: Prevents race conditions and duplicate bookings
5. **Structured Logging**: Audit trail for all booking events

### Data Consistency

- Consultation creation is atomic (all-or-nothing)
- Session creation failures don't block consultation creation
- Availability checks use consistent time windows
- All time comparisons use server timezone

---

## Testing Checklist

- [ ] Patient can view available doctors
- [ ] Doctor availability schedule is respected
- [ ] Time slots show correctly based on schedule
- [ ] Booked slots are marked as unavailable
- [ ] Real-time conflict checking works
- [ ] Database locking prevents concurrent bookings
- [ ] 409 Conflict error handled gracefully in UI
- [ ] Consultation mode is set correctly
- [ ] Vonage session created for in-app modes
- [ ] Notifications sent to patient and doctor
- [ ] Multi-patient bookings work with in-app modes
- [ ] UI refreshes availability after conflicts

---

## Files Modified

1. **`app/Http/Controllers/Patient/DashboardController.php`**:
   - Enhanced `createScheduledConsultation()` with database locking
   - Added `consultation_mode` mapping
   - Added Vonage session creation
   - Enhanced conflict detection
   - Added structured logging

2. **`app/Services/BookingService.php`**:
   - Added `consultation_mode` mapping for multi-patient bookings
   - Added Vonage session creation for in-app modes

3. **`resources/views/patient/doctors.blade.php`**:
   - Enhanced time slot display with visual indicators
   - Added refresh button for availability
   - Improved conflict handling in UI
   - Enhanced consultation mode selection UI
   - Added real-time availability updates

---

## Backward Compatibility

- ✅ All existing WhatsApp consultations remain unaffected
- ✅ Legacy `consult_mode` field preserved
- ✅ Existing booking flows continue to work
- ✅ No breaking changes to existing endpoints
- ✅ All changes are additive

---

**Last Updated**: 2026-01-13
**Version**: 1.0.0

