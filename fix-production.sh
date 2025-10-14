#!/bin/bash

##############################################################
# FIX PRODUCTION ASSETS
# 
# Fixes the "127.0.0.1:5173 Failed to load resource" error
# by setting APP_ENV=production and clearing Laravel caches.
#
# Usage: sudo bash fix-production.sh
##############################################################

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }
print_error() { echo -e "${RED}✗ $1${NC}"; }
print_info() { echo -e "${BLUE}ℹ $1${NC}"; }

echo ""
echo "================================================"
echo "  VITE PRODUCTION ASSET LOADING FIX"
echo "================================================"
echo ""

# Determine if we need sudo
if [ "$EUID" -eq 0 ]; then
    SUDO=""
    WEB_USER_PREFIX="sudo -u www-data"
else
    SUDO="sudo"
    WEB_USER_PREFIX="sudo -u www-data"
    print_info "This script requires sudo privileges for some operations"
fi

# Try to detect the application directory
if [ -f "artisan" ]; then
    APP_DIR=$(pwd)
    print_success "Detected Laravel application in: $APP_DIR"
elif [ -f "/var/www/doctorontap/artisan" ]; then
    APP_DIR="/var/www/doctorontap"
    cd $APP_DIR
    print_success "Using production directory: $APP_DIR"
else
    print_error "Could not find Laravel application!"
    echo "Please run this script from your Laravel root directory"
    echo "Example: cd /var/www/doctorontap && bash fix-vite-production.sh"
    exit 1
fi

echo ""
print_info "Step 1: Checking .env file..."
if [ ! -f ".env" ]; then
    print_error ".env file not found!"
    exit 1
fi

# Backup .env
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
print_success "Created .env backup"

# Fix APP_ENV
if grep -q "^APP_ENV=production" .env; then
    print_success "APP_ENV is already set to production"
else
    if grep -q "^APP_ENV=" .env; then
        sed -i 's/^APP_ENV=.*/APP_ENV=production/' .env
    else
        echo "APP_ENV=production" >> .env
    fi
    print_success "Updated APP_ENV to production"
fi

# Fix APP_DEBUG
if grep -q "^APP_DEBUG=false" .env; then
    print_success "APP_DEBUG is already false"
else
    if grep -q "^APP_DEBUG=" .env; then
        sed -i 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env
    else
        echo "APP_DEBUG=false" >> .env
    fi
    print_success "Updated APP_DEBUG to false"
fi

echo ""
print_info "Step 2: Verifying build assets..."
if [ -f "public/build/manifest.json" ]; then
    print_success "Build assets found in public/build/"
    echo "   Assets:"
    cat public/build/manifest.json | grep -o '"file"[^,]*' | head -2 | sed 's/"file": "/   - /' | sed 's/"//'
else
    print_warning "Build manifest not found!"
    print_info "Running: npm run build"
    if command -v npm &> /dev/null; then
        npm run build
        print_success "Assets built successfully"
    else
        print_error "npm not found! Please build assets manually: npm run build"
        exit 1
    fi
fi

echo ""
print_info "Step 3: Clearing all Laravel caches..."
php artisan config:clear 2>/dev/null || $WEB_USER_PREFIX php artisan config:clear
php artisan cache:clear 2>/dev/null || $WEB_USER_PREFIX php artisan cache:clear
php artisan view:clear 2>/dev/null || $WEB_USER_PREFIX php artisan view:clear
php artisan route:clear 2>/dev/null || $WEB_USER_PREFIX php artisan route:clear
print_success "All caches cleared"

echo ""
print_info "Step 4: Rebuilding optimized cache..."
php artisan config:cache 2>/dev/null || $WEB_USER_PREFIX php artisan config:cache
php artisan route:cache 2>/dev/null || $WEB_USER_PREFIX php artisan route:cache
php artisan view:cache 2>/dev/null || $WEB_USER_PREFIX php artisan view:cache
print_success "Optimized cache built"

echo ""
print_info "Step 5: Restarting services..."
# Try to restart PHP-FPM (try multiple versions)
PHP_RESTARTED=false
for version in 8.3 8.2 8.1 8.0; do
    if $SUDO systemctl restart php${version}-fpm 2>/dev/null; then
        print_success "Restarted php${version}-fpm"
        PHP_RESTARTED=true
        break
    fi
done

if [ "$PHP_RESTARTED" = false ]; then
    if $SUDO systemctl restart php-fpm 2>/dev/null; then
        print_success "Restarted php-fpm"
    else
        print_warning "Could not restart PHP-FPM (may not be needed)"
    fi
fi

# Try to restart web server
if $SUDO systemctl restart nginx 2>/dev/null; then
    print_success "Restarted nginx"
elif $SUDO systemctl restart apache2 2>/dev/null; then
    print_success "Restarted apache2"
elif $SUDO systemctl restart httpd 2>/dev/null; then
    print_success "Restarted httpd"
else
    print_warning "Could not restart web server (you may need to do this manually)"
fi

echo ""
echo "================================================"
print_success "FIX COMPLETED SUCCESSFULLY!"
echo "================================================"
echo ""
echo "Your application should now load assets from:"
echo "  ✓ https://your-domain.com/build/assets/app-*.css"
echo "  ✓ https://your-domain.com/build/assets/app-*.js"
echo ""
echo "Instead of:"
echo "  ✗ http://127.0.0.1:5173/resources/css/app.css"
echo "  ✗ http://127.0.0.1:5173/resources/js/app.js"
echo ""
print_info "IMPORTANT: Clear your browser cache!"
echo "  • Chrome/Firefox: Ctrl+Shift+R (Windows/Linux)"
echo "  • Chrome/Firefox: Cmd+Shift+R (Mac)"
echo "  • Or open in incognito/private mode"
echo ""

# Show current environment
echo "Current configuration:"
echo "  APP_ENV=$(grep "^APP_ENV=" .env | cut -d'=' -f2)"
echo "  APP_DEBUG=$(grep "^APP_DEBUG=" .env | cut -d'=' -f2)"
echo ""

print_info "If issues persist, check:"
echo "  1. Browser console (F12) for any remaining errors"
echo "  2. Laravel logs: tail -f storage/logs/laravel.log"
echo "  3. Web server logs: /var/log/nginx/error.log"
echo ""

