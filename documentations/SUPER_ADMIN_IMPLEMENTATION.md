# 🎯 Super Admin System - Implementation Guide

## ✅ Implementation Complete

This document provides a complete guide to the Super Admin system implementation for DoctorOnTap.

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [What Has Been Implemented](#what-has-been-implemented)
3. [Database Structure](#database-structure)
4. [Getting Started](#getting-started)
5. [Usage Guide](#usage-guide)
6. [Security Features](#security-features)
7. [API Reference](#api-reference)
8. [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

The Super Admin system provides complete oversight and control over the entire DoctorOnTap application. It uses a **role-based approach** (not a separate guard) and follows security best practices.

### Key Features

- ✅ Universal access to all user types and data
- ✅ Comprehensive activity logging
- ✅ User impersonation (with security controls)
- ✅ System health monitoring
- ✅ Audit trail and compliance reporting
- ✅ Rate limiting and security hardening

---

## ✅ What Has Been Implemented

### 1. Database Structure

**Migration Files:**
- `2026_01_11_005120_add_role_fields_to_admin_users_table.php`
- `2026_01_11_005121_create_activity_logs_table.php`

**New Fields in `admin_users`:**
- `role` (ENUM: super_admin, admin, moderator, support)
- `permissions` (JSON, nullable)
- `can_impersonate` (BOOLEAN, default: false)
- `last_impersonation_at` (TIMESTAMP, nullable)

**New Table: `activity_logs`**
- Comprehensive audit trail
- Indexed for performance
- Stores user actions, changes, IP addresses, etc.

### 2. Models

**AdminUser Model:**
- `isSuperAdmin()`: Check if user is super admin
- `hasRole(string $role)`: Check specific role
- `hasPermission(string $permission)`: Check permission
- `activityLogs()`: Relationship to activity logs

**ActivityLog Model:**
- Full model with relationships
- Scopes for filtering
- Proper casting for JSON fields

### 3. Middleware

**SuperAdminMiddleware:**
- Validates admin authentication
- Checks for super_admin role
- Logs access attempts
- Returns proper HTTP status codes

### 4. Services

**ActivityLogService:**
- Centralized logging system
- Automatic IP, user agent, route capture
- Sensitive field masking (PII/PHI)
- Impersonation logging methods

### 5. Controllers

All controllers are in `app/Http/Controllers/SuperAdmin/`:

- **DashboardController**: System-wide statistics
- **UserManagementController**: Unified user management
- **ActivityLogController**: View and export logs
- **SystemHealthController**: Read-only health checks
- **ImpersonationController**: Secure user impersonation

### 6. Routes

All routes are under `/super-admin` prefix:
- Protected with `auth:admin` and `super_admin` middleware
- Rate limiting applied

### 7. Views

All views are in `resources/views/super-admin/`:

- `dashboard.blade.php`: Main dashboard
- `users/index.blade.php`: User management
- `activity-logs/index.blade.php`: Activity logs list
- `activity-logs/show.blade.php`: Log details
- `system-health/index.blade.php`: System health monitoring

---

## 🗄️ Database Structure

### Admin Users Table

```sql
ALTER TABLE admin_users
ADD COLUMN role ENUM('super_admin', 'admin', 'moderator', 'support') DEFAULT 'admin',
ADD COLUMN permissions JSON NULL,
ADD COLUMN can_impersonate BOOLEAN DEFAULT FALSE,
ADD COLUMN last_impersonation_at TIMESTAMP NULL;
```

### Activity Logs Table

```sql
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_type VARCHAR(255) NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(255) NOT NULL,
    model_type VARCHAR(255) NULL,
    model_id BIGINT UNSIGNED NULL,
    changes JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    route VARCHAR(255) NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_user (user_type, user_id),
    INDEX idx_model (model_type, model_id),
    INDEX idx_created_at (created_at),
    INDEX idx_action (action)
);
```

---

## 🚀 Getting Started

### Step 1: Run Migrations

```bash
php artisan migrate
```

### Step 2: Create First Super Admin

**Option A: Using Tinker**

```bash
php artisan tinker
```

```php
$admin = \App\Models\AdminUser::first();
$admin->role = 'super_admin';
$admin->can_impersonate = true;
$admin->save();
```

**Option B: Using a Seeder**

```bash
php artisan make:seeder SuperAdminSeeder
```

```php
// database/seeders/SuperAdminSeeder.php
public function run()
{
    $admin = \App\Models\AdminUser::where('email', 'admin@example.com')->first();
    if ($admin) {
        $admin->update([
            'role' => 'super_admin',
            'can_impersonate' => true,
        ]);
    }
}
```

### Step 3: Access Super Admin Dashboard

1. Login as the super admin user
2. Navigate to: `http://your-domain.com/super-admin/dashboard`

---

## 📖 Usage Guide

### Accessing Super Admin Features

All super admin routes are under `/super-admin`:

- **Dashboard**: `/super-admin/dashboard`
- **User Management**: `/super-admin/users`
- **Activity Logs**: `/super-admin/activity-logs`
- **System Health**: `/super-admin/system-health`

### Logging Activities

Use the `ActivityLogService` to log activities:

```php
use App\Services\ActivityLogService;

$activityLogService = app(ActivityLogService::class);

// Log a simple action
$activityLogService->log('viewed', Consultation::class, $consultation->id);

// Log with changes
$activityLogService->log('updated', Patient::class, $patient->id, [
    'name' => ['Old Name', 'New Name'],
    'email' => ['old@example.com', 'new@example.com'],
]);

// Log impersonation
$activityLogService->logImpersonationStart($userId, 'doctor');
```

### Checking Super Admin Status

```php
$admin = Auth::guard('admin')->user();

if ($admin->isSuperAdmin()) {
    // Super admin logic
}

if ($admin->hasRole('admin')) {
    // Regular admin logic
}

if ($admin->hasPermission('manage_users')) {
    // Permission check
}
```

### User Impersonation

**Start Impersonation:**
```javascript
fetch('/super-admin/impersonate/doctor/123/start', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
})
```

**Check Status:**
```javascript
fetch('/super-admin/impersonate/status')
    .then(response => response.json())
    .then(data => {
        if (data.impersonating) {
            console.log('Currently impersonating:', data.user);
        }
    });
```

**Stop Impersonation:**
```javascript
fetch('/super-admin/impersonate/stop', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
})
```

---

## 🔐 Security Features

### 1. Role-Based Access Control

- Only users with `role = 'super_admin'` can access
- Middleware validates role on every request
- Failed access attempts are logged

### 2. Activity Logging

- **All actions are logged** with full context:
  - User type and ID
  - Action performed
  - Model affected
  - Changes made
  - IP address
  - User agent
  - Route accessed
  - Timestamp

### 3. Sensitive Data Masking

The following fields are automatically masked in logs:
- `password`
- `password_confirmation`
- `token`
- `api_key`
- `secret`
- `ssn`
- `credit_card`
- `bank_account`
- `pin`

### 4. Impersonation Security

- Requires `can_impersonate = true`
- Time-limited (1 hour)
- Full audit trail
- Session-based
- Easy exit mechanism

### 5. Rate Limiting

All super admin routes are rate-limited to prevent abuse.

### 6. Read-Only System Health

System health checks are **read-only** and production-safe:
- No shell execution
- No dangerous PHP functions
- Only safe status checks

---

## 📚 API Reference

### Routes

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/super-admin/dashboard` | Dashboard |
| GET | `/super-admin/users` | User management |
| POST | `/super-admin/users/{type}/{id}/toggle-status` | Toggle user status |
| POST | `/super-admin/users/{type}/{id}/reset-password` | Reset password |
| GET | `/super-admin/activity-logs` | Activity logs |
| GET | `/super-admin/activity-logs/{id}` | Log details |
| GET | `/super-admin/activity-logs/export/csv` | Export logs |
| GET | `/super-admin/system-health` | System health |
| POST | `/super-admin/impersonate/{type}/{id}/start` | Start impersonation |
| POST | `/super-admin/impersonate/stop` | Stop impersonation |
| GET | `/super-admin/impersonate/status` | Impersonation status |

### ActivityLogService Methods

#### `log(string $action, ?string $modelType, ?int $modelId, ?array $changes, ?array $metadata)`

Log a general activity.

**Parameters:**
- `$action`: Action performed (created, updated, deleted, viewed, etc.)
- `$modelType`: Model class name (optional)
- `$modelId`: Model ID (optional)
- `$changes`: Array of changes (optional)
- `$metadata`: Additional context (optional)

**Returns:** `ActivityLog` instance

#### `logImpersonationStart(int $userId, string $userType)`

Log the start of impersonation.

**Parameters:**
- `$userId`: ID of user being impersonated
- `$userType`: Type of user (doctor, patient, etc.)

**Returns:** `ActivityLog` instance

#### `logImpersonationEnd(int $userId, string $userType, int $durationSeconds)`

Log the end of impersonation.

**Parameters:**
- `$userId`: ID of user being impersonated
- `$userType`: Type of user
- `$durationSeconds`: Duration in seconds

**Returns:** `ActivityLog` instance

---

## 🧪 Testing

### Manual Testing Checklist

1. **Access Control**
   - [ ] Regular admin cannot access super admin routes (403)
   - [ ] Unauthenticated user cannot access (401)
   - [ ] Super admin can access all routes

2. **User Management**
   - [ ] Can view all user types
   - [ ] Can toggle user status
   - [ ] Can reset passwords
   - [ ] Actions are logged

3. **Activity Logs**
   - [ ] All actions are logged
   - [ ] Sensitive fields are masked
   - [ ] Filters work correctly
   - [ ] Export works

4. **Impersonation**
   - [ ] Only super admins with `can_impersonate = true` can impersonate
   - [ ] Impersonation is logged
   - [ ] Time limit enforced
   - [ ] Can exit impersonation

5. **System Health**
   - [ ] All checks are read-only
   - [ ] No dangerous operations
   - [ ] Accurate status reporting

---

## 🔧 Configuration

### Middleware Registration

Already registered in `bootstrap/app.php`:

```php
'super_admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
```

### Rate Limiting

Ensure your rate limiting middleware is configured in `bootstrap/app.php`.

### Session Configuration

Impersonation uses sessions. Ensure your session driver is properly configured in `.env`:

```env
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

---

## ⚠️ Important Notes

### 1. First Super Admin

You must manually create the first super admin (see Getting Started).

### 2. Activity Logs Growth

Activity logs can grow large over time. Consider:
- Implementing log rotation
- Archiving old logs
- Setting up automated cleanup

### 3. Security Best Practices

- Never bypass middleware
- Always log sensitive actions
- Review activity logs regularly
- Monitor for suspicious activity
- Keep super admin accounts to a minimum

### 4. Impersonation Guidelines

- Only use impersonation for legitimate support/debugging
- Always exit impersonation when done
- Document why impersonation was used
- Review impersonation logs regularly

---

## 🐛 Troubleshooting

### Issue: Cannot Access Super Admin Routes

**Solution:**
1. Check that user has `role = 'super_admin'`
2. Verify middleware is registered
3. Check route middleware stack
4. Review Laravel logs for errors

### Issue: Activity Logs Not Being Created

**Solution:**
1. Check database connection
2. Verify `activity_logs` table exists
3. Check ActivityLogService is being called
4. Review Laravel logs for errors

### Issue: Impersonation Not Working

**Solution:**
1. Verify `can_impersonate = true`
2. Check session configuration
3. Verify session driver is working
4. Check for session conflicts

### Issue: System Health Shows Errors

**Solution:**
1. Check database connectivity
2. Verify cache configuration
3. Check queue configuration
4. Review server resources

---

## 📁 File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── SuperAdmin/
│   │       ├── DashboardController.php
│   │       ├── UserManagementController.php
│   │       ├── ActivityLogController.php
│   │       ├── SystemHealthController.php
│   │       └── ImpersonationController.php
│   └── Middleware/
│       └── SuperAdminMiddleware.php
├── Models/
│   ├── AdminUser.php (updated)
│   └── ActivityLog.php
└── Services/
    └── ActivityLogService.php

database/migrations/
├── 2026_01_11_005120_add_role_fields_to_admin_users_table.php
└── 2026_01_11_005121_create_activity_logs_table.php

resources/views/
└── super-admin/
    ├── shared/
    │   ├── sidebar.blade.php
    │   └── header.blade.php
    ├── dashboard.blade.php
    ├── users/
    │   └── index.blade.php
    ├── activity-logs/
    │   ├── index.blade.php
    │   └── show.blade.php
    └── system-health/
        └── index.blade.php

routes/
└── web.php (updated with super admin routes)
```

---

## ✅ Implementation Status

- [x] Database migrations
- [x] Models and relationships
- [x] Middleware
- [x] Services
- [x] Controllers
- [x] Routes
- [x] Views
- [x] Security hardening
- [x] Rate limiting
- [x] Documentation

---

## 🎯 Next Steps

1. **Run Migrations**: `php artisan migrate`
2. **Create First Super Admin**: Use Tinker or Seeder
3. **Test Access**: Login and access `/super-admin/dashboard`
4. **Review Logs**: Check activity logs are being created
5. **Configure Rate Limiting**: Ensure it's properly set up
6. **Set Up Log Rotation**: Plan for activity log management

---

## 📞 Support

For issues or questions:
1. Check this documentation
2. Review Laravel logs
3. Check activity logs for errors
4. Verify database and configuration

---

**Last Updated**: January 2026
**Version**: 1.0.0
