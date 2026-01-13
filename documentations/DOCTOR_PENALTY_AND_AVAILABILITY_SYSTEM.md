# Doctor Penalty & Availability Management System

**Version:** 1.0.0  
**Last Updated:** January 13, 2026  
**Status:** Production Ready

---

## Table of Contents

1. [Overview](#overview)
2. [Doctor Penalty System](#doctor-penalty-system)
3. [Availability Management](#availability-management)
4. [Communication Method Lock](#communication-method-lock)
5. [Admin Controls](#admin-controls)
6. [Database Schema](#database-schema)
7. [API Endpoints](#api-endpoints)
8. [Service Classes](#service-classes)
9. [Scheduled Tasks](#scheduled-tasks)
10. [UI Components](#ui-components)
11. [Security & Access Control](#security--access-control)
12. [Troubleshooting](#troubleshooting)

---

## Overview

This system implements automatic penalty management for doctors who miss consultations, ensuring accountability and maintaining service quality. The system tracks missed consultations, automatically applies penalties when thresholds are reached, and provides admin-only controls for penalty management.

### Key Features

- **Automatic Penalty System**: Doctors are automatically set to unavailable after missing 3 consultations
- **Missed Consultation Tracking**: Real-time tracking of missed consultations per doctor
- **Admin Notifications**: Automatic notifications to all admins when penalties are applied
- **Admin-Only Reset**: Only administrators can reset penalties and restore doctor availability
- **Communication Method Lock**: Patients select consultation mode; doctors cannot change it
- **Audit Trail**: Complete logging of all penalty actions

---

## Doctor Penalty System

### How It Works

1. **Missed Consultation Detection**
   - System checks consultations that are:
     - Scheduled in the past (`scheduled_at < now()`)
     - Status is still `scheduled` or `pending` (not completed)
     - Doctor has not joined/started the session
   - For in-app consultations: Session status is not `active` or `completed`
   - For WhatsApp consultations: Status is not `completed`

2. **Penalty Threshold**
   - **Threshold**: 3 missed consultations
   - When threshold is reached:
     - Doctor is automatically set to `is_available = false`
     - `is_auto_unavailable` flag is set to `true`
     - `unavailable_reason` is populated with penalty details
     - `penalty_applied_at` timestamp is recorded
     - All admins receive notifications

3. **Penalty Reset**
   - **Only admins can reset penalties**
   - Resetting a penalty:
     - Clears `missed_consultations_count`
     - Resets `is_auto_unavailable` to `false`
     - Clears `unavailable_reason`
     - Sets doctor back to `is_available = true`
     - `penalty_applied_at` is kept for audit trail

### Missed Consultation Criteria

A consultation is considered "missed" when **ALL** of the following are true:

- Consultation has a `scheduled_at` timestamp
- `scheduled_at` is in the past (consultation time has passed)
- Consultation `status` is `scheduled` or `pending` (not `completed` or `cancelled`)
- Doctor has not joined/started the consultation:
  - **In-app consultations** (`voice`, `video`, `chat`): `session_status` is not `active` or `completed`
  - **WhatsApp consultations**: `status` is not `completed`

---

## Availability Management

### Doctor Controls

**Normal State (Not Penalized):**
- Doctors can toggle their availability on/off
- Can update their weekly schedule
- Changes take effect immediately

**Penalized State (Auto-Unavailable):**
- **Availability toggle is DISABLED**
- Doctors **CANNOT** set themselves to available
- Can still update weekly schedule (but remains unavailable)
- Clear messaging: "Please contact an administrator to resolve this issue"

### Admin Controls

**Viewing Penalties:**
- Doctors list shows visual indicators:
  - Red "Penalty Applied" badge for auto-unavailable doctors
  - Yellow warning badge for doctors with missed consultations (but not yet penalized)
- Doctor profile page shows detailed penalty information

**Resetting Penalties:**
- Admin-only action via doctor profile page
- Requires confirmation before reset
- Resets missed count and sets doctor to available
- Full audit trail logged

---

## Communication Method Lock

### Patient Selection

- Patients select consultation mode during booking:
  - Voice (audio-only WebRTC)
  - Video (video WebRTC)
  - Chat (text-based)
  - WhatsApp (legacy)

### Doctor Restrictions

- **Doctors CANNOT change consultation mode**
- Mode is locked after patient selection
- All doctor update methods explicitly exclude `consultation_mode`
- UI clearly shows "(Selected by Patient)" label
- Clear messaging: "This mode was selected by the patient during booking and cannot be changed"

### Enforcement Points

1. **Controller Level**: All doctor update methods exclude `consultation_mode` from allowed fields
2. **Model Level**: `consultation_mode` is in `$fillable` but protected by controller validation
3. **UI Level**: Visual indicators and disabled fields prevent changes

---

## Admin Controls

### Notification System

**When Penalty is Applied:**
- All admins receive in-app notification (bell icon)
- Notification includes:
  - Doctor name and ID
  - Number of missed consultations
  - Threshold information
  - Direct link to doctor profile
- Notification type: `warning` (red indicator)

**Viewing Notifications:**
- Click notification bell icon in admin header
- See unread count badge
- Click notification to go directly to doctor profile
- Mark notifications as read

### Doctor Management

**Doctors List Page (`/admin/doctors`):**
- Statistics card: "With Penalties" showing count
- Visual indicators on doctor cards:
  - Red badge: "Penalty Applied" (auto-unavailable)
  - Yellow badge: "X Missed" (approaching threshold)
- Detailed penalty info in expanded cards

**Doctor Profile Page (`/admin/doctors/{id}/profile`):**
- Dedicated "Missed Consultations & Penalty" section
- Shows:
  - Penalty status
  - Missed count
  - Threshold information
  - Penalty applied date
  - Last missed consultation date
- **"Reset Penalty & Set to Available" button** (admin only)

---

## Database Schema

### New Fields in `doctors` Table

```sql
missed_consultations_count INT DEFAULT 0
    COMMENT 'Number of missed consultations (resets after penalty is applied)';

last_missed_consultation_at TIMESTAMP NULL
    COMMENT 'Timestamp of the last missed consultation';

penalty_applied_at TIMESTAMP NULL
    COMMENT 'Timestamp when penalty was last applied (auto-unavailable)';

unavailable_reason TEXT NULL
    COMMENT 'Reason for being unavailable (e.g., "Auto-set unavailable due to 3 missed consultations")';

is_auto_unavailable BOOLEAN DEFAULT FALSE
    COMMENT 'Flag indicating if doctor was auto-set to unavailable due to penalties';
```

### Migration

**File:** `database/migrations/2026_01_13_053926_add_missed_consultation_tracking_to_doctors_table.php`

**Run Migration:**
```bash
php artisan migrate
```

---

## API Endpoints

### Admin Endpoints

#### Reset Doctor Penalty

**Endpoint:** `POST /admin/doctors/{id}/reset-penalty`

**Authentication:** Admin only

**Request:**
```json
{
  // No body required - uses CSRF token
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Doctor {name} has been set back to available. Penalty has been reset.",
  "doctor": {
    "id": 123,
    "name": "Dr. John Doe",
    "is_available": true,
    "is_auto_unavailable": false,
    "missed_consultations_count": 0
  }
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "This doctor is not under penalty. No action needed."
}
```

**Status Codes:**
- `200`: Success
- `400`: Doctor not under penalty
- `401`: Unauthorized
- `403`: Forbidden (not admin)
- `500`: Server error

---

## Service Classes

### DoctorPenaltyService

**Location:** `app/Services/DoctorPenaltyService.php`

**Key Methods:**

#### `checkMissedConsultations(Doctor $doctor): array`

Checks and tracks missed consultations for a doctor.

**Returns:**
```php
[
    'success' => true,
    'missed_count' => 3,
    'penalty_applied' => true,
    'message' => 'Penalty applied: Doctor set to unavailable due to 3 missed consultations'
]
```

#### `applyPenalty(Doctor $doctor, int $missedCount): bool`

Applies penalty: Sets doctor to unavailable and notifies admins.

**Actions:**
- Sets `is_available = false`
- Sets `is_auto_unavailable = true`
- Sets `penalty_applied_at = now()`
- Sets `unavailable_reason`
- Sends notifications to all admins
- Logs action

#### `resetMissedCount(Doctor $doctor): bool`

Resets missed consultations count (called by admin).

**Actions:**
- Resets `missed_consultations_count = 0`
- Clears `last_missed_consultation_at`
- Sets `is_auto_unavailable = false`
- Clears `unavailable_reason`
- Keeps `penalty_applied_at` for audit trail

#### `checkAllDoctors(): array`

Batch check for all doctors (used by scheduled task).

**Returns:**
```php
[
    'total_doctors' => 50,
    'doctors_checked' => 50,
    'doctors_with_missed' => 5,
    'penalties_applied' => 2,
    'errors' => []
]
```

#### `markConsultationAsMissed(Consultation $consultation): bool`

Marks a specific consultation as missed and increments count.

---

## Scheduled Tasks

### Check Missed Consultations

**Command:** `consultations:check-missed`

**Schedule:** Runs every hour

**Configuration:** `bootstrap/app.php`

```php
$schedule->command('consultations:check-missed')
    ->hourly()
    ->withoutOverlapping();
```

**What It Does:**
1. Checks all approved doctors
2. Identifies missed consultations
3. Updates missed counts
4. Applies penalties when threshold reached
5. Sends admin notifications
6. Logs summary

**Manual Execution:**
```bash
php artisan consultations:check-missed
```

**Output:**
```
Checking for missed consultations...
Checked 50 doctors
Found 5 doctors with missed consultations
Applied 2 penalties
```

---

## UI Components

### Doctor Availability Page

**File:** `resources/views/doctor/availability.blade.php`

**Features:**
- Availability toggle (disabled when auto-unavailable)
- Warning messages for missed consultations
- Penalty information display
- Schedule management

**States:**
1. **Normal**: Toggle enabled, can change availability
2. **Warning**: Yellow alert showing missed count (not yet penalized)
3. **Penalized**: Red alert, toggle disabled, "Contact admin" message

### Admin Doctor Profile Page

**File:** `resources/views/admin/doctor-profile.blade.php`

**New Section:** "Missed Consultations & Penalty"

**Features:**
- Penalty status display
- Missed consultations count
- Threshold information
- Penalty applied date
- Last missed consultation date
- **Reset Penalty button** (admin only)

### Admin Doctors List Page

**File:** `resources/views/admin/doctors.blade.php`

**Features:**
- Statistics card: "With Penalties"
- Visual badges on doctor cards:
  - Red: "Penalty Applied"
  - Yellow: "X Missed"
- Detailed penalty info in expanded cards

### Consultation Lifecycle Banner

**File:** `resources/views/consultation/partials/lifecycle-banner.blade.php`

**Features:**
- Shows consultation mode
- For doctors: "(Selected by Patient)" label
- Clear indication that mode cannot be changed

---

## Security & Access Control

### Doctor Restrictions

1. **Cannot Set Availability When Penalized**
   - Controller validates `is_auto_unavailable` flag
   - Returns error if doctor tries to set availability
   - UI toggle is disabled

2. **Cannot Change Consultation Mode**
   - All update methods exclude `consultation_mode`
   - Validation prevents mode changes
   - UI shows read-only indicators

### Admin-Only Actions

1. **Penalty Reset**
   - Route protected by `admin.auth` middleware
   - Controller verifies admin authentication
   - Validates doctor is actually penalized
   - Full audit logging

2. **Viewing Penalty Information**
   - Admin-only routes
   - Sensitive information only visible to admins

### Audit Trail

All penalty actions are logged with:
- Admin/Doctor ID and name
- Timestamp
- Action type (applied/reset)
- Missed count
- Previous state

**Log Locations:**
- Laravel logs: `storage/logs/laravel.log`
- Database: `penalty_applied_at` timestamp
- Notification data: Stored in notifications table

---

## Troubleshooting

### Doctor Cannot Set Availability

**Symptom:** Doctor sees disabled toggle or error message

**Check:**
1. Verify `is_auto_unavailable` flag: `SELECT is_auto_unavailable FROM doctors WHERE id = ?`
2. Check missed count: `SELECT missed_consultations_count FROM doctors WHERE id = ?`
3. Review penalty applied date: `SELECT penalty_applied_at FROM doctors WHERE id = ?`

**Solution:**
- Admin must reset penalty via doctor profile page
- Doctor should contact admin for assistance

### Penalty Not Applied

**Symptom:** Doctor has 3+ missed consultations but not penalized

**Check:**
1. Verify scheduled task is running: `php artisan schedule:list`
2. Check missed consultation detection logic
3. Review consultation statuses: `SELECT status, session_status, scheduled_at FROM consultations WHERE doctor_id = ?`

**Solution:**
- Run manual check: `php artisan consultations:check-missed`
- Verify consultation criteria matches missed definition
- Check logs for errors

### Admin Cannot Reset Penalty

**Symptom:** Reset button doesn't work or returns error

**Check:**
1. Verify admin authentication
2. Check doctor is actually penalized: `SELECT is_auto_unavailable FROM doctors WHERE id = ?`
3. Review browser console for JavaScript errors
4. Check Laravel logs for server errors

**Solution:**
- Verify admin is logged in
- Ensure doctor has `is_auto_unavailable = true`
- Check CSRF token is valid
- Review network tab for API errors

### Notifications Not Received

**Symptom:** Admins don't receive penalty notifications

**Check:**
1. Verify admins exist: `SELECT * FROM admin_users`
2. Check notification creation in logs
3. Verify notification bell icon is working
4. Check notification table: `SELECT * FROM notifications WHERE type = 'warning'`

**Solution:**
- Check `DoctorPenaltyService::notifyAdmins()` method
- Verify notification system is working
- Check admin user IDs are correct

---

## Configuration

### Penalty Threshold

**Location:** `app/Services/DoctorPenaltyService.php`

**Constant:**
```php
const MISSED_CONSULTATION_THRESHOLD = 3;
```

**To Change Threshold:**
1. Update constant in `DoctorPenaltyService`
2. Update documentation
3. Update UI messages if needed

### Scheduled Task Frequency

**Location:** `bootstrap/app.php`

**Current:** Every hour

**To Change:**
```php
// Every 30 minutes
$schedule->command('consultations:check-missed')
    ->everyThirtyMinutes()
    ->withoutOverlapping();

// Every 15 minutes
$schedule->command('consultations:check-missed')
    ->everyFifteenMinutes()
    ->withoutOverlapping();
```

---

## Testing Checklist

### Doctor Penalty System

- [ ] Doctor misses 1 consultation → Warning shown
- [ ] Doctor misses 2 consultations → Warning shown
- [ ] Doctor misses 3 consultations → Auto-set to unavailable
- [ ] Admin receives notification when penalty applied
- [ ] Doctor cannot set availability when penalized
- [ ] Admin can reset penalty
- [ ] After reset, doctor can set availability again
- [ ] Missed count resets after admin reset

### Communication Method Lock

- [ ] Patient selects consultation mode during booking
- [ ] Doctor sees mode in consultation details
- [ ] Doctor sees "(Selected by Patient)" label
- [ ] Doctor cannot change mode in any form
- [ ] Controller rejects mode changes
- [ ] UI shows mode as read-only

### Admin Controls

- [ ] Admin sees penalty indicators in doctors list
- [ ] Admin sees detailed penalty info in profile
- [ ] Admin can reset penalty via button
- [ ] Reset requires confirmation
- [ ] Reset logs action for audit
- [ ] Notification links to correct doctor profile

---

## Related Documentation

- [In-App Consultation Architecture](./IN_APP_CONSULTATION_ARCHITECTURE.md)
- [Booking Flow Architecture](./BOOKING_FLOW_ARCHITECTURE.md)

---

## Support & Maintenance

### Regular Maintenance

1. **Monitor Penalties**: Review doctors with penalties weekly
2. **Check Scheduled Tasks**: Ensure `consultations:check-missed` runs hourly
3. **Review Logs**: Check for errors in penalty application
4. **Audit Trail**: Review penalty resets for compliance

### Common Issues

See [Troubleshooting](#troubleshooting) section above.

### Future Enhancements

Potential improvements:
- Configurable penalty thresholds per doctor
- Graduated penalties (warnings before auto-unavailable)
- Automatic penalty expiration after time period
- Email notifications to doctors about penalties
- Dashboard analytics for penalty trends

---

**Document Version:** 1.0.0  
**Last Updated:** January 13, 2026  
**Maintained By:** Development Team

