# Migration Script for Remaining Mail Classes

Due to the large number of Mail classes (25 remaining), I've created a migration pattern. Here's how to update each remaining class:

## Migration Pattern

For each Mail class, follow these steps:

1. **Add the import:**
```php
use App\Services\EmailTemplateService;
```

2. **Add template properties:**
```php
public $templateContent;
public $templateSubject;
```

3. **Update constructor to fetch template:**
```php
public function __construct($data)
{
    $this->data = $data;
    
    // Prepare template data with all required variables
    $templateData = [
        // Map all variables needed by the template
        'variable1' => $data['variable1'] ?? '',
        'variable2' => $data['variable2'] ?? '',
        // ... see EMAILS_IN_APPLICATION.md for required variables
    ];

    // Try to get template from CommunicationTemplate system
    $rendered = EmailTemplateService::render('MailClassName', $templateData);
    
    if ($rendered) {
        $this->templateContent = $rendered['content'];
        $this->templateSubject = $rendered['subject'];
    } else {
        // Fallback to default view if template not found
        $this->templateContent = null;
        $this->templateSubject = 'Default Subject Here';
    }
}
```

4. **Update envelope() method:**
```php
public function envelope(): Envelope
{
    return new Envelope(
        subject: $this->templateSubject,
        // ... other settings
    );
}
```

5. **Update content() method:**
```php
public function content(): Content
{
    // If template content is available, use it; otherwise fallback to view
    if ($this->templateContent) {
        return new Content(
            htmlString: $this->templateContent,
        );
    }

    return new Content(
        view: 'emails.original-view-name',
    );
}
```

## Remaining Classes to Migrate

### Consultation-Related
- [x] ConsultationConfirmation ✅
- [x] ConsultationAdminAlert ✅
- [x] ConsultationDoctorNotification ✅
- [ ] ConsultationReminder
- [ ] ConsultationStatusChange

### Payment-Related
- [x] PaymentRequest ✅
- [ ] PaymentReceivedAdminNotification
- [ ] PaymentReceivedDoctorNotification
- [ ] PaymentFailedNotification
- [ ] FeeAdjustmentNotification
- [ ] FeeAdjustmentAdminNotification

### Treatment Plan
- [ ] TreatmentPlanNotification
- [ ] TreatmentPlanReadyNotification

### Review & Feedback
- [ ] ReviewRequest

### Account Creation
- [ ] CanvasserAccountCreated
- [ ] NurseAccountCreated
- [ ] CustomerCareAccountCreated
- [ ] CareGiverAccountCreated

### Doctor-Related
- [ ] DoctorReassignmentNotification
- [ ] ReferralNotification
- [ ] DocumentsForwardedToDoctor

### System & Security
- [ ] SecurityAlert
- [ ] DelayQueryNotification

### Campaign & Marketing
- [ ] CampaignNotification

### Canvasser-Specific
- [ ] CanvasserConsultationConfirmation

### Medical Reports
- [ ] VitalSignsReport

### Custom Communication
- [ ] CustomCommunication

## Quick Reference: Template Variable Mappings

See `EMAILS_IN_APPLICATION.md` for complete variable requirements for each email type.

## Automated Migration

You can use this pattern to batch-update all remaining classes. The seeder (`EmailTemplatesSeeder.php`) has already created all default templates, so once the Mail classes are updated, they will automatically use the templates.

