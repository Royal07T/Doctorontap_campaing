# Admin Payment Request Guide

## Overview
Admins can send payment request emails to patients after consultations are completed. This feature allows for the "Pay After Consultation" model where patients only pay once their medical consultation has been successfully completed.

## Feature Summary

### Key Capabilities
- ✅ Send payment request emails from admin dashboard
- ✅ Payment requests sent ONLY after consultation is completed
- ✅ Prevents duplicate requests (tracks when sent)
- ✅ Beautiful email with DoctorOnTap branding and logo
- ✅ Includes payment button linking to secure checkout
- ✅ Resend capability if needed

## How to Send Payment Request

### Step-by-Step Process

#### 1. Navigate to Consultation Details
1. Log into admin dashboard at `/admin/login`
2. Click on **"Consultations"** in the navigation
3. Find the completed consultation
4. Click on the consultation to view details

#### 2. Mark Consultation as Completed
Before sending payment request:
1. Locate the **"Update Status"** section
2. Select **"Completed"** from the dropdown
3. System will record completion timestamp

#### 3. Send Payment Request
1. Scroll to the **"Payment Status"** section
2. You'll see the current payment status (Unpaid/Pending/Paid)
3. Click the **"Send Payment Request"** button (purple button with email icon)
4. Confirm the action in the popup dialog
5. Email will be sent immediately
6. Page refreshes to show "Payment Request Sent" status

### Button States

#### Normal State
```
[📧 Send Payment Request]
```
- Purple gradient background
- Email icon on left
- Clear call-to-action

#### Loading State
```
[⏳ Sending...]
```
- Spinning loader icon
- Button disabled during send
- Prevents duplicate clicks

#### After Sent
Shows confirmation:
```
✓ Payment Request Sent
   Oct 10, 2025 2:30 PM
```

## When Payment Request Appears

### Requirements
Payment request button appears when:
- ✅ Consultation status = **"Completed"**
- ✅ Payment status ≠ **"Paid"**
- ✅ Doctor has consultation fee > 0

### Hidden When
Button doesn't appear when:
- ❌ Consultation not yet completed
- ❌ Payment already made
- ❌ No consultation fee required

### Helpful Note
If consultation isn't completed, you'll see:
```
💡 Note: Payment request will be available once the 
   consultation status is marked as "Completed"
```

## Payment Request Email

### What Patient Receives

#### Email Subject
```
Payment Request - DoctorOnTap Consultation
```

#### Email Content
- **DoctorOnTap Logo** at top
- Personalized greeting with patient name
- Confirmation of completed consultation
- Doctor name and consultation details
- **Payment Amount** prominently displayed (large font)
- **"Pay Now"** button (links to secure checkout)
- Payment instructions
- Support contact information

#### Email Features
- Professional purple gradient header
- Clear payment amount in NGN
- One-click payment button
- Mobile-responsive design
- Secure payment link

### Sample Email Preview
```
┌─────────────────────────────────────┐
│  [DoctorOnTap Logo - White]         │
│  💰 Payment Request                  │
│  Thank you for consulting with us!  │
└─────────────────────────────────────┘

Hi John! 👋

Thank you for choosing DoctorOnTap. Your 
consultation with Dr. Sarah Johnson has been 
completed successfully.

Your Consultation Fee:
   NGN 5,000.00

[Pay Now →]

Payment is quick and secure through our 
payment processor.
```

## Resending Payment Requests

### When to Resend
Resend payment request if:
- Patient didn't receive original email
- Email went to spam folder
- Patient requests another reminder
- Payment link expired

### How to Resend
1. Navigate to consultation details
2. Button will show **"Resend Payment Request"**
3. Click to send another email
4. Timestamp updates to latest send time

### Best Practices
- ⏰ Wait 24-48 hours before resending
- 📞 Contact patient first (via WhatsApp/phone)
- 📧 Check patient's email address is correct
- 💬 Inform patient to check spam folder

## Payment Tracking

### Payment Status Types

#### Unpaid (Red)
```
🔴 Unpaid
```
- Default status after booking
- Patient hasn't paid yet
- Payment request can be sent

#### Pending (Yellow)
```
🟡 Pending
```
- Payment initiated but not confirmed
- Awaiting payment processor confirmation
- Monitor for completion

#### Paid (Green)
```
🟢 Paid
```
- Payment completed successfully
- No further action needed
- Payment request button hidden

### Viewing Payment Details
When payment is made, you'll see:
- Transaction ID
- Payment amount
- Payment date/time
- Payment method (if captured)

## Admin Dashboard Navigation

### Quick Access
```
Dashboard → Consultations → [Select Consultation]
```

### Payment Status Section Location
1. Open consultation details
2. Scroll past patient/doctor/medical info
3. Find **"Payment Status"** card
4. Located near bottom of page

## Workflow Example

### Complete Payment Request Flow

**Day 1 - Consultation Booked**
1. Patient books consultation (Pay After model)
2. Payment status: **Unpaid**
3. Payment request button: **Hidden** (not completed yet)

**Day 2 - Consultation Conducted**
1. Doctor completes consultation via call/video
2. Admin marks status as **"Completed"**
3. Payment request button: **Now Visible**

**Day 2 - Payment Request Sent**
1. Admin clicks **"Send Payment Request"**
2. Email sent to patient@email.com
3. Status shows: "Payment Request Sent"
4. Button changes to **"Resend Payment Request"**

**Day 2 - Patient Pays**
1. Patient clicks **"Pay Now"** in email
2. Completes payment via Korapay
3. System updates: Payment status = **Paid**
4. Payment request button: **Hidden** (payment complete)

## Troubleshooting

### Email Not Sending

**Problem:** Click "Send" but no email sent

**Solutions:**
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify email configuration in `.env`:
   ```
   MAIL_MAILER=smtp
   MAIL_HOST=your-smtp-host
   MAIL_PORT=587
   MAIL_USERNAME=your-email
   MAIL_PASSWORD=your-password
   ```
3. Test email config: `php artisan tinker` → `Mail::raw('Test', ...)`

### Button Not Appearing

**Problem:** Can't see payment request button

**Check:**
1. Is consultation marked as **"Completed"**?
2. Is payment status not **"Paid"**?
3. Does doctor have a consultation fee set?
4. Refresh the page

### Patient Didn't Receive Email

**Solutions:**
1. Verify patient email address is correct
2. Ask patient to check spam/junk folder
3. Resend payment request
4. Check email server logs
5. Use alternative contact method (WhatsApp)

### Payment Link Not Working

**Problem:** Patient clicks "Pay Now" but link broken

**Check:**
1. `APP_URL` set correctly in `.env`
2. Payment reference is valid
3. Korapay integration configured
4. Check payment webhook logs

## API Endpoint Details

### Send Payment Request
**Endpoint:** `POST /admin/consultation/{id}/send-payment`

**Authentication:** Admin login required

**Headers:**
```
X-CSRF-TOKEN: [token]
```

**Response Success:**
```json
{
  "success": true,
  "message": "Payment request email sent successfully to patient@email.com"
}
```

**Response Errors:**
```json
// Consultation not completed
{
  "success": false,
  "message": "Consultation must be completed before sending payment request"
}

// Already sent
{
  "success": false,
  "message": "Payment request already sent on Oct 10, 2025 14:30"
}

// No payment required
{
  "success": false,
  "message": "This consultation does not require payment (no fee set)"
}
```

## Database Fields

### Consultation Table
- `payment_status` - Current payment status (unpaid/pending/paid)
- `payment_request_sent` - Boolean flag (true/false)
- `payment_request_sent_at` - Timestamp of when request was sent
- `payment_id` - Links to payment record (when paid)

## Best Practices

### Timing
- ⏰ Send payment request within 24 hours of completion
- 📞 Inform patient about consultation outcome first
- 💬 Use WhatsApp to notify about incoming email
- 🔄 Follow up if no payment after 48 hours

### Communication
- 📧 Professional email language
- 💰 Clear payment amount display
- 🔒 Emphasize secure payment process
- 📞 Provide support contact info

### Admin Workflow
- ✅ Mark consultation as completed promptly
- ✅ Verify doctor fee is correctly set
- ✅ Double-check patient email address
- ✅ Monitor payment status regularly
- ✅ Follow up on unpaid consultations

## Security Features

- 🔒 Admin authentication required
- 🔒 CSRF protection on requests
- 🔒 Unique payment references
- 🔒 Secure payment processor (Korapay)
- 🔒 Prevents duplicate request sending
- 🔒 Encrypted payment links

## Related Documentation

- [ADMIN_DASHBOARD.md](ADMIN_DASHBOARD.md) - Dashboard overview
- [KORAPAY_INTEGRATION.md](KORAPAY_INTEGRATION.md) - Payment processor setup
- [PAY_AFTER_CAMPAIGN.md](PAY_AFTER_CAMPAIGN.md) - Campaign details
- [EMAIL_BRANDING_UPDATE.md](EMAIL_BRANDING_UPDATE.md) - Email logo branding

---

**Feature Status:** ✅ Active and Working  
**Last Updated:** October 10, 2025  
**Admin Access:** `/admin/login`

