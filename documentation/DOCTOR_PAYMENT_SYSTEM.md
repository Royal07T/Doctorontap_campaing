# Doctor Payment System - Complete Guide

## Overview

The DoctorOnTap platform uses a **revenue-sharing model** where doctors receive a percentage of consultation fees after patients pay. Payments are processed through **KoraPay** for automated bank transfers.

---

## 💰 Payment Structure

### Revenue Split

- **Default Split**: 70% to Doctor, 30% to Platform
- **Configurable**: Admin can adjust `doctor_payment_percentage` in settings
- **Calculation**:
  ```
  Total Consultation Amount = Sum of all consultation fees
  Doctor Amount = (Total Amount × Doctor Percentage) / 100
  Platform Fee = (Total Amount × Platform Percentage) / 100
  ```

### Example

If a doctor completes 5 consultations at ₦5,000 each:
- **Total Amount**: ₦25,000
- **Doctor Share (70%)**: ₦17,500
- **Platform Fee (30%)**: ₦7,500

---

## 🔄 Payment Flow

### Step 1: Consultation Completion & Payment

1. **Patient pays** for consultation (via KoraPay)
2. **Consultation status** changes to `completed`
3. **Payment status** changes to `paid`
4. Consultation becomes **eligible** for doctor payment

### Step 2: Admin Creates Payment Record

**Requirements:**
- ✅ Consultations must be `completed`
- ✅ Consultations must have `payment_status = 'paid'`
- ✅ Doctor must have a **verified bank account**
- ✅ All consultations must belong to the same doctor

**Process:**
1. Admin selects doctor
2. Admin selects paid consultations to include
3. System calculates:
   - Total consultation amount
   - Doctor percentage (from settings or custom)
   - Doctor amount
   - Platform fee
4. Payment record created with status `pending`

**Code Location**: `app/Http/Controllers/Admin/DashboardController::createDoctorPayment()`

### Step 3: Payout Initiation

**Two Methods:**

#### Method A: Automated KoraPay Payout (Recommended)

1. Admin clicks "Initiate Payout" for a payment
2. System validates:
   - Doctor has verified bank account
   - Bank account has bank code
   - Payment is in `pending` status
3. **KoraPay API Call**:
   - Verifies bank account
   - Initiates bank transfer
   - Returns transaction reference
4. Payment status updated:
   - `status` → `processing`
   - `korapay_reference` → stored
   - `payout_initiated_at` → timestamp

**Code Location**: `app/Services/KoraPayPayoutService::initiatePayout()`

#### Method B: Manual Completion

1. Admin processes payment manually (cash, manual transfer, etc.)
2. Admin marks payment as `completed`
3. Admin enters:
   - Payment method
   - Transaction reference
   - Notes

**Code Location**: `app/Http/Controllers/Admin/DashboardController::completeDoctorPayment()`

### Step 4: Payment Verification

1. Admin can verify KoraPay payout status
2. System checks KoraPay API for transaction status
3. If successful:
   - `status` → `completed`
   - `korapay_status` → `success`
   - `payout_completed_at` → timestamp

**Code Location**: `app/Http/Controllers/Admin/DashboardController::verifyPayoutStatus()`

---

## 📊 Database Structure

### `doctor_payments` Table

**Key Fields:**
- `reference` - Unique payment reference (e.g., "DOCPAY-ABC123")
- `doctor_id` - Doctor receiving payment
- `bank_account_id` - Bank account for payout
- `total_consultations_amount` - Sum of all consultation fees
- `total_consultations_count` - Number of consultations
- `paid_consultations_count` - Paid consultations included
- `unpaid_consultations_count` - Unpaid consultations (excluded)
- `doctor_percentage` - Doctor's share percentage (default: 70)
- `platform_percentage` - Platform's share percentage (default: 30)
- `doctor_amount` - Amount to pay doctor
- `platform_fee` - Platform fee
- `status` - `pending`, `processing`, `completed`, `failed`, `cancelled`
- `consultation_ids` - JSON array of consultation IDs included
- `period_from` / `period_to` - Optional date range
- `korapay_reference` - KoraPay transaction reference
- `korapay_status` - KoraPay transaction status
- `payout_initiated_at` - When payout was initiated
- `payout_completed_at` - When payout was completed

**Model**: `app/Models/DoctorPayment.php`

---

## 🏦 Bank Account Requirements

### Doctor Bank Account Setup

1. **Doctor adds bank account**:
   - Account name
   - Account number
   - Bank name
   - Bank code (required for KoraPay)

2. **Admin verifies bank account**:
   - Sets `is_verified = true`
   - Marks as default if needed

3. **System uses**:
   - Default bank account (if set)
   - Or first verified bank account

**Model**: `app/Models/DoctorBankAccount.php`

---

## 🔧 Key Features

### 1. Bulk Payouts

- Process multiple payments at once
- Uses KoraPay bulk payout API
- Reduces API calls and processing time

**Code Location**: `app/Services/KoraPayPayoutService::processBulkPayouts()`

### 2. Payment Calculation

```php
DoctorPayment::calculatePayment($consultations, $doctorPercentage = 70)
```

**Returns:**
- Total consultations amount
- Doctor amount (70% by default)
- Platform fee (30% by default)
- Counts of paid/unpaid consultations

### 3. Payment Status Tracking

- **Pending**: Payment created, awaiting payout
- **Processing**: Payout initiated, awaiting completion
- **Completed**: Payment successfully paid
- **Failed**: Payout failed (can be retried)
- **Cancelled**: Payment cancelled

### 4. Retry Failed Payments

- Failed payments can be retried
- System resets status to `pending`
- Allows re-initiating payout

---

## 📝 Admin Workflow

### Creating a Payment

1. Go to **Admin → Doctor Payments**
2. Select doctor
3. View unpaid consultations
4. Select consultations to include
5. Set payment period (optional)
6. Set custom doctor percentage (optional)
7. Click "Create Payment"
8. Payment record created

### Processing Payout

**Option A: KoraPay Automated**
1. Click "Initiate Payout" on payment
2. System processes via KoraPay
3. Payment status → `processing`
4. Verify status later

**Option B: Manual**
1. Process payment manually
2. Click "Mark as Completed"
3. Enter payment details
4. Payment status → `completed`

### Verifying Payout

1. Click "Verify Status" on payment
2. System checks KoraPay API
3. Updates payment status if completed

---

## 🔐 Security & Validation

### Payment Creation Validation

- ✅ All consultations belong to selected doctor
- ✅ All consultations are `completed`
- ✅ All consultations have `payment_status = 'paid'`
- ✅ Doctor has verified bank account
- ✅ No duplicate consultations in multiple payments

### Payout Validation

- ✅ Doctor has verified bank account
- ✅ Bank account has bank code
- ✅ Payment is in valid status
- ✅ Bank account verification passes

---

## 📈 Doctor Dashboard

Doctors can view:
- **Total Earnings**: Sum of all completed payments
- **Pending Payments**: Payments awaiting payout
- **Payment History**: All past payments
- **Unpaid Consultations**: Completed consultations not yet in a payment

**Code Location**: `app/Http/Controllers/Doctor/DashboardController::index()`

---

## 🛠️ Configuration

### Settings

- `doctor_payment_percentage` - Default doctor share (default: 70)
- `default_consultation_fee` - Default consultation fee
- `consultation_fee_pay_later` - Fee for pay-later consultations
- `consultation_fee_pay_now` - Fee for pay-now consultations

**Model**: `app/Models/Setting.php`

---

## 🔄 KoraPay Integration

### Payout Service

**Service**: `app/Services/KoraPayPayoutService.php`

**Key Methods:**
- `initiatePayout()` - Single payout
- `processBulkPayouts()` - Multiple payouts
- `verifyPayoutStatus()` - Check transaction status
- `verifyBankAccount()` - Verify bank account before payout

### API Endpoints Used

- `POST /transactions/disburse` - Single payout
- `POST /transactions/disburse/bulk` - Bulk payout
- `GET /transactions/{reference}` - Verify status
- `POST /merchants/api/v1/accounts/resolve` - Verify bank account

---

## 📊 Reports & Analytics

### Admin Dashboard Shows:
- Total payments created
- Pending payments count
- Completed payments count
- Total amount paid to doctors
- Total platform fees collected

**Code Location**: `app/Http/Controllers/Admin/DashboardController::doctorPayments()`

---

## ⚠️ Important Notes

1. **Only Paid Consultations**: Doctors only get paid for consultations where patients have paid
2. **Bank Account Required**: Doctor must have verified bank account before payout
3. **Bank Code Required**: Bank code is required for KoraPay payouts
4. **No Duplicate Payments**: Same consultation cannot be in multiple payments
5. **Percentage Configurable**: Admin can set custom percentage per payment
6. **Retry Failed Payments**: Failed payouts can be retried

---

## 🔍 Code References

- **Model**: `app/Models/DoctorPayment.php`
- **Controller**: `app/Http/Controllers/Admin/DashboardController.php`
- **Service**: `app/Services/KoraPayPayoutService.php`
- **Migration**: `database/migrations/2025_12_13_023812_create_doctor_payments_table.php`
- **Views**: `resources/views/admin/doctor-payments.blade.php`

---

## 📞 Support

For issues with doctor payments:
1. Check payment status in admin panel
2. Verify doctor's bank account is verified
3. Check KoraPay transaction status
4. Review logs: `storage/logs/laravel.log`

