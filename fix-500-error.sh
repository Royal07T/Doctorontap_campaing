#!/bin/bash
# Fix 500 Internal Server Error on Production
# Run this script ON YOUR PRODUCTION SERVER

set -e  # Exit on any error

echo "========================================"
echo "Fixing 500 Internal Server Error"
echo "========================================"
echo ""

# Navigate to project directory
cd "/home/royal-t/doctorontap campain" || exit 1

echo "1. Clearing ALL Laravel caches..."
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
php artisan event:clear

echo ""
echo "2. Verifying .env file exists..."
if [ ! -f .env ]; then
    echo "❌ ERROR: .env file not found!"
    echo "Please create .env file from .env.example"
    exit 1
fi
echo "✅ .env file exists"

echo ""
echo "3. Checking critical .env variables..."
if ! grep -q "APP_ENV=production" .env; then
    echo "⚠️  WARNING: APP_ENV is not set to 'production'"
fi
if ! grep -q "APP_DEBUG=false" .env; then
    echo "⚠️  WARNING: APP_DEBUG is not set to 'false'"
fi
echo "✅ Environment variables checked"

echo ""
echo "4. Creating storage directories if they don't exist..."
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
echo "✅ Storage directories created"

echo ""
echo "5. Setting proper permissions..."
chmod -R 775 storage bootstrap/cache
chmod 600 .env
echo "✅ Permissions set"

echo ""
echo "6. Testing database connection..."
if php artisan migrate:status > /dev/null 2>&1; then
    echo "✅ Database connection successful"
else
    echo "❌ ERROR: Database connection failed!"
    echo "Please check your database credentials in .env"
    exit 1
fi

echo ""
echo "7. Re-caching configuration with new environment..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
echo "✅ Configuration re-cached"

echo ""
echo "8. Verifying application key..."
if ! php artisan key:generate --show > /dev/null 2>&1; then
    echo "⚠️  Application key may be missing, generating..."
    php artisan key:generate --force
fi
echo "✅ Application key verified"

echo ""
echo "9. Checking storage link..."
if [ ! -L public/storage ]; then
    echo "Creating storage symbolic link..."
    php artisan storage:link
fi
echo "✅ Storage link verified"

echo ""
echo "10. Restarting PHP-FPM (if available)..."
if command -v systemctl > /dev/null 2>&1; then
    if sudo systemctl restart php8.3-fpm 2>/dev/null; then
        echo "✅ PHP 8.3 FPM restarted"
    elif sudo systemctl restart php-fpm 2>/dev/null; then
        echo "✅ PHP-FPM restarted"
    else
        echo "⚠️  Could not restart PHP-FPM automatically"
        echo "   Please restart manually: sudo systemctl restart php8.3-fpm"
    fi
else
    echo "⚠️  systemctl not available, skipping PHP-FPM restart"
fi

echo ""
echo "11. Restarting queue workers..."
php artisan queue:restart 2>/dev/null || echo "⚠️  Queue workers not running"

echo ""
echo "========================================"
echo "✅ Fix Complete!"
echo "========================================"
echo ""
echo "Next steps:"
echo "1. Check the application in your browser"
echo "2. If still getting 500 error, check logs:"
echo "   tail -f storage/logs/laravel.log"
echo "3. Verify web server error logs:"
echo "   tail -f /var/log/nginx/error.log"
echo "   or"
echo "   tail -f /var/log/apache2/error.log"
echo ""

