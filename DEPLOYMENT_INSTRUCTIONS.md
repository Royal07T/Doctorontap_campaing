# 🚨 URGENT: Deploy Patient Email Fix to Production

## What's Fixed
- ✅ 500 errors on `/submit` endpoint (duplicate patient email constraint violations)
- ✅ Automatic restoration of soft-deleted patient records
- ✅ 404 errors on `/register` route

## Changes Committed
- Commit: `3a98032`
- Branch: `livewire`
- Status: **Pushed to GitHub** ✅

## 📋 Deployment Steps

### Option 1: If you have SSH access to production server

1. **SSH into your production server:**
   ```bash
   ssh your-username@new.doctorontap.com.ng
   # OR ssh into whatever server hosts new.doctorontap.com.ng
   ```

2. **Navigate to the project directory:**
   ```bash
   cd /path/to/doctorontap/project
   ```

3. **Pull the latest changes:**
   ```bash
   git pull origin livewire
   ```

4. **Clear all Laravel caches:**
   ```bash
   php artisan optimize:clear
   php artisan config:clear
   php artisan route:clear
   php artisan cache:clear
   php artisan view:clear
   ```

5. **Restart queue workers:**
   ```bash
   php artisan queue:restart
   ```

6. **Restart PHP-FPM:**
   ```bash
   sudo systemctl restart php8.3-fpm
   # OR
   sudo systemctl restart php-fpm
   ```

7. **Test the fix:**
   - Try submitting a consultation form with one of the emails that was failing:
     - `timmyroyalty@gmail.com`
     - `rollingstonny51@gmail.com`
     - `stonnyrolling001@gmail.com`

### Option 2: If using a deployment tool (Deployer, Envoyer, etc.)

Run your normal deployment process to deploy the `livewire` branch to production.

### Option 3: If this IS the production server

If you're already on the production server, simply run:

```bash
cd "/home/royal-t/doctorontap campain"
./deploy-fix-to-production.sh
```

## 🔍 Verify the Fix

After deployment, monitor your production logs:

```bash
tail -f /path/to/project/storage/logs/laravel.log
```

Try submitting a consultation form. You should see:
- ✅ No more "Duplicate entry" errors
- ✅ Successful consultation booking messages
- ✅ SMS and email notifications being sent

## 📊 What Changed

### Files Modified:
1. `app/Http/Controllers/ConsultationController.php` - Main `/submit` endpoint
2. `app/Http/Controllers/Canvasser/DashboardController.php` - Canvasser patient registration
3. `app/Http/Controllers/ReviewController.php` - Patient creation during reviews
4. `routes/web.php` - Added `/register` redirect

### Technical Details:
- Patient creation now checks for soft-deleted records using `withTrashed()`
- Soft-deleted patients are automatically restored and updated
- No more duplicate insert attempts that violate unique constraints

## ⚠️ Important Notes

- The fix is **backward compatible** - no database migrations needed
- Existing patient records are **not affected**
- Queue workers **must be restarted** to pick up the changes
- PHP-FPM **must be restarted** to clear OPcache

## 🆘 If Issues Persist

1. Check if changes were actually pulled:
   ```bash
   git log -1
   # Should show commit: "Fix: Handle duplicate patient email constraint violations"
   ```

2. Verify the environment is production:
   ```bash
   grep "APP_ENV=" .env
   # Should show: APP_ENV=production
   ```

3. Check PHP-FPM status:
   ```bash
   sudo systemctl status php8.3-fpm
   ```

4. Check for any new errors:
   ```bash
   tail -50 storage/logs/laravel.log
   ```

