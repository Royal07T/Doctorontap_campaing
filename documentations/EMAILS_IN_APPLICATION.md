# Complete List of Emails in DoctorOnTap Application

## 📧 Email Categories

### 1. Consultation-Related Emails

#### 1.1 ConsultationConfirmation
- **File:** `app/Mail/ConsultationConfirmation.php`
- **Recipient:** Patient
- **When Sent:** When a new consultation is created
- **Trigger Locations:**
  - `app/Services/ConsultationService.php:359`
  - `app/Services/BookingService.php:685`
  - `app/Http/Controllers/Patient/DashboardController.php:1873`
  - `app/Http/Controllers/Canvasser/DashboardController.php:365`
- **Template Variables Needed:** `first_name`, `last_name`, `reference`, `consultation_type`, `scheduled_date`, `scheduled_time`, `doctor_name`

#### 1.2 ConsultationAdminAlert
- **File:** `app/Mail/ConsultationAdminAlert.php`
- **Recipient:** Admin
- **When Sent:** When a new consultation is created
- **Trigger Locations:**
  - `app/Services/ConsultationService.php:392`
  - `app/Services/BookingService.php:760`
- **Template Variables Needed:** `reference`, `patient_name`, `consultation_type`, `scheduled_date`, `scheduled_time`, `doctor_name`

#### 1.3 ConsultationDoctorNotification
- **File:** `app/Mail/ConsultationDoctorNotification.php`
- **Recipient:** Assigned Doctor
- **When Sent:** When a consultation is assigned to a doctor
- **Trigger Locations:**
  - `app/Services/ConsultationService.php:408`
  - `app/Services/BookingService.php:791`
  - `app/Http/Controllers/Patient/DashboardController.php:1918`
  - `app/Livewire/Admin/ConsultationTable.php:136`
- **Template Variables Needed:** `doctor_name`, `reference`, `patient_name`, `consultation_type`, `scheduled_date`, `scheduled_time`

#### 1.4 ConsultationReminder
- **File:** `app/Mail/ConsultationReminder.php`
- **Recipient:** Patient
- **When Sent:** Before consultation (scheduled reminder)
- **Trigger Locations:**
  - `app/Http/Controllers/Admin/DashboardController.php:623`
- **Template Variables Needed:** `first_name`, `last_name`, `reference`, `scheduled_date`, `scheduled_time`, `doctor_name`

#### 1.5 ConsultationStatusChange
- **File:** `app/Mail/ConsultationStatusChange.php`
- **Recipient:** Admin
- **When Sent:** When consultation status changes
- **Trigger Locations:**
  - `app/Livewire/Admin/ConsultationTable.php:54`
- **Template Variables Needed:** `reference`, `old_status`, `new_status`, `patient_name`, `changed_by`

### 2. Payment-Related Emails

#### 2.1 PaymentRequest
- **File:** `app/Mail/PaymentRequest.php`
- **Recipient:** Patient
- **When Sent:** When payment is required
- **Trigger Locations:**
  - `app/Observers/ConsultationObserver.php:52`
  - `app/Http/Controllers/Admin/DashboardController.php:682, 1261, 1407, 1531`
  - `app/Http/Controllers/Doctor/DashboardController.php:585`
  - `app/Livewire/Admin/ConsultationTable.php:98`
- **Template Variables Needed:** `first_name`, `last_name`, `reference`, `amount`, `payment_link`, `due_date`

#### 2.2 PaymentReceivedAdminNotification
- **File:** `app/Mail/PaymentReceivedAdminNotification.php`
- **Recipient:** Admin
- **When Sent:** When payment is received
- **Trigger Locations:**
  - `app/Services/BookingService.php` (payment processing)
- **Template Variables Needed:** `reference`, `amount`, `patient_name`, `payment_method`, `transaction_id`

#### 2.3 PaymentReceivedDoctorNotification
- **File:** `app/Mail/PaymentReceivedDoctorNotification.php`
- **Recipient:** Doctor
- **When Sent:** When payment is received (doctor portion)
- **Trigger Locations:**
  - Payment processing services
- **Template Variables Needed:** `doctor_name`, `reference`, `amount`, `patient_name`, `doctor_portion`

#### 2.4 PaymentFailedNotification
- **File:** `app/Mail/PaymentFailedNotification.php`
- **Recipient:** Patient
- **When Sent:** When payment fails
- **Trigger Locations:**
  - Payment processing services
- **Template Variables Needed:** `first_name`, `last_name`, `reference`, `amount`, `failure_reason`, `retry_link`

#### 2.5 FeeAdjustmentNotification
- **File:** `app/Mail/FeeAdjustmentNotification.php`
- **Recipient:** Patient
- **When Sent:** When consultation fee is adjusted
- **Trigger Locations:**
  - `app/Services/BookingService.php:554`
- **Template Variables Needed:** `first_name`, `last_name`, `reference`, `old_amount`, `new_amount`, `reason`

#### 2.6 FeeAdjustmentAdminNotification
- **File:** `app/Mail/FeeAdjustmentAdminNotification.php`
- **Recipient:** Admin
- **When Sent:** When consultation fee is adjusted
- **Trigger Locations:**
  - `app/Services/BookingService.php:562`
- **Template Variables Needed:** `reference`, `old_amount`, `new_amount`, `patient_name`, `adjusted_by`, `reason`

### 3. Treatment Plan Emails

#### 3.1 TreatmentPlanNotification
- **File:** `app/Mail/TreatmentPlanNotification.php`
- **Recipient:** Patient
- **When Sent:** When treatment plan is created/updated
- **Trigger Locations:**
  - `app/Observers/ConsultationObserver.php:155`
  - `app/Http/Controllers/Admin/DashboardController.php:1334`
  - `app/Http/Controllers/Doctor/DashboardController.php:647`
- **Template Variables Needed:** `first_name`, `last_name`, `reference`, `doctor_name`, `treatment_plan_link`

#### 3.2 TreatmentPlanReadyNotification
- **File:** `app/Mail/TreatmentPlanReadyNotification.php`
- **Recipient:** Patient
- **When Sent:** When treatment plan is finalized
- **Trigger Locations:**
  - Treatment plan completion
- **Template Variables Needed:** `first_name`, `last_name`, `reference`, `doctor_name`, `view_link`

### 4. Review & Feedback Emails

#### 4.1 ReviewRequest
- **File:** `app/Mail/ReviewRequest.php`
- **Recipient:** Patient
- **When Sent:** After consultation completion
- **Trigger Locations:**
  - `app/Observers/ConsultationObserver.php:207`
  - `app/Http/Controllers/Doctor/DashboardController.php:683`
- **Template Variables Needed:** `first_name`, `last_name`, `reference`, `doctor_name`, `review_link`

### 5. Account Creation Emails

#### 5.1 CanvasserAccountCreated
- **File:** `app/Mail/CanvasserAccountCreated.php`
- **Recipient:** Canvasser
- **When Sent:** When canvasser account is created
- **Trigger Locations:**
  - `app/Http/Controllers/Admin/DashboardController.php:2158`
- **Template Variables Needed:** `name`, `email`, `password`, `admin_name`, `login_link`

#### 5.2 NurseAccountCreated
- **File:** `app/Mail/NurseAccountCreated.php`
- **Recipient:** Nurse
- **When Sent:** When nurse account is created
- **Trigger Locations:**
  - `app/Http/Controllers/Admin/DashboardController.php:2324`
- **Template Variables Needed:** `name`, `email`, `password`, `admin_name`, `login_link`

#### 5.3 CustomerCareAccountCreated
- **File:** `app/Mail/CustomerCareAccountCreated.php`
- **Recipient:** Customer Care Agent
- **When Sent:** When customer care account is created
- **Trigger Locations:**
  - `app/Http/Controllers/Admin/DashboardController.php:2491`
- **Template Variables Needed:** `name`, `email`, `password`, `admin_name`, `login_link`

#### 5.4 CareGiverAccountCreated
- **File:** `app/Mail/CareGiverAccountCreated.php`
- **Recipient:** Care Giver
- **When Sent:** When care giver account is created
- **Trigger Locations:**
  - `app/Http/Controllers/Admin/DashboardController.php:2674`
- **Template Variables Needed:** `name`, `email`, `password`, `admin_name`, `login_link`

### 6. Doctor-Related Emails

#### 6.1 DoctorReassignmentNotification
- **File:** `app/Mail/DoctorReassignmentNotification.php`
- **Recipient:** Patient & Doctor
- **When Sent:** When doctor is reassigned
- **Trigger Locations:**
  - `app/Http/Controllers/Admin/DashboardController.php:986, 1004`
- **Template Variables Needed:** `name`, `reference`, `old_doctor_name`, `new_doctor_name`, `reason`

#### 6.2 ReferralNotification
- **File:** `app/Mail/ReferralNotification.php`
- **Recipient:** Doctor & Patient
- **When Sent:** When consultation is referred
- **Trigger Locations:**
  - `app/Http/Controllers/Doctor/DashboardController.php:1723, 1737`
- **Template Variables Needed:** `name`, `reference`, `referring_doctor`, `referred_to_doctor`, `reason`

#### 6.3 DocumentsForwardedToDoctor
- **File:** `app/Mail/DocumentsForwardedToDoctor.php`
- **Recipient:** Doctor
- **When Sent:** When documents are forwarded to doctor
- **Trigger Locations:**
  - `app/Http/Controllers/Admin/DashboardController.php:1719`
- **Template Variables Needed:** `doctor_name`, `reference`, `patient_name`, `document_count`, `view_link`

### 7. System & Security Emails

#### 7.1 SecurityAlert
- **File:** `app/Mail/SecurityAlert.php`
- **Recipient:** Admin
- **When Sent:** On security events
- **Trigger Locations:**
  - `app/Http/Middleware/SecurityMonitoring.php:443`
  - `app/Http/Controllers/Admin/DashboardController.php:3220`
- **Template Variables Needed:** `event_type`, `severity`, `timestamp`, `details`, `ip_address`, `user_agent`

#### 7.2 DelayQueryNotification
- **File:** `app/Mail/DelayQueryNotification.php`
- **Recipient:** Doctor
- **When Sent:** When delay query is sent
- **Trigger Locations:**
  - `app/Http/Controllers/Admin/DashboardController.php:1189`
- **Template Variables Needed:** `doctor_name`, `reference`, `patient_name`, `query_message`, `response_link`

### 8. Campaign & Marketing Emails

#### 8.1 CampaignNotification
- **File:** `app/Mail/CampaignNotification.php`
- **Recipient:** Doctor
- **When Sent:** Campaign announcements
- **Trigger Locations:**
  - `app/Http/Controllers/Admin/DashboardController.php:1886`
- **Template Variables Needed:** `doctor_name`, `campaign_title`, `campaign_details`, `action_link`

### 9. Canvasser-Specific Emails

#### 9.1 CanvasserConsultationConfirmation
- **File:** `app/Mail/CanvasserConsultationConfirmation.php`
- **Recipient:** Canvasser
- **When Sent:** When canvasser creates consultation
- **Trigger Locations:**
  - Canvasser dashboard
- **Template Variables Needed:** `canvasser_name`, `reference`, `patient_name`, `consultation_type`, `scheduled_date`

### 10. Medical Reports

#### 10.1 VitalSignsReport
- **File:** `app/Mail/VitalSignsReport.php`
- **Recipient:** Patient
- **When Sent:** When vital signs report is generated
- **Trigger Locations:**
  - `app/Http/Controllers/Nurse/DashboardController.php:247`
- **Template Variables Needed:** `first_name`, `last_name`, `report_date`, `vital_signs_summary`, `view_link`

### 11. Custom Communication

#### 11.1 CustomCommunication
- **File:** `app/Mail/CustomCommunication.php`
- **Recipient:** Various
- **When Sent:** Custom messages from Customer Care
- **Trigger Locations:**
  - `app/Http/Controllers/CustomerCare/CommunicationController.php:113`
- **Template Variables Needed:** Dynamic (based on template)

---

## 📊 Summary

**Total Email Types:** 26
**Total Trigger Locations:** ~50+

### Email Categories Breakdown:
- Consultation-Related: 5 emails
- Payment-Related: 6 emails
- Treatment Plan: 2 emails
- Review & Feedback: 1 email
- Account Creation: 4 emails
- Doctor-Related: 3 emails
- System & Security: 2 emails
- Campaign & Marketing: 1 email
- Canvasser-Specific: 1 email
- Medical Reports: 1 email
- Custom Communication: 1 email

---

## 🔄 Migration Plan

All these emails need to be migrated to use the `CommunicationTemplate` system where:
1. Admin/Super Admin creates templates in the `communication_templates` table
2. Each email type maps to a template by name/slug
3. Mail classes fetch and render templates dynamically
4. Variables are replaced from the data passed to the Mail class

