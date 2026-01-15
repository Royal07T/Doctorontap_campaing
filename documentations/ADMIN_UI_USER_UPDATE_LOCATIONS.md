# Admin UI - Where to Update User Information

## Overview
This document shows where admins can update user information (email, password, name) in the UI after the unified user system implementation.

## 🎯 Locations in the UI

### 1. Admin Users Management (`/admin/admin-users`)

**Location:** Admin Dashboard → Admin Users

**What you can update:**
- ✅ Name
- ✅ Email
- ✅ Password
- ✅ Active Status

**How to access:**
1. Navigate to **Admin Dashboard**
2. Click on **"Admin Users"** in the sidebar
3. Click the **"Edit"** button (pencil icon) on any admin user card
4. Update the information in the modal
5. Click **"Update Admin"**

**Route:** `PUT /admin/admin-users/{id}`

**Controller:** `Admin\DashboardController@updateAdminUser` (uses UnifiedUserUpdateService)

---

### 2. Super Admin User Management (`/super-admin/users`)

**Location:** Super Admin Dashboard → User Management

**What you can update:**
- ✅ Password (via Reset Password modal)
- ✅ Active Status (via toggle button)
- ⚠️ Email/Name (coming soon - see below)

**How to access:**
1. Navigate to **Super Admin Dashboard**
2. Click on **"User Management"** in the sidebar
3. Select user type (Admin, Doctor, Patient, etc.)
4. Use **"Reset Password"** button to update password
5. Use **"Activate/Deactivate"** button to toggle status

**Routes:**
- `POST /super-admin/users/{type}/{id}/reset-password`
- `POST /super-admin/users/{type}/{id}/toggle-status`

**Controller:** `SuperAdmin\UserManagementController` (uses UnifiedUserUpdateService)

---

### 3. Patient Management (`/admin/patients`)

**Location:** Admin Dashboard → Patients

**Current Status:** View-only interface

**Note:** Patient updates should be done through the API or by adding an edit modal similar to admin-users.

---

### 4. Doctor Management (`/admin/doctors`)

**Location:** Admin Dashboard → Doctors

**Current Status:** View-only interface with profile page

**Note:** Doctor updates can be done through the doctor profile page or by adding an edit modal.

---

## 🔧 How It Works

### Unified Update Process

When an admin updates user information through the UI:

1. **Form Submission** → Sends data to controller
2. **Controller** → Calls `UnifiedUserUpdateService`
3. **Service** → Updates both:
   - `users` table (email, password, name)
   - Role-specific table (patients, admin_users, etc.)
4. **Response** → Returns success/error message

### Email Updates

- ✅ Email is validated for uniqueness in `users` table
- ✅ If email exists for another user → Error shown
- ✅ If email exists for same user → Update allowed
- ✅ Both `users` and role table are updated

### Password Updates

- ✅ Password is automatically hashed
- ✅ Password confirmation is required
- ✅ Minimum 8 characters required
- ✅ Both `users` and role table are updated

---

## 📝 Adding Edit Functionality to Other Pages

### For Super Admin Users Page

To add email/name editing to the super-admin users page:

1. Add an "Edit" button in the actions column
2. Create an edit modal similar to admin-users.blade.php
3. Add JavaScript to handle form submission
4. Use the existing `updateUser` method in `UserManagementController`

**Example route to add:**
```php
Route::put('/super-admin/users/{type}/{id}', [UserManagementController::class, 'updateUser'])
    ->name('super-admin.users.update');
```

### For Patient/Doctor Pages

Similar approach:
1. Add edit button to patient/doctor cards
2. Create edit modal
3. Use `UnifiedUserUpdateService` directly or via controller method

---

## 🎨 UI Components

### Edit Modal Structure

```html
<!-- Edit Modal -->
<div id="editUserModal" class="modal">
    <form id="editUserForm">
        <input type="text" name="name" required>
        <input type="email" name="email" required>
        <input type="password" name="password" placeholder="Leave blank to keep current">
        <input type="password" name="password_confirmation">
        <button type="submit">Update User</button>
    </form>
</div>
```

### JavaScript Example

```javascript
async function updateUser(type, id, formData) {
    const response = await fetch(`/super-admin/users/${type}/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(formData)
    });
    
    const data = await response.json();
    
    if (data.message) {
        alert(data.message);
        location.reload();
    }
}
```

---

## ✅ Current Implementation Status

| Page | Edit Email | Edit Name | Edit Password | Toggle Status |
|------|------------|-----------|---------------|---------------|
| Admin Users (`/admin/admin-users`) | ✅ | ✅ | ✅ | ✅ |
| Super Admin Users (`/super-admin/users`) | ⚠️ | ⚠️ | ✅ | ✅ |
| Patients (`/admin/patients`) | ❌ | ❌ | ❌ | ❌ |
| Doctors (`/admin/doctors`) | ❌ | ❌ | ❌ | ❌ |
| Canvassers (`/admin/canvassers`) | ❌ | ❌ | ❌ | ❌ |
| Nurses (`/admin/nurses`) | ❌ | ❌ | ❌ | ❌ |
| Customer Care (`/admin/customer-cares`) | ❌ | ❌ | ❌ | ❌ |
| Care Givers (`/admin/care-givers`) | ❌ | ❌ | ❌ | ❌ |

**Legend:**
- ✅ Fully implemented
- ⚠️ Partially implemented (password only)
- ❌ Not yet implemented

---

## 🚀 Quick Access Links

### Admin Dashboard
- **Admin Users:** `/admin/admin-users`
- **Patients:** `/admin/patients`
- **Doctors:** `/admin/doctors`
- **Canvassers:** `/admin/canvassers`
- **Nurses:** `/admin/nurses`
- **Customer Care:** `/admin/customer-cares`
- **Care Givers:** `/admin/care-givers`

### Super Admin Dashboard
- **User Management:** `/super-admin/users`
- **Activity Logs:** `/super-admin/activity-logs`

---

## 📚 Related Documentation

- `documentations/ADMIN_USER_UPDATE_GUIDE.md` - Technical guide for updating users
- `documentations/USER_UNIFICATION_REFACTOR.md` - Architecture overview
- `app/Services/UnifiedUserUpdateService.php` - Service implementation
- `app/Http/Controllers/SuperAdmin/UserManagementController.php` - Controller methods

---

## 💡 Best Practices

1. **Always validate** email uniqueness before updating
2. **Use the UnifiedUserUpdateService** for all email/password updates
3. **Show clear error messages** if email already exists
4. **Log all updates** using ActivityLogService
5. **Require password confirmation** for password changes
6. **Show success messages** after successful updates

---

## 🔐 Security Notes

- All password updates are automatically hashed
- Email uniqueness is enforced across all user types
- Updates are logged for audit purposes
- CSRF protection is enabled on all forms
- Only authorized admins can update user information

