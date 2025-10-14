# DoctorOnTap - Production Deployment Guide

## Quick Fix: Assets Loading Error (127.0.0.1:5173)

If your production site shows errors loading CSS/JS from `127.0.0.1:5173`:

```bash
cd /var/www/doctorontap
sudo bash fix-production.sh
```

Then clear browser cache: `Ctrl+Shift+R`

---

## Full Deployment Process

### 1. Upload Files to Server
```bash
scp -r * user@server:/var/www/doctorontap/
```

### 2. Set Up Environment
```bash
# Copy and configure .env
cp .env.example .env
nano .env

# Required settings:
APP_ENV=production
APP_DEBUG=false
APP_URL=https://new.doctorontap.com.ng
```

### 3. Deploy Application
```bash
cd /var/www/doctorontap
sudo bash deploy-production.sh
```

This script handles:
- Environment verification
- Dependency installation
- Asset building
- Database migrations
- Cache optimization
- Service restarts

---

## Troubleshooting

### Assets not loading
```bash
# Verify environment
grep APP_ENV .env

# Should show: APP_ENV=production
# If not, run: sudo bash fix-production.sh
```

### Database errors
```bash
# Check connection
php artisan tinker
>>> DB::connection()->getPdo();
```

### Email not sending
```bash
# Test email
php artisan tinker
>>> Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'));
```

### Cache issues
```bash
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan config:cache
```

---

## Important Files

- `deploy-production.sh` - Main deployment script
- `fix-production.sh` - Quick fix for asset loading issues
- `.htaccess.production` - Apache configuration
- `quick-start.sh` - Local development setup

---

## Security Checklist

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] Strong database password
- [ ] Strong admin password
- [ ] HTTPS enabled
- [ ] Korapay LIVE keys (not test)
- [ ] Valid email credentials
- [ ] `.env` permissions set to 600
- [ ] `.env` not in Git

---

## Logs

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Nginx logs
sudo tail -f /var/log/nginx/error.log

# PHP-FPM logs
sudo tail -f /var/log/php8.1-fpm/error.log
```

---

## Services

```bash
# Restart all services
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx

# Check status
sudo systemctl status php8.1-fpm
sudo systemctl status nginx
```

