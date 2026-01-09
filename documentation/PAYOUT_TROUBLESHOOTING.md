# Doctor Payout Troubleshooting Guide

## 🔍 Common Payout Failure Reasons

### 1. **Bank Account Not Verified**
**Error Message**: `"Doctor does not have a verified bank account"`

**Solution**:
- Go to **Admin → Doctors → [Select Doctor] → Bank Accounts**
- Verify the bank account by setting `is_verified = true`
- Ensure account name matches exactly with bank records

---

### 2. **Missing Bank Code**
**Error Message**: `"Bank code is missing for this bank account"`

**Solution**:
- Go to **Admin → Doctors → [Select Doctor] → Bank Accounts**
- Add the correct bank code (e.g., "033" for UBA, "044" for Access Bank)
- Bank codes are available from KoraPay API or bank list

**How to get bank code**:
```php
// Use KoraPay service to fetch banks
$payoutService = app(\App\Services\KoraPayPayoutService::class);
$banks = $payoutService->fetchBanks('NG');
```

---

### 3. **Bank Account Verification Fails**
**Error Message**: `"Bank account verification failed: [error details]"`

**Common Causes**:
- Invalid account number
- Account number doesn't match bank code
- Account is closed or inactive
- Bank code is incorrect

**Solution**:
1. Verify account number is correct (no spaces, correct digits)
2. Verify bank code matches the bank name
3. Test account with KoraPay's bank resolution API
4. Contact bank to confirm account is active

---

### 4. **Insufficient Funds in KoraPay Wallet**
**Error Message**: `"Insufficient funds in disbursement wallet"`

**Solution**:
- Check KoraPay dashboard for wallet balance
- Top up your KoraPay disbursement wallet
- Ensure wallet has enough funds to cover payout amount + fees

**Check Balance**:
- Login to KoraPay dashboard: https://dashboard.korapay.com
- Navigate to **Wallet** or **Disbursement** section
- Check available balance

---

### 5. **Invalid API Keys**
**Error Message**: `"Unauthorized"` or `"Invalid API key"`

**Solution**:
1. Check `.env` file has correct keys:
   ```env
   KORAPAY_SECRET_KEY=sk_live_...
   KORAPAY_PUBLIC_KEY=pk_live_...
   KORAPAY_API_URL=https://api.korapay.com/merchant/api/v1
   ```

2. Verify keys are from **live** environment (not test)
3. Ensure keys have **disbursement/payout** permissions
4. Regenerate keys if needed from KoraPay dashboard

---

### 6. **Invalid Request Data**
**Error Message**: `"invalid request data"` or validation errors

**Common Issues**:
- Amount format incorrect
- Missing required fields
- Invalid email format
- Account number format issues

**Solution**:
- Check payment amount is valid (positive number)
- Ensure doctor email is valid
- Verify account number format (numbers only, correct length)
- Check bank account name is not empty

---

### 7. **Network/Timeout Issues**
**Error Message**: `"Connection timeout"` or `"Request timeout"`

**Solution**:
- Check server internet connection
- Verify KoraPay API is accessible
- Increase timeout in code (currently 30 seconds)
- Retry the payout

---

### 8. **Bank Not Supported**
**Error Message**: `"bank not found"` or `"unsupported bank"`

**Solution**:
- Verify bank is supported by KoraPay
- Check bank code is correct
- Use KoraPay's bank list to verify supported banks

---

## 🔧 How to Diagnose Payout Failures

### Step 1: Check Payment Status

1. Go to **Admin → Doctor Payments**
2. Find the failed payment
3. Click to view details
4. Check:
   - `status` field
   - `korapay_status` field
   - `korapay_response` field (JSON with error details)

### Step 2: Check Logs

```bash
# View recent payout errors
tail -f storage/logs/laravel.log | grep -i "korapay\|payout"

# Or search for specific payment reference
grep "DOCPAY-XXXXX" storage/logs/laravel.log
```

### Step 3: Check Database

```sql
-- View failed payments
SELECT 
    id,
    reference,
    doctor_id,
    doctor_amount,
    status,
    korapay_status,
    korapay_reference,
    korapay_response,
    created_at
FROM doctor_payments
WHERE status = 'failed'
ORDER BY created_at DESC;

-- View korapay_response for error details
SELECT 
    reference,
    korapay_response
FROM doctor_payments
WHERE status = 'failed'
LIMIT 10;
```

### Step 4: Test Bank Account Verification

```php
// In tinker: php artisan tinker
$payoutService = app(\App\Services\KoraPayPayoutService::class);
$result = $payoutService->verifyBankAccount('033', '1234567890');
dd($result);
```

---

## 🛠️ Fixing Common Issues

### Fix 1: Verify Bank Account

```php
// In tinker
$doctor = \App\Models\Doctor::find(1);
$bankAccount = $doctor->bankAccounts()->first();

// Verify account
$payoutService = app(\App\Services\KoraPayPayoutService::class);
$verification = $payoutService->verifyBankAccount(
    $bankAccount->bank_code,
    $bankAccount->account_number
);

if ($verification['success']) {
    // Update account name if different
    $bankAccount->update([
        'account_name' => $verification['data']['account_name'] ?? $bankAccount->account_name,
        'is_verified' => true
    ]);
    echo "Bank account verified!\n";
} else {
    echo "Verification failed: " . $verification['message'] . "\n";
}
```

### Fix 2: Add Missing Bank Code

```php
// In tinker
$bankAccount = \App\Models\DoctorBankAccount::find(1);

// Common Nigerian bank codes
$bankCodes = [
    'Access Bank' => '044',
    'UBA' => '033',
    'GTBank' => '058',
    'First Bank' => '011',
    'Zenith Bank' => '057',
    'Fidelity Bank' => '070',
    'Union Bank' => '032',
    // Add more as needed
];

$bankName = $bankAccount->bank_name;
if (isset($bankCodes[$bankName])) {
    $bankAccount->update(['bank_code' => $bankCodes[$bankName]]);
    echo "Bank code updated: {$bankCodes[$bankName]}\n";
} else {
    echo "Bank code not found for: {$bankName}\n";
    echo "Fetch from KoraPay API or check KoraPay dashboard\n";
}
```

### Fix 3: Retry Failed Payment

1. Go to **Admin → Doctor Payments**
2. Find failed payment
3. Click **"Initiate Payout"** again
4. System will reset status and retry

Or via code:
```php
// In tinker
$payment = \App\Models\DoctorPayment::find(1);
$payment->update([
    'status' => 'pending',
    'korapay_status' => null,
    'korapay_reference' => null,
]);
// Then retry from admin panel
```

---

## 📋 Pre-Payout Checklist

Before initiating a payout, verify:

- [ ] Doctor has a bank account added
- [ ] Bank account is verified (`is_verified = true`)
- [ ] Bank code is set and correct
- [ ] Account number is correct (no spaces, correct format)
- [ ] Account name matches bank records
- [ ] KoraPay API keys are valid and have payout permissions
- [ ] KoraPay wallet has sufficient funds
- [ ] Payment amount is valid (positive, > 0)
- [ ] Doctor email is valid

---

## 🔍 Debugging Steps

### 1. Check Payment Record

```php
$payment = \App\Models\DoctorPayment::with(['doctor', 'bankAccount'])->find(1);

// Check all details
dd([
    'payment_reference' => $payment->reference,
    'doctor_amount' => $payment->doctor_amount,
    'doctor' => $payment->doctor->email,
    'bank_account' => [
        'account_name' => $payment->bankAccount->account_name,
        'account_number' => $payment->bankAccount->account_number,
        'bank_code' => $payment->bankAccount->bank_code,
        'bank_name' => $payment->bankAccount->bank_name,
        'is_verified' => $payment->bankAccount->is_verified,
    ],
    'status' => $payment->status,
    'korapay_status' => $payment->korapay_status,
    'korapay_response' => json_decode($payment->korapay_response, true),
]);
```

### 2. Test Bank Verification

```php
$payoutService = app(\App\Services\KoraPayPayoutService::class);

$result = $payoutService->verifyBankAccount(
    '033', // Bank code
    '1234567890' // Account number
);

dd($result);
```

### 3. Test Payout Manually

```php
$payment = \App\Models\DoctorPayment::find(1);
$payoutService = app(\App\Services\KoraPayPayoutService::class);

$result = $payoutService->initiatePayout($payment);

dd($result);
```

---

## 📞 Getting Help

### Check KoraPay Dashboard

1. Login: https://dashboard.korapay.com
2. Go to **Transactions** or **Disbursements**
3. Search for transaction by reference
4. View error details and status

### Contact KoraPay Support

- Email: support@korapay.com
- Include:
  - Transaction reference
  - Error message
  - Payment details
  - Screenshots if available

### Check Laravel Logs

```bash
# Real-time log monitoring
tail -f storage/logs/laravel.log

# Search for specific errors
grep -i "korapay\|payout\|failed" storage/logs/laravel.log | tail -50
```

---

## ✅ Success Indicators

When payout succeeds:
- `status` = `completed`
- `korapay_status` = `success`
- `paid_at` = timestamp
- `payout_completed_at` = timestamp
- `korapay_reference` = transaction reference

---

## 🔄 Retry Process

1. **Check Error**: Review `korapay_response` field for specific error
2. **Fix Issue**: Address the root cause (bank account, funds, etc.)
3. **Reset Status**: Payment status will auto-reset when you click "Initiate Payout"
4. **Retry**: Click "Initiate Payout" again

---

## 💡 Pro Tips

1. **Always verify bank account first** before creating payment
2. **Check KoraPay wallet balance** regularly
3. **Keep bank codes updated** - banks may change codes
4. **Monitor logs** for patterns in failures
5. **Test with small amounts** first before large payouts
6. **Use bulk payouts** for multiple payments (more efficient)

---

## 📊 Common Error Messages & Solutions

| Error Message | Cause | Solution |
|--------------|-------|----------|
| "Doctor does not have a verified bank account" | Bank account not verified | Verify bank account in admin panel |
| "Bank code is missing" | Bank code not set | Add bank code to bank account |
| "Bank account verification failed" | Invalid account details | Verify account number and bank code |
| "Insufficient funds in disbursement wallet" | Low wallet balance | Top up KoraPay wallet |
| "Unauthorized" | Invalid API keys | Check and update API keys |
| "bank not found" | Invalid bank code | Verify bank code is correct |
| "invalid request data" | Missing/invalid fields | Check all required fields are valid |

---

For more details, check the `korapay_response` field in the `doctor_payments` table for specific error details from KoraPay API.

