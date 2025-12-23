# Two Consultation Types System - Complete Implementation

## 🎯 Overview

Your DoctorOnTap platform now supports **TWO types of consultations**:

1. **💳 Consult Now, Pay Later** (Recommended)
2. **🔒 Pay Before Consultation** (Instant Access)

Plus full patient account management with email verification.

---

## ✨ Consultation Types Explained

### **Type 1: Consult Now, Pay Later** (Default)

**How it works:**
```
1. Patient fills consultation form
2. Patient submits (no payment yet)
3. Consultation created immediately
4. Doctor assigned
5. Consultation happens
6. After consultation complete → Payment request sent
7. Patient pays
```

**Benefits:**
- ✅ No upfront payment required
- ✅ Immediate consultation booking
- ✅ Pay only after receiving service
- ✅ More flexible for patients

**Use Case:** Regular consultations, non-urgent cases, patients who prefer to pay after service

---

### **Type 2: Pay Before Consultation** (New)

**How it works:**
```
1. Patient fills consultation form
2. Patient selects "Pay Before Consultation"
3. Patient submits
4. Redirected to payment page
5. Patient completes payment
6. After payment confirmed → Consultation created
7. Doctor assigned immediately
8. Priority consultation
```

**Benefits:**
- ✅ Faster doctor assignment
- ✅ Priority consultation
- ✅ Guaranteed payment
- ✅ Instant access after payment

**Use Case:** Urgent cases, patients who want immediate attention, guaranteed service

---

## 🔄 Complete User Flows

### **Flow 1: Pay Later (Current System - Enhanced)**

```
┌─────────────────────────────────────────┐
│ Patient visits homepage                 │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│ Fills consultation form                 │
│ - Personal details                      │
│ - Medical problem                       │
│ - Selects "Consult Now, Pay Later"     │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│ Submits form                            │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│ System Actions:                         │
│ 1. Creates patient account (if new)    │
│ 2. Sends verification email             │
│ 3. Creates consultation (pending)       │
│ 4. Notifies admin & doctor              │
│ 5. Shows success message                │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│ Patient verifies email (optional now)   │
│ Can login to track consultation         │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│ Doctor completes consultation           │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│ Payment request sent to patient         │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│ Patient pays                            │
│ Consultation marked as "paid"           │
└─────────────────────────────────────────┘
```

---

### **Flow 2: Pay First (New Feature)**

```
┌─────────────────────────────────────────┐
│ Patient visits homepage                 │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│ Fills consultation form                 │
│ - Personal details                      │
│ - Medical problem                       │
│ - Selects "Pay Before Consultation"    │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│ Submits form                            │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│ System Actions:                         │
│ 1. Validates form                       │
│ 2. Stores data in session               │
│ 3. Calculates consultation fee          │
│ 4. Redirects to payment page            │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│ Patient on payment page                 │
│ Sees consultation fee                   │
│ Payment gateway (Korapay) displayed     │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│ Patient completes payment               │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│ Payment webhook received                │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│ System Actions:                         │
│ 1. Verifies payment                     │
│ 2. Creates patient account              │
│ 3. Sends verification email             │
│ 4. Creates consultation (paid status)   │
│ 5. Assigns doctor (priority)            │
│ 6. Notifies doctor immediately          │
│ 7. Creates payment record               │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│ Success page shown                      │
│ Patient gets consultation reference     │
│ Can login to dashboard                  │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│ Doctor sees PAID consultation           │
│ Priority assignment                     │
│ Starts consultation immediately         │
└─────────────────────────────────────────┘
```

---

## 💾 Database Schema Changes

### **Consultations Table - New Fields**

```sql
consultation_type        ENUM('pay_now', 'pay_later') DEFAULT 'pay_later'
requires_payment_first   BOOLEAN DEFAULT FALSE
payment_completed_at     TIMESTAMP NULL
```

**Field Descriptions:**
- `consultation_type`: Type of consultation selected by patient
- `requires_payment_first`: Flag to indicate if payment is required before consultation
- `payment_completed_at`: Timestamp when payment was completed (for pay_now type)

---

## 🎨 Frontend Updates

### **Consultation Form**

**New Section: Payment Option**

Located after "Consultation Mode" field:

```html
<!-- Payment Option Selection -->
┌─────────────────────────────────────────────┐
│  Payment Option *                           │
│                                             │
│  ┌─────────────────────────────────────┐   │
│  │ 💳 Consult Now, Pay Later          │   │
│  │ [Recommended]                       │   │
│  │ Start immediately. Pay after.       │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  ┌─────────────────────────────────────┐   │
│  │ 🔒 Pay Before Consultation         │   │
│  │ [Instant Access]                    │   │
│  │ Pay first, connect faster.          │   │
│  └─────────────────────────────────────┘   │
└─────────────────────────────────────────────┘
```

**Visual Design:**
- Radio buttons with full card selection
- Hover effects (border color change)
- Active state (purple border + background)
- Badge indicators ("Recommended", "Instant Access")
- Clear descriptions

---

## 🔧 Controller Logic

### **ConsultationController - Updated store() Method**

```php
public function store(Request $request)
{
    // 1. Validate form (including consultation_type)
    $validated = $request->validate([
        // ... existing validation
        'consultation_type' => 'required|in:pay_now,pay_later',
    ]);

    // 2. Check consultation type
    if ($validated['consultation_type'] === 'pay_now') {
        // PAY FIRST FLOW
        return $this->handlePayFirstConsultation($validated, $request);
    } else {
        // PAY LATER FLOW (existing system)
        return $this->handlePayLaterConsultation($validated, $request);
    }
}

private function handlePayFirstConsultation($validated, $request)
{
    // 1. Store consultation data in session
    session()->put('pending_consultation', $validated);
    
    // 2. Calculate consultation fee
    $fee = $this->calculateConsultationFee($validated);
    
    // 3. Store uploaded files temporarily
    if ($request->hasFile('medical_documents')) {
        // Handle file upload
    }
    
    // 4. Initialize payment
    $paymentReference = 'PAY-' . time() . '-' . Str::random(6);
    
    // 5. Create payment record (pending)
    $payment = Payment::create([
        'reference' => $paymentReference,
        'amount' => $fee,
        'status' => 'pending',
        'type' => 'consultation_prepayment',
    ]);
    
    // 6. Redirect to payment page
    return redirect()->route('payment.consultation-prepay', [
        'reference' => $paymentReference
    ]);
}

private function handlePayLaterConsultation($validated, $request)
{
    // Existing logic (current system)
    // 1. Create/update patient
    // 2. Send verification email
    // 3. Create consultation
    // 4. Send notifications
    // 5. Return success response
}
```

---

## 💰 Payment Flow (Pay First)

### **New Payment Page**

**Route:** `/payment/consultation/{reference}`

**Page Features:**
- Shows consultation summary
- Displays fee amount
- Shows selected doctor (if any)
- Payment gateway integration (Korapay)
- Timer (optional: 15 minutes to complete payment)
- "Cancel" option (returns to homepage)

### **Payment Confirmation**

**After Successful Payment:**
```php
// In PaymentController@webhook or callback

public function handleConsultationPrepayment($payment)
{
    // 1. Retrieve consultation data from session
    $consultationData = session('pending_consultation');
    
    // 2. Create patient account
    $patient = Patient::create([
        'email' => $consultationData['email'],
        'name' => $consultationData['first_name'] . ' ' . $consultationData['last_name'],
        // ... other fields
    ]);
    
    // 3. Send verification email
    $patient->sendEmailVerificationNotification();
    
    // 4. Create consultation with PAID status
    $consultation = Consultation::create([
        'reference' => 'CONSULT-' . time() . '-' . Str::random(6),
        'consultation_type' => 'pay_now',
        'requires_payment_first' => true,
        'payment_completed_at' => now(),
        'payment_status' => 'paid',
        'payment_id' => $payment->id,
        'status' => 'pending',
        // ... other fields
    ]);
    
    // 5. Assign doctor (priority)
    // Logic to assign doctor immediately
    
    // 6. Notify doctor (urgent notification)
    // Send SMS, email, push notification
    
    // 7. Clear session data
    session()->forget('pending_consultation');
    
    // 8. Redirect to success page
    return redirect()->route('consultation.payment-success', [
        'reference' => $consultation->reference
    ]);
}
```

---

## 📧 Patient Account & Email Verification

### **How It Works:**

#### **For Pay Later:**
```
1. Patient submits consultation
2. Account created automatically
3. Verification email sent
4. Patient can use app immediately (consultation tracking)
5. Email verification optional for now
6. Must verify to access full dashboard features
```

#### **For Pay First:**
```
1. Patient completes payment
2. Account created after payment
3. Verification email sent
4. Patient can login immediately
5. Consultation already paid and active
6. Can access dashboard to track consultation
```

### **Email Verification Benefits:**

✅ **Access patient dashboard**
✅ **View consultation history**
✅ **See medical records**
✅ **Track payments**
✅ **Book future consultations easier**
✅ **Manage dependents**

---

## 🎯 Key Differences Between Types

| Feature | Pay Later | Pay First |
|---------|-----------|-----------|
| **Payment Timing** | After consultation | Before consultation |
| **Consultation Creation** | Immediate | After payment |
| **Doctor Assignment** | Normal priority | High priority |
| **Patient Account** | Created immediately | Created after payment |
| **Email Verification** | Sent immediately | Sent after payment |
| **Consultation Status** | `pending` + `unpaid` | `pending` + `paid` |
| **Payment Request** | Sent after completion | Already paid |
| **Use Case** | Regular consultations | Urgent / Priority |

---

## 🔐 Security & Validation

### **Pay First Security:**

1. **Session Storage**: Consultation data stored securely in session
2. **Payment Verification**: Korapay webhook verification
3. **Timeout**: Payment must complete within timeframe
4. **Duplicate Prevention**: Check for duplicate payments
5. **Rollback**: If payment fails, consultation not created

### **Payment Gateway Integration:**

```php
// Initialize payment for consultation
$response = Http::withHeaders([
    'Authorization' => 'Bearer ' . config('services.korapay.secret_key'),
])->post('https://api.korapay.com/merchant/api/v1/charges/initialize', [
    'amount' => $fee,
    'currency' => 'NGN',
    'customer' => [
        'name' => $consultationData['first_name'] . ' ' . $consultationData['last_name'],
        'email' => $consultationData['email'],
    ],
    'metadata' => [
        'consultation_type' => 'pay_first',
        'patient_data' => json_encode($consultationData),
    ],
    'redirect_url' => route('payment.consultation-callback'),
]);
```

---

## 🚀 Implementation Status

| Component | Status |
|-----------|--------|
| Database migration | ✅ Done |
| Consultation form update | ✅ Done |
| Frontend UI (payment option) | ✅ Done |
| Controller updates | ⏳ In Progress |
| Payment flow (pay first) | ⏳ In Progress |
| Email verification | ✅ Done |
| Patient dashboard | ✅ Done |
| Documentation | ✅ Done |

---

## 📝 Next Steps

1. ✅ **Complete ConsultationController updates**
2. ✅ **Create payment prepayment page**
3. ✅ **Update PaymentController webhook handler**
4. ✅ **Add success/failure pages**
5. ✅ **Test both flows thoroughly**
6. ✅ **Update admin dashboard to show consultation types**

---

## 🎉 Benefits Summary

**For Patients:**
- ✅ **Choice**: Can choose payment method
- ✅ **Flexibility**: Pay later for non-urgent cases
- ✅ **Priority**: Pay first for urgent cases
- ✅ **Dashboard**: Full account management
- ✅ **Tracking**: Can track all consultations

**For Doctors:**
- ✅ **Guaranteed Payment**: Pay-first consultations are pre-paid
- ✅ **Priority System**: Know which consultations are urgent
- ✅ **Clear Status**: Can see payment status immediately

**For Admin:**
- ✅ **Payment Flexibility**: Support both models
- ✅ **Revenue**: Upfront payments for some consultations
- ✅ **Better Tracking**: Know consultation types
- ✅ **Analytics**: Can analyze which type is preferred

---

**Last Updated**: December 13, 2025  
**Status**: ✅ Implementation In Progress  
**System**: Two Consultation Types with Patient Account Management

