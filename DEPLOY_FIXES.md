# Deploy 500 Error Fixes to Production

## 🚨 CRITICAL: You Must Upload These Files to Production!

The fixes I made are currently **ONLY on your local computer**. You need to upload them to your production server at `new.doctorontap.com.ng`.

---

## 📁 Files That Need to Be Uploaded

Upload these files to your production server at `/home/doctoron/domains/new.doctorontap.com.ng/laravel/`:

### 1. Controller (Most Important!)
```
app/Http/Controllers/ConsultationController.php
```
Upload to: `/home/doctoron/domains/new.doctorontap.com.ng/laravel/app/Http/Controllers/ConsultationController.php`

### 2. Middleware (Critical for preventing crashes)
```
app/Http/Middleware/SecurityMonitoring.php
app/Http/Middleware/RateLimitMiddleware.php
```
Upload to: `/home/doctoron/domains/new.doctorontap.com.ng/laravel/app/Http/Middleware/`

### 3. Views (For regex fix)
```
resources/views/consultation/index.blade.php
resources/views/doctor/register.blade.php
```
Upload to: `/home/doctoron/domains/new.doctorontap.com.ng/laravel/resources/views/`

---

## 🔧 Option 1: Upload via FTP/SFTP (Recommended)

1. Open FileZilla or your FTP client
2. Connect to your server
3. Navigate to `/home/doctoron/domains/new.doctorontap.com.ng/laravel/`
4. Upload the 5 files listed above to their respective directories
5. **After uploading**, connect via SSH and run:

```bash
cd /home/doctoron/domains/new.doctorontap.com.ng/laravel
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

---

## 🔧 Option 2: Upload via SSH/SCP

If you have SSH access:

```bash
# From your local computer
cd "/home/royal-t/doctorontap campain"

# Upload ConsultationController
scp "app/Http/Controllers/ConsultationController.php" \
    user@new.doctorontap.com.ng:/home/doctoron/domains/new.doctorontap.com.ng/laravel/app/Http/Controllers/

# Upload Middleware files
scp "app/Http/Middleware/SecurityMonitoring.php" \
    user@new.doctorontap.com.ng:/home/doctoron/domains/new.doctorontap.com.ng/laravel/app/Http/Middleware/
    
scp "app/Http/Middleware/RateLimitMiddleware.php" \
    user@new.doctorontap.com.ng:/home/doctoron/domains/new.doctorontap.com.ng/laravel/app/Http/Middleware/

# Upload View files
scp "resources/views/consultation/index.blade.php" \
    user@new.doctorontap.com.ng:/home/doctoron/domains/new.doctorontap.com.ng/laravel/resources/views/consultation/

scp "resources/views/doctor/register.blade.php" \
    user@new.doctorontap.com.ng:/home/doctoron/domains/new.doctorontap.com.ng/laravel/resources/views/doctor/

# Then SSH in and clear caches
ssh user@new.doctorontap.com.ng
cd /home/doctoron/domains/new.doctorontap.com.ng/laravel
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

Replace `user` with your actual SSH username.

---

## 🔧 Option 3: Git Push (If Using Git)

If you're using Git:

```bash
cd "/home/royal-t/doctorontap campain"

# Add the modified files
git add app/Http/Controllers/ConsultationController.php
git add app/Http/Middleware/SecurityMonitoring.php
git add app/Http/Middleware/RateLimitMiddleware.php
git add resources/views/consultation/index.blade.php
git add resources/views/doctor/register.blade.php

# Commit
git commit -m "Fix 500 error: Add error handling and fix regex patterns"

# Push to your repository
git push origin main

# Then on production server, pull the changes
ssh user@new.doctorontap.com.ng
cd /home/doctoron/domains/new.doctorontap.com.ng/laravel
git pull origin main
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## 🐛 Debugging: Check The Actual Error on Production

### Method 1: Check Production Logs

SSH into your server and check the logs:

```bash
ssh user@new.doctorontap.com.ng
cd /home/doctoron/domains/new.doctorontap.com.ng/laravel
tail -50 storage/logs/laravel.log
```

Look for the most recent error with "production.ERROR" in the timestamp.

### Method 2: Enable Debug Mode Temporarily

**⚠️ WARNING: Only do this for a few minutes, then turn it back off!**

Edit `.env` on production:
```env
APP_DEBUG=true
APP_ENV=local
```

Try submitting the form again. You'll see the full error message in the browser.

**IMMEDIATELY** turn debug back off:
```env
APP_DEBUG=false
APP_ENV=production
```

### Method 3: Use Debug File

I created `debug-submit.php`. Upload it to your server root:
```
/home/doctoron/domains/new.doctorontap.com.ng/public_html/debug-submit.php
```

Then visit:
```
https://new.doctorontap.com.ng/debug-submit.php
```

It will show you environment information. **DELETE IT** after using!

---

## ✅ After Deployment Checklist

1. ✅ All 5 files uploaded to correct directories
2. ✅ SSH into server and cleared all caches
3. ✅ Restarted PHP-FPM (if applicable): `sudo systemctl restart php8.2-fpm`
4. ✅ Test form submission
5. ✅ Check logs if still failing: `tail -50 storage/logs/laravel.log`

---

## 🆘 Still Getting 500 Error?

If you still get 500 errors after uploading the files:

1. **Check file permissions:**
   ```bash
   cd /home/doctoron/domains/new.doctorontap.com.ng/laravel
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

2. **Check production logs for the EXACT error:**
   ```bash
   tail -100 storage/logs/laravel.log | grep "production.ERROR"
   ```

3. **Verify database connection:**
   ```bash
   cd /home/doctoron/domains/new.doctorontap.com.ng/laravel
   php artisan tinker
   >>> DB::connection()->getPdo();
   ```

4. **Check if queue table exists:**
   ```bash
   php artisan migrate:status
   ```

5. **Share the FULL error from logs** - Copy the complete error stack trace and share it so I can help identify the exact issue.

---

## 📝 Common Production Issues

### Issue: "Class not found" 
**Solution:** Run `composer dump-autoload` on production

### Issue: "Cache driver not supported"
**Solution:** Check `.env` has `CACHE_DRIVER=file` or `CACHE_DRIVER=database`

### Issue: "Queue table doesn't exist"
**Solution:** Run `php artisan migrate` on production

### Issue: "Storage permission denied"
**Solution:** 
```bash
chmod -R 775 storage
chown -R www-data:www-data storage
```

---

## 🎯 What The Fixes Do

1. **ConsultationController.php** - Catches all errors, returns JSON instead of crashing
2. **SecurityMonitoring.php** - Prevents cache errors from breaking requests
3. **RateLimitMiddleware.php** - Gracefully handles cache failures
4. **Blade views** - Fixes invalid regex pattern causing browser validation errors

Once uploaded, your form will work even if cache/queue isn't perfectly configured!

