# Korapay Payout Implementation Guide

## Overview

This document describes the implementation of **Korapay Payout (Disbursement) to Doctors' Bank Accounts** using the official Korapay Payout API.

**Important**: This system works exactly like the existing `admin/doctor-payments` system:
- Calculates **ALL unpaid consultations** for a doctor
- Creates **ONE payout record** with multiple consultations
- Sends **ONE payout** for the total amount

---

## How It Works (Like Admin/Doctor-Payments)

### Step 1: Get Unpaid Consultations

Admin selects a doctor, system shows all unpaid consultations:
- ✅ Status = `completed`
- ✅ Payment status = `paid` (patient has paid)
- ❌ Not yet included in any payout

**Endpoint**: `GET /api/doctor-payouts/doctor/{doctorId}/unpaid-consultations`

### Step 2: Admin Selects Multiple Consultations

Admin selects multiple consultations to include in the payout.

### Step 3: System Calculates Total

System calculates:
- **Total Amount** = Sum of all selected consultation fees
- **Doctor Amount** = Total × 70% (configurable)
- **Platform Fee** = Total × 30%

### Step 4: Create Batch Payout

System creates **ONE payout record** with:
- `consultation_ids` = JSON array of all consultation IDs
- `amount` = Total amount for all consultations
- `status` = `pending`

**Endpoint**: `POST /api/doctor-payouts/create-batch`

### Step 5: Initiate Payout

Admin initiates payout, system sends **ONE payout** to doctor's bank account for the total amount.

---

## Components

### 1. Service Class: `App\Services\KorapayPayoutService`

**Location**: `app/Services/KorapayPayoutService.php`

**Key Methods**:
- `sendPayout(Doctor $doctor, float $amount, string $payoutReference, array $metadata)`: Sends payout (works for single or batch)
- `verifyBankAccount(string $bankCode, string $accountNumber)`: Verifies bank account
- `generatePayoutReference()`: Generates unique reference (DR-PAYOUT-XXXX)

### 2. Model: `App\Models\DoctorPayout`

**Location**: `app/Models/DoctorPayout.php`

**Database Table**: `doctor_payouts`

**Key Fields**:
- `consultation_ids`: JSON array of consultation IDs (like `DoctorPayment`)
- `total_consultations_amount`: Sum of all consultation fees
- `total_consultations_count`: Number of consultations
- `doctor_percentage`: Doctor's share percentage (default: 70%)
- `amount`: Amount to pay doctor (total × doctor_percentage)
- `platform_fee`: Platform fee (total × platform_percentage)
- `status`: pending, processing, success, failed

**Key Methods**:
- `calculatePayment($consultations, $doctorPercentage)`: Calculates payment details (like `DoctorPayment::calculatePayment`)
- `consultations()`: Returns all consultations included in this payout

### 3. Controller: `App\Http\Controllers\DoctorPayoutController`

**Location**: `app/Http/Controllers/DoctorPayoutController.php`

**Endpoints**:

1. **Get Unpaid Consultations**
   ```
   GET /api/doctor-payouts/doctor/{doctorId}/unpaid-consultations
   ```
   Returns all consultations that are completed, paid, but not yet paid to doctor.

2. **Create Batch Payout**
   ```
   POST /api/doctor-payouts/create-batch
   ```
   Creates a payout record for multiple consultations (like `createDoctorPayment`).

3. **Get Payout History**
   ```
   GET /api/doctor-payouts/doctor/{doctorId}/history
   ```
   Returns payout history for a doctor.

4. **Get Payout Details**
   ```
   GET /api/doctor-payouts/{payoutId}
   ```
   Returns payout details with consultations.

5. **Webhook**
   ```
   POST /webhooks/korapay/payout
   ```
   Handles Korapay webhook for payout status updates.

### 4. Webhook Handler

**Location**: `app/Http/Controllers/DoctorPayoutController::webhook()`

**Purpose**: Updates payout status when Korapay sends webhook notification.

---

## Database Migration

**File**: `database/migrations/2026_01_09_052114_create_doctor_payouts_table.php`

Run migration:
```bash
php artisan migrate
```

**Note**: The migration includes `consultation_ids` JSON field (like `doctor_payments` table).

---

## Usage Examples

### 1. Get Unpaid Consultations

```php
// GET /api/doctor-payouts/doctor/1/unpaid-consultations
$response = [
    'success' => true,
    'consultations' => [
        [
            'id' => 1,
            'reference' => 'CONS-001',
            'patient_name' => 'John Doe',
            'date' => '2026-01-09',
            'amount' => 5000
        ],
        // ... more consultations
    ],
    'total_count' => 5,
    'total_amount' => 25000
];
```

### 2. Create Batch Payout

```php
// POST /api/doctor-payouts/create-batch
$request = [
    'doctor_id' => 1,
    'consultation_ids' => [1, 2, 3, 4, 5],
    'doctor_percentage' => 70, // Optional, defaults to setting
    'period_from' => '2026-01-01', // Optional
    'period_to' => '2026-01-31', // Optional
];

// Response
$response = [
    'success' => true,
    'message' => 'Payout initiated successfully',
    'payout' => [
        'id' => 1,
        'payout_reference' => 'DR-PAYOUT-XXXX',
        'consultation_ids' => [1, 2, 3, 4, 5],
        'total_consultations_amount' => 25000,
        'total_consultations_count' => 5,
        'doctor_percentage' => 70,
        'amount' => 17500, // 70% of 25000
        'platform_fee' => 7500, // 30% of 25000
        'status' => 'processing',
    ]
];
```

### 3. Check Payout Status

```php
use App\Models\DoctorPayout;

$payout = DoctorPayout::find(1);

echo "Status: " . $payout->status; // pending, processing, success, failed
echo "Amount: ₦" . number_format($payout->amount, 2);
echo "Consultations: " . $payout->total_consultations_count;
echo "Korapay Reference: " . $payout->korapay_reference;

// Get all consultations in this payout
$consultations = $payout->consultations();
```

---

## Payout Flow

1. **Admin Views Unpaid Consultations**
   - Admin selects doctor
   - System shows all unpaid consultations (completed + paid by patient)

2. **Admin Selects Consultations**
   - Admin selects multiple consultations
   - System validates all are completed and paid

3. **System Calculates Total**
   - Sums all consultation fees
   - Calculates doctor's share (70%)
   - Calculates platform fee (30%)

4. **Create Payout Record**
   - Creates `DoctorPayout` with `consultation_ids` array
   - Status = `pending`

5. **Initiate Payout**
   - Calls `KorapayPayoutService::sendPayout()`
   - Sends **ONE payout** for total amount
   - Updates status to `processing`

6. **Webhook Updates Status**
   - Korapay sends webhook when payout completes
   - Status changes to `success` or `failed`

---

## Comparison with DoctorPayment

| Feature | DoctorPayment | DoctorPayout |
|---------|--------------|--------------|
| Table | `doctor_payments` | `doctor_payouts` |
| Consultation IDs | `consultation_ids` (JSON) | `consultation_ids` (JSON) |
| Calculation | `calculatePayment()` | `calculatePayment()` |
| Payout Method | KoraPay (via `KoraPayPayoutService`) | Korapay (via `KorapayPayoutService`) |
| Webhook | `/payment/payout-webhook` | `/webhooks/korapay/payout` |
| Purpose | Admin-managed batch payments | API-managed batch payouts |

**Note**: Both systems work the same way - calculate all unpaid consultations and create one payout for the total.

---

## Configuration

### Environment Variables

```env
KORAPAY_SECRET_KEY=sk_live_...
KORAPAY_PUBLIC_KEY=pk_live_...
KORAPAY_ENCRYPTION_KEY=...
KORAPAY_API_URL=https://api.korapay.com/merchant/api/v1
```

### Settings

Doctor payment percentage (default: 70%):
```php
\App\Models\Setting::set('doctor_payment_percentage', 70);
```

---

## Security

1. **Webhook Signature Verification**
   - All webhook requests verified using HMAC SHA256
   - Invalid signatures rejected

2. **Duplicate Prevention**
   - Checks for existing payouts before creating new ones
   - Excludes consultations already in pending/processing/success payouts

3. **Bank Account Verification**
   - Bank accounts verified before payout
   - Only verified accounts used

---

## Error Handling

### Validation Errors
- Consultations must be `completed` and `paid`
- Doctor must have verified bank account
- Total amount must be > 0
- Consultations must not already be in a payout

### API Errors
- All API errors logged
- Payout status set to `failed`
- Error details stored in `korapay_response`

---

## Testing

### Test Get Unpaid Consultations
```bash
curl -X GET http://localhost:8000/api/doctor-payouts/doctor/1/unpaid-consultations \
  -H "Content-Type: application/json"
```

### Test Create Batch Payout
```bash
curl -X POST http://localhost:8000/api/doctor-payouts/create-batch \
  -H "Content-Type: application/json" \
  -d '{
    "doctor_id": 1,
    "consultation_ids": [1, 2, 3],
    "doctor_percentage": 70
  }'
```

---

## Next Steps

1. **Run Migration**
   ```bash
   php artisan migrate
   ```

2. **Test Endpoints**
   - Get unpaid consultations
   - Create batch payout
   - Check payout status

3. **Configure Webhook**
   - URL: `https://yourdomain.com/webhooks/korapay/payout`
   - Events: `transfer.success`, `transfer.failed`

4. **Integrate with Admin Panel**
   - Add UI to select consultations
   - Call API endpoints
   - Display payout history

---

## Key Differences from Initial Implementation

1. ✅ **Batch Payouts**: One payout for multiple consultations (not per-consultation)
2. ✅ **consultation_ids Array**: Stores multiple consultation IDs (like `DoctorPayment`)
3. ✅ **Calculation Method**: Uses `calculatePayment()` like `DoctorPayment`
4. ✅ **No Auto-Trigger**: Admin manually creates payouts (removed from observer)
5. ✅ **Unpaid Consultations Endpoint**: Returns all unpaid consultations (like `getDoctorUnpaidConsultations`)

This implementation matches the existing `admin/doctor-payments` workflow exactly!
