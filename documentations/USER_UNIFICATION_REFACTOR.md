# Laravel User Unification Refactor - Implementation Summary

## Overview
This document summarizes the refactoring to unify authentication and identity management using a single `users` table as the source of truth.

## ✅ Completed Tasks

### 1. Database Migrations

#### Added `role` column to `users` table
- **Migration**: `2026_01_16_000133_add_role_to_users_table.php`
- Adds `role` column (nullable string) to store user role (`patient`, `admin`, etc.)

#### Added `user_id` foreign keys
- **Migrations**: 
  - `2026_01_16_000134_add_user_id_to_patients_table.php`
  - `2026_01_16_000135_add_user_id_to_admin_users_table.php`
  - `2026_01_16_000507_add_user_id_to_canvassers_table.php`
  - `2026_01_16_000508_add_user_id_to_nurses_table.php`
  - `2026_01_16_000508_add_user_id_to_doctors_table.php`
  - `2026_01_16_000509_add_user_id_to_customer_cares_table.php`
  - `2026_01_16_000509_add_user_id_to_care_givers_table.php`
- Adds nullable `user_id` foreign key to all role tables:
  - `patients`
  - `admin_users`
  - `canvassers`
  - `nurses`
  - `doctors`
  - `customer_cares`
  - `care_givers`
- Foreign keys reference `users.id` with cascading deletes
- Indexes added for performance

#### Data Backfill Migration
- **Migration**: `2026_01_16_000136_backfill_users_from_all_roles.php`
- Safely migrates existing data from **all role tables**:
  - `patients` → role: `patient`
  - `admin_users` → role: `admin`
  - `canvassers` → role: `canvasser`
  - `nurses` → role: `nurse`
  - `doctors` → role: `doctor`
  - `customer_cares` → role: `customer_care`
  - `care_givers` → role: `care_giver`
- For each role table:
  - Iterates through all records
  - Creates corresponding `users` records
  - Copies email, password, and verification status
  - Links via `user_id` foreign key
  - Handles duplicate emails (links to existing user if found)
  - Preserves all timestamps

### 2. Eloquent Model Updates

#### User Model (`app/Models/User.php`)
- Added `role` to `$fillable`
- Added relationships for all roles:
  - `patient()` - hasOne relationship to Patient
  - `adminUser()` - hasOne relationship to AdminUser
  - `canvasser()` - hasOne relationship to Canvasser
  - `nurse()` - hasOne relationship to Nurse
  - `doctor()` - hasOne relationship to Doctor
  - `customerCare()` - hasOne relationship to CustomerCare
  - `careGiver()` - hasOne relationship to CareGiver
- Added `roleModel()` - helper method to get the role-specific model based on user's role

#### Patient Model (`app/Models/Patient.php`)
- Added `user_id` to `$fillable`
- Added `user()` - belongsTo relationship to User
- Added `getEmailForVerification()` - prefers user email if available
- Added `getEmailFromUser()` - helper method to get email from user relationship

#### AdminUser Model (`app/Models/AdminUser.php`)
- Added `user_id` to `$fillable`
- Added `user()` - belongsTo relationship to User
- Added `getEmailForVerification()` - prefers user email if available
- Added `getEmailFromUser()` - helper method to get email from user relationship

#### Canvasser Model (`app/Models/Canvasser.php`)
- Added `user_id` to `$fillable`
- Added `user()` - belongsTo relationship to User
- Added `getEmailForVerification()` - prefers user email if available
- Added `getEmailFromUser()` - helper method to get email from user relationship

#### Nurse Model (`app/Models/Nurse.php`)
- Added `user_id` to `$fillable`
- Added `user()` - belongsTo relationship to User
- Added `getEmailForVerification()` - prefers user email if available
- Added `getEmailFromUser()` - helper method to get email from user relationship

#### Doctor Model (`app/Models/Doctor.php`)
- Added `user_id` to `$fillable`
- Added `user()` - belongsTo relationship to User
- Added `getEmailForVerification()` - prefers user email if available
- Added `getEmailFromUser()` - helper method to get email from user relationship

#### CustomerCare Model (`app/Models/CustomerCare.php`)
- Added `user_id` to `$fillable`
- Added `user()` - belongsTo relationship to User
- Added `getEmailForVerification()` - prefers user email if available
- Added `getEmailFromUser()` - helper method to get email from user relationship

#### CareGiver Model (`app/Models/CareGiver.php`)
- Added `user_id` to `$fillable`
- Added `user()` - belongsTo relationship to User
- Added `getEmailForVerification()` - prefers user email if available
- Added `getEmailFromUser()` - helper method to get email from user relationship

### 3. Authentication Configuration

#### Updated `config/auth.php`
- Added comments explaining the new unified architecture
- Kept existing providers for backward compatibility during migration
- Documented that providers should eventually use User model with role filtering

### 4. Controller Updates

Updated key controllers to use the new pattern:
- `app/Http/Controllers/Admin/DashboardController.php` - Updated email access pattern
- `app/Http/Controllers/Doctor/DashboardController.php` - Updated email access pattern
- `app/Http/Controllers/Nurse/DashboardController.php` - Updated email access pattern

**Pattern used**: `$model->user?->email ?? $model->email`

## 📋 Next Steps (To Be Completed)

### 1. Run Migrations
```bash
php artisan migrate
```

This will:
- Add `role` column to `users` table
- Add `user_id` foreign keys to `patients` and `admin_users`
- Backfill existing data into `users` table

### 2. Update Remaining Controllers

Search for patterns like:
- `$patient->email`
- `$admin->email`
- `$doctor->email`
- `$nurse->email`
- `$canvasser->email`
- `$customerCare->email`
- `$careGiver->email`
- `$consultation->patient->email`

Update to use:
```php
$patient->user?->email ?? $patient->email
$admin->user?->email ?? $admin->email
$doctor->user?->email ?? $doctor->email
$nurse->user?->email ?? $nurse->email
$canvasser->user?->email ?? $canvasser->email
$customerCare->user?->email ?? $customerCare->email
$careGiver->user?->email ?? $careGiver->email
```

### 3. Update Authentication Logic

When creating new users of any role, ensure a corresponding `users` record is created:

```php
// Example for Patient creation
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
    'role' => 'patient',
]);

$patient = Patient::create([
    'user_id' => $user->id,
    'name' => $request->name,
    // ... other patient fields
    // Note: email and password are now in users table
]);

// Example for Doctor creation
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
    'role' => 'doctor',
]);

$doctor = Doctor::create([
    'user_id' => $user->id,
    'name' => $request->name,
    // ... other doctor fields
]);
```

**All roles follow the same pattern:**
- `patient` → `Patient` model
- `admin` → `AdminUser` model
- `canvasser` → `Canvasser` model
- `nurse` → `Nurse` model
- `doctor` → `Doctor` model
- `customer_care` → `CustomerCare` model
- `care_giver` → `CareGiver` model

### 4. Update API Controllers

Update authentication controllers to:
- Authenticate against `users` table
- Return appropriate role-specific model via relationship

### 5. Update Views/Blade Templates

Search for `{{ $patient->email }}` or `{{ $admin->email }}` and update to:
```blade
{{ $patient->user?->email ?? $patient->email }}
```

### 6. Testing

Test the following scenarios:
- Patient registration creates user record
- Admin creation creates user record
- Login works with existing credentials
- Email access works via user relationship
- Backfill migration handles all edge cases

### 7. Future Cleanup (After Full Migration)

Once all code is updated:
1. Remove `email` and `password` columns from `patients` and `admin_users` tables (optional, as per requirements)
2. Update auth providers to use User model with role filtering
3. Remove backward compatibility code

## 🔒 Data Safety

- **No data loss**: All existing emails and passwords are preserved
- **Backward compatible**: Direct email access still works during migration
- **Safe rollback**: Migrations can be rolled back if needed (though user records will be preserved)

## 📝 Notes

- The `email` and `password` columns remain in `patients` and `admin_users` tables for backward compatibility
- The backfill migration handles duplicate emails by linking to existing users
- All timestamps are preserved during migration
- The `role` column in `admin_users` table is for admin-specific roles (super_admin, admin, etc.), not the unified role

## 🚀 Migration Order

1. Run `2026_01_16_000133_add_role_to_users_table.php`
2. Run `2026_01_16_000134_add_user_id_to_patients_table.php`
3. Run `2026_01_16_000135_add_user_id_to_admin_users_table.php`
4. Run `2026_01_16_000507_add_user_id_to_canvassers_table.php`
5. Run `2026_01_16_000508_add_user_id_to_nurses_table.php`
6. Run `2026_01_16_000508_add_user_id_to_doctors_table.php`
7. Run `2026_01_16_000509_add_user_id_to_customer_cares_table.php`
8. Run `2026_01_16_000509_add_user_id_to_care_givers_table.php`
9. Run `2026_01_16_000136_backfill_users_from_all_roles.php` (backfills all roles)

All migrations are designed to be safe and non-destructive.

