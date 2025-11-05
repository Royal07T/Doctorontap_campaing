#!/bin/bash

# Quick Queue Worker Restart Script
# Use this to restart the queue worker with new settings

echo "🔄 Restarting Queue Worker..."
echo ""

# Kill existing queue workers
echo "⏹️  Stopping old workers..."
php artisan queue:restart
sleep 2
pkill -f "queue:work" 2>/dev/null

# Wait a moment
echo "⏳ Waiting for processes to stop..."
sleep 3

# Start fresh worker
echo "🚀 Starting new worker with optimized settings..."
echo "   - Memory limit: 256MB"
echo "   - Max jobs: 100 (was 1000)"
echo "   - Auto-restart on memory limit"
echo ""

# Run in background
nohup php artisan queue:work database \
    --sleep=3 \
    --tries=3 \
    --timeout=90 \
    --max-jobs=100 \
    --max-time=3600 \
    --memory=256 \
    --verbose > storage/logs/worker.log 2>&1 &

# Get process ID
sleep 2
PID=$(pgrep -f "queue:work" | head -1)

if [ ! -z "$PID" ]; then
    echo "✅ Queue worker started successfully!"
    echo "   Process ID: $PID"
    echo ""
    echo "📊 To monitor:"
    echo "   tail -f storage/logs/worker.log"
    echo ""
    echo "🛑 To stop:"
    echo "   php artisan queue:restart"
    echo "   pkill -f 'queue:work'"
else
    echo "❌ Failed to start queue worker!"
    echo "   Check storage/logs/laravel.log for errors"
    exit 1
fi

