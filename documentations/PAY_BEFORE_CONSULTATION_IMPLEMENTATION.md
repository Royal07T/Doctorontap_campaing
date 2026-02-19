# Pay Before Consultation - Implementation Summary

## Overview
The application logic has been changed from "consult and pay later" to "pay before consultation". Patients must now complete payment before consultations can proceed.

## Changes Implemented

### 1. Consultation Model (`app/Models/Consultation.php`)
**Added Methods:**
- `canProceed()`: Checks if consultation can proceed (payment must be made if required)
- `requiresPaymentBeforeStart()`: Checks if payment is required before consultation can start

### 2. Consultation Controller (`app/Http/Controllers/ConsultationController.php`)
**Changes:**
- After consultation creation, automatically initializes payment if required
- Returns payment URL in JSON response for frontend redirect
- Links payment to consultation immediately

### 3. Doctor Dashboard Controller (`app/Http/Controllers/Doctor/DashboardController.php`)
**Changes:**
- Added payment verification before allowing status change to `scheduled` or `in_progress`
- Returns error message if payment is not completed
- Prevents doctors from starting consultations without payment

### 4. Video Room Controller (`app/Http/Controllers/VideoRoomController.php`)
**Changes:**
- Added payment check before creating video room
- Added payment check before allowing patient to join room
- Doctors can still join to inform patient about payment requirement

### 5. Consultation Session Controller (`app/Http/Controllers/ConsultationSessionController.php`)
**Changes:**
- Added payment check in `waitingRoom()` method (for patients)
- Added payment check in `getToken()` method (for patients)
- Redirects patients to payment page if payment not completed

### 6. Patient Dashboard Controller (`app/Http/Controllers/Patient/DashboardController.php`)
**Changes:**
- After booking consultation, automatically initializes payment
- Redirects to payment checkout page if payment is required
- Links payment to consultation immediately

### 7. Customer Care Booking Controller (`app/Http/Controllers/CustomerCare/BookingController.php`)
**Changes:**
- After booking consultation, automatically initializes payment
- Shows payment link in success message
- Links payment to consultation immediately

## Payment Flow

### Before (Pay Later)
1. Patient books consultation → `payment_status = 'unpaid'`
2. Consultation proceeds
3. After completion, payment request sent
4. Patient pays → `payment_status = 'paid'`
5. Treatment plan unlocked

### After (Pay Before)
1. Patient books consultation → `payment_status = 'unpaid'`
2. **Payment initialized immediately** → Redirect to payment page
3. Patient pays → `payment_status = 'paid'`
4. **Only after payment**, consultation can proceed
5. Treatment plan unlocked (already paid)

## Status Progression

### Consultation Status
- `pending` → Consultation created, awaiting payment
- `scheduled` → Payment confirmed, consultation scheduled
- `in_progress` → Consultation started
- `completed` → Consultation finished
- `cancelled` → Consultation cancelled

### Payment Status
- `unpaid` → Consultation created, payment required
- `pending` → Payment initiated, awaiting confirmation
- `paid` → Payment confirmed, consultation can proceed
- `failed` → Payment failed, retry required
- `cancelled` → Payment cancelled, retry required

## Security Checks

### Payment Verification Points
1. **Consultation Creation**: Payment initialized immediately
2. **Status Updates**: Doctor cannot change status to `scheduled`/`in_progress` without payment
3. **Video Room Creation**: Payment required before room creation
4. **Video Room Join**: Patient cannot join without payment
5. **Session Token**: Patient cannot get session token without payment
6. **Waiting Room**: Patient cannot access waiting room without payment

### Doctor Access
- Doctors can still access consultations to inform patients about payment
- Doctors cannot start consultations without payment
- Doctors receive notifications when payment is completed

## Doctor Payment Logic

### No Changes Required
- Doctor payments are calculated based on `payment_status = 'paid'` consultations
- Only paid consultations are included in doctor payment calculations
- This ensures doctors only receive payments for consultations that were paid upfront

## Frontend Integration

### JSON Response Format
When payment is required, the API returns:
```json
{
    "success": true,
    "requires_payment": true,
    "message": "Please complete payment to confirm your consultation booking.",
    "consultation_reference": "CONSULT-1234567890-ABC123",
    "payment_url": "https://checkout.korapay.com/...",
    "redirect_to_payment": true
}
```

### Frontend Actions
1. Check for `requires_payment: true` in response
2. If present, redirect to `payment_url`
3. After payment, redirect back to consultation page
4. Consultation will be accessible after payment confirmation

## Testing Checklist

- [ ] Patient books consultation → Payment page appears
- [ ] Patient completes payment → Consultation becomes accessible
- [ ] Doctor tries to start consultation without payment → Error message
- [ ] Patient tries to join video room without payment → Redirected to payment
- [ ] Patient tries to get session token without payment → Error message
- [ ] Doctor payment calculations only include paid consultations
- [ ] Treatment plan unlocked after payment (already implemented)

## Rollback Plan

If issues arise, the changes can be rolled back by:
1. Removing payment checks from status update methods
2. Removing payment initialization from booking flows
3. Reverting to original "pay later" message in ConsultationController

## Notes

- All existing payment webhook logic remains unchanged
- Treatment plan unlocking logic remains unchanged
- Doctor payout calculations remain unchanged
- Only the timing of payment requirement has changed

