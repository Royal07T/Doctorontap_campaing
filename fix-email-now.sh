#!/bin/bash

# Emergency Email Fix Script
# This script will try multiple solutions to fix your email issues

echo "🚨 Emergency Email Fix - DoctorOnTap"
echo "===================================="
echo ""

# Navigate to project directory
cd "$(dirname "$0")"

# Check current mail settings
echo "📋 Current Mail Settings:"
grep -E "MAIL_HOST|MAIL_PORT|MAIL_ENCRYPTION" .env
echo ""

# Backup .env
echo "💾 Creating backup..."
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
echo "✅ Backup created"
echo ""

# Ask user which fix to apply
echo "Choose a fix:"
echo "1) Try port 465 with SSL (recommended - usually works)"
echo "2) Use log driver (testing - emails won't send but queue works)"
echo "3) Use Gmail SMTP (temporary - requires your Gmail)"
echo "4) Skip email changes (just restart queue)"
echo ""
read -p "Enter choice (1-4): " choice

case $choice in
    1)
        echo ""
        echo "🔧 Applying Fix 1: Port 465 with SSL"
        # Update .env
        sed -i 's/^MAIL_PORT=.*/MAIL_PORT=465/' .env
        sed -i 's/^MAIL_ENCRYPTION=.*/MAIL_ENCRYPTION=ssl/' .env
        echo "✅ Updated to port 465 (SSL)"
        ;;
    2)
        echo ""
        echo "🔧 Applying Fix 2: Log Driver (for testing)"
        sed -i 's/^MAIL_MAILER=.*/MAIL_MAILER=log/' .env
        echo "✅ Emails will be logged to storage/logs/laravel.log"
        echo "⚠️  Remember to change back to SMTP when network is fixed!"
        ;;
    3)
        echo ""
        echo "🔧 Applying Fix 3: Gmail SMTP"
        read -p "Enter your Gmail address: " gmail
        read -s -p "Enter your Gmail app password: " gmailpass
        echo ""
        
        sed -i 's/^MAIL_HOST=.*/MAIL_HOST=smtp.gmail.com/' .env
        sed -i 's/^MAIL_PORT=.*/MAIL_PORT=587/' .env
        sed -i 's/^MAIL_ENCRYPTION=.*/MAIL_ENCRYPTION=tls/' .env
        sed -i "s/^MAIL_USERNAME=.*/MAIL_USERNAME=$gmail/" .env
        sed -i "s/^MAIL_PASSWORD=.*/MAIL_PASSWORD='$gmailpass'/" .env
        sed -i "s/^MAIL_FROM_ADDRESS=.*/MAIL_FROM_ADDRESS=\"$gmail\"/" .env
        
        echo "✅ Gmail SMTP configured"
        ;;
    4)
        echo ""
        echo "⏭️  Skipping email configuration changes"
        ;;
    *)
        echo "Invalid choice. Exiting."
        exit 1
        ;;
esac

echo ""
echo "🧹 Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear

echo ""
echo "⏹️  Stopping old queue workers..."
php artisan queue:restart
sleep 2
pkill -f "queue:work" 2>/dev/null
sleep 2

echo ""
echo "🗑️  Clearing old failed jobs..."
php artisan queue:flush

echo ""
echo "🚀 Starting queue worker with optimized settings..."
echo "   - Timeout: 120s (was 90s)"
echo "   - Max jobs: 50 (was 100)"
echo "   - Memory: 512MB (was 256MB)"
echo ""

# Start worker in background
nohup php artisan queue:work database \
    --sleep=3 \
    --tries=3 \
    --timeout=120 \
    --max-jobs=50 \
    --max-time=1800 \
    --memory=512 \
    --verbose > storage/logs/worker.log 2>&1 &

sleep 3

# Check if running
PID=$(pgrep -f "queue:work" | head -1)

if [ ! -z "$PID" ]; then
    echo "✅ Queue worker started successfully!"
    echo "   Process ID: $PID"
    echo ""
    echo "📊 To monitor:"
    echo "   tail -f storage/logs/worker.log"
    echo ""
    echo "🧪 To test email:"
    echo "   php artisan tinker"
    echo "   >>> Mail::raw('Test', fn(\$m) => \$m->to('your@email.com')->subject('Test'));"
    echo ""
    
    # Show current settings
    echo "📋 Current Configuration:"
    grep -E "MAIL_MAILER|MAIL_HOST|MAIL_PORT|MAIL_ENCRYPTION" .env
    echo ""
    
    if [ "$choice" = "2" ]; then
        echo "⚠️  REMINDER: Emails are going to logs only!"
        echo "   Check: storage/logs/laravel.log"
        echo "   Change MAIL_MAILER back to 'smtp' when network is fixed"
    fi
    
else
    echo "❌ Failed to start queue worker!"
    echo "   Check storage/logs/laravel.log for errors"
    exit 1
fi

echo ""
echo "🎉 Setup complete!"
echo ""
echo "Next steps:"
echo "1. Monitor: tail -f storage/logs/worker.log"
echo "2. Test email in tinker (see above)"
echo "3. If still failing, try another fix option"


