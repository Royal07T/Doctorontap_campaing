#!/bin/bash

# Queue Worker Keep-Alive Script
# This script ensures the queue worker is running
# Run via cron every 5 minutes

PROJECT_DIR="/home/royal-t/doctorontap campain"
LOG_FILE="$PROJECT_DIR/storage/logs/worker.log"
CRON_LOG="/tmp/keep-worker-cron.log"

# Navigate to project directory
cd "$PROJECT_DIR" || exit 1

# Check if queue worker is already running
if pgrep -f "queue:work database" > /dev/null; then
    echo "$(date): Queue worker is already running" >> "$CRON_LOG"
    exit 0
fi

# Queue worker is not running, start it using artisan command
echo "$(date): Starting queue worker..." >> "$CRON_LOG"

php artisan queue:ensure-worker >> "$CRON_LOG" 2>&1

if [ $? -eq 0 ]; then
    echo "$(date): Queue worker started successfully" >> "$CRON_LOG"
else
    echo "$(date): Failed to start queue worker" >> "$CRON_LOG"
fi

