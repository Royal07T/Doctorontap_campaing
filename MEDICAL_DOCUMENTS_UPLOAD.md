# Medical Documents Upload Feature

## Overview
Patients can now upload medical documents (test results, lab reports, X-rays, prescriptions, etc.) when booking a consultation. This feature helps doctors better understand the patient's condition before the consultation begins.

## Features Implemented

### 1. **File Upload Capability**
- Patients can upload multiple files during consultation booking
- Supported formats: PDF, JPG, JPEG, PNG, DOC, DOCX
- Maximum file size: 5MB per file
- Files are stored securely in `storage/app/public/medical_documents/`

### 2. **User Interface Enhancements**
- Clean, modern file upload interface with drag-and-drop support
- Real-time file preview showing:
  - File name
  - File size
  - File type icon
- Ability to remove files before submission
- Visual feedback for uploaded files

### 3. **Database Changes**
- Added `medical_documents` column to `consultations` table
- Stores file metadata as JSON including:
  - Original filename
  - Stored filename
  - File path
  - File size
  - MIME type

### 4. **Email Notifications**
All email templates now include information about uploaded documents:
- **Patient Confirmation Email**: Shows count of uploaded files
- **Admin Alert Email**: Highlights uploaded documents with visual indicator
- **Doctor Notification Email**: Alerts doctor about available documents

### 5. **Admin Dashboard**
- Displays all uploaded documents in consultation details
- Provides download links for each document
- Shows file metadata (name, size)
- Visual file cards with icons

## Files Modified

### Backend
1. **Migration**: `database/migrations/2025_10_10_100612_add_medical_documents_to_consultations_table.php`
   - Adds `medical_documents` JSON column

2. **Model**: `app/Models/Consultation.php`
   - Added `medical_documents` to fillable array
   - Added JSON casting for `medical_documents`

3. **Controller**: `app/Http/Controllers/ConsultationController.php`
   - Added file validation rules
   - Handles file upload and storage
   - Generates unique filenames to prevent conflicts
   - Stores file metadata in database

### Frontend
4. **Consultation Form**: `resources/views/consultation/index.blade.php`
   - Added file upload input field
   - Added Alpine.js file handling methods
   - Updated form submission to use FormData API
   - Added file preview and removal functionality

### Email Templates
5. **Confirmation Email**: `resources/views/emails/consultation-confirmation.blade.php`
6. **Admin Alert Email**: `resources/views/emails/consultation-admin-alert.blade.php`
7. **Doctor Notification Email**: `resources/views/emails/consultation-doctor-notification.blade.php`

### Admin Panel
8. **Consultation Details**: `resources/views/admin/consultation-details.blade.php`
   - Added documents display section
   - Download buttons for each file

## Usage

### For Patients

1. Navigate to the consultation booking form
2. Fill in your personal and medical information
3. In the "Medical Triage" section, look for "Upload Medical Documents (Optional)"
4. Click the file input or drag files to upload
5. You can upload multiple files (each up to 5MB)
6. Preview your selected files and remove any if needed
7. Submit the form - files will be uploaded automatically

### For Doctors/Admin

1. Log into the admin dashboard
2. Navigate to consultations list
3. Click on any consultation to view details
4. Scroll to the "Medical Triage" section
5. If documents were uploaded, you'll see a "📎 Medical Documents" section
6. Click "Download" on any document to view/download it

## Technical Details

### File Storage
- Files are stored in: `storage/app/public/medical_documents/`
- Public access via: `public/storage/medical_documents/`
- Symbolic link created using: `php artisan storage:link`

### File Naming Convention
```
{timestamp}_{uniqueid}_{original_filename}
```
Example: `1728561972_66fb3d8a12345_lab_report.pdf`

### Validation Rules
```php
'medical_documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120'
```

### Database Structure
The `medical_documents` column stores an array of objects:
```json
[
  {
    "original_name": "lab_report.pdf",
    "stored_name": "1728561972_66fb3d8a12345_lab_report.pdf",
    "path": "medical_documents/1728561972_66fb3d8a12345_lab_report.pdf",
    "size": 245678,
    "mime_type": "application/pdf"
  }
]
```

## Security Considerations

1. **File Validation**: Only specific file types are allowed
2. **File Size Limit**: 5MB per file prevents server overload
3. **Unique Filenames**: Prevents file overwriting and conflicts
4. **Secure Storage**: Files stored outside public directory
5. **Access Control**: Files accessible only through authenticated routes (admin panel)

## Future Enhancements (Optional)

1. **Image Preview**: Show thumbnail previews for image files
2. **Virus Scanning**: Integrate antivirus scanning for uploaded files
3. **File Compression**: Automatically compress images to save storage
4. **Download All**: Bulk download option for multiple documents
5. **Document Expiry**: Auto-delete documents after consultation is completed
6. **Patient Portal**: Allow patients to view/manage their uploaded documents

## Environment Variables

No additional environment variables needed. The feature works with existing Laravel storage configuration.

## Troubleshooting

### Files not uploading?
- Check file size (must be under 5MB)
- Verify file format is supported
- Ensure storage directory has write permissions: `chmod -R 775 storage`

### Files not accessible?
- Make sure symbolic link exists: `php artisan storage:link`
- Check storage permissions: `chmod -R 775 storage/app/public`

### Download links not working?
- Verify `APP_URL` is set correctly in `.env`
- Ensure public storage symlink exists

## Testing

### Manual Testing Steps

1. **Upload Test**:
   - Book a consultation with different file types
   - Verify files appear in `storage/app/public/medical_documents/`
   - Check database record includes file metadata

2. **Email Test**:
   - Verify emails mention uploaded documents
   - Check document count is accurate

3. **Admin Dashboard Test**:
   - View consultation details
   - Verify documents are displayed
   - Test download functionality

4. **Edge Cases**:
   - Try uploading without files (should work)
   - Try uploading multiple files
   - Try uploading files larger than 5MB (should fail with validation)
   - Try uploading unsupported file types (should fail)

## Support

For issues or questions, please contact the development team or refer to Laravel's [File Storage Documentation](https://laravel.com/docs/filesystem).

