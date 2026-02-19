# Pay Before Consultation - Implementation Analysis

## Current Flow (Pay Later)
1. Patient books consultation → `payment_status = 'unpaid'`, `status = 'pending'`
2. Consultation is scheduled/assigned to doctor
3. Consultation proceeds (doctor and patient can start)
4. After consultation completes, payment request is sent
5. Patient pays → `payment_status = 'paid'`
6. Treatment plan is unlocked

## Target Flow (Pay Before)
1. Patient books consultation → `payment_status = 'unpaid'`, `status = 'pending'`
2. **Payment is required immediately** → Redirect to payment page
3. Patient pays → `payment_status = 'paid'`
4. **Only after payment**, consultation can be scheduled/assigned
5. Consultation proceeds (doctor and patient can start)
6. Treatment plan is unlocked (already paid)

## Key Changes Required

### 1. Consultation Creation
- **File**: `app/Services/ConsultationService.php`
- **Change**: After creating consultation, return payment URL instead of success message
- **Action**: Modify `createConsultation()` to return payment initialization

### 2. Payment Redirect
- **File**: `app/Http/Controllers/ConsultationController.php`
- **Change**: After consultation creation, redirect to payment page
- **Action**: Modify `store()` to redirect to payment initialization

### 3. Payment Verification Before Consultation Start
- **Files**: 
  - `app/Http/Controllers/Doctor/DashboardController.php` (updateConsultationStatus)
  - `app/Http/Controllers/VideoRoomController.php` (createRoom, joinRoom)
  - `app/Http/Controllers/ConsultationSessionController.php` (getStatus, startSession)
- **Change**: Check `payment_status === 'paid'` before allowing consultation to start
- **Action**: Add payment verification middleware/checks

### 4. Patient Dashboard Booking
- **File**: `app/Http/Controllers/Patient/DashboardController.php`
- **Change**: After booking, redirect to payment page
- **Action**: Modify booking flow to require payment first

### 5. Customer Care Booking
- **File**: `app/Http/Controllers/CustomerCare/BookingController.php`
- **Change**: After booking, redirect to payment page or show payment link
- **Action**: Modify booking flow to require payment first

### 6. Consultation Model
- **File**: `app/Models/Consultation.php`
- **Change**: Add method to check if consultation can proceed (payment required)
- **Action**: Add `canProceed()` method

## Payment Status Flow
- `unpaid` → Consultation created, payment required
- `pending` → Payment initiated, awaiting confirmation
- `paid` → Payment confirmed, consultation can proceed
- `failed` → Payment failed, retry required
- `cancelled` → Payment cancelled, retry required

## Status Progression (After Payment)
- `pending` → Consultation created, awaiting payment
- `scheduled` → Payment confirmed, consultation scheduled
- `in_progress` → Consultation started
- `completed` → Consultation finished
- `cancelled` → Consultation cancelled

## Doctor Payment Logic
- **No Change Required**: Doctor payments are calculated based on `payment_status = 'paid'` consultations
- **Impact**: Doctors will only receive payments for consultations that were paid upfront
- **Benefit**: Reduces unpaid consultations in doctor payment calculations

