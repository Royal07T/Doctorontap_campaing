# Admin Users Management Guide

## 📋 Overview

This guide explains how to manage admin users in the DoctorOnTap platform. The admin users management system allows existing administrators to create, edit, activate/deactivate other admin accounts.

---

## 🔐 Security Features

### 1. **Access Control**
- Only authenticated admins can access the Admin Users page
- Protected by authentication middleware
- Admins cannot deactivate their own accounts

### 2. **Account Status**
- Inactive admins cannot log in
- Active status can be toggled by other admins
- Last login tracking for security monitoring

### 3. **Password Security**
- Minimum 8 characters required
- Passwords are encrypted using bcrypt
- Password confirmation required on creation

---

## 🎯 Features

### **Admin Users Dashboard**
- View all admin users in a paginated list
- See key statistics:
  - Total admin users
  - Active admins
  - Inactive admins
- Track last login times
- Monitor account status

### **Create New Admin**
- Add new admin users via modal form
- Required fields:
  - Full Name
  - Email (must be unique)
  - Password (minimum 8 characters)
  - Password Confirmation
- Optional: Set active status

### **Edit Admin Users**
- Update admin details
- Change name and email
- Reset passwords (optional)
- Toggle active status

### **Activate/Deactivate Admins**
- Quick toggle buttons
- Prevent admins from deactivating themselves
- Inactive admins cannot log in

---

## 📖 How to Use

### **Accessing Admin Users Page**

1. Log in to the admin dashboard
2. Click **"Admin Users"** in the sidebar navigation
3. You'll see the Admin Users management page

### **Creating a New Admin**

1. Click the **"Add New Admin"** button (top right)
2. Fill in the form:
   - **Full Name**: Admin's complete name
   - **Email**: Unique email address
   - **Password**: At least 8 characters
   - **Confirm Password**: Must match password
   - **Is Active**: Check to activate immediately
3. Click **"Create Admin"**
4. The new admin can now log in with their credentials

### **Editing an Admin**

1. Find the admin in the list
2. Click the **"Edit"** button
3. Update the fields you want to change:
   - Change name or email
   - Leave password blank to keep current password
   - Or enter new password to reset it
4. Click **"Update Admin"**

### **Activating/Deactivating an Admin**

1. Find the admin in the list
2. Click **"Deactivate"** (for active admins) or **"Activate"** (for inactive admins)
3. Confirm the action
4. The admin's status will be updated immediately

**Note:** You cannot deactivate your own account.

---

## 🚀 Production Setup

### **Method 1: First Admin via Database**

When you first deploy to production, create your initial admin user:

```bash
# SSH into your production server
ssh user@your-server.com

# Navigate to your Laravel project
cd /path/to/doctorontap

# Open Laravel Tinker
php artisan tinker

# Create the first admin
App\Models\AdminUser::create([
    'name' => 'Super Admin',
    'email' => 'admin@doctorontap.com',
    'password' => bcrypt('YourSecurePassword123!'),
    'is_active' => true
]);

# Exit tinker
exit
```

### **Method 2: First Admin via Database Seeder**

Create a seeder for the first admin:

```bash
php artisan make:seeder FirstAdminSeeder
```

Edit `database/seeders/FirstAdminSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;

class FirstAdminSeeder extends Seeder
{
    public function run(): void
    {
        AdminUser::create([
            'name' => 'Super Admin',
            'email' => 'admin@doctorontap.com',
            'password' => Hash::make('YourSecurePassword123!'),
            'is_active' => true
        ]);
    }
}
```

Run the seeder:

```bash
php artisan db:seed --class=FirstAdminSeeder
```

### **Method 3: After First Admin Created**

Once you have one admin account:

1. Log in with the first admin
2. Go to **Admin Users** page
3. Click **"Add New Admin"**
4. Create additional admin accounts as needed

---

## 🔒 Security Best Practices

### **Password Requirements**
- ✅ Minimum 8 characters
- ✅ Use strong, unique passwords
- ✅ Consider password managers
- ✅ Change default passwords immediately

### **Account Management**
- ✅ Review admin list regularly
- ✅ Deactivate accounts when staff leave
- ✅ Monitor last login times
- ✅ Use unique email addresses

### **Access Control**
- ✅ Only give admin access to trusted staff
- ✅ Create individual accounts (no sharing)
- ✅ Deactivate instead of delete (for audit trail)
- ✅ Review active admins quarterly

---

## 🎨 User Interface

### **Admin Users Page Layout**

**Statistics Cards (Top)**
- Total Admins
- Active Admins
- Inactive Admins

**Admin List Table**
- Name (with avatar initial)
- Email address
- Status badge (Active/Inactive)
- Created date
- Last login timestamp
- Action buttons (Edit, Activate/Deactivate)

**Add New Admin Button**
- Located at top right
- Opens modal form
- Clean, modern design

---

## ⚠️ Important Notes

1. **Cannot Deactivate Yourself**: You cannot deactivate your own admin account for security reasons.

2. **Email Uniqueness**: Each admin must have a unique email address.

3. **Password Reset**: When editing an admin, leave the password field blank to keep the current password.

4. **Inactive Accounts**: Inactive admins cannot log in but their data is preserved.

5. **Last Login Tracking**: Last login time is automatically updated when admins log in.

---

## 🛠 Technical Details

### **Database Table: `admin_users`**

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `name` | string | Admin's full name |
| `email` | string | Unique email address |
| `password` | string | Encrypted password |
| `is_active` | boolean | Active status (default: true) |
| `last_login_at` | timestamp | Last login timestamp (nullable) |
| `remember_token` | string | Remember me token |
| `created_at` | timestamp | Account creation date |
| `updated_at` | timestamp | Last update date |

### **Routes**

```php
// Admin Users Management
Route::get('/admin-users', 'adminUsers')->name('admin-users');
Route::post('/admin-users', 'storeAdminUser')->name('admin-users.store');
Route::put('/admin-users/{id}', 'updateAdminUser')->name('admin-users.update');
Route::post('/admin-users/{id}/toggle-status', 'toggleAdminStatus')->name('admin-users.toggle-status');
```

### **Permissions**
- All routes require admin authentication
- Protected by `auth:admin` middleware
- Only accessible after login

---

## 🎯 Quick Start Checklist

For **first-time production setup**:

- [ ] Deploy application to production server
- [ ] Run migrations: `php artisan migrate`
- [ ] Create first admin using Tinker or Seeder
- [ ] Test first admin login
- [ ] Create additional admin accounts from dashboard
- [ ] Update all default passwords
- [ ] Document admin credentials securely
- [ ] Set up password manager for team

For **ongoing management**:

- [ ] Review admin list monthly
- [ ] Monitor last login times
- [ ] Deactivate former staff accounts
- [ ] Update admin details as needed
- [ ] Audit active admin accounts

---

## 🆘 Troubleshooting

### **"Your account has been deactivated" Error**
**Solution**: An active admin needs to reactivate your account from the Admin Users page.

### **Cannot Create Admin - Email Already Exists**
**Solution**: Email addresses must be unique. Use a different email or update the existing admin.

### **Forgot Admin Password**
**Solution**: Another admin can edit your account and reset your password.

### **Lost All Admin Access**
**Solution**: Use SSH + Tinker to create a new admin account (see Method 1 above).

---

## 📞 Support

For technical issues or questions about admin management:
- Contact your system administrator
- Review this documentation
- Check Laravel logs: `storage/logs/laravel.log`

---

**Last Updated**: October 10, 2025  
**Version**: 1.0.0

