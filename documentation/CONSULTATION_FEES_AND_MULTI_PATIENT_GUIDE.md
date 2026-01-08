# Consultation Fees & Multi-Patient Booking Guide

## 🎯 Part 1: Admin Consultation Fee Settings

### **Overview**

Admin can now set **different fees** for the two consultation types:
1. **💳 Consult Now, Pay Later** - Standard price
2. **🔒 Pay Before Consultation** - Can be discounted to incentivize upfront payment

---

### **📍 How to Set Consultation Fees**

#### **Step 1: Access Settings**

1. Login as Admin
2. Go to **Admin Dashboard**
3. Click **Settings** in sidebar
4. Scroll to **Pricing Settings**

#### **Step 2: Configure Fees**

You'll see two fee input fields:

```
┌─────────────────────────────────────────────┐
│ 💳 Consult Now, Pay Later Fee (₦)          │
│ ┌─────────────────────────────┐             │
│ │ ₦ 5000                      │             │
│ └─────────────────────────────┘             │
│ Fee for patients who pay after service      │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ 🔒 Pay Before Consultation Fee (₦)         │
│ ┌─────────────────────────────┐             │
│ │ ₦ 4500                      │             │
│ └─────────────────────────────┘             │
│ Set lower to incentivize upfront payment    │
│                                              │
│ Discount Preview:                            │
│ Customers save ₦500 (10%) paying upfront   │
└─────────────────────────────────────────────┘
```

#### **Step 3: Save Settings**

Click **Save Settings** button at bottom

---

### **💡 Pricing Strategy Examples**

#### **Example 1: Discount for Upfront Payment**
```
Pay Later:  ₦5,000
Pay Now:    ₦4,500 (₦500 discount, 10% off)

Strategy: Incentivize upfront payment
Benefit: Better cash flow, guaranteed payment
```

#### **Example 2: Premium for Priority**
```
Pay Later:  ₦5,000
Pay Now:    ₦6,000 (₦1,000 premium)

Strategy: Pay more for priority/instant service
Benefit: Filter urgent cases, premium service
```

#### **Example 3: Same Price**
```
Pay Later:  ₦5,000
Pay Now:    ₦5,000 (no difference)

Strategy: Let patients choose based on preference
Benefit: Flexibility, no pricing complexity
```

---

### **🎨 Live Discount Preview**

The system shows real-time discount calculation:

**When Pay Now < Pay Later:**
```
✅ Customers save ₦500 (10%) when paying upfront
(Green highlight)
```

**When Pay Now > Pay Later:**
```
⚠️ Pay Now fee is higher than Pay Later! Consider reversing this.
(Red highlight - Warning)
```

**When Equal:**
```
ℹ️ Both prices are the same
(Blue highlight)
```

---

### **⚙️ Additional Settings**

#### **Doctor Payment Percentage**
```
Doctor Share: 70% of consultation fee
Platform Fee: 30% of consultation fee
```

This percentage applies to **BOTH** consultation types.

---

## 🎯 Part 2: Multi-Patient Booking System

### **Overview**

The **Multi-Patient Booking System** allows patients to book consultations for **multiple people** in a single booking (e.g., mother + children, family members).

---

### **🔍 How It Works**

#### **Current System (Regular Booking)**

```
1. Patient fills form
2. One consultation created
3. One patient record
4. One payment
```

#### **Multi-Patient System (Family Booking)**

```
1. Patient fills booking form
2. Adds multiple patients (family members)
3. System creates:
   - One booking record
   - Multiple patient records
   - Multiple consultation records (one per patient)
   - One invoice with line items (one per patient)
4. Payment covers all consultations
```

---

### **📋 Use Cases**

#### **Use Case 1: Mother + Child**

**Scenario:** Amina wants to consult for herself and her 6-year-old son.

**How it works:**
1. Amina goes to booking page
2. Fills her details first (as payer/guardian)
3. Clicks "Add Another Patient"
4. Adds son's details:
   - Name: Tunde
   - Age: 6
   - Relationship: Son
   - Medical history: Penicillin allergy, ear infections
5. Reviews booking:
   - Patient 1: Amina (self)
   - Patient 2: Tunde (son)
   - Total: ₦10,000 (₦5,000 × 2)
6. Chooses payment type (Pay Now or Pay Later)
7. Submits

**Result:**
- 2 separate patient records created
- 2 separate consultation records
- 1 invoice with 2 line items
- 1 payment (covering both)
- Tunde's allergy recorded in HIS medical record (not Amina's)

---

#### **Use Case 2: Family Consultation**

**Scenario:** Father books for himself, wife, and 2 children.

**Process:**
1. Father fills primary details
2. Adds wife
3. Adds child 1
4. Adds child 2
5. Reviews: 4 patients total
6. Total fee: ₦20,000 (₦5,000 × 4)
7. Pays (if Pay Now) or submits (if Pay Later)

**Result:**
- 4 separate patient records
- 4 separate consultations
- Each with own medical history
- 1 invoice with 4 line items

---

### **🎯 Key Benefits**

#### **1. Separate Medical Records** ✅
- Each patient has their own record
- No data mixing
- Clean medical history
- AI/Analytics ready

#### **2. Correct Billing** ✅
- Each consultation billed separately
- Doctor paid for each patient
- Clear invoice line items
- Transparent pricing

#### **3. Guardian Relationships** ✅
- Clear parent-child links
- Guardian information tracked
- Minor handling
- Family tree structure

#### **4. Individual Consultations** ✅
- Each patient gets separate consultation
- Doctor sees each patient
- Individual diagnoses
- Separate treatment plans

---

### **📊 Data Structure**

#### **Booking Table**
```sql
id              | 1
reference       | BOOK-202512-ABC123
payer_patient_id| 101 (Amina)
total_amount    | 10000
status          | pending
```

#### **Booking Patients Table**
```sql
booking_id | patient_id | relationship   | consultation_id | fee
1          | 101        | self           | 1001           | 5000
1          | 102        | son            | 1002           | 5000
```

#### **Patients Table**
```sql
id  | name   | age | guardian_id | is_minor
101 | Amina  | 32  | NULL        | false
102 | Tunde  | 6   | 101         | true
```

#### **Consultations Table**
```sql
id   | patient_id | booking_id | reference      | status
1001 | 101        | 1          | CONSULT-001    | pending
1002 | 102        | 1          | CONSULT-002    | pending
```

#### **Invoice Table**
```sql
id | booking_id | total_amount | status
1  | 1          | 10000        | pending
```

#### **Invoice Items Table**
```sql
invoice_id | patient_id | patient_name | amount | description
1          | 101        | Amina        | 5000   | Medical consultation
1          | 102        | Tunde        | 5000   | Pediatric consultation
```

---

### **🚀 How Patients Book Multi-Patient Consultations**

#### **Option 1: From Homepage**

1. Go to homepage
2. Click **"Book for Multiple Patients"** button
3. Fill primary patient (payer) details
4. Add additional patients
5. Review and submit

#### **Option 2: From Patient Dashboard**

1. Login to patient dashboard
2. Click **"New Consultation"**
3. Select **"Book for Family/Multiple Patients"**
4. Add family members
5. Review and submit

---

### **💰 Pricing for Multi-Patient Bookings**

The system calculates fees per patient:

**Example with Pay Later (₦5,000 each):**
```
Patient 1: ₦5,000
Patient 2: ₦5,000
Patient 3: ₦5,000
────────────────────
Total:     ₦15,000
```

**Example with Pay Now (₦4,500 each with discount):**
```
Patient 1: ₦4,500
Patient 2: ₦4,500
Patient 3: ₦4,500
────────────────────
Total:     ₦13,500
Saved:     ₦1,500 (₦500 × 3)
```

---

### **🔧 Fee Adjustments**

Doctors can adjust fees per patient:

#### **Scenario:**
- Mother: Standard fee ₦5,000
- Child 1: Discounted ₦3,500
- Child 2: Discounted ₦3,500

**Total:** ₦12,000 (instead of ₦15,000)

**How it works:**
1. Doctor views multi-patient booking
2. Clicks "Adjust Fees"
3. Sets custom fee per patient
4. Adds justification note
5. Saves adjustment
6. System logs change
7. Patient and admin notified

**Fee Adjustment Log:**
```
Doctor: Dr. John
Booking: BOOK-202512-ABC123
Original: ₦15,000
Adjusted: ₦12,000
Reason: "Family discount - 2 children"
```

---

### **📧 Notifications for Multi-Patient**

#### **Patient Email:**
```
Subject: Multi-Patient Booking Confirmation

Dear Amina,

Your booking for 2 patients has been confirmed:

Booking Reference: BOOK-202512-ABC123

Patients:
1. Amina (Self)
   - Reference: CONSULT-001
   - Fee: ₦5,000

2. Tunde (Son)
   - Reference: CONSULT-002
   - Fee: ₦5,000

Total Amount: ₦10,000

[View Booking Details]
```

#### **Doctor Notification:**
```
New Multi-Patient Booking Assigned

Booking: BOOK-202512-ABC123
Payer: Amina
Patients: 2

Consultations:
1. Amina (32 years, Female) - CONSULT-001
2. Tunde (6 years, Male) - CONSULT-002

Status: Paid ✅ / Unpaid ⏳

[View All Consultations]
```

---

## 🎯 Integration: Consultation Types + Multi-Patient

### **Scenario: Multi-Patient with Pay Now**

**Family of 4 chooses "Pay Before Consultation":**

1. Fill booking form for 4 people
2. Select "Pay Before Consultation"
3. System calculates: ₦4,500 × 4 = ₦18,000
4. Redirected to payment page
5. Pay ₦18,000
6. After payment confirmed:
   - 4 patient accounts created
   - 4 consultations created (all PAID)
   - 4 separate medical records
   - Priority assignment to doctor
7. Doctor sees "PAID" status on all 4
8. Family gets immediate attention

---

### **Scenario: Multi-Patient with Pay Later**

**Family of 3 chooses "Consult Now, Pay Later":**

1. Fill booking form for 3 people
2. Select "Consult Now, Pay Later"
3. Submit (no payment yet)
4. System creates:
   - 3 patient accounts
   - 3 consultations (all UNPAID)
   - 3 separate medical records
5. Doctor completes all 3 consultations
6. Payment request sent: ₦5,000 × 3 = ₦15,000
7. Family pays later

---

## 📊 Comparison Table

| Feature | Single Patient | Multi-Patient |
|---------|----------------|---------------|
| Patients per booking | 1 | 2+ |
| Medical records | 1 | Separate per patient |
| Consultations | 1 | One per patient |
| Billing | Simple | Line items per patient |
| Payment | One amount | Total (sum of all) |
| Doctor workload | 1 consultation | Multiple consultations |
| Invoice | Single line | Multiple lines |
| Fee adjustment | One fee | Per patient |

---

## ✅ Current Status

### **Consultation Fees**
- ✅ Database migration done
- ✅ Admin settings UI updated
- ✅ Discount preview working
- ✅ Controller updated
- ⏳ Frontend consultation form needs fee display

### **Multi-Patient Booking**
- ✅ Database schema complete
- ✅ Models and relationships done
- ✅ Backend logic implemented
- ⏳ Frontend booking form needed
- ⏳ Patient dashboard integration needed

---

## 🚀 Next Steps

### **To Complete Consultation Fees:**
1. ✅ Update ConsultationController to use correct fee based on type
2. ✅ Display fees on consultation form
3. ✅ Update payment initialization with correct amount

### **To Enable Multi-Patient Booking:**
1. Create multi-patient booking form
2. Update patient dashboard with "Book for Family" option
3. Add booking summary/review page
4. Test complete flow

---

## 📝 Important Notes

### **Consultation Fees:**
- Admin sets fees for BOTH types
- Fees can be different or same
- Discount preview helps with pricing strategy
- Applies to ALL patients (unless doctor adjusts individually)

### **Multi-Patient Booking:**
- Each patient = separate medical record
- Clear guardian relationships
- Transparent billing per patient
- Doctor can adjust fees per patient with justification
- All changes logged for audit
- Supports both Pay Now and Pay Later

---

**Last Updated:** December 13, 2025  
**Status:** ✅ Consultation Fees Ready, ⏳ Multi-Patient Booking Framework Ready  
**Branch:** fullap

