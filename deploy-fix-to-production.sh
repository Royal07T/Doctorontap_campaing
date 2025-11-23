#!/bin/bash
# Deploy patient duplicate email fix to production
# Run this script ON YOUR PRODUCTION SERVER

set -e  # Exit on any error

echo "========================================"
echo "Deploying Patient Email Fix to Production"
echo "========================================"
echo ""

# Navigate to project directory
cd "/home/royal-t/doctorontap campain" || exit 1

echo "1. Pulling latest changes from Git..."
git pull origin livewire

echo ""
echo "2. Clearing Laravel caches..."
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

echo ""
echo "3. Restarting queue workers..."
php artisan queue:restart

echo ""
echo "4. Restarting PHP-FPM (requires sudo)..."
sudo systemctl restart php8.3-fpm

echo ""
echo "========================================"
echo "✅ Deployment Complete!"
echo "========================================"
echo ""
echo "The following issues are now fixed:"
echo "  - 500 errors on /submit for duplicate patient emails"
echo "  - Soft-deleted patients are automatically restored"
echo "  - 404 errors on /register route"
echo ""
echo "Please test by submitting a consultation form."

