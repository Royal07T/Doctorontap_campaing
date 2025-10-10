# 📊 Admin Dashboard - Complete Guide

## ✅ Admin Dashboard Successfully Created!

You now have a fully functional admin dashboard to manage consultations, send payment requests, and track payments!

---

## 🔗 Access URLs

### **Admin Dashboard:**
```
http://your-domain.com/admin/dashboard
```

### **Manage Consultations:**
```
http://your-domain.com/admin/consultations
```

### **View Payments:**
```
http://your-domain.com/admin/payments
```

---

## 📊 Dashboard Overview

### **Main Dashboard Features:**

✅ **Real-time Statistics:**
- Total Consultations
- Pending Consultations
- Completed Consultations
- Awaiting Payment (Completed but Unpaid)
- Paid Consultations
- Total Revenue (₦)

✅ **Quick Actions:**
- Manage Consultations (Direct Link)
- View Payments (Direct Link)

---

## 📋 Consultations Management

### **Features:**

1. **Filter & Search**
   - Search by name, email, or reference
   - Filter by consultation status (Pending/Scheduled/Completed/Cancelled)
   - Filter by payment status (Unpaid/Pending/Paid)

2. **View All Details**
   - Patient information (name, email, mobile)
   - Doctor assigned
   - Medical problem/complaint
   - Consultation and payment status
   - Creation date and time

3. **Update Status** (One-Click!)
   - Change status directly from dropdown
   - Options: Pending → Scheduled → Completed → Cancelled
   - Auto-saves when changed

4. **Send Payment Requests**
   - "Send Payment" button appears when:
     - Consultation is marked as "Completed"
     - Payment is "Unpaid"
     - No payment request sent yet
   - "Resend Payment" button if already sent once
   - Confirmation dialog before sending

---

## 🔄 Complete Workflow

### **Step 1: Patient Books Consultation**
- Patient fills form on website
- Consultation appears in admin dashboard
- Status: **Pending**
- Payment Status: **Unpaid**

### **Step 2: Schedule & Conduct Consultation**
1. Go to: `http://your-domain.com/admin/consultations`
2. Find the consultation in the list
3. Change status dropdown to **"Scheduled"**
4. Contact patient via WhatsApp/Phone
5. Conduct the consultation

### **Step 3: Mark as Completed**
1. After consultation is done
2. Change status dropdown to **"Completed"**
3. Status updates automatically

### **Step 4: Send Payment Request**
1. **"Send Payment" button** appears automatically
2. Click the button
3. Confirm sending email
4. ✅ Patient receives beautiful payment email
5. Button changes to **"Resend Payment"** if needed

### **Step 5: Track Payment**
- Patient clicks payment link in email
- Makes payment via Korapay
- Payment status updates to **"Paid"** automatically
- View in Payments section

---

## 💰 Payments Section

### **Features:**

✅ **View All Transactions**
- Payment reference numbers
- Customer details
- Doctor assigned
- Amount paid
- Transaction fees
- Payment status (Pending/Success/Failed)
- Payment method used
- Date and time

✅ **Filter Options**
- Filter by payment status
- View successful payments only
- Track failed payments

✅ **Revenue Tracking**
- All successful payments shown
- Calculate total revenue easily

---

## 🎨 User Interface

### **Dashboard Design:**
- 📊 **Modern & Clean** - Professional Tailwind CSS design
- 🎨 **Color-Coded** - Easy to identify statuses at a glance
- 📱 **Responsive** - Works on desktop, tablet, and mobile
- ⚡ **Fast** - One-click status updates
- 🔍 **Search & Filter** - Find anything quickly

### **Status Color Codes:**

**Consultation Status:**
- 🟡 **Pending** - Yellow badge
- 🔵 **Scheduled** - Blue badge  
- 🟢 **Completed** - Green badge
- 🔴 **Cancelled** - Red badge

**Payment Status:**
- 🔴 **Unpaid** - Red badge
- 🟡 **Pending** - Yellow badge
- 🟢 **Paid** - Green badge

---

## 🔧 Quick Actions Guide

### **To Send Payment Request:**

**Method 1: From Consultations List**
```
1. Go to: /admin/consultations
2. Filter to show "Completed" consultations
3. Find consultation with "Unpaid" status
4. Click "Send Payment" button
5. Confirm
6. ✅ Done! Email sent
```

**Method 2: Using API**
```bash
curl -X POST http://your-domain.com/admin/consultation/{id}/send-payment \
  -H "X-CSRF-TOKEN: your-token"
```

**Method 3: Using PHP**
```php
use App\Http\Controllers\Admin\DashboardController;

$dashboard = new DashboardController();
$response = $dashboard->sendPaymentRequest($consultationId);
```

---

## 📝 Admin Actions

### **Update Consultation Status:**
```javascript
// Auto-triggered when dropdown changes
POST /admin/consultation/{id}/status
Body: { "status": "completed" }
```

### **Send Payment Request:**
```javascript
POST /admin/consultation/{id}/send-payment
Response: { "success": true, "message": "Payment request email sent" }
```

### **Validation Rules:**
- ✓ Consultation must be "completed"
- ✓ Payment must be "unpaid"
- ✓ Doctor must have a consultation fee

---

## 🔍 Search & Filter Examples

### **Find Specific Patient:**
```
Search: "john@email.com"
or
Search: "CONSULT-1696850400"
```

### **View Unpaid Completed Consultations:**
```
Status: Completed
Payment Status: Unpaid
```

### **View All Scheduled Consultations:**
```
Status: Scheduled
```

---

## 📊 Dashboard Statistics

### **What Each Stat Means:**

1. **Total Consultations** - All bookings ever made
2. **Pending Consultations** - Need to be scheduled
3. **Completed Consultations** - Consultations finished
4. **Awaiting Payment** - Completed but not paid yet (ACTION NEEDED!)
5. **Paid Consultations** - Successfully paid
6. **Total Revenue** - Sum of all successful payments

---

## 🚀 Daily Workflow Recommendation

### **Morning Check (9 AM):**
1. Check "Pending Consultations" count
2. Contact patients to schedule appointments
3. Update statuses to "Scheduled"

### **During Day:**
1. Conduct consultations
2. Mark as "Completed" immediately after
3. Send payment requests right away

### **Evening Review (6 PM):**
1. Check "Awaiting Payment" count
2. Send reminders if needed (via WhatsApp)
3. Review "Paid Consultations" for the day
4. Check total revenue

---

## 💡 Pro Tips

1. **Mark Completed Immediately** - Don't delay marking consultations as completed. This ensures prompt payment requests.

2. **Send Payment Requests Fast** - Send within 1 hour of consultation for best payment rates.

3. **Use Filters Effectively** - Filter by "Completed + Unpaid" to see who needs payment requests.

4. **Monitor Payment Status** - Check payments section daily to track revenue.

5. **Follow Up on Failed Payments** - If payment fails, contact patient via WhatsApp to assist.

6. **Keep References Handy** - Use consultation references when communicating with patients.

---

## 🔐 Security Notes

**Current Status:**
- ⚠️ Dashboard is publicly accessible (no login required yet)
- 🔒 All admin actions require CSRF tokens
- ✅ Payment requests are validated before sending

**To Add Authentication (Recommended):**

You can add middleware to protect admin routes later. For now, keep the admin URLs private and don't share them publicly.

**Quick Security Option:**
Add `.htpasswd` protection on your web server:
```apache
# Apache .htaccess
<Location /admin>
    AuthType Basic
    AuthName "Admin Area"
    AuthUserFile /path/to/.htpasswd
    Require valid-user
</Location>
```

---

## 📱 Mobile Access

The admin dashboard is fully responsive and works on:
- ✅ Desktop computers
- ✅ Tablets
- ✅ Mobile phones

You can manage consultations on the go!

---

## 🐛 Troubleshooting

### **"Send Payment" Button Not Showing:**
- Check if consultation status is "Completed"
- Verify doctor has a consultation fee set
- Ensure payment status is "Unpaid"

### **Email Not Sent:**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Test mail configuration
php artisan tinker
config('mail')
```

### **Status Not Updating:**
- Check browser console for JavaScript errors
- Verify CSRF token is present
- Clear cache: `php artisan cache:clear`

---

## 📈 Analytics & Reports

### **Get Monthly Revenue:**
```php
use App\Models\Payment;
use Carbon\Carbon;

$monthlyRevenue = Payment::where('status', 'success')
    ->whereMonth('created_at', Carbon::now()->month)
    ->sum('amount');
```

### **Count Today's Consultations:**
```php
use App\Models\Consultation;
use Carbon\Carbon;

$todayCount = Consultation::whereDate('created_at', Carbon::today())->count();
```

### **Unpaid Consultations Report:**
```php
$unpaid = Consultation::where('status', 'completed')
    ->where('payment_status', 'unpaid')
    ->with('doctor')
    ->get();
```

---

## 🎯 Key Features Summary

✅ **Dashboard** - Real-time statistics and overview  
✅ **Consultations Management** - View, filter, search, update  
✅ **One-Click Status Updates** - Change status via dropdown  
✅ **Payment Request Sending** - One-click email to patients  
✅ **Payments Tracking** - View all transactions  
✅ **Search & Filter** - Find anything quickly  
✅ **Responsive Design** - Works on all devices  
✅ **Color-Coded Status** - Easy visual identification  
✅ **Pagination** - Handle large datasets  
✅ **AJAX Updates** - No page reloads needed  

---

## 🎊 You're All Set!

Your admin dashboard is ready to use! Access it at:

```
http://your-domain.com/admin/dashboard
```

**Quick Start:**
1. Visit dashboard to see overview
2. Click "Manage Consultations"
3. Mark consultation as "Completed"
4. Click "Send Payment"
5. Patient receives email
6. Track payment in Payments section

---

**Need Help?**
- Check Laravel logs: `storage/logs/laravel.log`
- All routes are RESTful and follow Laravel conventions
- Frontend uses Alpine.js for interactivity

🎉 **Happy Managing!**

