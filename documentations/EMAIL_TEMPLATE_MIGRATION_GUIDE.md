# Email Template Migration Guide

## 📋 Overview

This guide documents the migration of all email notifications in the DoctorOnTap application to use the `CommunicationTemplate` system, allowing Admin and Super Admin to manage all email templates centrally.

## ✅ Completed

### 1. Email Inventory
- **File:** `EMAILS_IN_APPLICATION.md`
- **Status:** ✅ Complete
- **Details:** Comprehensive list of all 26 email types with their trigger locations and required template variables

### 2. EmailTemplateService
- **File:** `app/Services/EmailTemplateService.php`
- **Status:** ✅ Complete
- **Features:**
  - Maps Mail class names to CommunicationTemplate names
  - Renders templates with variable replacement
  - Provides fallback to default views if template not found
  - Logs warnings when templates are missing

### 3. Example Migrations
- **Files Updated:**
  - `app/Mail/ConsultationConfirmation.php` ✅
  - `app/Mail/PaymentRequest.php` ✅
- **Pattern:** Both classes now check for CommunicationTemplate first, fallback to original view if not found

## 🔄 Migration Pattern

All Mail classes should follow this pattern:

```php
<?php

namespace App\Mail;

use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class YourEmailClass extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $data;
    public $templateContent;
    public $templateSubject;

    public function __construct($data)
    {
        $this->data = $data;
        
        // Prepare template data with all required variables
        $templateData = [
            'variable1' => $data['variable1'] ?? '',
            'variable2' => $data['variable2'] ?? '',
            // ... all required variables
        ];

        // Try to get template from CommunicationTemplate system
        $rendered = EmailTemplateService::render('YourEmailClass', $templateData);
        
        if ($rendered) {
            $this->templateContent = $rendered['content'];
            $this->templateSubject = $rendered['subject'];
        } else {
            // Fallback to default view if template not found
            $this->templateContent = null;
            $this->templateSubject = 'Default Subject';
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->templateSubject,
            // ... other envelope settings
        );
    }

    public function content(): Content
    {
        // If template content is available, use it; otherwise fallback to view
        if ($this->templateContent) {
            return new Content(
                htmlString: $this->templateContent,
            );
        }

        return new Content(
            view: 'emails.your-email-view',
        );
    }
}
```

## 📝 Remaining Mail Classes to Migrate

### Consultation-Related (3 remaining)
- [ ] `ConsultationAdminAlert.php`
- [ ] `ConsultationDoctorNotification.php`
- [ ] `ConsultationReminder.php`
- [ ] `ConsultationStatusChange.php`

### Payment-Related (4 remaining)
- [ ] `PaymentReceivedAdminNotification.php`
- [ ] `PaymentReceivedDoctorNotification.php`
- [ ] `PaymentFailedNotification.php`
- [ ] `FeeAdjustmentNotification.php`
- [ ] `FeeAdjustmentAdminNotification.php`

### Treatment Plan (2 remaining)
- [ ] `TreatmentPlanNotification.php`
- [ ] `TreatmentPlanReadyNotification.php`

### Review & Feedback (1 remaining)
- [ ] `ReviewRequest.php`

### Account Creation (4 remaining)
- [ ] `CanvasserAccountCreated.php`
- [ ] `NurseAccountCreated.php`
- [ ] `CustomerCareAccountCreated.php`
- [ ] `CareGiverAccountCreated.php`

### Doctor-Related (3 remaining)
- [ ] `DoctorReassignmentNotification.php`
- [ ] `ReferralNotification.php`
- [ ] `DocumentsForwardedToDoctor.php`

### System & Security (2 remaining)
- [ ] `SecurityAlert.php`
- [ ] `DelayQueryNotification.php`

### Campaign & Marketing (1 remaining)
- [ ] `CampaignNotification.php`

### Canvasser-Specific (1 remaining)
- [ ] `CanvasserConsultationConfirmation.php`

### Medical Reports (1 remaining)
- [ ] `VitalSignsReport.php`

### Custom Communication (1 remaining)
- [ ] `CustomCommunication.php`

**Total Remaining:** 24 Mail classes

## 🗄️ Database Setup

### Required Templates in `communication_templates` Table

Each template should be created with:
- `name`: Template identifier (e.g., 'consultation_confirmation')
- `channel`: 'email'
- `subject`: Email subject with variables (e.g., 'Consultation Request Confirmation - {{reference}}')
- `body`: HTML email body with variables
- `variables`: JSON array of variable names
- `active`: true
- `created_by`: Admin/Super Admin ID

### Template Variables Reference

See `EMAILS_IN_APPLICATION.md` for complete list of required variables per email type.

## 🚀 Next Steps

1. **Create Default Templates**
   - Admin/Super Admin should create templates in the admin panel
   - Or create a seeder to populate default templates

2. **Migrate Remaining Mail Classes**
   - Update each Mail class following the pattern above
   - Test each email to ensure templates work correctly
   - Keep original views as fallback

3. **Testing**
   - Test each email type with template
   - Test fallback to original view when template missing
   - Verify variable replacement works correctly

4. **Documentation**
   - Document template variable requirements
   - Create admin guide for template management

## 📌 Important Notes

- **Backward Compatibility:** All Mail classes maintain fallback to original views if template not found
- **Variable Naming:** Use consistent variable names across templates (see `EMAILS_IN_APPLICATION.md`)
- **Template Management:** Only Admin and Super Admin can create/edit templates
- **Active Status:** Only active templates are used; inactive templates trigger fallback
- **Logging:** Missing templates are logged as warnings for monitoring

## 🔍 Template Mapping

The `EmailTemplateService` maps Mail class names to template names:

```php
'ConsultationConfirmation' => 'consultation_confirmation',
'PaymentRequest' => 'payment_request',
// ... see EmailTemplateService::$templateMappings for full list
```

To add a new mapping, update `EmailTemplateService::$templateMappings`.

