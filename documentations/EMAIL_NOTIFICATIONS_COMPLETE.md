# Complete Email Notifications Guide

This document lists all emails sent by the DoctorOnTap system, when they are triggered, and who receives them.

## 📧 Email Categories

### 1. Consultation-Related Emails

#### 1.1 ConsultationConfirmation
**Recipient:** Patient  
**Subject:** Consultation Request Confirmation - DoctorOnTap  
**When Sent:**
- ✅ When a new consultation is created via `ConsultationService::createConsultation()`
- ✅ When a booking is completed via `BookingService::completeBooking()`
- ✅ When a patient creates a consultation via Patient Dashboard
- ✅ When a canvasser creates a consultation

**Trigger Locations:**
- `app/Services/ConsultationService.php:338`
- `app/Services/BookingService.php:685` (payer)
- `app/Services/BookingService.php:731` (individual patients)
- `app/Http/Controllers/Patient/DashboardController.php:1583`
- `app/Http/Controllers/Canvasser/DashboardController.php:365`

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

---

#### 1.2 ConsultationAdminAlert
**Recipient:** Admin  
**Subject:** New Consultation Request - DoctorOnTap  
**When Sent:**
- ✅ When a new consultation is created
- ✅ When a booking is completed

**Trigger Locations:**
- `app/Services/ConsultationService.php:371`
- `app/Services/BookingService.php:760`
- `app/Http/Controllers/Canvasser/DashboardController.php:387`

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

---

#### 1.3 ConsultationDoctorNotification
**Recipient:** Assigned Doctor  
**Subject:** New Consultation Request - DoctorOnTap  
**When Sent:**
- ✅ When a consultation is assigned to a doctor
- ✅ When a booking is completed and doctor is assigned

**Trigger Locations:**
- `app/Services/ConsultationService.php:387`
- `app/Services/BookingService.php:791`
- `app/Http/Controllers/Patient/DashboardController.php:1628`
- `app/Http/Controllers/Canvasser/DashboardController.php:391`

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

---

#### 1.4 ConsultationReminder
**Recipient:** Patient  
**Subject:** Reminder: Your Consultation with DoctorOnTap - {reference}  
**When Sent:**
- ✅ Manually by admin via Admin Dashboard (sends reminder for scheduled consultations)

**Trigger Locations:**
- `app/Http/Controllers/Admin/DashboardController.php:462`

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

---

#### 1.5 ConsultationStatusChange
**Recipient:** Patient/Doctor (depending on status)  
**Subject:** Consultation Status Update  
**When Sent:**
- ⚠️ **Currently not actively used** (class exists but not triggered in codebase)

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

---

### 2. Payment-Related Emails

#### 2.1 PaymentRequest
**Recipient:** Patient  
**Subject:** Payment Request - Your Consultation with Dr. {doctor_name}  
**When Sent:**
- ✅ When treatment plan is created and payment is NOT made (automatic via Observer)
- ✅ When admin manually requests payment
- ✅ When doctor creates/updates treatment plan and payment is pending
- ✅ When consultation is created and payment is required upfront
- ✅ When admin forwards treatment plan and payment is pending

**Trigger Locations:**
- `app/Observers/ConsultationObserver.php:52` (automatic after treatment plan creation)
- `app/Http/Controllers/Admin/DashboardController.php:521` (manual)
- `app/Http/Controllers/Admin/DashboardController.php:1100` (forward treatment plan)
- `app/Http/Controllers/Admin/DashboardController.php:1186` (forward treatment plan - unpaid)
- `app/Http/Controllers/Admin/DashboardController.php:1243` (manual)
- `app/Http/Controllers/Admin/DashboardController.php:1367` (manual)
- `app/Http/Controllers/Doctor/DashboardController.php:528` (after treatment plan)
- `app/Http/Controllers/ConsultationController.php:115` (manual)
- `app/Livewire/Admin/ConsultationTable.php:98` (manual)

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

---

#### 2.2 PaymentReceivedDoctorNotification
**Recipient:** Doctor  
**Subject:** Payment Received - Consultation {reference}  
**When Sent:**
- ✅ When payment is successfully processed and doctor needs to be notified

**Trigger Locations:**
- `app/Http/Controllers/PaymentController.php` (payment success flow)

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

---

#### 2.3 PaymentReceivedAdminNotification
**Recipient:** Admin  
**Subject:** Payment Received - Consultation {reference}  
**When Sent:**
- ✅ When payment is successfully processed

**Trigger Locations:**
- `app/Http/Controllers/PaymentController.php` (payment success flow)

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

---

#### 2.4 PaymentFailedNotification
**Recipient:** Patient  
**Subject:** Payment Failed - Consultation {reference}  
**When Sent:**
- ✅ When payment processing fails

**Trigger Locations:**
- `app/Http/Controllers/PaymentController.php:621`

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

---

### 3. Treatment Plan Emails

#### 3.1 TreatmentPlanNotification
**Recipient:** Patient  
**Subject:** Your Treatment Plan is Ready - {reference}  
**When Sent:**
- ✅ When payment status changes to 'paid' AND treatment plan exists (automatic via Observer)
- ✅ When treatment plan is created AND payment is already made (automatic via Observer)
- ✅ When doctor creates/updates treatment plan and payment is made
- ✅ When admin forwards treatment plan and payment is made

**Trigger Locations:**
- `app/Observers/ConsultationObserver.php:155` (automatic)
- `app/Http/Controllers/Admin/DashboardController.php:1170` (forward treatment plan)
- `app/Http/Controllers/Doctor/DashboardController.php:590` (doctor creates/updates)

**Queue:** Yes (ShouldQueue)  
**Attachments:** 
- ✅ PDF: Patient-friendly treatment plan (without clinical documentation)
- ✅ All treatment plan file attachments (if any)

**Special Notes:**
- Automatically followed by `ReviewRequest` email
- Includes notification tracking via `NotificationTrackingService`

---

#### 3.2 TreatmentPlanReadyNotification
**Recipient:** Patient  
**Subject:** Treatment Plan Ready  
**When Sent:**
- ⚠️ **Currently not actively used** (class exists but `TreatmentPlanNotification` is used instead)

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

---

### 4. Review & Feedback Emails

#### 4.1 ReviewRequest
**Recipient:** Patient  
**Subject:** How was your consultation with Dr. {doctor_name}?  
**When Sent:**
- ✅ Automatically after treatment plan is sent (via Observer)
- ✅ When doctor manually requests review

**Trigger Locations:**
- `app/Observers/ConsultationObserver.php:207` (automatic after treatment plan)
- `app/Http/Controllers/Doctor/DashboardController.php:626` (manual)

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

**Special Notes:**
- Includes notification tracking via `NotificationTrackingService`

---

### 5. Doctor-Related Emails

#### 5.1 DoctorReassignmentNotification
**Recipient:** Patient & New Doctor  
**Subject:** Doctor Reassignment - Consultation {reference}  
**When Sent:**
- ✅ When admin reassigns a consultation to a different doctor

**Trigger Locations:**
- `app/Http/Controllers/Admin/DashboardController.php:825` (to patient)
- `app/Http/Controllers/Admin/DashboardController.php:843` (to new doctor)

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

---

#### 5.2 DelayQueryNotification
**Recipient:** Doctor  
**Subject:** Delay Query Notification  
**When Sent:**
- ✅ When admin queries a doctor about consultation delays

**Trigger Locations:**
- `app/Http/Controllers/Admin/DashboardController.php:1028`

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

---

#### 5.3 DocumentsForwardedToDoctor
**Recipient:** Doctor  
**Subject:** Medical Documents Forwarded - Consultation {reference}  
**When Sent:**
- ✅ When admin forwards patient documents to doctor

**Trigger Locations:**
- `app/Http/Controllers/Admin/DashboardController.php:1555`

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

---

#### 5.4 CampaignNotification
**Recipient:** Doctor  
**Subject:** Campaign Notification  
**When Sent:**
- ✅ When admin creates/sends a campaign to doctors

**Trigger Locations:**
- `app/Http/Controllers/Admin/DashboardController.php:1722`
- `app/Livewire/Admin/DoctorsTable.php:243`

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

---

#### 5.5 ReferralNotification
**Recipient:** Referred Doctor & Patient  
**Subject:** Consultation Referral  
**When Sent:**
- ✅ When a doctor refers a consultation to another doctor

**Trigger Locations:**
- `app/Http/Controllers/Doctor/DashboardController.php:1620` (to referred doctor)
- `app/Http/Controllers/Doctor/DashboardController.php:1634` (to patient)

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

---

### 6. Account Creation Emails

#### 6.1 CanvasserAccountCreated
**Recipient:** New Canvasser  
**Subject:** Your Canvasser Account Has Been Created  
**When Sent:**
- ✅ When admin creates a new canvasser account

**Trigger Locations:**
- `app/Http/Controllers/Admin/DashboardController.php:1982`

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

**Special Notes:**
- Includes temporary password in email

---

#### 6.2 NurseAccountCreated
**Recipient:** New Nurse  
**Subject:** Your Nurse Account Has Been Created  
**When Sent:**
- ✅ When admin creates a new nurse account

**Trigger Locations:**
- `app/Http/Controllers/Admin/DashboardController.php:2148`

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

**Special Notes:**
- Includes temporary password in email

---

#### 6.3 CustomerCareAccountCreated
**Recipient:** New Customer Care Staff  
**Subject:** Your Customer Care Account Has Been Created  
**When Sent:**
- ✅ When admin creates a new customer care account

**Trigger Locations:**
- `app/Http/Controllers/Admin/DashboardController.php:2315`

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

**Special Notes:**
- Includes temporary password in email

---

#### 6.4 CareGiverAccountCreated
**Recipient:** New Care Giver  
**Subject:** Your Care Giver Account Has Been Created  
**When Sent:**
- ✅ When admin creates a new care giver account

**Trigger Locations:**
- `app/Http/Controllers/Admin/DashboardController.php:2498`

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

**Special Notes:**
- Includes temporary password in email

---

### 7. Booking & Fee Adjustment Emails

#### 7.1 FeeAdjustmentNotification
**Recipient:** Booking Payer  
**Subject:** Fee Adjustment Notification  
**When Sent:**
- ✅ When booking fees are adjusted

**Trigger Locations:**
- `app/Services/BookingService.php:554`

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

---

#### 7.2 FeeAdjustmentAdminNotification
**Recipient:** Admin/Accountant  
**Subject:** Fee Adjustment Admin Notification  
**When Sent:**
- ✅ When booking fees are adjusted (admin notification)

**Trigger Locations:**
- `app/Services/BookingService.php:562`

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

---

### 8. Medical Reports & Documents

#### 8.1 VitalSignsReport
**Recipient:** Patient  
**Subject:** Your Vital Signs Report  
**When Sent:**
- ✅ When nurse sends vital signs report to patient (manual action)

**Trigger Locations:**
- `app/Http/Controllers/Nurse/DashboardController.php:246`

**Queue:** Yes (ShouldQueue)  
**Attachments:**
- ✅ PDF: Vital signs report with patient data

**Special Notes:**
- Only sent once per vital signs record (tracked by `email_sent` flag)

---

### 9. Security & System Emails

#### 9.1 SecurityAlert
**Recipient:** Security Team/Admin  
**Subject:** {SEVERITY} Security Alert: {EVENT_TYPE} - DoctorOnTap  
**When Sent:**
- ✅ When security events are detected (failed logins, suspicious activity, etc.)
- ✅ Test alerts from admin dashboard

**Trigger Locations:**
- `app/Http/Middleware/SecurityMonitoring.php:426` (automatic)
- `app/Http/Controllers/Admin/DashboardController.php:3008` (test)

**Queue:** Yes (ShouldQueue)  
**Attachments:** None

**Severity Levels:**
- 🚨 CRITICAL
- ⚠️ HIGH
- ⚡ MEDIUM
- ℹ️ LOW

---

## 📊 Email Summary Table

| Email Type | Recipient | Queue | Auto-Triggered | Manual Only |
|------------|-----------|-------|----------------|-------------|
| ConsultationConfirmation | Patient | ✅ | ✅ | ❌ |
| ConsultationAdminAlert | Admin | ✅ | ✅ | ❌ |
| ConsultationDoctorNotification | Doctor | ✅ | ✅ | ❌ |
| ConsultationReminder | Patient | ✅ | ❌ | ✅ |
| PaymentRequest | Patient | ✅ | ✅ | ✅ |
| PaymentReceivedDoctorNotification | Doctor | ✅ | ✅ | ❌ |
| PaymentReceivedAdminNotification | Admin | ✅ | ✅ | ❌ |
| PaymentFailedNotification | Patient | ✅ | ✅ | ❌ |
| TreatmentPlanNotification | Patient | ✅ | ✅ | ✅ |
| ReviewRequest | Patient | ✅ | ✅ | ✅ |
| DoctorReassignmentNotification | Patient/Doctor | ✅ | ❌ | ✅ |
| DelayQueryNotification | Doctor | ✅ | ❌ | ✅ |
| DocumentsForwardedToDoctor | Doctor | ✅ | ❌ | ✅ |
| CampaignNotification | Doctor | ✅ | ❌ | ✅ |
| ReferralNotification | Doctor/Patient | ✅ | ❌ | ✅ |
| CanvasserAccountCreated | Canvasser | ✅ | ❌ | ✅ |
| NurseAccountCreated | Nurse | ✅ | ❌ | ✅ |
| CustomerCareAccountCreated | Customer Care | ✅ | ❌ | ✅ |
| CareGiverAccountCreated | Care Giver | ✅ | ❌ | ✅ |
| FeeAdjustmentNotification | Payer | ✅ | ✅ | ❌ |
| FeeAdjustmentAdminNotification | Admin | ✅ | ✅ | ❌ |
| VitalSignsReport | Patient | ✅ | ❌ | ✅ |
| SecurityAlert | Security Team | ✅ | ✅ | ✅ |

---

## 🔄 Automatic Email Workflows

### Workflow 1: New Consultation Created
1. **ConsultationConfirmation** → Patient
2. **ConsultationAdminAlert** → Admin
3. **ConsultationDoctorNotification** → Assigned Doctor

### Workflow 2: Treatment Plan Created (Payment Pending)
1. **PaymentRequest** → Patient (automatic via Observer)

### Workflow 3: Payment Made (Treatment Plan Exists)
1. **TreatmentPlanNotification** → Patient (automatic via Observer)
2. **ReviewRequest** → Patient (automatic, immediately after treatment plan)

### Workflow 4: Booking Completed
1. **ConsultationConfirmation** → Payer
2. **ConsultationConfirmation** → Individual Patients (if different email)
3. **ConsultationAdminAlert** → Admin
4. **ConsultationDoctorNotification** → Assigned Doctor

---

## ⚙️ Configuration

### Email Settings
All emails use the following configuration:
- **From Address:** `config('mail.from.address')`
- **Admin Email:** `config('mail.admin_email')`
- **Reply To:** Admin email (where applicable)

### Queue Configuration
All emails implement `ShouldQueue` interface, meaning they are sent asynchronously via Laravel's queue system. This prevents blocking HTTP requests.

**Queue Driver:** Configured in `.env`:
```env
QUEUE_CONNECTION=database  # or redis, sqs, etc.
```

---

## 📝 Email Templates

All email templates are located in:
```
resources/views/emails/
```

Template files follow the naming convention:
- `consultation-confirmation.blade.php`
- `payment-request.blade.php`
- `treatment-plan-notification.blade.php`
- etc.

---

## 🔍 Tracking & Logging

### Notification Tracking
Some emails (TreatmentPlanNotification, ReviewRequest) use `NotificationTrackingService` to track:
- Email send status
- Message ID
- Delivery status
- Failure reasons

### Logging
All email sends are logged with:
- Recipient email
- Consultation reference (where applicable)
- Success/failure status
- Error messages (if failed)

Log locations:
- `storage/logs/laravel.log`

---

## 🚨 Important Notes

1. **Queue Processing:** Ensure queue workers are running:
   ```bash
   php artisan queue:work
   ```

2. **Failed Jobs:** Monitor failed jobs:
   ```bash
   php artisan queue:failed
   ```

3. **Email Delivery:** Check mail configuration in `.env`:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=your_username
   MAIL_PASSWORD=your_password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=noreply@doctorontap.com
   MAIL_FROM_NAME="${APP_NAME}"
   ```

4. **Observer Behavior:** The `ConsultationObserver` automatically triggers emails when consultation status changes. This happens in the background and doesn't block requests.

5. **Email Priority:** All emails are queued with default priority. For critical emails (like security alerts), consider implementing priority queues.

---

## 📚 Related Documentation

- [Laravel Mail Documentation](https://laravel.com/docs/mail)
- [Laravel Queue Documentation](https://laravel.com/docs/queues)
- [Notification Tracking Service](./NOTIFICATION_TRACKING.md) (if exists)

---

**Last Updated:** 2024-01-14  
**Total Email Types:** 25  
**Auto-Triggered:** 12  
**Manual Only:** 13

