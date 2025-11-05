#!/bin/bash

# DoctorOnTap Queue Worker Startup Script
# This script ensures the queue worker is running for email processing

echo "🚀 Starting DoctorOnTap Queue Worker..."
echo ""

# Check if already running
if pgrep -f "queue:work" > /dev/null; then
    echo "⚠️  Queue worker is already running!"
    echo ""
    echo "To restart it:"
    echo "  1. Stop: php artisan queue:restart"
    echo "  2. Run this script again"
    exit 1
fi

# Navigate to project directory
cd "$(dirname "$0")"

# Check if Laravel is installed
if [ ! -f "artisan" ]; then
    echo "❌ Error: Laravel artisan file not found!"
    echo "   Make sure you're in the project root directory"
    exit 1
fi

# Run migrations (safe to run multiple times)
echo "📦 Checking database migrations..."
php artisan migrate --force

# Clear caches for production
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear

# Start queue worker
echo ""
echo "✅ Starting queue worker..."
echo "   - Emails will be processed in the background"
echo "   - This terminal must stay open"
echo "   - Press Ctrl+C to stop"
echo ""
echo "📊 Monitoring queue jobs..."
echo "================================================"

# Run queue worker with retries and timeout
# Reduced max-jobs to prevent memory issues
php artisan queue:work database \
    --sleep=3 \
    --tries=3 \
    --timeout=90 \
    --max-jobs=100 \
    --max-time=3600 \
    --memory=256 \
    --verbose

