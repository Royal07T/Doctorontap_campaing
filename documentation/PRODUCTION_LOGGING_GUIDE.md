# Production Logging Guide

## Current Issues

1. **LOG_LEVEL=info** - Only logs INFO level and above (INFO, NOTICE, WARNING, ERROR, CRITICAL, ALERT, EMERGENCY)
   - DEBUG level logs are filtered out
   - This is normal for production, but you may want more details

2. **Exception Handler** - Now configured to log full exception details including stack traces

3. **File Permissions** - Storage directory must be writable

## Solutions

### 1. For More Detailed Logging (Recommended for Debugging)

Update your production `.env` file:

```env
# Change from 'info' to 'debug' temporarily for detailed logging
LOG_LEVEL=debug

# Or use 'error' to only log errors and above (less verbose)
LOG_LEVEL=error
```

**Note:** `LOG_LEVEL=debug` will log everything, which can be verbose. Use it temporarily when debugging, then switch back to `info` or `error`.

### 2. Use Daily Logs (Recommended for Production)

Update your production `.env` file:

```env
LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=error  # or 'info' for more details
```

This will create separate log files per day: `laravel-2026-01-09.log`, `laravel-2026-01-10.log`, etc.

### 3. Check File Permissions on Production Server

Run these commands on your production server:

```bash
# Navigate to your project directory
cd /path/to/your/project

# Ensure storage directory is writable
chmod -R 775 storage
chown -R www-data:www-data storage  # Adjust user/group as needed

# Ensure logs directory exists and is writable
mkdir -p storage/logs
chmod -R 775 storage/logs
chown -R www-data:www-data storage/logs
```

### 4. Verify Logging is Working

Test logging on production:

```bash
# Via Artisan Tinker
php artisan tinker
>>> \Log::error('Test error log');
>>> \Log::info('Test info log');
>>> \Log::warning('Test warning log');
```

Then check the log file:
```bash
tail -f storage/logs/laravel.log
# Or if using daily logs:
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log
```

### 5. Recommended Production Configuration

For production, use this balanced configuration:

```env
APP_ENV=production
APP_DEBUG=false
LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=error  # Logs errors, critical, alert, emergency
LOG_DEPRECATIONS_CHANNEL=null
```

This will:
- Log all errors and critical issues
- Create daily log files (easier to manage)
- Not log debug/info messages (reduces log size)
- Keep logs organized by date

### 6. For Maximum Detail (Temporary Debugging)

If you need maximum detail temporarily:

```env
LOG_LEVEL=debug
```

**Warning:** This will create very large log files. Only use when actively debugging.

## Log File Locations

- **Single log:** `storage/logs/laravel.log`
- **Daily logs:** `storage/logs/laravel-YYYY-MM-DD.log`
- **Security logs:** `storage/logs/security-YYYY-MM-DD.log`
- **Audit logs:** `storage/logs/audit-YYYY-MM-DD.log`

## Viewing Logs

```bash
# View latest log entries
tail -n 100 storage/logs/laravel.log

# Follow log in real-time
tail -f storage/logs/laravel.log

# Search for errors
grep -i error storage/logs/laravel.log

# View today's daily log
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log
```

## After Making Changes

1. Update your `.env` file on production
2. Clear config cache: `php artisan config:clear`
3. Test logging: `php artisan tinker` → `\Log::error('Test');`
4. Check log file: `tail -f storage/logs/laravel.log`

