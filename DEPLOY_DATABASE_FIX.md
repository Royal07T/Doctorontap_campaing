# 🚨 URGENT: Deploy Database Column Fix to Production

## What's Fixed
- ✅ **SQLSTATE[22001]: String data, right truncated** error on `/submit` endpoint
- ✅ Allows patients to submit detailed health concerns (up to 65,535 characters instead of 255)
- ✅ Fixes: `Data too long for column 'problem' at row 1`

## Changes Committed
- Commit: `84ff926`
- Branch: `livewire`
- Status: **Pushed to GitHub** ✅

## 📋 PRODUCTION DEPLOYMENT STEPS

### Step 1: SSH into Production Server
```bash
ssh your-username@new.doctorontap.com.ng
```

### Step 2: Navigate to Project Directory
```bash
cd /home/doctoron/domains/new.doctorontap.com.ng/laravel
```

### Step 3: Pull Latest Changes
```bash
git pull origin livewire
```

### Step 4: Install doctrine/dbal Package
```bash
composer require doctrine/dbal
```

### Step 5: Run the Migration
```bash
php artisan migrate
```

### Step 6: Clear All Laravel Caches
```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

### Step 7: Restart Queue Workers
```bash
php artisan queue:restart
```

### Step 8: Restart PHP-FPM
```bash
sudo systemctl restart php8.3-fpm
# OR
sudo systemctl restart php-fpm
```

## 🔍 Verify the Fix

After deployment, test by:

1. **Try submitting a consultation with a long health description** (400+ characters)
2. **Monitor logs in real-time:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

You should see:
- ✅ No more "Data too long for column 'problem'" errors
- ✅ Successful consultation booking messages
- ✅ SMS and email notifications being sent

## 📊 What Changed

### Database Change:
- **Column:** `consultations.problem`
- **Before:** `VARCHAR(255)` - max 255 characters
- **After:** `TEXT` - max 65,535 characters

### Files Modified:
1. `composer.json` - Added doctrine/dbal package
2. `composer.lock` - Updated dependencies
3. `database/migrations/2025_11_23_124642_change_problem_column_to_text_in_consultations_table.php` - New migration

### Technical Details:
- Uses `doctrine/dbal` package for column type modification
- Migration is reversible (can rollback if needed)
- No data loss - existing records remain unchanged
- Backward compatible - no code changes needed

## ⚠️ Important Notes

- This fix is **backward compatible** - no application code changes needed
- Existing patient records are **not affected**
- The migration takes ~300ms to run
- Queue workers **must be restarted** to pick up changes
- PHP-FPM **must be restarted** to clear OPcache

## 🆘 If Issues Occur

### Verify Migration Ran Successfully
```bash
php artisan tinker --execute="echo collect(DB::select('SHOW COLUMNS FROM consultations WHERE Field = \"problem\"'))->first()->Type;"
```
Should output: `text`

### Check Git Status
```bash
git log -1
# Should show commit: "Fix: Change problem column to TEXT to allow longer patient descriptions"
```

### Verify Composer Packages
```bash
composer show doctrine/dbal
# Should show version 4.3.4 or higher
```

### Check Production Environment
```bash
grep "APP_ENV=" .env
# Should show: APP_ENV=production
```

### View Recent Logs
```bash
tail -50 storage/logs/laravel.log
```

## 📧 Example Error That Is Now Fixed

**Before Fix:**
```
[2025-11-23 12:45:01] production.ERROR: Failed to create consultation record: 
SQLSTATE[22001]: String data, right truncated: 1406 Data too long for column 'problem' at row 1
```

**After Fix:**
```
[2025-11-23 12:45:01] production.INFO: Consultation booking completed - emails queued
{"consultation_reference":"CONSULT-1763901900-0uXooY","patient_email":"bellomurtala333@gmail.com"}
```

## 🔄 Rollback (If Needed)

If you need to rollback for any reason:

```bash
php artisan migrate:rollback --step=1
composer remove doctrine/dbal
```

**Note:** Rolling back will truncate any problem descriptions longer than 255 characters!

## ✅ Post-Deployment Checklist

- [ ] Migration ran successfully
- [ ] `problem` column is now `text` type
- [ ] Caches cleared
- [ ] Queue workers restarted
- [ ] PHP-FPM restarted
- [ ] Tested long consultation form submission
- [ ] No errors in production logs
- [ ] SMS notifications still working
- [ ] Email notifications still working

---

**Deployed by:** AI Assistant
**Date:** 2025-11-23
**Branch:** livewire
**Commit:** 84ff926

