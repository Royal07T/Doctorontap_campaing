# 📧 Vital Signs Email & PDF Report Feature

## ✅ Feature Overview

When a nurse records vital signs for a patient, the system now automatically:
1. ✅ **Saves the vital signs** to the database
2. ✅ **Generates a professional PDF report** with all measurements
3. ✅ **Sends the report via email** to the patient's email address

---

## 🎯 How It Works

### **Nurse Workflow:**

1. **Navigate to Nurse Dashboard**
   - URL: `/nurse/dashboard`
   - Login with nurse credentials

2. **Search for Patient**
   - Click "Search Patients" or go to `/nurse/patients`
   - Search by name, email, or phone number

3. **Record Vital Signs**
   - Click on a patient to view their details
   - Click "Record Vital Signs" button
   - Fill in the vital signs form:
     - Blood Pressure (e.g., 120/80)
     - Heart Rate (bpm)
     - Oxygen Saturation (%)
     - Temperature (°C)
     - Respiratory Rate (breaths/min)
     - Blood Sugar (mg/dL)
     - Height (cm)
     - Weight (kg)
     - Notes (optional observations)
   
4. **Submit & Automatic Email Sent**
   - Click "Record Vital Signs" button
   - System saves to database
   - PDF is generated automatically
   - Email is sent to patient with PDF attachment

---

## 📄 PDF Report Contents

The generated PDF includes:

### **Report Header:**
- DoctorOnTap branding
- Report title
- Generation date & time

### **Patient Information:**
- Full name
- Gender
- Phone number
- Email address

### **Recording Details:**
- Nurse name
- Date & time recorded
- Report generation timestamp

### **Vital Signs Measurements:**
All recorded vitals displayed in professional cards:
- Blood Pressure (with interpretation)
- Heart Rate
- Oxygen Saturation
- Temperature
- Respiratory Rate
- Blood Sugar

### **Physical Measurements:**
- Height
- Weight
- BMI (automatically calculated)
- BMI interpretation (Normal, Underweight, Overweight, Obese)

### **Nurse's Notes:**
- Any observations or comments recorded by the nurse

### **Footer:**
- Report ID for tracking
- Disclaimer about medical advice
- Contact information
- Confidentiality notice

---

## 📧 Email Details

**Subject:** "Your Vital Signs Report from DoctorOnTap"

**Email Contains:**
- Professional HTML template with DoctorOnTap branding
- Summary of vital signs
- Recording details (nurse name, date/time)
- PDF report attached as: `vital-signs-report.pdf`
- Call-to-action button to book consultation
- Footer with contact information

---

## 🛠️ Technical Implementation

### **Files Created/Modified:**

1. **Controller:** `app/Http/Controllers/Nurse/DashboardController.php`
   - Added PDF generation using DomPDF
   - Added email sending functionality
   - Error handling for email failures

2. **Mailable:** `app/Mail/VitalSignsReport.php`
   - Handles email composition
   - Attaches PDF to email

3. **Email Template:** `resources/views/emails/vital-signs-report.blade.php`
   - Professional HTML email design
   - Responsive layout
   - DoctorOnTap branding

4. **PDF Template:** `resources/views/pdfs/vital-signs-report.blade.php`
   - Professional medical report layout
   - Comprehensive vital signs display
   - BMI calculation and interpretation
   - Blood pressure interpretation

### **Package Installed:**
- **barryvdh/laravel-dompdf** (v3.1) - For PDF generation

---

## 🧪 Testing Instructions

### **Step 1: Test Email Configuration**

First, ensure your email configuration is set in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@doctorontap.com
MAIL_FROM_NAME="DoctorOnTap"
```

### **Step 2: Test Vital Signs Recording**

1. Login as a nurse:
   - Email: `demo.nurse@doctorontap.com`
   - Password: `password123`

2. Navigate to `/nurse/patients`

3. Search for a patient or use a test patient

4. Click "Record Vital Signs"

5. Fill in sample data:
   ```
   Blood Pressure: 120/80
   Heart Rate: 72
   Oxygen Saturation: 98
   Temperature: 37.0
   Respiratory Rate: 16
   Blood Sugar: 95
   Height: 170
   Weight: 70
   Notes: Patient appears healthy
   ```

6. Submit the form

7. Check:
   - ✅ Success message appears
   - ✅ Database record created
   - ✅ Patient receives email with PDF attachment

### **Step 3: Verify Database**

```bash
php artisan tinker
```

```php
// Check latest vital sign record
\App\Models\VitalSign::latest()->first();

// Check if email was queued (if using queue)
// Or check your email inbox
```

---

## 📊 Database Schema

**Table:** `vital_signs`

```sql
- id (bigint, primary key)
- patient_id (foreign key to patients)
- nurse_id (foreign key to nurses)
- blood_pressure (string, nullable)
- oxygen_saturation (decimal, nullable)
- temperature (decimal, nullable)
- blood_sugar (decimal, nullable)
- height (decimal, nullable)
- weight (decimal, nullable)
- heart_rate (integer, nullable)
- respiratory_rate (integer, nullable)
- notes (text, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

---

## 🔍 Error Handling

### **If Email Fails:**
- Vital signs are still saved to database ✅
- User receives message: "Vital signs recorded but failed to send email"
- Error is logged in `storage/logs/laravel.log`
- Patient can still access their data through the system

### **Common Email Issues:**

1. **SMTP Configuration Error**
   - Check `.env` mail settings
   - Verify SMTP credentials
   - Test with: `php artisan tinker` → `Mail::raw('Test', fn($msg) => $msg->to('test@example.com')->subject('Test'))`

2. **PDF Generation Error**
   - Check if `barryvdh/laravel-dompdf` is installed
   - Clear views: `php artisan view:clear`
   - Check PDF template syntax

3. **Patient Email Invalid**
   - System validates email format on patient creation
   - Update patient email if needed

---

## 🎨 Customization

### **Change Email Design:**
Edit: `resources/views/emails/vital-signs-report.blade.php`

### **Change PDF Layout:**
Edit: `resources/views/pdfs/vital-signs-report.blade.php`

### **Modify Email Subject:**
Edit: `app/Mail/VitalSignsReport.php` - `envelope()` method

### **Add More Vital Signs:**
1. Add column to `vital_signs` table
2. Update form in nurse dashboard
3. Add validation in controller
4. Update PDF and email templates

---

## 🚀 Features Included

✅ **Automatic PDF Generation** - No manual intervention needed  
✅ **Professional Medical Report** - Branded and formatted  
✅ **Email with Attachment** - Sent to patient automatically  
✅ **BMI Auto-Calculation** - Computed from height & weight  
✅ **Blood Pressure Interpretation** - Normal, High, Low indicators  
✅ **Error Logging** - All errors tracked in logs  
✅ **Database Storage** - All data persisted  
✅ **Responsive Email Template** - Works on all devices  
✅ **Print-Friendly PDF** - Patients can print for records  
✅ **HIPAA-Conscious Design** - Confidentiality notices included  

---

## 📞 Support

If you encounter any issues:
1. Check `storage/logs/laravel.log` for errors
2. Verify email configuration in `.env`
3. Test PDF generation separately
4. Ensure patient has valid email address

---

## 🔒 Security & Privacy

- ✅ Vital signs data is encrypted in transit
- ✅ PDF is generated on-the-fly (not stored on server)
- ✅ Email contains disclaimer about medical advice
- ✅ Only authorized nurses can record vital signs
- ✅ Patients only receive their own reports
- ✅ All actions are logged for audit trail

---

**Last Updated:** {{ date('Y-m-d') }}  
**Version:** 1.0.0  
**Status:** ✅ Production Ready

