# Email Workflow Documentation

This document outlines all emails sent throughout the consultation and treatment plan workflow.

## Email Flow Overview

### 1. When Consultation is Created (Patient submits consultation form)

**Location:** `app/Http/Controllers/ConsultationController.php`

**Emails Sent:**
- ✅ **ConsultationConfirmation** → Patient
  - Queued asynchronously
  - Retry: 3 attempts with backoff (60s, 180s, 300s)
  - Timeout: 30 seconds
  - Contains: Booking confirmation, consultation details, payment link

- ✅ **ConsultationAdminAlert** → Admin
  - Queued asynchronously
  - Contains: New consultation notification with patient details

- ✅ **ConsultationDoctorNotification** → Assigned Doctor
  - Queued asynchronously (if doctor email available)
  - Contains: New consultation assignment notification

---

### 2. When Treatment Plan is Created (Doctor creates treatment plan)

**Location:** `app/Http/Controllers/Doctor/DashboardController.php`

**Emails Sent:**
- ✅ **TreatmentPlanReadyNotification** → Patient
  - Queued asynchronously
  - Retry: 3 attempts with backoff (60s, 180s, 300s)
  - Timeout: 30 seconds
  - Contains: Treatment plan ready notification, payment required to access

---

### 3. When Payment is Completed

**Location:** `app/Observers/ConsultationObserver.php`

**Emails Sent:**
- ✅ **TreatmentPlanNotification** → Patient
  - Queued asynchronously via Observer
  - Retry: 3 attempts with backoff (60s, 180s, 300s)
  - Timeout: 60 seconds (longer for PDF generation)
  - Contains: Treatment plan access notification with PDF attachment
  - Automatically sent when `payment_status` changes to `paid`

---

### 4. Other Email Notifications

**Payment Request:**
- `PaymentRequest` → Patient (when payment is requested)

**Status Changes:**
- `ConsultationStatusChange` → Admin (when consultation status changes)

**Document Forwarding:**
- `DocumentsForwardedToDoctor` → Doctor (when admin forwards documents)

**Canvasser Workflow:**
- `CanvasserConsultationConfirmation` → Patient (when canvasser creates consultation)

---

## Email Configuration

### All Queued Emails Include:
- ✅ Retry logic (3 attempts)
- ✅ Exponential backoff (60s, 180s, 300s)
- ✅ Exception throttling (5 exceptions per 10 minutes)
- ✅ Failed job logging
- ✅ Timeout protection

### Queue Configuration:
- **Connection:** Database (configurable via `QUEUE_CONNECTION`)
- **Retry After:** 90 seconds
- **Queue Worker:** Should be running continuously

---

## Email Templates

All email templates are located in: `resources/views/emails/`

- `consultation-confirmation.blade.php`
- `consultation-admin-alert.blade.php`
- `consultation-doctor-notification.blade.php`
- `treatment-plan-ready.blade.php`
- `treatment-plan-notification.blade.php`
- `payment-request.blade.php`

---

## Troubleshooting

### If emails are not being sent:

1. **Check Queue Worker:**
   ```bash
   php artisan queue:work
   ```

2. **Check Failed Jobs:**
   ```bash
   php artisan queue:failed
   ```

3. **Retry Failed Jobs:**
   ```bash
   php artisan queue:retry all
   ```

4. **Check Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

5. **Verify Mail Configuration:**
   - Check `.env` file for `MAIL_*` settings
   - Verify SMTP credentials
   - Test connection: `php artisan tinker` → `Mail::raw('test', fn($m) => $m->to('test@example.com')->subject('test'))`

---

## Email Status Summary

| Email | Queue | Retry | Timeout | Status |
|-------|-------|-------|---------|--------|
| ConsultationConfirmation | ✅ | ✅ | 30s | ✅ Configured |
| ConsultationAdminAlert | ✅ | ✅ | Default | ✅ Configured |
| ConsultationDoctorNotification | ✅ | ✅ | Default | ✅ Configured |
| TreatmentPlanReadyNotification | ✅ | ✅ | 30s | ✅ Configured |
| TreatmentPlanNotification | ✅ | ✅ | 60s | ✅ Configured |

---

## Notes

- All emails are queued asynchronously to improve performance
- Failed emails are logged with detailed error information
- Emails include retry logic to handle temporary network issues
- Treatment plan notification automatically sends when payment is confirmed via Observer pattern

