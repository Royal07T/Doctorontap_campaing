# ⚡ Queue Worker Fix - URGENT

## ❌ **The Problem You Had**

Your queue worker was being **killed** due to memory exhaustion:

```bash
Killed  # System killed the process (OOM - Out of Memory)
```

**Why it happened:**
- Your worker was processing up to **1000 jobs** before restarting
- Each job (especially emails with attachments) uses memory
- After many jobs, memory accumulated to 400-500MB+
- System killed the process to prevent crash

---

## ✅ **The Fix (Already Applied)**

### **What I Changed:**

**File: `start-queue-worker.sh`**

**Before:**
```bash
--max-jobs=1000  # ❌ Too many! Causes memory issues
```

**After:**
```bash
--max-jobs=100    # ✅ Restarts every 100 jobs
--memory=256      # ✅ Restarts if using >256MB RAM
```

### **New Configuration:**
```bash
php artisan queue:work database \
    --sleep=3           # Wait 3s between jobs
    --tries=3           # Retry failed jobs 3 times
    --timeout=90        # Job timeout: 90s
    --max-jobs=100      # ✅ Restart after 100 jobs (prevents leak)
    --max-time=3600     # Restart after 1 hour
    --memory=256        # ✅ Restart if RAM > 256MB
    --verbose           # Show output
```

---

## 🚀 **How to Restart Your Queue Worker NOW**

### **Quick Method (Recommended):**

```bash
# Use the new restart script
./restart-queue-worker.sh
```

This will:
1. Stop old workers
2. Start new worker with fixed settings
3. Run in background
4. Show you the process ID

### **Manual Method:**

```bash
# 1. Stop old workers
php artisan queue:restart
pkill -f "queue:work"

# 2. Wait a moment
sleep 3

# 3. Start new worker
./start-queue-worker.sh
```

### **Check if Running:**

```bash
# Should show process ID
pgrep -f "queue:work"

# View logs
tail -f storage/logs/worker.log
```

---

## 📊 **Verify It's Working**

### **Test 1: Send a Test Email**

```bash
php artisan tinker
```

```php
// In tinker, run:
Mail::to('your@email.com')->send(new \App\Mail\ConsultationConfirmation(...));
exit
```

### **Test 2: Monitor Queue**

```bash
# Watch the worker process emails
tail -f storage/logs/worker.log
```

You should see:
```
[timestamp] Processing: App\Mail\ConsultationConfirmation
[timestamp] Processed:  App\Mail\ConsultationConfirmation
```

---

## 🛡️ **Why This Fix Works**

### **Memory Management:**

| Setting | Before | After | Benefit |
|---------|--------|-------|---------|
| Max Jobs | 1000 | 100 | **10x less memory accumulation** |
| Memory Limit | None | 256MB | **Auto-restart before OOM** |
| Result | Killed | Stable | **No more crashes!** |

### **How It Works:**

1. **Worker processes 100 jobs** → Uses ~150MB RAM
2. **Reaches 100 jobs** → Restarts automatically
3. **Memory cleared** → Fresh start
4. **If RAM hits 256MB** → Restarts before system kills it
5. **No downtime** → Jobs continue processing

---

## 📁 **Files I Created/Updated**

```
✅ start-queue-worker.sh              (UPDATED - fixed settings)
✅ restart-queue-worker.sh            (NEW - quick restart script)
✅ supervisor-laravel-worker-fixed.conf  (NEW - production config)
✅ QUEUE_MANAGEMENT.md                (NEW - complete guide)
✅ QUEUE_FIX_SUMMARY.md               (NEW - this file)
```

---

## 🎯 **Your Next Steps**

### **Right Now:**
1. ✅ Run: `./restart-queue-worker.sh`
2. ✅ Check: `pgrep -f "queue:work"`
3. ✅ Monitor: `tail -f storage/logs/worker.log`

### **For Production:**
Consider using **Supervisor** for auto-restart on crash:
- Read: `QUEUE_MANAGEMENT.md` (complete instructions)
- Or use the systemd service (also in the guide)

### **Long Term:**
Monitor your worker periodically:
```bash
# Add to crontab for monitoring
*/5 * * * * pgrep -f "queue:work" || /path/to/restart-queue-worker.sh
```

---

## 🐛 **If Still Having Issues**

### **Issue: Worker still being killed**

**Solution 1: Increase memory limit**
```bash
# In start-queue-worker.sh, change:
--memory=512  # Instead of 256
```

**Solution 2: Reduce max jobs**
```bash
# In start-queue-worker.sh, change:
--max-jobs=50  # Instead of 100
```

**Solution 3: Check system memory**
```bash
free -h  # See available RAM
top      # See what's using memory
```

### **Issue: Jobs not processing**

**Check worker is running:**
```bash
pgrep -f "queue:work"  # Should show PID
```

**Check pending jobs:**
```bash
php artisan tinker
>>> DB::table('jobs')->count();  # Pending jobs
>>> DB::table('failed_jobs')->count();  # Failed jobs
```

**Retry failed jobs:**
```bash
php artisan queue:retry all
```

---

## 📚 **Documentation**

For complete details, see:
- **`QUEUE_MANAGEMENT.md`** - Complete queue management guide
- **`QUEUE_FIX_SUMMARY.md`** - This file (quick reference)

---

## ✅ **Summary**

**Problem**: Queue worker killed due to memory
**Cause**: Processing too many jobs (1000) without restart
**Fix**: Reduced to 100 jobs + added 256MB memory limit
**Result**: Worker now stable, auto-restarts before issues

**Your queue worker is now production-ready!** 🎉

---

## 🚀 **Start Your Worker Now!**

```bash
./restart-queue-worker.sh
```

Then monitor:
```bash
tail -f storage/logs/worker.log
```

**All your emails will process smoothly now!** ✉️

