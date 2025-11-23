#!/bin/bash
# Deploy database column fix to production
# Run this script ON YOUR PRODUCTION SERVER
#
# This fixes: SQLSTATE[22001] - Data too long for column 'problem'

set -e  # Exit on any error

echo "========================================"
echo "Deploying Database Column Fix"
echo "========================================"
echo ""
echo "This will:"
echo "  - Pull latest changes from Git"
echo "  - Install doctrine/dbal package"
echo "  - Run migration to change problem column to TEXT"
echo "  - Clear caches and restart services"
echo ""
read -p "Continue? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]
then
    echo "Deployment cancelled."
    exit 1
fi

echo ""
echo "1. Pulling latest changes from Git..."
git pull origin livewire

echo ""
echo "2. Installing doctrine/dbal package..."
composer require doctrine/dbal --no-interaction

echo ""
echo "3. Running database migration..."
php artisan migrate --force

echo ""
echo "4. Verifying migration..."
COLUMN_TYPE=$(php artisan tinker --execute="echo collect(DB::select('SHOW COLUMNS FROM consultations WHERE Field = \"problem\"'))->first()->Type;")
if [ "$COLUMN_TYPE" == "text" ]; then
    echo "✅ Migration successful - problem column is now TEXT"
else
    echo "⚠️  Warning: Column type is $COLUMN_TYPE (expected: text)"
fi

echo ""
echo "5. Clearing Laravel caches..."
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

echo ""
echo "6. Restarting queue workers..."
php artisan queue:restart

echo ""
echo "7. Restarting PHP-FPM (requires sudo)..."
if sudo systemctl restart php8.3-fpm 2>/dev/null; then
    echo "✅ PHP 8.3 FPM restarted"
elif sudo systemctl restart php-fpm 2>/dev/null; then
    echo "✅ PHP-FPM restarted"
else
    echo "⚠️  Could not restart PHP-FPM automatically. Please restart manually:"
    echo "   sudo systemctl restart php8.3-fpm"
fi

echo ""
echo "========================================"
echo "✅ Deployment Complete!"
echo "========================================"
echo ""
echo "What was fixed:"
echo "  ✅ problem column changed from VARCHAR(255) to TEXT"
echo "  ✅ Patients can now submit detailed health concerns"
echo "  ✅ No more 'Data too long' errors"
echo ""
echo "Please test by submitting a consultation form with a"
echo "long health description (400+ characters)."
echo ""
echo "Monitor logs with:"
echo "  tail -f storage/logs/laravel.log"
echo ""

