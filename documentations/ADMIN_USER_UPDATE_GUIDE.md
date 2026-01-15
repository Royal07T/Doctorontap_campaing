# Admin User Update Guide - Unified User System

## Overview

In the unified user system, user identity data (email, password, name) is stored in the `users` table, while role-specific data is stored in role tables (patients, admin_users, doctors, etc.). This guide explains how admins can update user information.

## Architecture

- **`users` table**: Source of truth for email, password, name, role
- **Role tables** (`patients`, `admin_users`, `doctors`, etc.): Store role-specific data
- **Relationship**: Role tables have `user_id` foreign key linking to `users.id`

## How to Update User Information

### 1. Using UnifiedUserUpdateService (Recommended)

The `UnifiedUserUpdateService` handles updating both the `users` table and role-specific tables automatically.

#### Example: Update User Email

```php
use App\Services\UnifiedUserUpdateService;

$service = app(UnifiedUserUpdateService::class);

// Update email for a patient
$result = $service->updateEmail('patient', $patientId, 'newemail@example.com');

// Update email for a doctor
$result = $service->updateEmail('doctor', $doctorId, 'newemail@example.com');
```

#### Example: Update Multiple Fields

```php
$result = $service->updateUser('patient', $patientId, [
    'name' => 'New Name',
    'email' => 'newemail@example.com',
    'password' => 'newpassword123',
    // Role-specific fields can also be included
    'phone' => '1234567890',
    'gender' => 'male',
]);
```

### 2. Using API Endpoints

#### Update User Information

```http
PUT /api/admin/users/{type}/{id}
Content-Type: application/json

{
    "name": "Updated Name",
    "email": "updated@example.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Available types:**
- `patient`
- `admin`
- `doctor`
- `nurse`
- `canvasser`
- `customer_care`
- `care_giver`

#### Update Email Only

```http
PUT /api/admin/users/{type}/{id}/email
Content-Type: application/json

{
    "email": "newemail@example.com"
}
```

#### Reset Password

```http
PUT /api/admin/users/{type}/{id}/password
Content-Type: application/json

{
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

### 3. Direct Controller Usage

#### In SuperAdmin/UserManagementController

```php
// Update any user
$controller = new \App\Http\Controllers\SuperAdmin\UserManagementController(
    app(\App\Services\ActivityLogService::class),
    app(\App\Services\UnifiedUserUpdateService::class)
);

$request = new \Illuminate\Http\Request([
    'name' => 'New Name',
    'email' => 'newemail@example.com',
]);

$response = $controller->updateUser($request, 'patient', $patientId);
```

## What Gets Updated Where

### Users Table (Source of Truth)
- ✅ `email` - Updated here first
- ✅ `password` - Updated here first
- ✅ `name` - Updated here first
- ✅ `role` - Set automatically based on role type

### Role Tables (Role-Specific Data)
- ✅ `name` - Also updated here for backward compatibility
- ✅ Role-specific fields (phone, gender, specialization, etc.)
- ✅ `user_id` - Linked automatically if not already linked

## Important Considerations

### 1. Email Uniqueness

The service checks email uniqueness in the `users` table:
- If email exists for a different user → Returns error
- If email exists for the same user → Update allowed
- If email is new → Creates/updates user record

### 2. Password Updates

- Passwords are automatically hashed
- If password is not provided, it won't be updated
- Password updates require confirmation field

### 3. User Record Creation

If a role-specific record doesn't have a linked `user_id`:
- The service automatically creates a `users` record
- Links the role record to the new user
- Sets the appropriate role

### 4. Transaction Safety

All updates are wrapped in database transactions:
- If any part fails, entire update is rolled back
- Ensures data consistency between `users` and role tables

## Code Examples

### Update Patient Email

```php
use App\Services\UnifiedUserUpdateService;

$service = app(UnifiedUserUpdateService::class);

$result = $service->updateEmail('patient', 123, 'patient@example.com');

if ($result['success']) {
    echo "Email updated: " . $result['user']->email;
} else {
    echo "Error: " . $result['message'];
}
```

### Update Doctor Information

```php
$result = $service->updateUser('doctor', $doctorId, [
    'name' => 'Dr. John Doe',
    'email' => 'doctor@example.com',
    'specialization' => 'Cardiology', // Role-specific field
    'phone' => '1234567890', // Role-specific field
]);

// Access updated data
$user = $result['user']; // User model
$doctor = $result['roleModel']; // Doctor model
```

### Update Admin User (via DashboardController)

```php
// In Admin/DashboardController
$request = new Request([
    'name' => 'Admin Name',
    'email' => 'admin@example.com',
    'password' => 'newpassword',
    'password_confirmation' => 'newpassword',
    'is_active' => true,
]);

$response = $this->updateAdminUser($request, $adminId);
```

## Validation Rules

### Email
- Required format: valid email address
- Must be unique in `users` table (except for same user)
- Max length: 255 characters

### Password
- Minimum length: 8 characters
- Must be confirmed (password_confirmation field)
- Automatically hashed before storage

### Name
- Required
- Max length: 255 characters
- Updated in both `users` and role tables

## Error Handling

The service returns a structured response:

```php
[
    'success' => true/false,
    'message' => 'Success or error message',
    'user' => User|null, // Updated User model
    'roleModel' => Model|null, // Updated role-specific model
]
```

### Common Errors

1. **Email already exists**: Another user has this email
2. **User not found**: Role-specific record doesn't exist
3. **Validation failed**: Invalid input data
4. **Database error**: Transaction rollback occurred

## Best Practices

1. **Always use UnifiedUserUpdateService** for email/password updates
2. **Check the result** before assuming success
3. **Log updates** using ActivityLogService for audit trail
4. **Validate input** before calling the service
5. **Handle errors gracefully** with user-friendly messages

## Migration Path

For existing code that updates users directly:

### Before (Old Way)
```php
$patient = Patient::find($id);
$patient->email = 'new@example.com';
$patient->save();
```

### After (Unified Way)
```php
$service = app(UnifiedUserUpdateService::class);
$result = $service->updateEmail('patient', $id, 'new@example.com');
```

## Testing

When testing user updates:

1. Test email uniqueness validation
2. Test password hashing
3. Test transaction rollback on errors
4. Test user record creation for unlinked records
5. Test role-specific field updates

## Support

For questions or issues with user updates, refer to:
- `app/Services/UnifiedUserUpdateService.php` - Service implementation
- `app/Http/Controllers/SuperAdmin/UserManagementController.php` - Controller examples
- `documentations/USER_UNIFICATION_REFACTOR.md` - Architecture overview

