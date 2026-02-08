# Doctor Profile Verification Process

## Overview
This document explains how doctors can complete their profile verification to remove the "Action Required" banner from their dashboard.

## What Triggers the Verification Banner?

The amber warning banner appears when:
- `mdcn_certificate_verified` is **false** (MDCN certificate not verified by admin)
- OR `is_approved` is **false** (Account not approved by admin)

The banner **automatically disappears** when BOTH conditions are met:
- ✅ `mdcn_certificate_verified` = `true`
- ✅ `is_approved` = `true`

## Verification Checklist (5 Steps)

### Step 1: Complete Basic Information ✏️
**Location:** Profile → Basic Info tab

**Required Fields:**
- First Name
- Last Name
- Email Address
- Phone Number
- Gender
- Specialization (e.g., Dermatologist, Cardiologist)
- Experience (years)
- Location (e.g., Lagos, Nigeria)
- Place of Work
- Bio (Professional summary)

**How to Complete:**
1. Click "Go to Profile" button on dashboard banner
2. Navigate to "Basic Info" tab
3. Fill in all fields marked with asterisk (*)
4. Upload a professional photo (optional but recommended)
5. Click "Save Changes" at the bottom

### Step 2: Upload MDCN Certificate 📄
**Location:** Profile → Licenses & KYC tab

**What You Need:**
- Valid MDCN (Medical and Dental Council of Nigeria) certificate
- File formats: PDF, JPG, JPEG, PNG
- Maximum file size: 5MB

**How to Upload:**
1. Go to Profile page
2. Click on "Licenses & KYC" tab
3. Find "MDCN Certificate" section
4. Click on the upload area or drag-and-drop your certificate
5. Wait for upload to complete
6. Click "Save Changes"

**Status Badges:**
- 🟡 "ACTION REQUIRED" - No certificate uploaded yet
- 🔵 "UPLOADED" - Certificate uploaded, awaiting verification
- 🟢 "VERIFIED" - Certificate verified by admin

### Step 3: Upload Insurance Documents 🛡️
**Location:** Profile → Licenses & KYC tab

**What You Need:**
- Professional liability insurance document
- File formats: PDF, JPG, JPEG, PNG
- Maximum file size: 5MB

**How to Upload:**
1. In the same "Licenses & KYC" tab
2. Scroll to "Insurance Documents" section
3. Click on the upload area
4. Select your insurance document
5. Click "Save Changes"

**Why This is Required:**
Professional liability insurance protects both you and your patients. It's a standard requirement for telemedicine practice.

### Step 4: Wait for Admin Verification ⏳
**Timeline:** Usually 24-48 hours

**What Happens:**
1. Admin team receives notification of your profile submission
2. They review your:
   - MDCN certificate authenticity
   - Insurance document validity
   - Profile completeness
3. They verify your credentials against MDCN database
4. Once verified, they approve your account

**During This Time:**
- You can still access your dashboard
- You cannot accept consultations yet
- The banner shows "Awaiting admin verification"
- A spinning loader icon indicates review in progress

**You'll Be Notified When:**
- ✅ Your MDCN certificate is verified
- ✅ Your account is approved
- 📧 You'll receive an email confirmation
- 🔔 Dashboard banner will turn green

### Step 5: Verification Complete ✅
**What Changes:**
- Banner turns green with "Your profile is 100% compliant. KYC Verified"
- You can now accept patient consultations
- You're visible in patient search results
- Full platform access granted

## Enhanced Dashboard Banner Features

### Progress Tracking
The new banner shows:
- **Progress bar** - Visual indicator of completion (X/5 steps)
- **Percentage** - Shows exact completion percentage
- **Color-coded status** - Green checkmarks for completed items

### Expandable Checklist
Click "Show Details" to see:
- ✅ Completed items (green checkmark)
- ⏳ Pending items (orange spinner for admin review)
- ❌ Incomplete items (gray warning icon)

### Action Buttons
- **"Go to Profile"** - Direct link to profile page (orange button)
- **"Show/Hide Details"** - Toggle detailed checklist (white button)

### Real-Time Status Updates
The banner automatically updates as you:
- Complete basic information
- Upload certificates
- Upload insurance documents
- Receive admin verification

## Common Issues & Solutions

### Issue 1: "I uploaded my certificate but it still shows as pending"
**Solution:**
- Wait for admin verification (24-48 hours)
- Check your email for admin requests for additional information
- Ensure the uploaded file is clear and readable
- Verify the certificate is current and not expired

### Issue 2: "The progress bar isn't updating"
**Solution:**
- Clear your browser cache
- Refresh the dashboard page
- Ensure you clicked "Save Changes" after uploading
- Check if the file upload was successful

### Issue 3: "I don't have professional liability insurance"
**Solution:**
- Contact Nigerian medical insurance providers
- Options include: AIICO, AXA Mansard, Leadway
- Some medical associations provide group insurance
- This is a mandatory requirement for practice

### Issue 4: "My MDCN certificate is being processed"
**Solution:**
- Upload a provisional certificate if available
- Contact MDCN to expedite your certificate
- Inform admin team via support ticket
- Temporary provisional access may be granted in special cases

### Issue 5: "How long does admin verification take?"
**Solution:**
- Standard processing: 24-48 hours
- Peak times (weekends/holidays): Up to 72 hours
- If delayed beyond 72 hours, contact support
- Email: support@doctorontap.com

## Technical Details

### Database Fields Checked

```php
// Banner disappears when BOTH are true:
$isFullyVerified = $doctor->mdcn_certificate_verified && $doctor->is_approved;

// Individual checks for progress tracking:
$hasBasicInfo = $doctor->first_name && $doctor->last_name && 
                $doctor->email && $doctor->phone && 
                $doctor->specialization;

$hasCertificate = $doctor->certificate_path || $doctor->certificate_data;

$hasInsurance = $doctor->insurance_document;

$isCertificateVerified = $doctor->mdcn_certificate_verified;

$isApproved = $doctor->is_approved;
```

### Progress Calculation

```php
$completedCount = 0;
if ($hasBasicInfo) $completedCount++;        // Step 1
if ($hasCertificate) $completedCount++;      // Step 2
if ($hasInsurance) $completedCount++;        // Step 3
if ($isCertificateVerified) $completedCount++; // Step 4
if ($isApproved) $completedCount++;          // Step 5

$totalSteps = 5;
$progressPercentage = ($completedCount / $totalSteps) * 100;
```

## Admin Verification Process

### For Administrators:
Doctors cannot self-verify. The following must be done by admin:

1. **Review Uploaded Documents**
   - Verify MDCN certificate authenticity
   - Check certificate expiration date
   - Validate insurance document

2. **Set Verification Flags**
   ```sql
   UPDATE doctors SET 
       mdcn_certificate_verified = 1,
       mdcn_certificate_verified_at = NOW(),
       mdcn_certificate_verified_by = [admin_id]
   WHERE id = [doctor_id];
   ```

3. **Approve Account**
   ```sql
   UPDATE doctors SET 
       is_approved = 1,
       approved_at = NOW(),
       approved_by = [admin_id]
   WHERE id = [doctor_id];
   ```

4. **Send Notification**
   - Email confirmation to doctor
   - SMS notification (optional)
   - In-app notification

## Benefits of Full Verification

### For Doctors:
- ✅ Accept patient consultations
- ✅ Appear in search results
- ✅ Receive consultation requests
- ✅ Access full earnings
- ✅ Higher patient trust
- ✅ Priority listing in some searches

### For Platform:
- ✅ Regulatory compliance
- ✅ Patient safety assurance
- ✅ Reduced liability
- ✅ Professional standard maintenance
- ✅ Trust and credibility

## Support & Assistance

### Need Help?
- 📧 Email: support@doctorontap.com
- 📞 Phone: [Support Number]
- 💬 Live Chat: Available on dashboard
- 📝 Support Ticket: Create from dashboard

### Useful Links:
- MDCN Website: https://mdcn.gov.ng/
- Insurance Providers Directory: [Link]
- Verification FAQ: [Link]
- Video Tutorial: [Link]

## Summary

**To remove the verification banner:**
1. ✏️ Complete basic information
2. 📄 Upload MDCN certificate
3. 🛡️ Upload insurance documents
4. ⏳ Wait for admin verification (24-48 hours)
5. ✅ Banner automatically disappears when verified

**The banner will turn green automatically** when both:
- MDCN certificate is verified by admin
- Account is approved by admin

No additional action required from doctor after admin approval!

---

**Last Updated:** February 8, 2026  
**Status:** ✅ Implementation Complete

