# Treatment Plan - Patient Privacy Fix

## Problem
Patients are currently seeing ALL clinical documentation including:
- ❌ Presenting Complaint
- ❌ History of Complaint
- ❌ Past Medical History
- ❌ Family History
- ❌ Drug History
- ❌ Social History
- ❌ Diagnosis  
- ❌ Investigation

**These are clinical notes meant for doctors, not patients!**

## Solution
Created patient-friendly versions that ONLY show:
- ✅ Treatment Plan
- ✅ Prescribed Medications
- ✅ Follow-up Instructions
- ✅ Lifestyle Recommendations
- ✅ Next Appointment
- ✅ Referrals
- ✅ Additional Notes

## Files Created/Modified

### 1. New Patient-Friendly PDF
**File:** `resources/views/pdfs/treatment-plan-patient.blade.php`
- Patient-friendly PDF with NO clinical documentation
- Only shows treatment and medications
- Includes safety warnings and emergency instructions

### 2. Updated Email Notification  
**File:** `app/Mail/TreatmentPlanNotification.php`
- Changed line 92 from `'pdfs.treatment-plan'` to `'pdfs.treatment-plan-patient'`
- Patients now receive the simplified PDF via email

### 3. Web View (Still Needs Update)
**File:** `resources/views/consultation/treatment-plan.blade.php`
- Currently shows ALL clinical documentation (lines 81-172)
- **Action Needed:** Create patient-friendly web version OR hide clinical sections

## Original Files (Keep for Doctors/Admins)
- `resources/views/pdfs/treatment-plan.blade.php` - Full clinical PDF
- Keep this for internal use, doctor dashboards, admin exports

## Deployment Steps

1. **Commit changes:**
```bash
git add resources/views/pdfs/treatment-plan-patient.blade.php
git add app/Mail/TreatmentPlanNotification.php
git commit -m "Privacy fix: Hide clinical documentation from patients

- Created patient-friendly PDF (treatment-plan-patient.blade.php)
- Patients now only see treatment, medications, follow-up instructions
- Clinical notes (diagnosis, history, investigations) hidden from patients
- Full clinical PDF (treatment-plan.blade.php) kept for doctors/admins"
```

2. **Push to GitHub:**
```bash
git push origin livewire
```

3. **Deploy to Production:**
```bash
cd /home/doctoron/domains/new.doctorontap.com.ng/laravel
git pull origin livewire
php artisan view:clear
php artisan optimize
```

## What's Still TODO

### Option A: Hide Clinical Sections from Web View
Wrap lines 81-172 in `consultation/treatment-plan.blade.php` with an if-statement:
```blade
@if(auth()->guard('doctor')->check() || auth()->guard('admin')->check())
    <!-- Show clinical documentation only to doctors/admins -->
@endif
```

### Option B: Create Separate Web Views
- `consultation/treatment-plan.blade.php` - Keep for doctors
- `consultation/treatment-plan-patient.blade.php` - Create new for patients
- Update `ConsultationController@accessTreatmentPlan` to route accordingly

## Recommendation
**Option A** is simpler and requires minimal changes. Just wrap the clinical sections with authentication checks.

## Testing
After deployment, verify:
1. ✅ Patient receives email with simplified PDF
2. ✅ PDF only shows treatment/medications
3. ✅ No diagnosis or clinical notes visible  
4. ✅ Emergency instructions included
5. ❓ Web view still shows clinical notes (until TODO completed)

## Impact
- **Privacy:** ✅ Improved - Clinical notes protected
- **Compliance:** ✅ Better HIPAA/medical privacy practices
- **User Experience:** ✅ Better - Patients see only what they need
- **Doctor Workflow:** ✅ Unchanged - Full clinical PDF still available

