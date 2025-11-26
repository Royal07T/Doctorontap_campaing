# 🔔 Korapay Webhook Implementation - Complete Payment Status Tracking

## 📋 Overview

The Korapay webhook has been **enhanced** to properly handle **ALL payment statuses** - not just successful payments. The system now tracks and responds to every payment event from Korapay.

---

## ✅ What's Been Implemented

### 1. **Complete Event Handling**

The webhook now handles ALL Korapay payment events:

| Event | Status | Action Taken |
|-------|--------|--------------|
| ✅ `charge.success` | Success | ✓ Update payment to "success"<br>✓ Mark consultation as "paid"<br>✓ Unlock treatment plan<br>✓ Send treatment plan email |
| ❌ `charge.failed` | Failed | ✓ Update payment to "failed"<br>✓ Mark consultation as "failed"<br>✓ Send failure notification email |
| ⏳ `charge.pending` | Pending | ✓ Update payment to "pending"<br>✓ Mark consultation as "pending"<br>✓ Log status for tracking |
| ⏳ `charge.processing` | Processing | ✓ Update payment to "pending"<br>✓ Keep consultation status updated |
| 🚫 `charge.cancelled` | Cancelled | ✓ Update payment to "cancelled"<br>✓ Mark consultation as "cancelled"<br>✓ Log cancellation |
| 🚫 `charge.abandoned` | Abandoned | ✓ Same as cancelled<br>✓ Track abandoned payments |

### 2. **Comprehensive Logging**

Every webhook event is now logged with:
- ✅ Event type
- ✅ Payment reference
- ✅ Timestamp
- ✅ Payment status changes
- ✅ Consultation updates
- ✅ Email notifications sent
- ✅ Any errors encountered

### 3. **Payment Status Updates**

The `payments` table now supports these statuses:
- `pending` - Payment initiated but not completed
- `success` - Payment completed successfully
- `failed` - Payment failed
- `cancelled` - User cancelled payment
- `processing` - Payment being processed

### 4. **Consultation Status Synchronization**

Consultations are automatically updated when payment status changes:
- ✅ **Success** → Consultation marked as "paid" + treatment plan unlocked
- ❌ **Failed** → Consultation marked as "failed"
- ⏳ **Pending** → Consultation remains "pending"
- 🚫 **Cancelled** → Consultation marked as "cancelled"

### 5. **Email Notifications**

#### Success Notifications (existing):
- ✉️ Treatment plan notification email

#### **NEW - Failure Notifications:**
- ✉️ Payment failed notification
- Includes failure reason
- Provides retry payment link
- Offers support contact information

---

## 🔐 Security Features

### 1. **Webhook Signature Verification**

All webhooks are verified using HMAC SHA256:

```php
$signature = $request->header('x-korapay-signature');
$expectedSignature = hash_hmac('sha256', json_encode($data), $secretKey);

if (!hash_equals($expectedSignature, $signature)) {
    // Reject unauthorized webhook
    return response()->json(['status' => 'invalid_signature'], 401);
}
```

### 2. **Middleware Protection**

The webhook endpoint is protected by `VerifyKorapayWebhook` middleware:

```php
Route::post('/webhook', [PaymentController::class, 'webhook'])
    ->middleware('verify.korapay.webhook')
    ->name('payment.webhook');
```

---

## 📁 Files Changed

### 1. **PaymentController.php** (Updated)
- Enhanced `webhook()` method to handle all payment events
- Added comprehensive logging for each event type
- Implemented status-specific actions

**Location:** `/app/Http/Controllers/PaymentController.php`

### 2. **PaymentFailedNotification.php** (New)
- New Mailable class for payment failure notifications
- Includes consultation details, payment reference, and failure reason

**Location:** `/app/Mail/PaymentFailedNotification.php`

### 3. **payment-failed.blade.php** (New)
- Beautiful email template for payment failures
- Includes retry payment button
- Shows failure reason and support contact info

**Location:** `/resources/views/emails/payment-failed.blade.php`

### 4. **create_payments_table.php** (Updated)
- Updated comment to reflect all supported statuses

**Location:** `/database/migrations/2025_10_09_121506_create_payments_table.php`

---

## 🧪 Testing the Webhook

### Test Payment Events

You can test the webhook locally or in production by simulating Korapay events.

#### 1. **Test Success Payment**

```bash
curl -X POST https://yourdomain.com/payment/webhook \
  -H "Content-Type: application/json" \
  -H "x-korapay-signature: YOUR_SIGNATURE" \
  -d '{
    "event": "charge.success",
    "data": {
      "reference": "DOT-1234567890-ABC123XYZ",
      "amount": 5000,
      "status": "success",
      "payment_method": "card",
      "payment_reference": "KPY-REF-123456"
    }
  }'
```

#### 2. **Test Failed Payment**

```bash
curl -X POST https://yourdomain.com/payment/webhook \
  -H "Content-Type: application/json" \
  -H "x-korapay-signature: YOUR_SIGNATURE" \
  -d '{
    "event": "charge.failed",
    "data": {
      "reference": "DOT-1234567890-ABC123XYZ",
      "amount": 5000,
      "status": "failed",
      "failure_message": "Insufficient funds"
    }
  }'
```

#### 3. **Test Pending Payment**

```bash
curl -X POST https://yourdomain.com/payment/webhook \
  -H "Content-Type: application/json" \
  -H "x-korapay-signature: YOUR_SIGNATURE" \
  -d '{
    "event": "charge.pending",
    "data": {
      "reference": "DOT-1234567890-ABC123XYZ",
      "amount": 5000,
      "status": "pending",
      "payment_method": "bank_transfer"
    }
  }'
```

### Generate Webhook Signature

To generate a valid signature for testing:

```php
$data = ['reference' => 'DOT-1234567890-ABC123XYZ', 'amount' => 5000];
$secretKey = config('services.korapay.secret_key');
$signature = hash_hmac('sha256', json_encode($data), $secretKey);
echo $signature;
```

---

## 📊 Monitoring Webhooks

### View Webhook Logs

All webhook events are logged to Laravel's log file:

```bash
# View real-time webhook logs
tail -f storage/logs/laravel.log | grep -i "webhook"

# View Korapay-specific logs
tail -f storage/logs/laravel.log | grep -i "korapay"
```

### Key Log Entries to Watch For:

✅ **Success:**
```
✅ Processing SUCCESSFUL charge
✅ TREATMENT PLAN UNLOCKED SUCCESSFULLY
Treatment plan notification email sent
✅ Webhook processing completed successfully
```

❌ **Failure:**
```
❌ Processing FAILED charge
Payment record updated to FAILED
Consultation payment status updated to FAILED
Payment failure notification sent
❌ Failed payment webhook processed
```

⏳ **Pending:**
```
⏳ Processing PENDING/PROCESSING charge
Payment record updated to PENDING
Consultation payment status updated to PENDING
⏳ Pending payment webhook processed
```

🚫 **Cancelled:**
```
🚫 Processing CANCELLED/ABANDONED charge
Payment record updated to CANCELLED
Consultation payment status updated to CANCELLED
🚫 Cancelled payment webhook processed
```

---

## 🔧 Configuration

### Webhook URL Configuration

Make sure your webhook URL is configured in **Korapay Dashboard**:

**URL:** `https://yourdomain.com/payment/webhook`

### Environment Variables

Ensure these are set in `.env`:

```env
KORAPAY_SECRET_KEY=sk_live_YOUR_SECRET_KEY
KORAPAY_PUBLIC_KEY=pk_live_YOUR_PUBLIC_KEY
KORAPAY_ENCRYPTION_KEY=YOUR_ENCRYPTION_KEY
KORAPAY_API_URL=https://api.korapay.com/merchant/api/v1
KORAPAY_ENFORCE_WEBHOOK_SIGNATURE=true
```

---

## 🎯 Benefits

### For Users:
✅ **Transparent** - Users are immediately notified of payment status
✅ **Helpful** - Clear reasons for failures with easy retry options
✅ **Reliable** - All payment events are tracked and handled

### For Business:
✅ **Complete Tracking** - Know exactly what happened with every payment
✅ **Better Support** - Detailed logs help troubleshoot payment issues
✅ **Reduced Confusion** - Users get appropriate messages for each status
✅ **Improved Conversion** - Easy retry for failed payments

### For Developers:
✅ **Comprehensive Logging** - Easy debugging with detailed logs
✅ **Clean Code** - Well-structured event handling
✅ **Extensible** - Easy to add new event types
✅ **Secure** - Signature verification prevents unauthorized access

---

## 🚀 What Happens Now

### Successful Payment Flow:
1. User completes payment on Korapay → ✅
2. Korapay sends `charge.success` webhook → ✅
3. System updates payment status to "success" → ✅
4. Consultation marked as "paid" → ✅
5. Treatment plan unlocked → ✅
6. Treatment plan email sent to user → ✅

### Failed Payment Flow:
1. Payment fails on Korapay → ❌
2. Korapay sends `charge.failed` webhook → ❌
3. System updates payment status to "failed" → ❌
4. Consultation marked as "failed" → ❌
5. **NEW:** Failure notification email sent to user → ✉️
6. **NEW:** Email includes retry link and support info → 🔄

### Pending Payment Flow:
1. Payment initiated but not completed → ⏳
2. Korapay sends `charge.pending` webhook → ⏳
3. System updates payment status to "pending" → ⏳
4. Consultation remains "pending" → ⏳
5. System waits for final status update → ⏳

### Cancelled Payment Flow:
1. User cancels payment → 🚫
2. Korapay sends `charge.cancelled` webhook → 🚫
3. System updates payment status to "cancelled" → 🚫
4. Consultation marked as "cancelled" → 🚫
5. Payment can be retried if needed → 🔄

---

## 📝 Next Steps

### For Production Deployment:

1. **Verify Webhook URL** in Korapay Dashboard
   - URL: `https://new.doctorontap.com.ng/payment/webhook`
   - Method: POST
   - Authentication: Signature verification

2. **Test All Scenarios**
   - Use Korapay test cards to simulate different outcomes
   - Verify logs are being written correctly
   - Confirm emails are being sent

3. **Monitor Logs**
   - Check `storage/logs/laravel.log` regularly
   - Set up log alerts for failed payments
   - Track webhook response times

4. **Customer Communication**
   - Inform support team about new failure emails
   - Update help documentation
   - Prepare FAQs for common payment issues

---

## 🆘 Troubleshooting

### Webhook Not Triggering?

1. Check webhook URL in Korapay dashboard
2. Verify `KORAPAY_SECRET_KEY` is set correctly
3. Check firewall/security rules allow Korapay IPs
4. Review Laravel logs for errors

### Signature Verification Failing?

1. Ensure secret key matches Korapay dashboard
2. Check middleware is properly registered
3. Verify webhook payload format
4. Review signature generation logic

### Emails Not Sending?

1. Check mail configuration in `.env`
2. Verify email queue is running
3. Check Laravel logs for email errors
4. Ensure email templates exist

---

## 📞 Support

If you encounter any issues:

- **Check Logs:** `storage/logs/laravel.log`
- **Korapay Dashboard:** https://korapay.com/
- **Korapay Support:** support@korapay.com
- **Documentation:** https://developers.korapay.com/

---

**Last Updated:** November 26, 2025
**Version:** 2.0 - Complete Payment Status Tracking

