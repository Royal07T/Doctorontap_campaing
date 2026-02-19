# Admin Email Template Management Guide

## 📧 Overview

All emails in the DoctorOnTap application now use the **CommunicationTemplate** system, allowing Admin and Super Admin to centrally manage all email templates without code changes.

## 🎯 Accessing Template Management

### For Admin Users:
1. Login to Admin Dashboard
2. Navigate to **Comm Templates** in the sidebar
3. You'll see a list of all email templates

### For Super Admin:
1. Login to Super Admin Dashboard  
2. Navigate to **Communication Templates** section
3. Full CRUD access to all templates

## 📝 Template Management Features

### Viewing Templates
- **List View:** See all templates with status (Active/Inactive)
- **Search:** Filter templates by name or channel
- **Filter:** Filter by channel (SMS, Email, WhatsApp)
- **Status Toggle:** Quickly activate/deactivate templates

### Creating a New Template
1. Click **"Create Template"** button
2. Fill in the form:
   - **Name:** Template identifier (e.g., `consultation_confirmation`)
   - **Channel:** Select `email`
   - **Subject:** Email subject line (use `{{variable}}` for dynamic content)
   - **Body:** HTML email content (use `{{variable}}` for dynamic content)
   - **Variables:** System auto-detects variables from body/subject
   - **Active:** Check to activate immediately
3. Click **"Save Template"**

### Editing a Template
1. Click on a template from the list
2. Click **"Edit"** button
3. Modify subject, body, or variables
4. Click **"Update Template"**

### Template Variables

Variables are automatically detected from your template content. Use the format:
- `{{variable_name}}` - Double curly braces
- `{variable_name}` - Single curly braces (also supported)

**Common Variables:**
- `{{first_name}}` - Patient/User first name
- `{{last_name}}` - Patient/User last name
- `{{reference}}` - Consultation reference number
- `{{amount}}` - Payment amount
- `{{doctor_name}}` - Doctor's name
- `{{scheduled_date}}` - Consultation date
- `{{scheduled_time}}` - Consultation time

**See `EMAILS_IN_APPLICATION.md` for complete variable list per email type.**

## 📋 Available Email Templates

### Consultation-Related (5 templates)
1. **consultation_confirmation** - Sent to patients when consultation is confirmed
2. **consultation_admin_alert** - Sent to admin for new consultations
3. **consultation_doctor_notification** - Sent to doctors when assigned
4. **consultation_reminder** - Reminder before consultation
5. **consultation_status_change** - Notification when status changes

### Payment-Related (6 templates)
1. **payment_request** - Payment request to patients
2. **payment_received_admin** - Admin notification of payment
3. **payment_received_doctor** - Doctor notification of payment
4. **payment_failed** - Failed payment notification
5. **fee_adjustment** - Fee adjustment notification to patient
6. **fee_adjustment_admin** - Fee adjustment notification to admin

### Treatment Plan (2 templates)
1. **treatment_plan_notification** - Treatment plan ready notification
2. **treatment_plan_ready** - Treatment plan finalized notification

### Review & Feedback (1 template)
1. **review_request** - Request for patient review

### Account Creation (4 templates)
1. **canvasser_account_created** - Canvasser account creation
2. **nurse_account_created** - Nurse account creation
3. **customer_care_account_created** - Customer Care account creation
4. **care_giver_account_created** - Care Giver account creation

### Doctor-Related (3 templates)
1. **doctor_reassignment** - Doctor reassignment notification
2. **referral_notification** - Consultation referral notification
3. **documents_forwarded_to_doctor** - Documents forwarded notification

### System & Security (2 templates)
1. **security_alert** - Security event alerts
2. **delay_query_notification** - Delay query to doctors

### Campaign & Marketing (1 template)
1. **campaign_notification** - Campaign announcements

### Canvasser-Specific (1 template)
1. **canvasser_consultation_confirmation** - Canvasser consultation confirmation

### Medical Reports (1 template)
1. **vital_signs_report** - Vital signs report notification

### Custom Communication (1 template)
1. **custom_communication** - Custom messages from Customer Care

**Total: 26 Email Templates**

## 🎨 Template Design Guidelines

### HTML Structure
- Use inline CSS for email compatibility
- Keep width under 600px for mobile responsiveness
- Use tables for layout (better email client support)
- Test in multiple email clients

### Best Practices
1. **Keep it Simple:** Avoid complex layouts
2. **Use Brand Colors:** Maintain DoctorOnTap purple (#9333EA)
3. **Clear CTAs:** Make action buttons prominent
4. **Mobile-Friendly:** Ensure readability on small screens
5. **Test Variables:** Always test with real data

### Example Template Structure
```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{subject}}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #9333EA;">{{title}}</h2>
        <p>Dear {{first_name}} {{last_name}},</p>
        <!-- Content here -->
        <p>Best regards,<br>DoctorOnTap Team</p>
    </div>
</body>
</html>
```

## 🔄 How Templates Work

1. **Template Selection:** When an email is sent, the system looks for a template matching the Mail class name
2. **Variable Replacement:** All `{{variables}}` are replaced with actual data
3. **Fallback:** If template not found or inactive, system uses original email view
4. **Logging:** All template usage is logged for monitoring

## ✅ Activating/Deactivating Templates

- **Active Templates:** Used by the system when sending emails
- **Inactive Templates:** System falls back to original email views
- **Toggle:** Click the status toggle in the template list to activate/deactivate

## 🚨 Important Notes

1. **Backward Compatibility:** Original email views remain as fallback
2. **Variable Names:** Must match exactly (case-sensitive)
3. **HTML Safety:** Ensure HTML is properly escaped in variables
4. **Testing:** Always test templates after creation/modification
5. **Backup:** Keep backups of working templates

## 📊 Template Usage Tracking

- Templates are automatically used when:
  - Template exists in database
  - Template is active
  - Template channel matches (email)
  - Mail class is properly configured

## 🆘 Troubleshooting

### Email not using template?
1. Check template is active
2. Verify template name matches Mail class mapping
3. Check template channel is 'email'
4. Review application logs for errors

### Variables not replacing?
1. Verify variable names match exactly
2. Check variable is passed in Mail class constructor
3. Ensure variable format: `{{variable_name}}`

### Template not appearing?
1. Check you have Admin/Super Admin access
2. Verify template exists in database
3. Check filters/search terms

## 📞 Support

For template-related issues:
1. Check application logs: `storage/logs/laravel.log`
2. Review `EMAILS_IN_APPLICATION.md` for variable requirements
3. Contact system administrator for access issues

---

**Last Updated:** 2026-02-18  
**Version:** 1.0

