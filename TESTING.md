# Testing Guide for DoctorOnTap

## Quick Test Checklist

### 1. Start the Server
```bash
php artisan serve
```

### 2. Access the Landing Page
Open your browser and navigate to: `http://localhost:8000`

You should see:
- ✅ Purple gradient background
- ✅ "🩺 DoctorOnTap" header
- ✅ "FREE Medical Consultation" badge
- ✅ Information banner
- ✅ Form with all required fields

### 3. Test Form Validation

#### Test Empty Form
1. Click "Request Free Consultation" button without filling any fields
2. Browser's built-in HTML5 validation should prevent submission
3. Fields should show "Please fill out this field" messages

#### Test Invalid Data
Try submitting with:
- Invalid email format (e.g., "notanemail")
- Age below 1 or above 120
- Empty symptoms field

### 4. Test Successful Submission

Fill in the form with valid data:
```
First Name: John
Last Name: Doe
Email: john.doe@example.com
Age: 35
Gender: Male
Address: 123 Main Street, New York, NY 10001
Symptoms: Experiencing persistent headaches and mild fever for the past 3 days. Also feeling fatigued and have occasional body aches.
```

Click "Request Free Consultation"

**Expected Results:**
- ✅ Button changes to "Submitting..." and becomes disabled
- ✅ Page does NOT reload
- ✅ Green success message appears at the top
- ✅ Message reads: "Thank you for your consultation request! We have received your information and will get back to you shortly. Please check your email for confirmation."
- ✅ Form fields are cleared
- ✅ Page smoothly scrolls to top

### 5. Verify Emails Were Sent

Check the Laravel log file:
```bash
tail -f storage/logs/laravel.log
```

or

```bash
cat storage/logs/laravel.log | grep -A 50 "Consultation"
```

You should see TWO emails logged:

#### Email 1: User Confirmation
```
To: john.doe@example.com
Subject: Consultation Request Confirmation - DoctorOnTap
```

#### Email 2: Admin Alert
```
To: admin@doctorontap.com
Subject: New Consultation Request - DoctorOnTap
```

### 6. Test Mobile Responsiveness

1. Open Chrome DevTools (F12)
2. Toggle Device Toolbar (Ctrl+Shift+M or Cmd+Shift+M)
3. Test on various screen sizes:
   - iPhone SE (375px)
   - iPad (768px)
   - Desktop (1920px)

**Expected Behavior:**
- ✅ Form adapts to screen width
- ✅ Two-column layout on desktop becomes single column on mobile
- ✅ Text sizes are readable on all devices
- ✅ Touch targets are appropriately sized

### 7. Test Alpine.js Functionality

Open browser console (F12) and verify:
- No JavaScript errors
- Alpine.js is loaded (check for Alpine version in console)

### 8. Test Multiple Submissions

1. Submit the form successfully
2. Fill in the form again with different data
3. Submit again

**Expected:**
- ✅ Each submission works independently
- ✅ Success message appears each time
- ✅ Emails are generated for each submission

## Browser Compatibility Testing

Test in multiple browsers:
- ✅ Chrome/Edge
- ✅ Firefox
- ✅ Safari
- ✅ Mobile browsers

## Performance Testing

### Check Page Load Time
1. Open Chrome DevTools > Network tab
2. Reload the page
3. Check the load time (should be < 1 second)

### Check Alpine.js CDN
- Alpine.js loads from CDN: https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js

## Common Issues and Solutions

### Issue: CSRF Token Mismatch
**Solution:** Clear cache and restart server
```bash
php artisan cache:clear
php artisan view:clear
php artisan serve
```

### Issue: Emails Not Appearing in Log
**Solution:** Check `.env` configuration
```
MAIL_MAILER=log
```

### Issue: Page Not Loading
**Solution:** Check if port 8000 is available
```bash
php artisan serve --port=8080
```

## Production Deployment Checklist

Before deploying to production:

1. ✅ Update `.env` with production mail settings
2. ✅ Set `APP_ENV=production`
3. ✅ Set `APP_DEBUG=false`
4. ✅ Change `MAIL_MAILER` from `log` to `smtp` or mail service
5. ✅ Configure proper database (if needed)
6. ✅ Run `php artisan config:cache`
7. ✅ Run `php artisan route:cache`
8. ✅ Run `php artisan view:cache`
9. ✅ Set proper file permissions

## Automated Testing (Optional)

Create a test file: `tests/Feature/ConsultationTest.php`

```php
public function test_consultation_form_submission()
{
    $response = $this->postJson('/submit', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'age' => 35,
        'gender' => 'male',
        'address' => '123 Main St',
        'symptoms' => 'Test symptoms'
    ]);

    $response->assertStatus(200)
             ->assertJson(['success' => true]);
}
```

Run tests:
```bash
php artisan test
```

## Security Notes

- ✅ CSRF protection enabled
- ✅ Input validation on all fields
- ✅ XSS protection via Blade escaping
- ✅ SQL injection protection (not using raw queries)
- ✅ Rate limiting should be added for production

## Performance Optimization Tips

1. Use a proper mail queue in production
2. Add rate limiting to prevent spam
3. Consider adding a honeypot field for bot protection
4. Add reCAPTCHA for additional security
5. Optimize CSS (consider using a preprocessor)

---

**All tests passing?** ✅ Your DoctorOnTap landing page is ready!

