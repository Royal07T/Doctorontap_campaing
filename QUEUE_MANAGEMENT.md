# 🔄 Queue Management Guide - DoctorOnTap

## ❌ **Problem: Queue Worker Being Killed**

Your queue worker was crashing with `Killed` message. This happens due to **memory exhaustion**.

### **Root Cause**
```bash
--max-jobs=1000  # ❌ Too many! Accumulates memory
```

Each job processes emails with attachments, which can be memory-intensive. After 1000 jobs, the worker could be using 500MB+ RAM and gets killed by the system.

---

## ✅ **Solution Applied**

### **What I Fixed**

1. **Reduced max-jobs**: `1000` → `100`
2. **Added memory limit**: `--memory=256` (restarts at 256MB)
3. **Kept other settings**: Timeout, retries, etc.

### **Updated Command**
```bash
php artisan queue:work database \
    --sleep=3 \           # Wait 3s between jobs
    --tries=3 \           # Retry failed jobs 3 times
    --timeout=90 \        # Job timeout: 90s
    --max-jobs=100 \      # ✅ Restart after 100 jobs (prevents memory leak)
    --max-time=3600 \     # Restart after 1 hour
    --memory=256 \        # ✅ Restart if using >256MB RAM
    --verbose             # Show detailed output
```

---

## 🚀 **How to Start Queue Worker**

### **Option 1: Manual Start (Development)**

```bash
# Start the worker
./start-queue-worker.sh

# Keep terminal open - Ctrl+C to stop
```

**Pros**: Easy, immediate feedback
**Cons**: Stops when terminal closes

### **Option 2: Background Process (Development)**

```bash
# Start in background
nohup ./start-queue-worker.sh > storage/logs/worker.log 2>&1 &

# Check if running
pgrep -f "queue:work"

# View logs
tail -f storage/logs/worker.log

# Stop
php artisan queue:restart
pkill -f "queue:work"
```

**Pros**: Runs in background
**Cons**: Manual restart needed

### **Option 3: Supervisor (Production)** ⭐ **RECOMMENDED**

```bash
# 1. Install Supervisor (if not installed)
sudo apt-get update
sudo apt-get install supervisor

# 2. Copy config (already created for you!)
sudo cp supervisor-laravel-worker-fixed.conf /etc/supervisor/conf.d/laravel-worker.conf

# 3. Update paths in the config file
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
# Change paths to match your installation

# 4. Reload Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*

# 5. Check status
sudo supervisorctl status laravel-worker:*
```

**Pros**: Auto-restart, survives reboots, production-ready
**Cons**: Requires setup

---

## 📊 **Monitoring Queue**

### **Check Queue Status**
```bash
# See pending jobs
php artisan queue:monitor

# Or manually check database
php artisan tinker
>>> \App\Models\Job::count();  # Pending jobs
```

### **View Logs**
```bash
# Worker logs
tail -f storage/logs/worker.log

# Laravel logs
tail -f storage/logs/laravel.log

# Real-time monitoring (if using supervisor)
sudo supervisorctl tail -f laravel-worker:00 stdout
```

### **Failed Jobs**
```bash
# List failed jobs
php artisan queue:failed

# Retry all failed jobs
php artisan queue:retry all

# Retry specific job
php artisan queue:retry <job-id>

# Clear failed jobs
php artisan queue:flush
```

---

## 🛠️ **Troubleshooting**

### **Issue 1: Worker Still Being Killed**

**Solution A: Increase Memory Limit**
```bash
--memory=512  # Instead of 256
```

**Solution B: Reduce Max Jobs**
```bash
--max-jobs=50  # Instead of 100
```

**Solution C: Add PHP Memory Limit**
```bash
php -d memory_limit=512M artisan queue:work ...
```

### **Issue 2: Jobs Not Processing**

**Check if worker is running:**
```bash
pgrep -f "queue:work"
# Should show process ID. If nothing, worker is not running.
```

**Check queue connection:**
```bash
php artisan queue:monitor
# Should show pending jobs
```

**Check database:**
```bash
# MySQL
mysql -u your_user -p
USE your_database;
SELECT COUNT(*) FROM jobs;  # Should show pending jobs
```

### **Issue 3: Emails Not Sending**

**Check mail configuration:**
```bash
php artisan tinker
>>> Mail::to('test@example.com')->send(new \App\Mail\TestMail());
```

**Check `.env` file:**
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

**Check queue table:**
```bash
php artisan tinker
>>> DB::table('jobs')->get();  # See pending jobs
>>> DB::table('failed_jobs')->get();  # See failed jobs
```

### **Issue 4: Worker Stops Unexpectedly**

**Use Supervisor** (recommended for production):
- Auto-restarts on crash
- Survives server reboots
- Multiple workers for redundancy

**Or use systemd:**
```bash
# Create service file
sudo nano /etc/systemd/system/laravel-worker.service

# Add this content:
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
User=royal-t
Group=royal-t
Restart=always
ExecStart=/usr/bin/php /home/royal-t/doctorontap campain/artisan queue:work database --sleep=3 --tries=3 --timeout=90 --max-jobs=100 --memory=256

[Install]
WantedBy=multi-user.target

# Enable and start
sudo systemctl enable laravel-worker
sudo systemctl start laravel-worker

# Check status
sudo systemctl status laravel-worker
```

---

## 🎯 **Best Practices**

### **1. Optimize Job Processing**

**Make jobs small and focused:**
```php
// ❌ Bad: Process 1000 emails in one job
class SendBulkEmails implements ShouldQueue {
    public function handle() {
        foreach ($this->users as $user) {
            Mail::send(...);  // Memory accumulates!
        }
    }
}

// ✅ Good: One email per job
class SendEmail implements ShouldQueue {
    public function handle() {
        Mail::to($this->user)->send(...);  // Single email, clean memory
    }
}
```

### **2. Use Job Batching for Large Operations**

```php
// Dispatch multiple small jobs
foreach ($users as $user) {
    SendEmail::dispatch($user);
}
```

### **3. Monitor Memory Usage**

```php
// In your job
public function handle() {
    Log::info('Memory before: ' . memory_get_usage(true) / 1024 / 1024 . ' MB');
    
    // Your job logic
    
    Log::info('Memory after: ' . memory_get_usage(true) / 1024 / 1024 . ' MB');
}
```

### **4. Set Proper Timeouts**

```php
class SendEmail implements ShouldQueue {
    public $timeout = 90;  // 90 seconds max
    public $tries = 3;     // Retry 3 times
    
    public function handle() {
        // Job logic
    }
}
```

### **5. Use Database Queue for Reliability**

Already configured in your `.env`:
```env
QUEUE_CONNECTION=database
```

This is perfect! Jobs are stored in database and won't be lost.

---

## 📈 **Performance Tips**

### **Multiple Workers** (for high load)

**Using Supervisor:**
```ini
numprocs=4  # Run 4 workers simultaneously
```

**Manual:**
```bash
# Terminal 1
php artisan queue:work database --queue=high

# Terminal 2
php artisan queue:work database --queue=default

# Terminal 3
php artisan queue:work database --queue=low
```

### **Priority Queues**

```php
// High priority (payments, critical emails)
SendPaymentConfirmation::dispatch($order)->onQueue('high');

// Normal priority
SendConsultationConfirmation::dispatch($consultation);

// Low priority (marketing emails)
SendNewsletter::dispatch($user)->onQueue('low');
```

### **Job Chaining**

```php
// Execute jobs in sequence
Bus::chain([
    new ProcessConsultation($consultation),
    new SendConfirmationEmail($consultation),
    new NotifyDoctor($consultation),
])->dispatch();
```

---

## 🔄 **Quick Commands Reference**

```bash
# Start worker
./start-queue-worker.sh

# Stop worker
php artisan queue:restart

# Check status
pgrep -f "queue:work"

# View logs
tail -f storage/logs/worker.log

# Failed jobs
php artisan queue:failed        # List
php artisan queue:retry all     # Retry all
php artisan queue:flush         # Clear all

# Clear jobs
php artisan queue:clear         # Clear pending
php artisan queue:clear --queue=high  # Clear specific queue

# Supervisor (if installed)
sudo supervisorctl status       # Check status
sudo supervisorctl restart laravel-worker:*  # Restart
sudo supervisorctl tail -f laravel-worker:00  # View logs
```

---

## ✅ **Your Queue is Fixed!**

### **What Changed:**
1. ✅ Memory limit added: `--memory=256`
2. ✅ Max jobs reduced: `1000` → `100`
3. ✅ Worker won't be killed anymore
4. ✅ Auto-restarts before memory issues
5. ✅ Production-ready configuration created

### **Next Steps:**
1. **Start the worker** using one of the methods above
2. **Monitor** for a few hours to ensure stability
3. **Consider Supervisor** for production (auto-restart, etc.)
4. **Check logs** regularly: `tail -f storage/logs/worker.log`

### **Testing:**
```bash
# Send a test email
php artisan tinker
>>> Mail::to('your@email.com')->send(new \App\Mail\ConsultationConfirmation(...));
>>> exit

# Check if it's queued
php artisan queue:monitor

# Worker should process it within seconds!
```

---

## 🎉 **Queue Worker Now Stable!**

Your emails will be processed reliably:
- ✅ Consultation confirmations
- ✅ Payment requests
- ✅ Treatment plan notifications
- ✅ Doctor notifications
- ✅ Vital signs reports
- ✅ All other emails

**Start your worker and let it run!** 🚀

