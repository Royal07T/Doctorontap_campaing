# 📧 Email Configuration Setup Guide

## ⚠️ Important: Configure Email Before Testing

The vital signs email feature requires proper email configuration in your `.env` file.

---

## 🛠️ Step 1: Add Mail Configuration to `.env`

Add the following to your `.env` file:

### **Option 1: Using Gmail (Development/Testing)**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@doctorontap.com
MAIL_FROM_NAME="DoctorOnTap"
```

**Note for Gmail:**
- You need to use an "App Password" instead of your regular password
- Enable 2-factor authentication on your Google account
- Generate an App Password at: https://myaccount.google.com/apppasswords

---

### **Option 2: Using Mailtrap (Development/Testing)**

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@doctorontap.com
MAIL_FROM_NAME="DoctorOnTap"
```

**Mailtrap Setup:**
1. Create free account at https://mailtrap.io
2. Go to Email Testing → Inboxes
3. Copy SMTP credentials
4. Paste into `.env`

---

### **Option 3: Using SendGrid (Production)**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@doctorontap.com
MAIL_FROM_NAME="DoctorOnTap"
```

---

### **Option 4: Using Mailgun (Production)**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-mailgun-username
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@doctorontap.com
MAIL_FROM_NAME="DoctorOnTap"
```

---

## 🧪 Step 2: Test Email Configuration

After adding mail configuration, clear config cache:

```bash
php artisan config:clear
php artisan cache:clear
```

### **Quick Test:**

```bash
php artisan tinker
```

Then in Tinker:

```php
Mail::raw('Test email from DoctorOnTap', function($message) {
    $message->to('your-test-email@example.com')
            ->subject('Test Email');
});
```

If successful, you should see:
```
=> Illuminate\Mail\SentMessage
```

If there's an error, check your credentials and try again.

---

## 📋 Step 3: Test Vital Signs Email Feature

1. **Login as Nurse:**
   - URL: `http://127.0.0.1:8000/nurse/login`
   - Email: `demo.nurse@doctorontap.com`
   - Password: `password123`

2. **Navigate to Patients:**
   - Click "Search Patients" or go to `/nurse/patients`

3. **Record Vital Signs:**
   - Search for a patient
   - Click "Record Vital Signs"
   - Fill in the form with test data
   - Submit

4. **Check Email:**
   - Patient should receive email with PDF attachment
   - Subject: "Your Vital Signs Report from DoctorOnTap"
   - Attachment: `vital-signs-report.pdf`

---

## 🔧 Troubleshooting

### **"Connection could not be established with host..."**

**Problem:** SMTP connection failed

**Solutions:**
- Verify MAIL_HOST is correct
- Check MAIL_PORT (587 for TLS, 465 for SSL)
- Ensure firewall isn't blocking SMTP ports
- Try using MAIL_ENCRYPTION=ssl instead of tls

---

### **"Authentication failed"**

**Problem:** Invalid credentials

**Solutions:**
- Double-check MAIL_USERNAME and MAIL_PASSWORD
- For Gmail, ensure you're using an App Password
- Verify your email provider credentials are correct
- Check for extra spaces in credentials

---

### **"Email sent but not received"**

**Problem:** Email delivery issue

**Solutions:**
- Check spam/junk folder
- Verify patient email address is valid
- Check email provider's send logs
- For Gmail, check "Sent" folder

---

### **PDF not generating**

**Problem:** DomPDF error

**Solutions:**
```bash
# Clear caches
php artisan view:clear
php artisan cache:clear

# Reinstall DomPDF if needed
composer require barryvdh/laravel-dompdf

# Check logs
tail -f storage/logs/laravel.log
```

---

## 📊 Email Logs

All email sending attempts are logged. Check:

```bash
tail -f storage/logs/laravel.log
```

Look for lines containing:
- `Failed to send vital signs email:`
- `VitalSignsReport`
- Email-related errors

---

## 🚀 Production Recommendations

### **For Production Use:**

1. **Use a Professional Email Service:**
   - SendGrid (99.9% deliverability)
   - Mailgun (robust API)
   - Amazon SES (cost-effective)
   - Postmark (fast delivery)

2. **Set Up Email Queue:**
   - Prevents timeout issues
   - Better performance
   - Handles failures gracefully

   ```env
   QUEUE_CONNECTION=database
   ```

   Then run:
   ```bash
   php artisan queue:table
   php artisan migrate
   php artisan queue:work
   ```

3. **Configure SPF and DKIM:**
   - Improves email deliverability
   - Reduces spam classification
   - Enhances email security

4. **Monitor Email Sending:**
   - Set up alerts for failures
   - Track delivery rates
   - Monitor bounce rates

---

## ✅ Verification Checklist

Before going live, verify:

- [ ] Mail configuration added to `.env`
- [ ] Config cache cleared
- [ ] Test email sent successfully
- [ ] Vital signs email with PDF works
- [ ] Patient receives email
- [ ] PDF opens correctly
- [ ] Email displays properly on mobile
- [ ] Spam folder checked
- [ ] Production email service configured
- [ ] Email queue set up (recommended)
- [ ] Monitoring in place

---

## 📞 Need Help?

If you're still having issues:

1. Check `storage/logs/laravel.log` for detailed errors
2. Verify all environment variables are set correctly
3. Test with a simple email first (using tinker)
4. Ensure your email provider allows SMTP access
5. Check if you need to enable "Less secure apps" or generate app passwords

---

## 🔐 Security Notes

- **Never commit `.env` file to version control**
- Use environment-specific credentials
- Rotate passwords regularly
- Use app-specific passwords for Gmail
- Enable 2FA on email accounts
- Monitor for suspicious activity

---

**Last Updated:** {{ date('Y-m-d') }}
**Status:** ⚠️ Requires Configuration

