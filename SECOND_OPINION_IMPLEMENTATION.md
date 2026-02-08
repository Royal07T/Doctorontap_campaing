# Second Opinion Feature Implementation

## Overview
The Second Opinion feature has been successfully implemented across the DoctorOnTap platform. This feature allows patients to request professional reviews of existing medical results, diagnoses, or treatment plans from qualified doctors.

## Key Business Rules

### Doctor Capabilities
1. **Local Consultants (Nigeria/Rwanda)**
   - Can provide both full consultations AND second opinions
   - Can conduct live consultations, prescribe locally, and manage follow-up care
   - Full access to all consultation features

2. **International Doctors**
   - **Restricted to second opinions only**
   - Cannot initiate or conduct full consultations
   - Cannot prescribe locally
   - Cannot manage follow-up care within Nigeria/Rwanda

3. **Default Behavior**
   - All doctors have second opinion capability enabled by default (`can_provide_second_opinion = true`)
   - Doctors can opt-out of second opinion services via their profile settings

### Patient Experience
- Simple service selection: "Full Consultation" or "Second Opinion"
- For second opinions: Upload medical documents (lab results, X-rays, previous diagnoses)
- The system automatically determines if a doctor can escalate to full consultation based on their role and location

## Database Changes

### Doctors Table
New columns added:
- `can_provide_second_opinion` (boolean, default: true) - Whether doctor can provide second opinions
- `is_international` (boolean, default: false) - Whether doctor is practicing outside Nigeria/Rwanda
- `country_of_practice` (string, nullable) - Country where doctor is licensed
- `license_restrictions` (text, nullable) - Any restrictions on the doctor's license

### Consultations Table
New columns added:
- `service_type` (enum: 'full_consultation', 'second_opinion', default: 'full_consultation') - Type of service requested
- `can_escalate_to_full` (boolean, default: false) - Whether second opinion can be escalated
- `escalated_from_consultation_id` (foreign key, nullable) - Links to original second opinion if escalated
- `escalated_at` (timestamp, nullable) - When escalation occurred
- `second_opinion_notes` (text, nullable) - Doctor's notes for second opinion
- `second_opinion_documents` (json, nullable) - Uploaded medical documents for review

## Model Updates

### Doctor Model (`app/Models/Doctor.php`)
**New Methods:**
- `canConductFullConsultation()` - Checks if doctor can do full consultations
- `canProvideSecondOpinion()` - Checks if doctor can provide second opinions
- `isLocalDoctor()` - Checks if doctor is based in Nigeria/Rwanda
- `getConsultationCapabilities()` - Returns array of doctor's capabilities

**New Scopes:**
- `local()` - Filter for local doctors only
- `international()` - Filter for international doctors only
- `canProvideSecondOpinion()` - Filter for doctors offering second opinions

### Consultation Model (`app/Models/Consultation.php`)
**New Methods:**
- `isSecondOpinion()` - Check if consultation is a second opinion
- `isFullConsultation()` - Check if consultation is a full consultation
- `canEscalateToFull()` - Check if second opinion can be escalated
- `escalateToFullConsultation()` - Create new full consultation from second opinion

**New Relationships:**
- `escalatedFrom()` - Original second opinion consultation
- `escalatedTo()` - Full consultation(s) created from this second opinion

**New Scopes:**
- `secondOpinions()` - Filter for second opinion consultations
- `fullConsultations()` - Filter for full consultations

## Validation

### New Custom Rule: `DoctorCanProvideServiceType`
Location: `app/Rules/DoctorCanProvideServiceType.php`

Validates that the selected doctor is capable of providing the requested service type:
- For `full_consultation`: Checks `canConductFullConsultation()`
- For `second_opinion`: Checks `canProvideSecondOpinion()`

### Updated Request Validation
Location: `app/Http/Requests/ConsultationRequest.php`

Added validation for:
- `service_type` (required, must be 'full_consultation' or 'second_opinion')
- `doctor` field now uses `DoctorCanProvideServiceType` custom rule

## Controller Updates

### ConsultationService (`app/Services/ConsultationService.php`)
- Updated `createConsultation()` to handle `service_type`
- Added handling for `second_opinion_documents` upload
- Added helper method `handleDocumentUploads()` for file uploads

### Doctor DashboardController (`app/Http/Controllers/Doctor/DashboardController.php`)
Updated `updateProfile()` method to include:
- `can_provide_second_opinion` (boolean)
- `is_international` (boolean)
- `country_of_practice` (string, required if international)

## User Interface Updates

### 1. Consultation Booking Form
**Location:** `resources/views/consultation/index.blade.php`

**Changes:**
- Added service type selector with two options:
  - 🩺 Full Consultation - Complete medical consultation
  - 🔍 Second Opinion - Review of existing medical results
- Added contextual info box for second opinion explaining the service
- Updated file upload section to be more prominent for second opinions
- Made medical documents "required" for second opinion requests
- Added `service_type` to form data (default: 'full_consultation')

### 2. Doctor Listing - By Specialization
**Location:** `resources/views/patient/doctors-by-specialization.blade.php`

**Changes:**
- Added "Second Opinion Available" badge (blue) for doctors offering second opinions
- Added "International Doctor" badge (purple) for international doctors
- Badges appear below availability status

### 3. Doctor Listing - All Doctors
**Location:** `resources/views/patient/doctors.blade.php`

**Changes:**
- Added compact badge display for "Second Opinion" capability
- Added "International" badge for international doctors
- Badges displayed with icons and proper color coding

### 4. Doctor Profile Edit
**Location:** `resources/views/doctor/profile.blade.php`

**Changes:**
- Added new "Service Capabilities" section in Professional Details tab
- Toggle for "Provide Second Opinion Services" (with explanation)
- Toggle for "International Doctor" (with confirmation dialog and restrictions warning)
- Conditional "Country of Practice" field (appears only if international)
- Clear explanations of what each capability means

## Visual Design

### Service Type Selector
- Cards with radio buttons for easy selection
- Visual feedback with border color changes and background tints
- Icons for visual recognition (🩺 for full consultation, 🔍 for second opinion)
- Clear descriptions of each service type

### Badges
- **Second Opinion Badge:** Blue background, checkmark icon
- **International Doctor Badge:** Purple background, globe icon
- **Available Now Badge:** Green background, checkmark icon
- All badges use consistent design language with icons

### Info Boxes
- Blue info box appears when "Second Opinion" is selected
- Provides context about what the service includes
- Encourages document upload for comprehensive review

## Testing Checklist

### Database
- [x] Migration runs successfully
- [x] New columns added to `doctors` table
- [x] New columns added to `consultations` table
- [x] Foreign key constraints working properly

### Models
- [x] Doctor model methods return correct values
- [x] Consultation model methods work as expected
- [x] Scopes filter data correctly
- [x] Relationships function properly

### Forms & Validation
- [x] Service type selector displays correctly
- [x] Form validation includes new fields
- [x] Custom validation rule works correctly
- [x] File uploads handled properly

### UI Components
- [x] Badges display on doctor listings
- [x] Profile edit form includes new fields
- [x] Conditional display logic works
- [x] No linting errors in any file

## Next Steps (Optional Enhancements)

1. **Email Notifications**
   - Create specific email templates for second opinion requests
   - Notify patients when second opinion review is complete
   - Notify doctors when they receive second opinion requests

2. **Admin Dashboard**
   - Add statistics for second opinion consultations
   - Create filters to view second opinions separately
   - Add reports for international doctor activity

3. **Payment Adjustments**
   - Consider different pricing for second opinions vs full consultations
   - Add settings for second opinion consultation fees

4. **Escalation Workflow**
   - UI for doctors to initiate escalation from second opinion to full consultation
   - Patient notification when escalation is available
   - Seamless transition with data carry-over

5. **International Doctor Onboarding**
   - Dedicated onboarding flow for international doctors
   - Clear explanation of service restrictions
   - License verification for international credentials

## Files Modified

### Database
- `database/migrations/2026_02_08_021513_add_second_opinion_capabilities_to_doctors_and_consultations.php`

### Models
- `app/Models/Doctor.php`
- `app/Models/Consultation.php`

### Controllers & Services
- `app/Http/Controllers/Doctor/DashboardController.php`
- `app/Services/ConsultationService.php`

### Validation
- `app/Rules/DoctorCanProvideServiceType.php` (new)
- `app/Http/Requests/ConsultationRequest.php`

### Views
- `resources/views/consultation/index.blade.php`
- `resources/views/patient/doctors.blade.php`
- `resources/views/patient/doctors-by-specialization.blade.php`
- `resources/views/doctor/profile.blade.php`

## Deployment Notes

1. Run migrations: `php artisan migrate`
2. Clear cache: `php artisan cache:clear`
3. Clear config: `php artisan config:clear`
4. Clear views: `php artisan view:clear`

## Support & Documentation

For questions or issues related to the Second Opinion feature, refer to:
- This implementation document
- Model documentation in respective files
- Custom validation rule documentation

---

**Implementation Date:** February 8, 2026  
**Feature Status:** ✅ Complete and Ready for Testing

