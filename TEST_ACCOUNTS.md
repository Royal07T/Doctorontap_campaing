# Test Accounts - DoctorOnTap

All test accounts are **automatically verified** and ready to use immediately after seeding.

## 🔐 Demo Accounts

### 1. **Doctor Account**
- 📧 **Email:** `doctor@demo.com`
- 🔑 **Password:** `password`
- 🔗 **Login URL:** `/doctor/login`
- ✅ **Status:** Email verified, Approved, Available
- 👤 **Name:** Dr. John Doe
- 🏥 **Specialization:** General Practice

### 2. **Canvasser Account**
- 📧 **Email:** `canvasser@demo.com`
- 🔑 **Password:** `password`
- 🔗 **Login URL:** `/canvasser/login`
- ✅ **Status:** Email verified, Active
- 👤 **Name:** Demo Canvasser

### 3. **Nurse Account**
- 📧 **Email:** `nurse@demo.com`
- 🔑 **Password:** `password`
- 🔗 **Login URL:** `/nurse/login`
- ✅ **Status:** Email verified, Active
- 👤 **Name:** Demo Nurse

### 4. **Admin Account**
- 📧 **Email:** `admin@doctorontap.com` (or your existing admin email)
- 🔑 **Password:** Your admin password
- 🔗 **Login URL:** `/admin/login`

---

## 🚀 How to Create Test Accounts

### Option 1: Seed All Demo Accounts at Once
```bash
php artisan db:seed
```

### Option 2: Seed Individual Accounts
```bash
# Seed demo doctor
php artisan db:seed --class=DemoDoctorSeeder

# Seed demo canvasser
php artisan db:seed --class=DemoCanvasserSeeder

# Seed demo nurse
php artisan db:seed --class=DemoNurseSeeder
```

### Option 3: Refresh Database & Seed Everything
```bash
php artisan migrate:fresh --seed
```
⚠️ **Warning:** This will delete all existing data!

---

## ✅ What's Pre-Configured

### Doctor Account Features:
- ✅ Email verified automatically
- ✅ Approved by admin
- ✅ Available for consultations
- ✅ Has consultation fee set
- ✅ MDCN license current
- ✅ Can view consultations
- ✅ Can update consultation status

### Canvasser Account Features:
- ✅ Email verified automatically
- ✅ Active status
- ✅ Can register new patients
- ✅ Can view registered patients
- ✅ Can see consultation statistics
- ✅ Can track revenue

### Nurse Account Features:
- ✅ Email verified automatically
- ✅ Active status
- ✅ Can search all patients
- ✅ Can record vital signs (BP, SpO2, Temp, etc.)
- ✅ Can view patient vital history
- ✅ Can track patients attended

---

## 🧪 Testing Workflows

### 1. Test Canvasser Workflow
```bash
1. Login: canvasser@demo.com / password
2. Register a new patient (form on dashboard)
3. View all patients
4. Check statistics (patients registered, consulted, revenue)
```

### 2. Test Nurse Workflow
```bash
1. Login: nurse@demo.com / password
2. Search for a patient
3. Record vital signs (BP, SpO2, Temp, Blood Sugar, etc.)
4. View patient's vital history
5. Check statistics (patients attended, vitals recorded)
```

### 3. Test Doctor Workflow
```bash
1. Login: doctor@demo.com / password
2. View consultations assigned to you
3. Update consultation status
4. Add doctor notes
5. Check dashboard statistics
```

### 4. Test Admin Workflow
```bash
1. Login as admin
2. View all statistics (consultations, canvassers, nurses, patients)
3. See top performing canvassers and nurses
4. Manage canvasser and nurse accounts
5. Oversee all activities
```

---

## 🔧 Troubleshooting

### If accounts don't work:
1. **Run migrations:**
   ```bash
   php artisan migrate
   ```

2. **Seed the accounts:**
   ```bash
   php artisan db:seed --class=DemoDoctorSeeder
   php artisan db:seed --class=DemoCanvasserSeeder
   php artisan db:seed --class=DemoNurseSeeder
   ```

3. **Clear cache:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

### If "Email not verified" error appears:
All demo accounts are automatically verified. If you see this error:
- Check that the seeder ran successfully
- Verify `email_verified_at` is set in the database
- Clear application cache

---

## 📝 Notes

- All demo accounts use the password: `password`
- All accounts are automatically email verified
- Doctor account is approved and available for consultations
- Canvasser and Nurse accounts are active
- These are for testing only - create real accounts for production

---

## 🎯 Quick Test Checklist

- [ ] Login as Doctor ✅
- [ ] Login as Canvasser ✅
- [ ] Login as Nurse ✅
- [ ] Login as Admin ✅
- [ ] Canvasser can register patient ✅
- [ ] Nurse can record vitals ✅
- [ ] Doctor can update consultation ✅
- [ ] Admin can see all statistics ✅

