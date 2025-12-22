# Patient Review Email Implementation

## Overview
Implemented a system to automatically send a "Rate Your Experience" email to patients immediately after they receive their treatment plan (which occurs upon payment confirmation).

## Changes Made

### 1. New Mailable Class
**File:** [`app/Mail/ReviewRequest.php`](file:///home/royal-t/doctorontap%20campain/app/Mail/ReviewRequest.php)
- Created a mechanism to send the review request email.
- Includes integration with `NotificationTrackingService` to log that the email was sent.

### 2. New Email Template
**File:** [`resources/views/emails/review-request.blade.php`](file:///home/royal-t/doctorontap%20campain/resources/views/emails/review-request.blade.php)
- Designed a professional, mobile-responsive email template.
- Matches the branding (purple gradients) of the other emails.
- Includes a direct link to the review form: `{{ route('reviews.consultation', $consultation->reference) }}`.

### 3. Consultation Observer Logic
**File:** [`app/Observers/ConsultationObserver.php`](file:///home/royal-t/doctorontap%20campain/app/Observers/ConsultationObserver.php)
- Modified the `updated` method to trigger the `ReviewRequest` email.
- **Trigger Condition:** The email is sent **immediately after** the `TreatmentPlanNotification` is successfully sent. this happens automatically when the consultation `payment_status` changes to `paid` and a treatment plan exists.

## Flow
1. Patient pays for consultation.
2. Webhook/Payment verification updates `consultation->payment_status` to `'paid'`.
3. `ConsultationObserver` detects the change.
4. Observer unlocks the treatment plan.
5. Observer sends **Treatment Plan Notification** (with PDF).
6. Observer immediately sends **Review Request** email.

## Verification
To verify:
1. Complete a consultation flow to the point of payment.
2. Make a successful payment (or simulate one).
3. Check the logs (`laravel.log`) or Mailtrap/Inbox.
4. You should see two emails sent:
   - "Your Treatment Plan is Ready"
   - "How was your consultation with Dr. X?"
