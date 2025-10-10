# Admin Document Forwarding Feature

## Overview
Admins can now view patient medical documents in the dashboard and forward them directly to assigned doctors via email with file attachments. This feature streamlines the process of sharing patient medical records with healthcare providers.

## Features Implemented

### 1. **View Medical Documents in Admin Dashboard**
- View all uploaded documents in consultation details page
- See document names, sizes, and types
- Download individual documents for review
- Visual indicators showing document count

### 2. **Forward Documents to Doctor**
- One-click forwarding to assigned doctor
- Sends email with all documents as attachments
- Prevents duplicate forwarding
- Tracks forwarding timestamp
- Shows confirmation when documents were sent

### 3. **Email Notifications**
- Professional email template with patient information
- All medical documents attached to email
- Includes consultation context (problem, severity, emergency symptoms)
- Confidentiality notice included

### 4. **Tracking & Status**
- Records when documents were forwarded
- Displays forwarding status in admin dashboard
- Shows timestamp and doctor name
- Prevents accidental re-forwarding

## How to Use

### For Admins

#### Viewing Documents
1. Log into the admin dashboard
2. Navigate to **Consultations** from the menu
3. Click on any consultation to view details
4. Scroll to the **"Medical Triage"** section
5. If the patient uploaded documents, you'll see:
   - "📎 Medical Documents" section
   - List of all uploaded files with sizes
   - Download button for each file

#### Forwarding Documents to Doctor
1. In the consultation details page, locate the **"📎 Medical Documents"** section
2. Click the green **"Forward to Doctor"** button
3. The system will:
   - Validate that a doctor is assigned
   - Check that documents exist
   - Send an email with all documents attached
   - Update the forwarding status
4. A success message will appear: "✓ Medical documents have been forwarded to the doctor successfully!"
5. The page will refresh to show the forwarding timestamp

#### Forwarding Status
After forwarding, you'll see:
```
✓ Documents forwarded to Dr. [Doctor Name] on Oct 10, 2025 10:30 AM
```

### For Doctors

When documents are forwarded, doctors receive:
1. An email with subject: **"Patient Medical Documents - [Patient Name] (Ref: [Reference])"**
2. Complete patient information (name, age, gender, contact)
3. Medical details (problem, severity, consult mode)
4. List of attached documents
5. All documents as email attachments (can be downloaded)

## Technical Details

### Database Changes

#### New Column: `documents_forwarded_at`
```sql
ALTER TABLE consultations ADD COLUMN documents_forwarded_at TIMESTAMP NULL;
```

This column tracks when documents were sent to the doctor.

### API Endpoint

**POST** `/admin/consultations/{id}/forward-documents`

**Authentication:** Admin middleware required

**Parameters:**
- `id` (URL parameter): Consultation ID

**Response:**
```json
{
  "success": true,
  "message": "Medical documents forwarded successfully to Dr. John Smith"
}
```

**Error Responses:**
```json
// No doctor assigned
{
  "success": false,
  "message": "No doctor assigned to this consultation"
}

// No documents to forward
{
  "success": false,
  "message": "No medical documents to forward"
}

// Already forwarded
{
  "success": false,
  "message": "Documents already forwarded to doctor on Oct 10, 2025 10:30"
}
```

### Email Template

**Template File:** `resources/views/emails/documents-forwarded-to-doctor.blade.php`

**Features:**
- Professional medical-themed design (green color scheme)
- Patient information card
- Medical details card
- Emergency symptom warnings
- Document list preview
- Confidentiality notice
- All files attached to email

### Files Modified/Created

#### New Files
1. **Migration:** `database/migrations/2025_10_10_101726_add_documents_forwarded_at_to_consultations_table.php`
   - Adds forwarding timestamp tracking

2. **Mailable:** `app/Mail/DocumentsForwardedToDoctor.php`
   - Handles email composition
   - Attaches all medical documents
   - Uses proper MIME types

3. **Email Template:** `resources/views/emails/documents-forwarded-to-doctor.blade.php`
   - Professional HTML email design
   - Mobile responsive
   - Clear document listing

#### Modified Files
1. **Model:** `app/Models/Consultation.php`
   - Added `documents_forwarded_at` to casts

2. **Controller:** `app/Http/Controllers/Admin/DashboardController.php`
   - Added `forwardDocumentsToDoctor()` method
   - Imported `DocumentsForwardedToDoctor` mail class

3. **Routes:** `routes/web.php`
   - Added POST route for forwarding documents

4. **View:** `resources/views/admin/consultation-details.blade.php`
   - Added "Forward to Doctor" button
   - Added forwarding status display
   - Added JavaScript function for forwarding
   - Added loading state UI

## Validation & Error Handling

### Pre-Forward Checks
- ✅ Doctor must be assigned to consultation
- ✅ At least one document must be uploaded
- ✅ Documents not already forwarded
- ✅ All document files must exist on server

### Security
- 🔒 Admin authentication required
- 🔒 Files verified to exist before attaching
- 🔒 Proper MIME type validation
- 🔒 Confidentiality notice in email

## Benefits

### For Healthcare Providers
- Receive patient records before consultation
- Better prepared for patient meetings
- Can review test results in advance
- Professional email delivery system

### For Admins
- One-click document sharing
- No manual file downloading/uploading
- Automatic tracking of sent documents
- Cannot accidentally send duplicates

### For Patients
- Their medical records reach doctors securely
- No need to re-upload documents
- Professional handling of sensitive data

## Email Attachment Details

### Supported Formats
All uploaded document formats are supported:
- PDF documents
- Images (JPG, JPEG, PNG)
- Word documents (DOC, DOCX)

### File Size
- Each file up to 5MB
- Multiple files supported
- Total email size automatically managed

### File Naming
Files retain their original names when attached to emails for easy identification.

## User Interface

### Forward Button
- **Color:** Green (medical/health theme)
- **Icon:** Email icon
- **Location:** Top right of Medical Documents section
- **States:**
  - Normal: "Forward to Doctor"
  - Loading: Spinner animation with "Forwarding..."
  - Disabled: After forwarding complete

### Status Indicator
After forwarding, displays:
```
✓ Documents forwarded to Dr. [Name] on [Date Time]
```
- **Color:** Green background
- **Icon:** Checkmark
- **Format:** Professional timestamp

## Troubleshooting

### Documents not forwarding?
1. Check that a doctor is assigned to the consultation
2. Verify documents were uploaded by patient
3. Ensure documents haven't been forwarded already
4. Check server email configuration

### Doctor not receiving email?
1. Verify doctor's email address in system
2. Check spam/junk folder
3. Review Laravel logs: `storage/logs/laravel.log`
4. Test email configuration

### Missing attachments?
1. Verify files exist in `storage/app/public/medical_documents/`
2. Check file permissions: `chmod -R 775 storage`
3. Ensure `php artisan storage:link` was run

### Button not working?
1. Check browser console for JavaScript errors
2. Verify CSRF token is present
3. Ensure admin is authenticated
4. Check network tab for API response

## Best Practices

### When to Forward Documents
- ✅ After reviewing documents yourself
- ✅ When doctor requests patient records
- ✅ Before scheduled consultations
- ✅ For urgent/severe cases immediately

### When NOT to Forward
- ❌ If documents contain wrong patient info
- ❌ If files appear corrupted
- ❌ If doctor already has the documents
- ❌ If consultation is cancelled

## Future Enhancements (Optional)

1. **Bulk Forwarding:** Forward documents for multiple consultations at once
2. **Custom Message:** Allow admin to add a note when forwarding
3. **Doctor Acknowledgment:** Track when doctor views/downloads documents
4. **Re-forward Option:** Allow re-sending with admin confirmation
5. **Document Preview:** View documents without downloading
6. **Forwarding History:** Log of all document forwarding events
7. **Selective Forwarding:** Choose specific documents to forward

## Testing Checklist

- [ ] Upload documents as patient
- [ ] View documents in admin dashboard
- [ ] Download individual documents
- [ ] Forward documents to doctor
- [ ] Verify email received by doctor
- [ ] Check email attachments open correctly
- [ ] Verify status shows "forwarded" after sending
- [ ] Try to forward again (should be prevented)
- [ ] Test with multiple document types
- [ ] Test with large files (up to 5MB)

## Support

For issues or questions regarding document forwarding:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify email configuration in `.env`
3. Test with different email providers
4. Contact development team if issues persist

## Related Documentation
- [MEDICAL_DOCUMENTS_UPLOAD.md](MEDICAL_DOCUMENTS_UPLOAD.md) - Patient document upload feature
- [ADMIN_DASHBOARD.md](ADMIN_DASHBOARD.md) - Admin dashboard overview
- [EMAIL_NOTIFICATIONS_FLOW.md](EMAIL_NOTIFICATIONS_FLOW.md) - Email system documentation

