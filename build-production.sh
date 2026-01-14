#!/bin/bash

# Production Build Script for DoctorOnTap
# This script prepares the application for production deployment

set -e  # Exit on any error

echo "🚀 Starting production build process..."
echo "=========================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ️  $1${NC}"
}

# Check if .env exists
if [ ! -f .env ]; then
    print_error ".env file not found!"
    if [ -f .envprodd ]; then
        print_info "Found .envprodd, copying to .env..."
        cp .envprodd .env
        print_success ".env file created from .envprodd"
    else
        print_error "Please create .env file first!"
        exit 1
    fi
fi

# Step 1: Install/Update Dependencies
echo ""
print_info "Step 1: Installing PHP dependencies..."
# First, ensure all dependencies are installed
composer install --no-interaction
# Then optimize autoloader (keeping dev dependencies for now)
composer dump-autoload --optimize --classmap-authoritative
print_success "PHP dependencies installed and optimized"

echo ""
print_info "Step 2: Installing Node dependencies..."
npm ci --production=false
print_success "Node dependencies installed"

# Step 2: Build Frontend Assets
echo ""
print_info "Step 3: Building frontend assets (Vite)..."
npm run build
print_success "Frontend assets built successfully"

# Step 3: Clear all caches
echo ""
print_info "Step 4: Clearing all caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear
print_success "All caches cleared"

# Step 4: Run migrations
echo ""
print_info "Step 5: Running database migrations..."
php artisan migrate --force
print_success "Database migrations completed"

# Step 5: Optimize for production
echo ""
print_info "Step 6: Optimizing Laravel for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
print_success "Laravel optimized for production"

# Step 6: Generate application key if needed
echo ""
print_info "Step 7: Checking application key..."
if ! php artisan key:generate --show > /dev/null 2>&1; then
    print_info "Application key not set, generating..."
    php artisan key:generate --force
    print_success "Application key generated"
else
    print_success "Application key already set"
fi

# Step 7: Set proper permissions
echo ""
print_info "Step 8: Setting file permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 755 public
print_success "File permissions set"

# Step 8: Create storage links if needed
echo ""
print_info "Step 9: Creating storage symlinks..."
php artisan storage:link 2>/dev/null || true
print_success "Storage links created"

# Step 9: Clear and optimize autoloader
echo ""
print_info "Step 10: Optimizing Composer autoloader..."
composer dump-autoload --optimize --classmap-authoritative
print_success "Autoloader optimized"

# Step 10: Verify build
echo ""
print_info "Step 11: Verifying build..."
if [ -d "public/build" ] && [ -f "public/build/manifest.json" ]; then
    print_success "Build verification passed - assets found in public/build"
else
    print_error "Build verification failed - assets not found!"
    exit 1
fi

# Final summary
echo ""
echo "=========================================="
print_success "Production build completed successfully! 🎉"
echo ""
echo "Next steps:"
echo "1. Ensure APP_ENV=production in .env"
echo "2. Ensure APP_DEBUG=false in .env"
echo "3. Start queue worker: php artisan queue:work"
echo "4. Start Reverb server: php artisan reverb:start"
echo "5. Configure your web server (Nginx/Apache)"
echo ""
echo "For production deployment, ensure:"
echo "- Queue workers are running"
echo "- Cron jobs are set up (if any)"
echo "- Web server is configured correctly"
echo "- SSL certificates are installed"
echo "=========================================="

