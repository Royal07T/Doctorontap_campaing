# Vonage SMS API Fix Summary

## Issue
The Vonage SMS API was failing with DNS resolution errors:
```
cURL error 6: Could not resolve host: rest.nexmo.com
```

This was preventing SMS from being sent to customer care recipients.

## Root Cause
- Intermittent DNS resolution failures for `rest.nexmo.com` endpoint
- No retry logic for network/DNS errors
- Single attempt failure would cause SMS sending to fail completely

## Solution Implemented

### 1. Added Retry Logic with Exponential Backoff
- **Location**: `app/Services/VonageService.php` - `sendViaLegacyAPI()` method
- **Retry Strategy**:
  - Maximum 3 attempts
  - Exponential backoff: 1s, 2s, 3s delays between retries
  - Only retries on DNS/network errors (cURL error 6, 28)
  - Does not retry on API errors (status codes)

### 2. Improved Error Detection
- Specifically detects DNS errors: "Could not resolve host", "cURL error 6", "cURL error 28"
- Better error messages for network issues
- Logs retry attempts for debugging

### 3. Testing Results
✅ **SMS Test Successful**:
- Message ID: `e293225e-e20d-4559-b8ed-4ba98d1cb4b5`
- Status: 0 (Success)
- Remaining Balance: 2558.89 EUR
- Network: 62120 (Nigeria)

## Customer Care Integration Points

The fix applies to all customer care SMS sending:

1. **CommunicationController** (`app/Http/Controllers/CustomerCare/CommunicationController.php`)
   - Single SMS sending via `$vonageService->sendSMS()`

2. **BulkSmsController** (`app/Http/Controllers/CustomerCare/BulkSmsController.php`)
   - Bulk SMS campaigns via `$smsService->sendSMS()`

3. **CustomerCareController** (`app/Http/Controllers/CustomerCareController.php`)
   - Direct SMS sending to patients

## Configuration

Current configuration (working):
- **API Method**: `legacy` (Legacy SMS API)
- **Enabled**: `true`
- **Brand Name**: `DoctorOnTap`
- **Provider**: `vonage` (set in `SMS_PROVIDER`)

## Testing

To test SMS sending:
```bash
php artisan vonage:test-sms 2347081114942 --message="Test message"
```

## Next Steps (Optional)

If DNS issues persist, consider:
1. **Switch to Messages API**: Set `VONAGE_API_METHOD=messages` in `.env`
   - Requires `VONAGE_APPLICATION_ID` and `VONAGE_PRIVATE_KEY`
   - Uses `api.nexmo.com` endpoint (more reliable DNS)

2. **Monitor Logs**: Check `storage/logs/laravel.log` for retry attempts

## Status
✅ **FIXED** - SMS sending is now working with retry logic for network failures.

