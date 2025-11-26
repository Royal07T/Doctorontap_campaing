# Production Server 500 Error Fix

## Immediate Steps to Fix Live Server Booking Issue

### 1. Check Live Server Logs
```bash
# SSH into your live server
ssh user@new.doctorontap.com.ng

# View Laravel logs
tail -100 storage/logs/laravel.log

# Or follow logs in real-time
tail -f storage/logs/laravel.log
```

### 2. Update .env File on Live Server

Make sure these variables are set correctly in your production `.env`:

```env
# Termii SMS Configuration
TERMII_API_KEY=your_actual_api_key_here
TERMII_SECRET_KEY=your_actual_secret_key_here
TERMII_SENDER_ID=DoctorOnTap
TERMII_BASE_URL=https://v3.api.termii.com
TERMII_CHANNEL=generic
TERMII_ENABLED=true

# WhatsApp Configuration (DISABLE if not configured)
TERMII_WHATSAPP_DEVICE_ID=
TERMII_WHATSAPP_ENABLED=false

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email@doctorontap.com.ng
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@doctorontap.com.ng
MAIL_FROM_NAME="DoctorOnTap"

# Admin Email
ADMIN_EMAIL=inquiries@doctorontap.com.ng

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

### 3. Fix File Permissions

```bash
# Run on your live server
cd /home/royal-t/doctorontap\ campain
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
sudo chown -R www-data:www-data storage/
sudo chown -R www-data:www-data bootstrap/cache/
```

### 4. Clear All Caches

```bash
# On your live server
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```

### 5. Deploy Updated Files

Make sure these new files are on your production server:
- `app/Notifications/ConsultationWhatsAppNotification.php`
- `app/Notifications/ConsultationSmsNotification.php`
- `app/Services/TermiiService.php`
- `app/Jobs/SendConsultationSms.php`

### 6. Check Queue Worker

```bash
# Check if queue worker is running
ps aux | grep queue

# Restart queue worker
php artisan queue:restart

# Start queue worker (if not running)
php artisan queue:work --daemon &
```

### 7. Test After Each Step

After each step, try booking a consultation again and check:
1. Does the form submit successfully?
2. Check the Laravel log for new errors
3. Check if SMS is sent
4. Check if emails are queued

## Common Error Patterns

### Error Pattern 1: Class Not Found
**Log shows:** `Class 'App\Notifications\ConsultationWhatsAppNotification' not found`

**Fix:** Deploy the file or run `composer dump-autoload`

### Error Pattern 2: Termii API Error
**Log shows:** `Termii API key not configured` or `Failed to send SMS`

**Fix:** Add TERMII_API_KEY to .env file

### Error Pattern 3: Database Error
**Log shows:** `SQLSTATE` or `Connection refused`

**Fix:** Check database credentials in .env

### Error Pattern 4: Storage Error
**Log shows:** `Unable to create directory` or `Permission denied`

**Fix:** Run the file permissions commands above

## Need Help?

1. Copy the error from `storage/logs/laravel.log`
2. Share the specific error message
3. We can provide targeted solution









