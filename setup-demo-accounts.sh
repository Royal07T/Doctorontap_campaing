#!/bin/bash

# Setup Demo Accounts for DoctorOnTap
# This script creates all test accounts with auto-verified emails

echo "🚀 Setting up DoctorOnTap Demo Accounts..."
echo ""

# Check if Laravel is ready
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Are you in the project root?"
    exit 1
fi

echo "📊 Creating demo accounts..."
echo ""

# Seed Demo Doctor
echo "👨‍⚕️ Creating Demo Doctor..."
php artisan db:seed --class=DemoDoctorSeeder
echo ""

# Seed Demo Canvasser
echo "🏃 Creating Demo Canvasser..."
php artisan db:seed --class=DemoCanvasserSeeder
echo ""

# Seed Demo Nurse
echo "👩‍⚕️ Creating Demo Nurse..."
php artisan db:seed --class=DemoNurseSeeder
echo ""

# Clear cache
echo "🧹 Clearing cache..."
php artisan cache:clear > /dev/null 2>&1
php artisan config:clear > /dev/null 2>&1
echo ""

echo "✅ All demo accounts created successfully!"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📝 Test Account Credentials:"
echo ""
echo "🔵 Doctor:"
echo "   Email: doctor@demo.com"
echo "   Password: password"
echo "   URL: /doctor/login"
echo ""
echo "🟢 Canvasser:"
echo "   Email: canvasser@demo.com"
echo "   Password: password"
echo "   URL: /canvasser/login"
echo ""
echo "🟣 Nurse:"
echo "   Email: nurse@demo.com"
echo "   Password: password"
echo "   URL: /nurse/login"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "✨ All accounts are email verified and ready to use!"
echo "📄 See TEST_ACCOUNTS.md for more details."
echo ""

