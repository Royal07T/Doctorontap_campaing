# Manual Payment Recording Feature

## Overview
Admins can now manually mark consultations as paid when patients pay through offline methods like bank transfer, cash, POS, USSD, mobile money, or cheque.

---

## How to Use

### Step 1: Navigate to Consultation
1. Go to **Admin Dashboard** → **Consultations**
2. Click **View Details** on the consultation you want to update

### Step 2: Mark Payment as Paid
1. In the **Payment Status** section (right sidebar)
2. Look for the green button: **"Mark as Paid (Manual Payment)"**
3. Click the button to open the payment modal

### Step 3: Fill Payment Details
The modal will show:
- **Patient Information** (name, email, consultation fee)
- **Payment Method** dropdown (required)
  - Bank Transfer
  - Cash
  - POS
  - USSD
  - Mobile Money
  - Cheque
  - Other
- **Payment Reference** (optional)
  - Transaction ID, receipt number, or any reference
- **Admin Notes** (optional)
  - Internal notes about the payment

### Step 4: Confirm
1. Review the payment details
2. Click **"Confirm & Mark as Paid"**
3. System will:
   - ✅ Create payment record
   - ✅ Mark consultation as PAID
   - ✅ Unlock treatment plan (if exists)
   - ✅ Send treatment plan email to patient
   - ✅ Log the action with your admin name

---

## When to Use This Feature

### Use Cases:
1. **Bank Transfer**: Patient transferred money directly to your account
2. **Cash Payment**: Patient paid cash at your office
3. **POS Payment**: Patient paid via card POS machine
4. **USSD Payment**: Patient used *737# or similar USSD code
5. **Mobile Money**: Patient paid via mobile money (e.g., M-Pesa)
6. **Cheque**: Patient paid by cheque
7. **Any Offline Method**: Any payment method outside online Korapay

### When NOT to Use:
- ❌ When patient pays online via Korapay (automatic)
- ❌ When payment is still pending
- ❌ When you're not sure payment was received

---

## What Happens After Marking as Paid

### Automatic Actions:
1. **Payment Record Created**
   - Reference: `MANUAL-[consultation_reference]`
   - Status: `success`
   - Metadata includes:
     - Payment method
     - Payment reference  
     - Admin notes
     - Admin name who marked it
     - Timestamp

2. **Consultation Updated**
   - `payment_status` → `paid`
   - `payment_id` → linked to payment record

3. **Treatment Plan Unlocked** (if exists)
   - `treatment_plan_unlocked` → `true`
   - `treatment_plan_accessible` → `true`
   - `treatment_plan_unlocked_at` → current timestamp

4. **Email Notification Sent**
   - Patient receives treatment plan email
   - PDF attachment included
   - Access link provided

5. **Action Logged**
   - Admin name recorded
   - Timestamp recorded
   - All details saved for audit trail

---

## UI Location

### In Consultation Details Page:
**Location:** Right sidebar → Payment Status section

**Button Appearance:**
- Green background
- Check mark icon
- Text: "Mark as Paid (Manual Payment)"
- Subtext: "For bank transfer, cash, POS, or other offline payments"

**Modal Appearance:**
- Green header: "Mark Payment as Paid"
- Clean form with dropdowns and text fields
- Warning box explaining what will happen
- Cancel and Confirm buttons

---

## Payment Methods Available

| Method | When to Use |
|--------|-------------|
| **Bank Transfer** | Patient transferred to your bank account |
| **Cash** | Patient paid cash in person |
| **POS** | Card payment via POS terminal |
| **USSD** | Payment via *737# or similar codes |
| **Mobile Money** | M-Pesa, Paga, or similar services |
| **Cheque** | Patient paid by cheque |
| **Other** | Any other offline method |

---

## Security & Audit Trail

### What Gets Logged:
```json
{
  "manual_payment": true,
  "consultation_id": 123,
  "consultation_reference": "CONSULT-123456",
  "payment_method": "Bank Transfer",
  "payment_reference": "TXN789012",
  "admin_notes": "Patient confirmed payment via phone",
  "marked_by_admin": "Admin Name",
  "marked_at": "2024-11-25 10:30:00"
}
```

### Access Control:
- ✅ Only admins can mark payments as paid
- ✅ Regular users cannot access this feature
- ✅ All actions logged with admin name
- ✅ Cannot mark already-paid consultations again

---

## Examples

### Example 1: Bank Transfer
```
Payment Method: Bank Transfer
Payment Reference: TXN-2024112501
Admin Notes: Patient transferred ₦5,000 on Nov 25, 2024. 
             Confirmed via bank statement screenshot on WhatsApp.
```

### Example 2: Cash Payment
```
Payment Method: Cash
Payment Reference: CASH-001
Admin Notes: Patient paid ₦5,000 cash at office on Nov 25, 2024.
             Receipt #001 issued to patient.
```

### Example 3: POS Payment
```
Payment Method: POS
Payment Reference: POS-REF-789012
Admin Notes: Patient paid via debit card on Nov 25, 2024.
             POS terminal confirmation received.
```

---

## Troubleshooting

### Issue: Button Not Showing
**Cause:** Consultation already marked as paid OR status not "completed"

**Solution:**
- Check if payment status is already "paid"
- Ensure consultation status is "completed"

### Issue: Modal Not Opening
**Cause:** JavaScript error or Alpine.js not loaded

**Solution:**
- Refresh the page
- Check browser console for errors
- Contact developer if issue persists

### Issue: "Already marked as paid" Error
**Cause:** Consultation payment_status is already "paid"

**Solution:**
- Check payment information section
- This is expected behavior to prevent duplicate payments
- If payment was marked incorrectly, contact developer to reverse

---

## API Endpoint

**Route:** `POST /admin/consultation/{id}/mark-payment-paid`

**Parameters:**
- `payment_method` (required) - string
- `payment_reference` (optional) - string
- `admin_notes` (optional) - string

**Response:**
```json
{
  "success": true,
  "message": "Payment marked as paid successfully! Treatment plan has been unlocked and sent to patient."
}
```

---

## Files Modified

### Backend:
- `app/Http/Controllers/Admin/DashboardController.php`
  - Added `markPaymentAsPaid()` method

- `routes/web.php`
  - Added `/admin/consultation/{id}/mark-payment-paid` route

### Frontend:
- `resources/views/admin/consultation-details.blade.php`
  - Added manual payment button
  - Added payment modal HTML
  - Added Alpine.js variables and functions

---

## Testing Checklist

After deployment, test:

- [ ] Button appears for unpaid, completed consultations
- [ ] Button does NOT appear for already-paid consultations
- [ ] Modal opens when button is clicked
- [ ] All payment methods available in dropdown
- [ ] Form validation works (payment method required)
- [ ] Payment gets marked successfully
- [ ] Treatment plan unlocks (if exists)
- [ ] Patient receives treatment plan email
- [ ] Payment record created in database
- [ ] Action logged with admin name
- [ ] Cannot mark same consultation as paid twice
- [ ] Page refreshes after successful marking

---

## Deployment

**Commit:** `e738995`  
**Branch:** `livewire`  
**Status:** ✅ Pushed to GitHub

### Deploy to Production:
```bash
cd /home/doctoron/domains/new.doctorontap.com.ng/laravel
git pull origin livewire
php artisan view:clear
php artisan route:clear
php artisan optimize
sudo systemctl restart php8.3-fpm
```

---

## Support

For issues or questions:
1. Check this documentation first
2. Review browser console for JavaScript errors  
3. Check Laravel logs: `storage/logs/laravel.log`
4. Contact the development team

---

**Created:** 2024-11-24  
**Feature:** Manual Payment Recording  
**Version:** 1.0  
**Status:** Production Ready

