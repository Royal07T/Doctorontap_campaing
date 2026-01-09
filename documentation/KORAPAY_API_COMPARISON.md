# KoraPay Payout API - Implementation Comparison

## ✅ What's Already Implemented

1. ✅ **List Banks API** - `fetchBanks()` method
   - Endpoint: `GET /merchant/api/v1/misc/banks?countryCode=NG`
   - Status: Implemented

2. ✅ **Verify Bank Account** - `verifyBankAccount()` method
   - Endpoint: `POST /merchant/api/v1/misc/banks/resolve`
   - Status: Implemented

3. ✅ **Request Payout** - `initiatePayout()` method
   - Endpoint: `POST /merchant/api/v1/transactions/disburse`
   - Status: Implemented

4. ✅ **Verify Payout Status** - `verifyPayoutStatus()` method
   - Endpoint: `GET /merchant/api/v1/transactions/:transactionReference`
   - Status: Implemented

5. ✅ **Bulk Payouts** - `processBulkPayouts()` method
   - Endpoint: `POST /merchant/api/v1/transactions/disburse/bulk`
   - Status: Implemented

6. ✅ **Query Bulk Payouts** - `queryBulkPayouts()` method
   - Endpoint: `GET /merchant/api/v1/transactions/bulk/:bulk_reference/payout`
   - Status: Implemented

---

## ❌ What's Missing

### 1. **Check Bank/Mobile Money Network Availability** ⚠️ IMPORTANT

**Documentation**: Step 3 in the payout workflow - Optional but recommended

**Endpoint**: `GET /merchant/api/v1/misc/banks/availability?bank={bank_code}&currency={currency}`

**Purpose**: Check if a bank or mobile money network is available for payouts before initiating

**Why It's Important**:
- Prevents payout failures due to network unavailability
- Better user experience
- Reduces failed transactions

**Status**: ❌ **NOT IMPLEMENTED**

---

### 2. **Payout History API** ⚠️ IMPORTANT

**Documentation**: Allows fetching all payout transactions

**Endpoint**: `GET /merchant/api/v1/payouts`

**Query Parameters**:
- `currency` (optional) - e.g., NGN, KES, GHS
- `date_from` (optional) - Format: YYYY-MM-DD-HH-MM-SS
- `date_to` (optional) - Format: YYYY-MM-DD-HH-MM-SS
- `limit` (optional) - Default: 10
- `starting_after` (optional) - For pagination
- `ending_before` (optional) - For pagination

**Purpose**: 
- Fetch payout history without accessing dashboard
- Generate reports
- Audit trail

**Status**: ❌ **NOT IMPLEMENTED**

---

### 3. **Mobile Money Support** ⚠️ OPTIONAL

**Documentation**: Supports mobile money payouts (e.g., M-Pesa, Airtel Money)

**Endpoint**: Same as bank payout but with `type: 'mobile_money'`

**Required Fields**:
- `mobile_money.operator` - Mobile money operator slug
- `mobile_money.phone` - Phone number

**List Mobile Money Operators**:
- Endpoint: `GET /merchant/api/v1/misc/mobile-money?countryCode=KE`

**Status**: ❌ **NOT IMPLEMENTED** (Only bank_account supported)

---

### 4. **Remittance Payout Support** ⚠️ OPTIONAL

**Documentation**: Special payout format for remittance merchants

**Endpoint**: `POST /merchant/api/v1/transactions/disburse` (with remittance-specific fields)

**Status**: ❌ **NOT IMPLEMENTED** (Not needed for doctor payments)

---

## 🔍 Payload Structure Comparison

### Current Implementation:
```php
$payload = [
    'reference' => $korapayReference,
    'destination' => [
        'type' => 'bank_account',
        'amount' => $amount,
        'currency' => 'NGN',
        'narration' => 'Doctor payment - ' . $payment->reference,
        'bank_account' => [
            'bank' => $bankAccount->bank_code,
            'account' => $bankAccount->account_number,
        ],
        'customer' => [
            'name' => $bankAccount->account_name,
            'email' => $doctor->email ?? 'doctor@doctorontap.com.ng',
        ],
    ],
];
```

### Documentation Structure:
```json
{
  "reference": "...",
  "destination": {
    "type": "bank_account",
    "amount": "...",
    "currency": "...",
    "narration": "...",
    "bank_account": {
      "bank": "...",
      "account": "..."
    },
    "customer": {
      "name": "...",
      "email": "..."
    }
  }
}
```

**Status**: ✅ **CORRECT** - Structure matches documentation

---

## 🚨 Critical Missing Features

### 1. Bank Availability Check (HIGH PRIORITY)

This should be checked before initiating payout to avoid failures.

**Impact**: 
- Reduces failed payouts
- Better error handling
- Improved user experience

---

### 2. Payout History API (MEDIUM PRIORITY)

Useful for:
- Admin dashboard reports
- Payment reconciliation
- Audit logs

---

## 📋 Implementation Priority

1. **HIGH**: Bank Availability Check
2. **MEDIUM**: Payout History API
3. **LOW**: Mobile Money Support (if needed)
4. **N/A**: Remittance Support (not needed)

---

## 🔗 Documentation Reference

Full documentation: https://developers.korapay.com/docs/payout-via-api

