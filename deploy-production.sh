#!/bin/bash

##################################################
# DoctorOnTap Production Deployment Script
#
# Complete deployment: dependencies, assets, 
# migrations, cache, and service restarts.
#
# Usage: sudo bash deploy-production.sh
##################################################

set -e

echo "🚀 Starting DoctorOnTap Production Deployment..."
echo "================================================"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="/var/www/doctorontap"
WEB_USER="www-data"

# Function to print colored output
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    print_error "Please run as root or with sudo"
    exit 1
fi

# Step 1: Maintenance Mode
echo ""
echo "Step 1: Enabling Maintenance Mode..."
cd $APP_DIR
sudo -u $WEB_USER php artisan down --retry=60 || true
print_success "Maintenance mode enabled"

# Step 2: Verify Production Environment
echo ""
echo "Step 2: Verifying production environment settings..."
if grep -q "^APP_ENV=production" .env; then
    print_success ".env is correctly set to production"
else
    print_error ".env is NOT set to production!"
    echo "Current setting: $(grep "^APP_ENV=" .env)"
    echo ""
    read -p "Update APP_ENV to production? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        sed -i 's/^APP_ENV=.*/APP_ENV=production/' .env
        sed -i 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env
        print_success "Updated to production environment"
    else
        print_error "Cannot deploy without APP_ENV=production"
        sudo -u $WEB_USER php artisan up
        exit 1
    fi
fi

if grep -q "^APP_DEBUG=false" .env; then
    print_success "APP_DEBUG is correctly set to false"
else
    print_warning "APP_DEBUG should be false in production!"
    sed -i 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env
    print_success "Updated APP_DEBUG to false"
fi

# Step 3: Pull Latest Code (if using Git)
echo ""
echo "Step 3: Checking for Git repository..."
if [ -d "$APP_DIR/.git" ]; then
    print_warning "Git repository found. Pulling latest code..."
    sudo -u $WEB_USER git pull origin main
    print_success "Code updated from Git"
else
    print_warning "No Git repository found. Skipping..."
fi

# Step 4: Install/Update Composer Dependencies
echo ""
echo "Step 4: Installing Composer dependencies..."
sudo -u $WEB_USER composer install --optimize-autoloader --no-dev --no-interaction
print_success "Composer dependencies installed"

# Step 5: Install/Update NPM Dependencies
echo ""
echo "Step 5: Installing NPM dependencies..."
sudo -u $WEB_USER npm install --production
print_success "NPM dependencies installed"

# Step 6: Build Frontend Assets
echo ""
echo "Step 6: Building frontend assets..."
sudo -u $WEB_USER npm run build
print_success "Frontend assets built"

# Step 7: Run Database Migrations
echo ""
echo "Step 7: Running database migrations..."
read -p "Do you want to run migrations? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    sudo -u $WEB_USER php artisan migrate --force
    print_success "Database migrations completed"
else
    print_warning "Skipping database migrations"
fi

# Step 8: Clear and Cache Configuration
echo ""
echo "Step 8: Optimizing application..."
sudo -u $WEB_USER php artisan config:clear
sudo -u $WEB_USER php artisan cache:clear
sudo -u $WEB_USER php artisan view:clear
sudo -u $WEB_USER php artisan route:clear
print_success "Caches cleared"

sudo -u $WEB_USER php artisan config:cache
sudo -u $WEB_USER php artisan route:cache
sudo -u $WEB_USER php artisan view:cache
print_success "Configuration cached"

# Step 9: Storage Link
echo ""
echo "Step 9: Creating storage link..."
sudo -u $WEB_USER php artisan storage:link || true
print_success "Storage link created"

# Step 10: Set Correct Permissions
echo ""
echo "Step 10: Setting file permissions..."
chown -R $WEB_USER:$WEB_USER $APP_DIR
find $APP_DIR -type d -exec chmod 755 {} \;
find $APP_DIR -type f -exec chmod 644 {} \;
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache
print_success "Permissions set correctly"

# Step 11: Restart Services
echo ""
echo "Step 11: Restarting services..."
systemctl restart php8.1-fpm
systemctl restart nginx
print_success "Services restarted"

# Step 12: Restart Queue Workers (if using)
echo ""
echo "Step 12: Restarting queue workers..."
if systemctl is-active --quiet supervisor; then
    supervisorctl restart all
    print_success "Queue workers restarted"
else
    print_warning "Supervisor not running. Skipping..."
fi

# Step 13: Disable Maintenance Mode
echo ""
echo "Step 13: Disabling maintenance mode..."
sudo -u $WEB_USER php artisan up
print_success "Maintenance mode disabled"

# Step 14: Health Check
echo ""
echo "Step 14: Running health checks..."
if curl -k -s -f "http://localhost" > /dev/null; then
    print_success "Application is responding"
else
    print_error "Application health check failed!"
fi

echo ""
echo "================================================"
echo "🎉 Deployment completed successfully!"
echo "================================================"
echo ""
echo "Next steps:"
echo "1. Test the application: https://your-domain.com"
echo "2. Login to admin panel: https://your-domain.com/admin/login"
echo "3. Monitor logs: tail -f $APP_DIR/storage/logs/laravel.log"
echo ""

