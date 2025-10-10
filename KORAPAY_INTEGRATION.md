# Korapay Payment Integration - DoctorOnTap

## ✅ Integration Complete

Korapay payment gateway has been successfully integrated into the DoctorOnTap consultation platform using the **Checkout Redirect** method.

---

## 🔐 Environment Variables (.env)

All sensitive credentials are stored in `.env` file:

```env
KORAPAY_PUBLIC_KEY=pk_live_your_public_key_here
KORAPAY_SECRET_KEY=sk_live_your_secret_key_here
KORAPAY_ENCRYPTION_KEY=your_encryption_key_here
KORAPAY_API_URL=https://api.korapay.com/merchant/api/v1
```

**⚠️ Important:** Never commit the `.env` file to version control!

---

## 📊 Database Schema

### Payments Table
Created migration: `2025_10_09_121506_create_payments_table.php`

**Fields:**
- `id` - Primary key
- `reference` - Unique transaction reference (format: DOT-{timestamp}-{random})
- `customer_email` - Customer email address
- `customer_name` - Customer full name
- `customer_phone` - Customer phone number
- `amount` - Payment amount (decimal)
- `currency` - Currency code (default: NGN)
- `status` - Payment status (pending/success/failed)
- `payment_method` - Payment method used (bank_transfer, card, pay_with_bank, mobile_money)
- `payment_reference` - Korapay payment reference
- `fee` - Transaction fee
- `checkout_url` - Korapay checkout URL
- `metadata` - Additional consultation data (JSON)
- `korapay_response` - Full Korapay API response (JSON)
- `doctor_id` - Foreign key to doctors table
- `timestamps` - Created at & Updated at

---

## 🎯 Payment Flow

### 1. User Books Consultation
- User fills out the consultation form
- Selects a doctor (with consultation fee)
- Submits the form

### 2. Consultation Booking
- `ConsultationController@store` validates and saves consultation
- Sends confirmation emails to patient and admin
- Returns payment data if doctor has a fee

### 3. Payment Initialization
- Frontend JavaScript calls `/payment/initialize`
- `PaymentController@initialize` creates payment record
- Makes API call to Korapay to get checkout URL
- Returns checkout URL to frontend

### 4. Payment Redirect
- User is redirected to Korapay checkout page
- User completes payment using their preferred method

### 5. Payment Callback
- After payment, Korapay redirects to `/payment/callback?reference=XXX`
- `PaymentController@callback` verifies the transaction
- Shows success or failed page based on status

### 6. Webhook Notification
- Korapay sends webhook to `/payment/webhook`
- `PaymentController@webhook` updates payment status
- Logs all webhook events

---

## 🛣️ Routes

```php
// Payment Routes
POST   /payment/initialize   - Initialize payment and get checkout URL
GET    /payment/callback     - Handle payment redirect after completion
GET    /payment/verify       - Manually verify a transaction
POST   /payment/webhook      - Receive webhook notifications from Korapay
```

---

## 📁 Files Created/Modified

### New Files:
1. **Migration:** `database/migrations/2025_10_09_121506_create_payments_table.php`
2. **Model:** `app/Models/Payment.php`
3. **Controller:** `app/Http/Controllers/PaymentController.php`
4. **Views:**
   - `resources/views/payment/success.blade.php`
   - `resources/views/payment/failed.blade.php`

### Modified Files:
1. **Routes:** `routes/web.php` - Added payment routes
2. **Consultation Controller:** `app/Http/Controllers/ConsultationController.php` - Added payment data to response
3. **Consultation Form:** `resources/views/consultation/index.blade.php` - Added payment initialization JavaScript

---

## 🔧 Payment Controller Methods

### `initialize(Request $request)`
**Purpose:** Initialize payment with Korapay

**Request Payload:**
```json
{
  "amount": 3000,
  "customer_email": "patient@example.com",
  "customer_name": "John Doe",
  "customer_phone": "08012345678",
  "doctor_id": 1,
  "metadata": {
    "consultation_for": "Headache",
    "severity": "mild"
  }
}
```

**Response:**
```json
{
  "success": true,
  "checkout_url": "https://checkout.korapay.com/reference/pay",
  "reference": "DOT-1696850400-Abc123Xy"
}
```

### `callback(Request $request)`
**Purpose:** Handle payment redirect after completion

**Query Parameters:**
- `reference` - Transaction reference

**Behavior:**
- Verifies transaction with Korapay
- Updates payment status
- Displays success or failed page

### `verify(Request $request)`
**Purpose:** Manually verify transaction status

**Query Parameters:**
- `reference` - Transaction reference

**Response:**
```json
{
  "success": true,
  "status": "success",
  "amount": 3000,
  "payment_method": "bank_transfer"
}
```

### `webhook(Request $request)`
**Purpose:** Receive webhook notifications from Korapay

**Expected Payload:**
```json
{
  "event": "charge.success",
  "data": {
    "reference": "DOT-1696850400-Abc123Xy",
    "currency": "NGN",
    "amount": 3000,
    "fee": 107.5,
    "status": "success",
    "payment_method": "bank_transfer",
    "payment_reference": "DOT-1696850400-Abc123Xy"
  }
}
```

---

## 🎨 Payment Views

### Success Page (`payment/success.blade.php`)
- Green checkmark icon
- Payment details display
- Next steps information
- Back to home button

### Failed Page (`payment/failed.blade.php`)
- Red X icon
- Error message
- Possible reasons for failure
- Try again and contact support buttons

---

## 🔄 Integration Flow Diagram

```
[User Fills Form] 
      ↓
[Submit to /submit]
      ↓
[Consultation Booked]
      ↓
[Return Payment Data]
      ↓
[Frontend: POST /payment/initialize]
      ↓
[Korapay API: Create Charge]
      ↓
[Return Checkout URL]
      ↓
[Redirect to Korapay]
      ↓
[User Completes Payment]
      ↓
[Korapay Redirects to /payment/callback]
      ↓
[Verify Transaction]
      ↓
[Show Success/Failed Page]
      ↓
[Korapay Webhook to /payment/webhook]
      ↓
[Update Payment Record]
```

---

## 🧪 Testing the Integration

### Test Flow:
1. Visit: `http://your-domain.com/`
2. Fill out consultation form
3. Select a doctor with a fee (e.g., Dr. Chinenye Agu - ₦10,000)
4. Complete the form and submit
5. You'll be redirected to Korapay checkout
6. Complete payment using test cards
7. After payment, you'll be redirected back to success/failed page

### Webhook Testing:
- Configure webhook URL in Korapay dashboard
- Webhook URL: `https://your-domain.com/payment/webhook`
- Check Laravel logs for webhook payloads: `storage/logs/laravel.log`

---

## 📝 Important Notes

1. **Merchant Fees:** Currently set to `merchant_bears_cost: false` (customer pays fees)
   - Change in `PaymentController@initialize` if merchant should bear costs

2. **Currency:** Hardcoded to NGN
   - Can be made dynamic if multi-currency support is needed

3. **Doctor Email Notifications:** Currently disabled
   - Uncomment lines 73-75 in `ConsultationController.php` to enable

4. **Payment Required Logic:**
   - Only charges if doctor has a consultation fee > 0
   - If no fee, consultation is booked without payment

5. **Security:**
   - All API calls use server-side secret key
   - CSRF protection on all POST requests
   - Webhook events are logged for audit trail

---

## 🐛 Debugging

### Check Payment Status:
```bash
php artisan tinker
Payment::where('reference', 'DOT-XXX')->first();
```

### View Webhook Logs:
```bash
tail -f storage/logs/laravel.log
```

### Test API Directly:
```bash
curl -X POST https://your-domain.com/payment/initialize \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -d '{
    "amount": 3000,
    "customer_email": "test@example.com",
    "customer_name": "Test User",
    "doctor_id": 1
  }'
```

---

## 🚀 Going Live

1. Ensure `.env` has production credentials
2. Set webhook URL in Korapay dashboard
3. Test full payment flow with real amounts
4. Monitor `storage/logs/laravel.log` for any errors
5. Set up email notifications for failed payments

---

## 📞 Support

- **Korapay Documentation:** https://developers.korapay.com/
- **Korapay Support:** support@korapay.com
- **DoctorOnTap Admin:** {{ env('ADMIN_EMAIL') }}

---

**Integration Date:** October 9, 2025  
**Status:** ✅ Complete and Ready for Testing

