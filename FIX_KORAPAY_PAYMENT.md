# 🚨 URGENT: Fix Korapay Payment Error on Production

## Current Error
```
[2025-11-23 13:45:12] production.ERROR: Korapay initialization failed 
{"response":{"status":false,"error":"not_authenticated","message":"authorization token is invalid"}}
```

**Cause:** Korapay API credentials are NOT configured in production `.env` file.

Log shows: `"has_secret":false` ← No secret key!

---

## 🔧 SOLUTION: Add Korapay Credentials

### Step 1: Get Your Korapay API Keys

1. Login to **Korapay Dashboard**: https://korapay.com/
2. Navigate to: **Settings** → **API Keys** → **API & Webhooks**
3. Copy these keys:
   - ✅ **Secret Key** (starts with `sk_live_` for production)
   - ✅ **Public Key** (starts with `pk_live_` for production)
   - ✅ **Encryption Key** (optional, if available)

**⚠️ IMPORTANT:** Use **LIVE keys** (not test keys) for production!

---

### Step 2: SSH into Production Server

```bash
ssh your-username@new.doctorontap.com.ng
```

---

### Step 3: Edit Production .env File

```bash
cd /home/doctoron/domains/new.doctorontap.com.ng/laravel
nano .env
```

---

### Step 4: Add Korapay Configuration

Add these lines to your `.env` file (or update if they exist):

```env
# ================================================
# Korapay Payment Gateway Configuration
# ================================================
KORAPAY_SECRET_KEY=sk_live_YOUR_ACTUAL_SECRET_KEY_HERE
KORAPAY_PUBLIC_KEY=pk_live_YOUR_ACTUAL_PUBLIC_KEY_HERE
KORAPAY_ENCRYPTION_KEY=YOUR_ENCRYPTION_KEY_HERE
KORAPAY_API_URL=https://api.korapay.com/merchant/api/v1
KORAPAY_ENFORCE_WEBHOOK_SIGNATURE=true
```

**Replace with your actual keys!**

Save and exit: `Ctrl + X`, then `Y`, then `Enter`

---

### Step 5: Clear Laravel Config Cache

```bash
php artisan config:clear
php artisan config:cache
php artisan optimize
```

---

### Step 6: Restart PHP-FPM

```bash
sudo systemctl restart php8.3-fpm
# OR
sudo systemctl restart php-fpm
```

---

### Step 7: Restart Queue Workers

```bash
php artisan queue:restart
```

---

## ✅ TEST THE FIX

1. **Try to initiate a payment** on your site
2. **Check logs** to verify credentials are working:
   ```bash
   tail -f storage/logs/laravel.log
   ```

You should now see:
```
✅ "has_secret":true
✅ Payment initialization successful
```

Instead of:
```
❌ "has_secret":false
❌ "authorization token is invalid"
```

---

## 🔍 VERIFY CONFIGURATION

Run this command to test if keys are loaded:

```bash
php artisan tinker --execute="echo 'Secret Key: ' . (config('services.korapay.secret_key') ? 'Configured ✓' : 'Missing ✗') . PHP_EOL; echo 'Public Key: ' . (config('services.korapay.public_key') ? 'Configured ✓' : 'Missing ✗') . PHP_EOL;"
```

Expected output:
```
Secret Key: Configured ✓
Public Key: Configured ✓
```

---

## 🛡️ SECURITY NOTES

1. **NEVER commit `.env` file to Git** - it contains sensitive credentials
2. **Use LIVE keys** for production (starts with `sk_live_` and `pk_live_`)
3. **Use TEST keys** for local development (starts with `sk_test_` and `pk_test_`)
4. **Keep keys secret** - don't share in Slack, email, or public places
5. **Rotate keys** if accidentally exposed

---

## 📊 ABOUT THE ERROR

### Error Types You Were Seeing:

#### 1. 419 Error (CSRF Token)
```
[2025-11-23 13:16:35] production.WARNING: Error response {"status_code":419}
```
- **What it is:** Session expired error
- **Cause:** User left form open too long
- **Is it a problem?** No - this is normal security behavior
- **User action:** Refresh page and try again

#### 2. Korapay Authentication Error (CRITICAL)
```
[2025-11-23 13:45:12] production.ERROR: Korapay initialization failed
{"error":"not_authenticated","message":"authorization token is invalid"}
```
- **What it is:** Missing Korapay API credentials
- **Cause:** `.env` file doesn't have `KORAPAY_SECRET_KEY`
- **Is it a problem?** **YES** - payments won't work!
- **Fix:** Add credentials as shown above

---

## 🆘 TROUBLESHOOTING

### Issue: Still showing "has_secret":false after adding keys

**Solution:**
```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

# Restart PHP-FPM
sudo systemctl restart php8.3-fpm
```

### Issue: "Invalid API key" error

**Causes:**
1. Using **test keys** instead of **live keys** in production
2. Extra spaces before/after the key in `.env`
3. Wrong key (public key in secret key field)

**Solution:**
- Verify you're using `sk_live_` keys (not `sk_test_`)
- Remove any quotes around the key value
- Double-check you copied the complete key

### Issue: "Webhook signature invalid"

**Cause:** Webhook requests from Korapay can't be verified

**Solution:**
- Make sure `KORAPAY_SECRET_KEY` is set correctly
- Check webhook URL in Korapay dashboard matches: `https://new.doctorontap.com.ng/payment/webhook`

---

## 📱 PRODUCTION CHECKLIST

After fixing, verify these work:

- [ ] User can submit consultation form
- [ ] Payment initialization succeeds
- [ ] User gets redirected to Korapay checkout
- [ ] Payment callback works after payment
- [ ] Treatment plan unlocks after successful payment
- [ ] Webhook notifications are received
- [ ] No "authorization token is invalid" errors in logs
- [ ] Logs show `"has_secret":true`

---

## 💡 OPTIONAL: Test Payment Flow

1. **Create a test consultation**
2. **Try to pay** for it
3. **Use Korapay test card** (if in test mode):
   - Card: `5061 0200 0000 0000 159`
   - Expiry: Any future date
   - CVV: `123`

4. **Monitor logs:**
   ```bash
   tail -f storage/logs/laravel.log | grep -i korapay
   ```

---

**Priority:** 🔴 **CRITICAL** - Payment system is currently broken  
**Impact:** Users cannot pay for consultations  
**Time to Fix:** ~5 minutes  
**Difficulty:** Easy - just add environment variables

---

**Created:** 2025-11-23  
**Issue:** Korapay authentication failure  
**Root Cause:** Missing KORAPAY_SECRET_KEY in production .env

