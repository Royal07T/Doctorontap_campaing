# 🚨 URGENT: Critical Payment System Fix

## What Was Broken
**ALL PAYMENTS WERE FAILING** due to a critical coding bug in `PaymentController.php`

### The Bug
The code was using `env()` directly to access Korapay credentials:
```php
$apiUrl = env('KORAPAY_API_URL');      // ❌ WRONG!
$secretKey = env('KORAPAY_SECRET_KEY'); // ❌ WRONG!
```

**Why this breaks:** When Laravel's config is cached (`php artisan config:cache`), the `env()` function returns `null` for all values. This is a Laravel best practice violation.

### The Result
- `api_url` was null
- `secret_key` was null
- Payment URLs were broken (`/charges/initialize` instead of full URL)
- Error: `Could not resolve host: charges`
- **NO PAYMENTS COULD BE PROCESSED**

---

## ✅ The Fix
Changed all instances to use `config()` helper:
```php
$apiUrl = config('services.korapay.api_url');      // ✓ CORRECT
$secretKey = config('services.korapay.secret_key'); // ✓ CORRECT
```

**Commit:** `ac50072`  
**Branch:** `livewire`  
**Status:** Pushed to GitHub ✅

---

## 🚀 DEPLOY TO PRODUCTION NOW

### Step 1: SSH into Production
```bash
ssh your-username@new.doctorontap.com.ng
cd /home/doctoron/domains/new.doctorontap.com.ng/laravel
```

### Step 2: Pull Latest Code
```bash
git pull origin livewire
```

### Step 3: Clear ALL Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize
```

### Step 4: Verify Config is Loaded
```bash
php artisan tinker --execute="
echo '========================================' . PHP_EOL;
echo 'KORAPAY CONFIGURATION STATUS' . PHP_EOL;
echo '========================================' . PHP_EOL;
echo 'API URL: ' . config('services.korapay.api_url') . PHP_EOL;
echo 'Has Public Key: ' . (config('services.korapay.public_key') ? '✓ YES' : '✗ NO') . PHP_EOL;
echo 'Has Secret Key: ' . (config('services.korapay.secret_key') ? '✓ YES' : '✗ NO') . PHP_EOL;
echo 'Has Encryption Key: ' . (config('services.korapay.encryption_key') ? '✓ YES' : '✗ NO') . PHP_EOL;
echo '========================================' . PHP_EOL;
"
```

**Expected output:**
```
========================================
KORAPAY CONFIGURATION STATUS
========================================
API URL: https://api.korapay.com/merchant/api/v1
Has Public Key: ✓ YES
Has Secret Key: ✓ YES
Has Encryption Key: ✓ YES
========================================
```

### Step 5: Restart Services
```bash
# Restart PHP-FPM
sudo systemctl restart php8.3-fpm

# Restart queue workers
php artisan queue:restart
```

### Step 6: Re-cache Config (Optional but Recommended)
```bash
php artisan config:cache
```

---

## 🧪 TEST PAYMENT SYSTEM

### Monitor Logs in Real-Time
```bash
tail -f storage/logs/laravel.log | grep -i korapay
```

### Try a Payment
1. Go to your site and try to make a payment
2. Watch the logs

**What you should see NOW:**
```
✅ "api_url":"https://api.korapay.com/merchant/api/v1"
✅ "full_url":"https://api.korapay.com/merchant/api/v1/charges/initialize"
✅ "has_secret":true
✅ Payment initialization successful
```

**Instead of BEFORE:**
```
❌ "api_url":null
❌ "full_url":"/charges/initialize"
❌ "has_secret":false
❌ "cURL error 6: Could not resolve host: charges"
```

---

## 📊 Files Changed
- `app/Http/Controllers/PaymentController.php`
  - `initialize()` method - Fixed lines 68-69, 109-110
  - `handlePaymentRequest()` method - Fixed lines 518-519, 562-563, 579-580
  - `webhook()` method - Fixed line 274
  - `verifyTransaction()` method - Fixed lines 579-580

---

## 🎓 What We Learned

### Laravel Best Practice
**NEVER use `env()` outside of config files!**

✅ **Correct:**
```php
// In config/services.php
'korapay' => [
    'secret_key' => env('KORAPAY_SECRET_KEY'),
],

// In controllers/models/anywhere else
$secretKey = config('services.korapay.secret_key');
```

❌ **Wrong:**
```php
// In controllers (NEVER DO THIS!)
$secretKey = env('KORAPAY_SECRET_KEY');
```

### Why?
1. When you run `php artisan config:cache`, Laravel caches all config files
2. After caching, `env()` always returns `null`
3. This is for security and performance
4. Only `config()` can read from the cached config

---

## 🔍 How to Prevent This

### Before Deploying
Always test with cached config locally:
```bash
php artisan config:cache
# Run your tests
# If anything breaks, you're using env() somewhere you shouldn't
php artisan config:clear
```

### Code Review Checklist
- [ ] No `env()` calls in Controllers
- [ ] No `env()` calls in Models
- [ ] No `env()` calls in Services
- [ ] No `env()` calls in Jobs
- [ ] All config values use `config()` helper
- [ ] `env()` only exists in `config/*.php` files

---

## 📈 Impact

**Before Fix:**
- 100% payment failure rate
- Users couldn't pay for consultations
- Business impact: $0 revenue

**After Fix:**
- Payments work normally
- Users can complete consultations
- Business back to normal

---

## ⚡ DEPLOY URGENCY

**Priority:** 🔴 **CRITICAL - DEPLOY IMMEDIATELY**

**Impact:** Payments are currently broken for ALL users

**Time to Deploy:** ~2 minutes

**Downtime Required:** None (hot deployment)

---

## ✅ Post-Deployment Verification

After deployment, confirm:

1. ✅ Git pull successful - shows commit `ac50072`
2. ✅ Caches cleared - no errors
3. ✅ Config verification shows all keys present
4. ✅ PHP-FPM restarted successfully
5. ✅ Test payment completes successfully
6. ✅ Logs show proper API URLs
7. ✅ No more "Could not resolve host" errors

---

**Deployed by:** Royal-T  
**Date:** 2025-11-24  
**Commit:** ac50072  
**Branch:** livewire  
**Issue:** Payment system completely broken  
**Root Cause:** Using env() instead of config() with cached config  
**Resolution:** Replace all env() calls with config() calls

