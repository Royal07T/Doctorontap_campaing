# 💰 "Consult Now, Pay Later" Campaign - Complete Implementation

## ✅ Campaign Successfully Implemented!

Your DoctorOnTap platform now supports the **"Pay After Consultation"** model where patients book consultations **without any upfront payment** and only receive a payment request **after** the consultation is completed.

---

## 🎯 How It Works

### **Patient Journey:**

```
1. Patient Books Consultation
   ↓
2. NO PAYMENT REQUIRED (Books instantly!)
   ↓
3. Receives Confirmation Email
   ↓
4. You Contact & Schedule Consultation
   ↓
5. Consultation Happens (Voice/Video/Chat)
   ↓
6. You Mark Consultation as "Completed"
   ↓
7. System Sends Payment Request Email
   ↓
8. Patient Clicks "PAY NOW" Button in Email
   ↓
9. Redirected to Korapay Checkout
   ↓
10. Payment Completed
   ↓
11. Everyone Gets Confirmation!
```

---

## 📧 Payment Request Email

The payment request email includes:

✅ **Professional Design** with your branding  
✅ **Clear Consultation Details** (Doctor, Date, Problem, etc.)  
✅ **Large Payment Amount** displayed prominently  
✅ **Secure "PAY NOW" Button** that redirects to Korapay  
✅ **Payment Method Icons** (Bank Transfer, Card, Mobile Money)  
✅ **Security Assurance** message  
✅ **Support Contact** information  
✅ **Consultation Reference** for tracking  

**Email Template:** `resources/views/emails/payment-request.blade.php`

---

## 🗄️ Database Structure

### **Consultations Table**
Tracks all consultation bookings:
- Patient information (name, email, mobile, age, gender)
- Medical details (problem, severity, symptoms)
- Consultation preferences (mode, doctor)
- Status tracking (pending → scheduled → completed → cancelled)
- Payment status (unpaid → pending → paid)
- Payment request tracking

### **Payments Table** (Already Created)
Tracks all payment transactions:
- Transaction references
- Customer details
- Payment amounts and fees
- Korapay responses
- Payment status

---

## 🔄 Complete Flow Details

### **Step 1: Patient Books Consultation**
- Patient fills form on website
- Selects doctor (with fee)
- Submits form
- **NO PAYMENT ASKED!** 🎉

**What Happens:**
```php
// Creates consultation record in database
Consultation::create([
    'reference' => 'CONSULT-1696850400-Abc123',
    'status' => 'pending',
    'payment_status' => 'unpaid',
    // ... other details
]);
```

**Patient Sees:**
> "Thank you! Your consultation has been booked successfully. We will contact you shortly via WhatsApp to schedule your consultation. Remember: You only pay AFTER your consultation is complete."

---

### **Step 2: You Complete the Consultation**

After you complete the consultation, you need to:
1. Mark consultation as "completed" in your system
2. Send payment request to patient

**To Send Payment Request:**

```bash
# Via API call or admin interface
POST /admin/consultation/{id}/send-payment
```

**Or manually update:**
```php
$consultation = Consultation::find($id);
$consultation->update([
    'status' => 'completed',
    'consultation_completed_at' => now()
]);

// Then send payment request
POST /admin/consultation/{id}/send-payment
```

---

### **Step 3: Patient Receives Payment Email**

Patient gets beautiful email with:
- **Subject:** "Payment Request - Your Consultation with Dr. [Name]"
- Large **₦X,XXX.00** amount display
- Big **"🔒 PAY NOW SECURELY"** button
- Consultation summary
- Security reassurance

**Payment Link Format:**
```
https://your-domain.com/payment/request/CONSULT-1696850400-Abc123
```

---

### **Step 4: Patient Clicks Payment Button**

When patient clicks the button:
1. System verifies consultation is completed
2. Creates payment record
3. Initializes Korapay payment
4. Redirects patient to Korapay checkout

**Patient Experience:**
- Clicks button in email
- Instantly redirected to secure Korapay page
- Chooses payment method (Bank/Card/Mobile Money)
- Completes payment
- Redirected back to your site with confirmation

---

### **Step 5: Payment Confirmation**

After successful payment:
1. Korapay redirects to `/payment/callback`
2. System verifies payment with Korapay
3. Updates consultation status to "paid"
4. Shows success page
5. Sends webhook to update records

**Webhook Updates:**
- Consultation payment_status → "paid"
- Payment status → "success"
- Links payment to consultation

---

## 🛠️ API Endpoints

### **For Admin/Staff:**

#### Send Payment Request
```http
POST /admin/consultation/{id}/send-payment
```

**Response:**
```json
{
  "success": true,
  "message": "Payment request email sent successfully"
}
```

**Validations:**
- ✓ Consultation must be completed
- ✓ Payment request not already sent
- ✓ Doctor has a consultation fee

---

### **For Patients:**

#### Payment Request Link
```http
GET /payment/request/{consultation_reference}
```

**What It Does:**
- Verifies consultation exists and is completed
- Checks if already paid
- Creates payment record
- Initializes Korapay
- Redirects to checkout

---

## 📊 Consultation Statuses

| Status | Description |
|--------|-------------|
| `pending` | Just booked, awaiting contact |
| `scheduled` | Appointment scheduled |
| `completed` | Consultation finished |
| `cancelled` | Consultation cancelled |

## 💰 Payment Statuses

| Status | Description |
|--------|-------------|
| `unpaid` | No payment made yet |
| `pending` | Payment in progress |
| `paid` | Payment completed |

---

## 🧪 Testing the "Pay After" Flow

### **Test Scenario:**

1. **Book Consultation:**
   - Go to your website
   - Fill consultation form
   - Select a doctor with fee
   - Submit
   - ✓ Should see success message (NO payment request)

2. **Simulate Consultation:**
   ```php
   // In Tinker or code
   $consultation = Consultation::latest()->first();
   $consultation->update([
       'status' => 'completed',
       'consultation_completed_at' => now()
   ]);
   ```

3. **Send Payment Request:**
   ```bash
   # Via API
   curl -X POST http://your-domain.com/admin/consultation/1/send-payment \
     -H "Content-Type: application/json" \
     -H "X-CSRF-TOKEN: your-token"
   ```

4. **Check Email:**
   - Patient should receive payment request email
   - Click "PAY NOW" button

5. **Complete Payment:**
   - Should redirect to Korapay
   - Complete payment
   - Should redirect back with success

---

## 📝 Sample Admin Workflow

```php
// After completing a consultation:

// 1. Get consultation
$consultation = Consultation::find($id);

// 2. Mark as completed
$consultation->update([
    'status' => 'completed',
    'consultation_completed_at' => now()
]);

// 3. Send payment request via controller
app(ConsultationController::class)->sendPaymentRequest($id);

// Patient receives email automatically!
```

---

## 🎨 Email Customization

The payment request email can be customized:

**File:** `resources/views/emails/payment-request.blade.php`

**Customizable Elements:**
- Colors and branding
- Button text and style
- Payment amount display
- Additional information
- Footer content

---

## 🔐 Security Features

✅ **Consultation Verification** - Ensures consultation exists and is completed  
✅ **Duplicate Prevention** - Prevents multiple payments for same consultation  
✅ **Secure Payment Links** - Unique consultation references  
✅ **CSRF Protection** - All POST requests protected  
✅ **Payment Verification** - Double-checks with Korapay before confirming  
✅ **Webhook Validation** - Logs all webhook events  

---

## 📈 Tracking & Reports

### View All Consultations:
```php
// In Tinker
Consultation::with('doctor', 'payment')->get();
```

### View Unpaid Consultations:
```php
Consultation::where('status', 'completed')
    ->where('payment_status', 'unpaid')
    ->get();
```

### View Paid Consultations:
```php
Consultation::where('payment_status', 'paid')
    ->with('payment')
    ->get();
```

---

## 🚀 Next Steps

### **Immediately:**
1. Test the complete flow
2. Verify email delivery
3. Test payment with small amount

### **Before Launch:**
1. Configure Korapay webhook URL in dashboard
2. Test with real Korapay account
3. Set up proper error monitoring
4. Train staff on marking consultations complete

### **Optional Enhancements:**
1. Admin dashboard to manage consultations
2. Automated reminders for unpaid consultations
3. Partial payment support
4. Multiple payment attempts tracking
5. SMS notifications alongside email

---

## 💡 Pro Tips

1. **Send Payment Requests Promptly** - Send within 24 hours of consultation for best payment rates

2. **Follow Up** - If payment not received in 48 hours, send reminder via WhatsApp

3. **Track Everything** - Use consultation references to track issues

4. **Monitor Logs** - Check `storage/logs/laravel.log` for payment issues

5. **Test Regularly** - Do end-to-end tests weekly

---

## 📞 Support & Troubleshooting

### **Payment Not Initialized:**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Verify Korapay credentials
php artisan tinker
env('KORAPAY_SECRET_KEY')
```

### **Email Not Sent:**
```bash
# Check mail configuration
php artisan tinker
config('mail')

# Test email manually
Mail::raw('Test', function($msg) {
    $msg->to('test@example.com')->subject('Test');
});
```

### **Payment Link Not Working:**
```php
// Verify consultation
$consultation = Consultation::where('reference', 'CONSULT-XXX')->first();
dd($consultation);

// Check if completed
$consultation->isCompleted();

// Check if already paid
$consultation->isPaid();
```

---

## 🎉 Campaign Benefits

### **For Patients:**
✅ Book instantly without payment barriers  
✅ Only pay after satisfied with consultation  
✅ Build trust with your service  
✅ Secure and convenient payment options  

### **For You:**
✅ More bookings (no payment friction)  
✅ Better conversion rates  
✅ Automated payment collection  
✅ Professional payment process  
✅ Complete audit trail  

---

## 📚 Files Modified/Created

### **New Files:**
- `app/Models/Consultation.php` - Consultation model
- `app/Mail/PaymentRequest.php` - Payment request mailable
- `resources/views/emails/payment-request.blade.php` - Email template
- `database/migrations/2025_10_09_125904_create_consultations_table.php`

### **Modified Files:**
- `app/Http/Controllers/ConsultationController.php` - Added consultation tracking
- `app/Http/Controllers/PaymentController.php` - Added payment request handling
- `routes/web.php` - Added payment request routes
- `resources/views/consultation/index.blade.php` - Removed upfront payment

---

## ✅ Implementation Checklist

- [x] Removed upfront payment requirement
- [x] Created consultations tracking table
- [x] Created Consultation model
- [x] Updated ConsultationController to save consultations
- [x] Created beautiful payment request email
- [x] Added payment request route
- [x] Added handlePaymentRequest method
- [x] Added admin route to send payment
- [x] Updated frontend to skip payment
- [x] Tested database migrations
- [x] Cleared all caches
- [ ] Test complete flow
- [ ] Configure Korapay webhook in dashboard
- [ ] Train staff on process

---

**🎊 Your "Consult Now, Pay Later" campaign is ready to launch!**

Patients can now book consultations instantly without any payment, building trust and increasing conversions. After consultations, they receive a beautiful payment request email with an embedded secure payment button.

**Campaign Tagline:** *"Talk to a Doctor Now. No Payment Needed Upfront. Pay Only After Your Consultation!"*

