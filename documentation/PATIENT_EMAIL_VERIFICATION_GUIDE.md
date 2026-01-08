# Patient Email Verification System - Complete Guide

## 🎯 Overview

A secure email verification system that ensures patients verify their email addresses before accessing the patient dashboard.

---

## ✨ How It Works

### **For Patients:**

```
1. Patient books consultation via website
   ↓
2. System creates patient account automatically
   ↓
3. Verification email sent to patient's email
   ↓
4. Patient clicks verification link in email
   ↓
5. Email verified ✅
   ↓
6. Patient can now login to dashboard
```

---

## 📧 Email Verification Flow

### **Step 1: Patient Registers (Books Consultation)**

When a patient submits a consultation form:

1. System checks if patient exists by email
2. If **new patient**:
   - Creates patient account
   - **Automatically sends verification email** ✅
   - Password is NOT set (patient hasn't logged in yet)

3. If **existing patient**:
   - Updates patient information
   - No verification email sent (already verified or pending)

### **Step 2: Verification Email Sent**

Patient receives an email with:
- **Subject**: "Verify Your Email - DoctorOnTap"
- **Purple branded template**
- **Verification button/link**
- **Expiration**: 24 hours
- **Security notice**

**Email Template Location**: `resources/views/emails/patient-verification.blade.php`

### **Step 3: Patient Clicks Verification Link**

The verification URL format:
```
https://yourdomain.com/patient/email/verify/{id}/{hash}
```

When clicked:
1. System validates the hash
2. Marks email as verified
3. Redirects to login page with success message
4. Patient can now login

### **Step 4: Patient Attempts Login**

**If email NOT verified:**
- Login blocked
- Error message: "Please verify your email address before logging in."
- Link to resend verification email

**If email IS verified:**
- Login successful ✅
- Redirected to patient dashboard

---

## 🔐 Security Features

1. **Hashed Verification Links**
   - Uses SHA1 hash of email
   - Cannot be tampered with
   - Unique per patient

2. **24-Hour Expiration**
   - Links expire after 24 hours
   - Must request new link if expired

3. **One-Time Use**
   - Once verified, can't be used again
   - Prevents replay attacks

4. **Secure Storage**
   - No plain text tokens in database
   - Uses Laravel's built-in verification system

---

## 📂 System Components

### **Controllers**

#### 1. **ConsultationController**
```php
Location: app/Http/Controllers/ConsultationController.php

Purpose: Handles consultation submission and patient creation
Key Method: store()

What it does:
- Creates new patient account
- Sends verification email automatically
- Logs verification email send attempts
```

#### 2. **Patient/AuthController**
```php
Location: app/Http/Controllers/Patient/AuthController.php

Purpose: Handles patient login
Key Method: login()

What it does:
- Checks if email is verified before allowing login
- Blocks unverified patients
- Shows appropriate error messages
```

#### 3. **Patient/VerificationController**
```php
Location: app/Http/Controllers/Patient/VerificationController.php

Key Methods:
- notice() - Shows verification notice page
- verify() - Processes verification link
- resend() - Resends verification email
```

---

### **Models**

#### **Patient Model**
```php
Location: app/Models/Patient.php

Key Method: sendEmailVerificationNotification()

Features:
- Generates verification token
- Creates verification URL
- Sends email with verification link
- Tracks verification status
```

**Database Fields:**
- `email_verified_at` - Timestamp when email was verified
- `email_verification_token` - Token for verification
- `verification_sent_at` - When verification email was sent
- `is_verified` - Boolean flag

---

### **Views**

#### 1. **Verification Email**
```
File: resources/views/emails/patient-verification.blade.php

Features:
- Professional purple-branded design
- Clear verification button
- Backup text link
- Security notices
- 24-hour expiration notice
```

#### 2. **Verification Notice Page**
```
File: resources/views/patient/verify-email.blade.php

Features:
- Purple gradient theme (matches login)
- Email icon and instructions
- "Resend Email" button
- Check spam folder notice
- Logout option
```

#### 3. **Login Page**
```
File: resources/views/patient/login.blade.php

Features:
- Shows error if email not verified
- Consistent purple theme
- Remember me option
- Forgot password link
```

---

## 🛣️ Routes

### **Public Routes** (No authentication required)

```php
// Verification link (sent via email)
GET  /patient/email/verify/{id}/{hash}
Route: patient.verification.verify
Controller: PatientVerificationController@verify
```

### **Protected Routes** (Requires authentication)

```php
// Verification notice page
GET  /patient/email/verify
Route: patient.verification.notice
Middleware: patient.auth
Controller: PatientVerificationController@notice

// Resend verification email
POST /patient/email/verification-notification
Route: patient.verification.resend
Middleware: patient.auth
Controller: PatientVerificationController@resend
```

---

## 🔄 User Flows

### **Flow 1: New Patient Registration**

```
1. Patient fills consultation form on website
2. Patient submits form
3. System creates patient account
4. ✉️ Verification email sent automatically
5. Patient receives email
6. Patient clicks "Verify Email Address" button
7. ✅ Email verified
8. Patient redirected to login page
9. Patient can login and access dashboard
```

### **Flow 2: Patient Tries to Login (Unverified)**

```
1. Patient goes to /patient/login
2. Patient enters email and password
3. System checks if email is verified
4. ❌ Email not verified
5. Login blocked with error message
6. Patient can click "Resend Verification Email"
7. New verification email sent
8. Patient verifies email
9. ✅ Patient can now login
```

### **Flow 3: Resend Verification Email**

```
1. Patient logs in (or goes to verification notice page)
2. Patient clicks "Resend Verification Email"
3. System sends new verification email
4. Success message: "Verification link sent!"
5. Patient checks email
6. Patient clicks verification link
7. ✅ Email verified
```

---

## 📊 Database Schema

### **Patients Table**

```sql
email_verified_at           TIMESTAMP NULL
email_verification_token    VARCHAR(255) NULL
is_verified                 BOOLEAN DEFAULT 0
verification_sent_at        TIMESTAMP NULL
```

**Status Indicators:**
- `email_verified_at IS NULL` → Not verified
- `email_verified_at IS NOT NULL` → Verified ✅
- `is_verified = 1` → Additional verification flag

---

## 🎨 Email Template Design

### **Template Features:**

```
┌─────────────────────────────┐
│     DoctorOnTap Logo        │
│                             │
│  Verify Your Email Address │
├─────────────────────────────┤
│                             │
│  Hello [Patient Name],      │
│                             │
│  Thank you for registering...│
│                             │
│  [Verify Email Button]      │
│  (Purple gradient)          │
│                             │
│  Important Information:     │
│  • Expires in 24 hours      │
│  • Don't share link         │
│  • Ignore if not you        │
│                             │
│  Backup Link:               │
│  https://...                │
│                             │
│  Benefits after verification:│
│  ✓ Access dashboard         │
│  ✓ Book consultations       │
│  ✓ View medical records     │
│  ✓ Receive treatment plans  │
├─────────────────────────────┤
│  © 2025 DoctorOnTap         │
└─────────────────────────────┘
```

---

## 🔧 Configuration

### **Mail Configuration**

Ensure your `.env` file has mail settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@doctorontap.com
MAIL_FROM_NAME="DoctorOnTap"
```

### **App URL**

```env
APP_URL=https://yourdomain.com
```
*Required for verification links to work correctly*

---

## 🚦 Testing Checklist

### **Patient Registration**
- [ ] Patient submits consultation form
- [ ] Patient account created in database
- [ ] `email_verified_at` is NULL
- [ ] `is_verified` is 0
- [ ] Verification email sent
- [ ] Email received in inbox
- [ ] Email has correct patient name
- [ ] Verification link works

### **Email Verification**
- [ ] Clicking verification link verifies email
- [ ] `email_verified_at` updated to current timestamp
- [ ] `is_verified` set to 1
- [ ] Redirects to login page
- [ ] Success message displayed

### **Login Restrictions**
- [ ] Unverified patient cannot login
- [ ] Error message shown
- [ ] Verified patient can login
- [ ] Redirects to dashboard after login

### **Resend Feature**
- [ ] "Resend" button works
- [ ] New verification email sent
- [ ] Success message displayed
- [ ] New link works

---

## ⚠️ Common Issues & Solutions

### **Issue 1: Verification Email Not Received**

**Possible Causes:**
1. Email in spam/junk folder
2. Mail configuration incorrect
3. Mail server blocking

**Solutions:**
- Check spam folder
- Verify mail credentials in `.env`
- Check mail server logs
- Test with different email provider

### **Issue 2: Verification Link Returns Error**

**Possible Causes:**
1. Link expired (24 hours)
2. Invalid hash
3. Patient already verified

**Solutions:**
- Request new verification email
- Check if already verified
- Ensure APP_URL is correct

### **Issue 3: Patient Can't Resend Email**

**Possible Causes:**
1. Not logged in
2. Already verified
3. Route not defined

**Solutions:**
- Login first
- Check verification status
- Clear route cache: `php artisan route:cache`

---

## 📝 Admin Tasks

### **Manually Verify a Patient (if needed)**

```php
// Via Tinker
php artisan tinker

$patient = \App\Models\Patient::where('email', 'patient@example.com')->first();
$patient->markEmailAsVerified();
$patient->is_verified = true;
$patient->save();
```

### **Check Patient Verification Status**

```php
php artisan tinker

$patient = \App\Models\Patient::find(1);
echo "Verified: " . ($patient->hasVerifiedEmail() ? 'Yes' : 'No');
echo "Verified At: " . $patient->email_verified_at;
```

### **Resend Verification to Multiple Patients**

```php
$unverifiedPatients = \App\Models\Patient::whereNull('email_verified_at')->get();

foreach ($unverifiedPatients as $patient) {
    $patient->sendEmailVerificationNotification();
    echo "Sent to: " . $patient->email . "\n";
}
```

---

## 🎯 Key Benefits

✅ **Security**: Only verified email addresses can login  
✅ **Automatic**: Emails sent automatically on registration  
✅ **User-Friendly**: Clear instructions and resend option  
✅ **Professional**: Branded email template  
✅ **Reliable**: Built on Laravel's verification system  
✅ **Spam Prevention**: Blocks fake email addresses  
✅ **Data Quality**: Ensures valid contact information  

---

## 🔐 Security Best Practices

1. **Never** include sensitive data in verification emails
2. **Always** use HTTPS for verification links
3. **Set** link expiration (24 hours)
4. **Log** verification attempts for security monitoring
5. **Validate** email format before sending
6. **Rate limit** resend requests (prevent abuse)
7. **Use** encrypted connections (TLS/SSL) for email

---

## 📞 Support

### **For Patients:**

**Can't find verification email?**
- Check spam/junk folder
- Click "Resend Verification Email" on login page
- Contact support if still not received

**Verification link expired?**
- Request new verification email
- Links expire after 24 hours

**Still having issues?**
- Contact: support@doctorontap.com
- WhatsApp: +234XXXXXXXXXX

---

## ✅ Summary

**Patient Email Verification System** is now **COMPLETE**!

**What Happens:**
1. ✅ Patient books consultation → Account created
2. ✅ Verification email sent automatically
3. ✅ Patient clicks link → Email verified
4. ✅ Patient can login → Access dashboard

**Key Files:**
- `ConsultationController.php` - Sends verification on registration
- `PatientVerificationController.php` - Handles verification
- `patient-verification.blade.php` - Email template
- `verify-email.blade.php` - Verification notice page
- `Patient.php` - Verification methods

**Status**: ✅ Production Ready

---

**Last Updated**: December 13, 2025  
**Version**: 1.0  
**System**: Patient Email Verification

