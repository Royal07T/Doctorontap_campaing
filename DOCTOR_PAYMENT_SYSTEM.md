# Doctor Payment Management System

## Overview
A comprehensive payment management system that allows doctors to manage their bank accounts and track payments, while giving admins full visibility into doctor profiles, consultations, and payment processing.

## Features Implemented

### 1. Database Structure

#### Doctor Bank Accounts Table (`doctor_bank_accounts`)
- Stores multiple bank accounts per doctor
- Fields: bank_name, account_name, account_number, account_type, bank_code, swift_code
- Verification system (verified by admin)
- Default account selection
- Soft deletes enabled

#### Doctor Payments Table (`doctor_payments`)
- Comprehensive payment tracking
- Fields include:
  - Payment reference (auto-generated)
  - Total consultations amount and count
  - Paid/unpaid consultation breakdown
  - Doctor percentage and platform fee percentage
  - Calculated doctor share and platform fee
  - Payment status (pending, processing, completed, failed, cancelled)
  - Payment method and transaction reference
  - Admin notes and payment notes
  - Period tracking (from/to dates)
  - Array of consultation IDs included in payment

### 2. Models

#### DoctorBankAccount Model
- Relationships with Doctor, AdminUser (who verified), and DoctorPayment
- Methods:
  - `getMaskedAccountNumberAttribute()` - Shows only last 4 digits
  - `setAsDefault()` - Sets account as default and unsets others
  - Scopes: `verified()`, `default()`

#### DoctorPayment Model
- Relationships with Doctor, BankAccount, and AdminUser (who paid)
- Methods:
  - `calculatePayment()` - Static method to calculate payment details from consultations
  - `markAsCompleted()` - Mark payment as completed with admin details
  - `consultations()` - Get consultations included in payment
  - Scopes: `pending()`, `completed()`, `forDoctor()`

#### Doctor Model Updates
- New relationships: `bankAccounts()`, `defaultBankAccount()`, `payments()`, `consultations()`
- Payment calculation attributes:
  - `total_earnings` - Total from paid consultations
  - `pending_earnings` - Unpaid consultation earnings
  - `paid_consultations_count`
  - `unpaid_consultations_count`
  - `total_paid_amount` - Total paid to doctor
- `calculateDoctorShare()` method for custom percentage calculations

### 3. Doctor Features

#### Bank Account Management (`/doctor/bank-accounts`)
- View all bank accounts
- Add new bank accounts with:
  - Bank name, account name, account number
  - Account type (savings/current)
  - Bank code and SWIFT code (optional)
  - Notes
- Set default bank account
- Delete bank accounts (with protection for default account)
- Verification status display
- First account automatically becomes default

####  Payment History Dashboard (`/doctor/payment-history`)
- View all payments received
- Statistics dashboard:
  - Total amount paid
  - Pending earnings
  - Paid consultations count
  - Unpaid consultations count
- Payment details include:
  - Reference number
  - Amount breakdown (total, doctor share, platform fee)
  - Payment status and date
  - Bank account used
  - Admin who processed payment
  - Consultations included

### 4. Admin Features

#### Doctor Profile View (`/admin/doctors/{id}/profile`)
- Complete doctor overview:
  - Personal information
  - All bank accounts with verification status
  - Consultation statistics (total, completed, paid, unpaid)
  - Total paid to doctor
  - Pending payment calculation
- Recent consultations list
- Payment history
- Unpaid consultations ready for payment

#### Bank Account Verification
- Verify doctor bank accounts
- Track who verified and when

#### Doctor Payment Management (`/admin/doctor-payments`)
- View all payments across all doctors
- Filter by:
  - Payment status
  - Doctor
  - Date range
- Statistics:
  - Total payments
  - Pending/completed breakdown
  - Total paid amount
  - Total platform fees collected

#### Create Doctor Payment
- Select doctor
- View unpaid consultations
- Select consultations to include
- Set doctor percentage (default 70%)
- System automatically:
  - Calculates total amount
  - Computes doctor share and platform fee
  - Counts paid/unpaid consultations
  - Links to doctor's default verified bank account

#### Complete Payment
- Mark payment as completed
- Record:
  - Payment method (bank transfer, cash, mobile money, etc.)
  - Transaction reference
  - Payment notes
  - Admin who processed it
  - Timestamp

### 5. Routes Added

#### Doctor Routes (Protected)
```
GET  /doctor/bank-accounts                    - View bank accounts
POST /doctor/bank-accounts                    - Add bank account
PUT  /doctor/bank-accounts/{id}               - Update bank account
POST /doctor/bank-accounts/{id}/set-default   - Set as default
DELETE /doctor/bank-accounts/{id}             - Delete bank account
GET  /doctor/payment-history                  - View payment history
```

#### Admin Routes (Protected)
```
GET  /admin/doctors/{id}/profile              - View doctor profile
POST /admin/doctors/bank-accounts/{id}/verify - Verify bank account
GET  /admin/doctor-payments                   - List all payments
POST /admin/doctor-payments                   - Create new payment
POST /admin/doctor-payments/{id}/complete     - Complete payment
GET  /admin/doctors/{id}/unpaid-consultations - Get unpaid consultations
```

### 6. Payment Calculation Flow

1. **Doctor completes consultations** - Marked as completed in system
2. **Admin reviews unpaid consultations** - Views doctor profile
3. **Admin creates payment** - Selects consultations, sets percentage
4. **System calculates**:
   - Total amount from consultations
   - Doctor share (default 70%)
   - Platform fee (default 30%)
5. **Payment created** - Status: pending
6. **Admin processes payment** - Bank transfer, mobile money, etc.
7. **Payment marked completed** - Records all details
8. **Doctor views payment** - Sees payment in history

### 7. Security Features

- Soft deletes on all tables
- Bank account verification by admin
- Only verified default bank account can receive payments
- Protection against deleting default bank account
- Masked account numbers display
- Admin audit trail (who verified, who paid)
- CSRF protection on all forms
- Proper authentication checks

## Usage Guide

### For Doctors

1. **First Time Setup**
   - Navigate to "Bank Accounts" in sidebar
   - Click "Add Bank Account"
   - Fill in bank details
   - Submit (will be pending verification)

2. **Managing Bank Accounts**
   - Add multiple accounts if needed
   - Set one as default for receiving payments
   - Wait for admin verification

3. **Viewing Payments**
   - Navigate to "Payment History"
   - View all received payments
   - See pending earnings
   - Track consultation payments

### For Admins

1. **Viewing Doctor Details**
   - Go to Doctors list
   - Click on doctor profile
   - View all information: consultations, bank accounts, payments

2. **Verifying Bank Accounts**
   - In doctor profile, find bank accounts section
   - Click "Verify" on bank account
   - System records verification

3. **Creating Payment**
   - Navigate to "Doctor Payments"
   - Click "Create Payment"
   - Select doctor
   - Select consultations to pay
   - Set doctor percentage (if different from default)
   - Submit

4. **Processing Payment**
   - Go to "Doctor Payments"
   - Find pending payment
   - Click "Mark as Completed"
   - Enter payment method and transaction details
   - Submit

## Database Migrations

Run migrations to create tables:
```bash
php artisan migrate
```

This creates:
- `doctor_bank_accounts` table
- `doctor_payments` table

## Configuration

### Default Percentages
In `DoctorPayment::calculatePayment()`, default is:
- Doctor: 70%
- Platform: 30%

Can be customized per payment.

### Payment Status Options
- `pending` - Created but not paid
- `processing` - Payment in progress
- `completed` - Payment successful
- `failed` - Payment failed
- `cancelled` - Payment cancelled

### Payment Methods
Flexible string field. Common values:
- bank_transfer
- cash
- mobile_money
- cheque

## Notes

- First bank account added automatically becomes default
- Only verified bank accounts can receive payments
- System prevents deletion of default account if multiple accounts exist
- All monetary values stored with 2 decimal precision
- Payment references auto-generated with format: DOCPAY-XXXXXXXXXXXX
- Consultation IDs stored as JSON array in payments table
- Platform fees and doctor shares calculated automatically

## Next Steps

To complete the implementation, you need to create these views:
1. `/resources/views/doctor/payment-history.blade.php` - Doctor payment history view
2. `/resources/views/admin/doctor-profile.blade.php` - Admin doctor profile view
3. `/resources/views/admin/doctor-payments.blade.php` - Admin payment management view

Each view should follow the same design pattern as existing views in the application.

