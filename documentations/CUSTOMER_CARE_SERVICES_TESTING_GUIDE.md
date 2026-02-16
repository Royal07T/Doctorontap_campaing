# Customer Care Services Module - Testing Guide

## ✅ Implementation Complete

All features have been implemented and are ready for testing.

---

## 📋 Pre-Testing Checklist

### 1. Run Migrations
```bash
php artisan migrate
```

**Expected Output:**
- ✅ `prospects` table created
- ✅ `communication_templates` table created
- ✅ `booked_by_customer_service` and `booked_by_agent_id` added to `consultations`
- ✅ `template_id` added to `patient_communications`

### 2. Verify Database Tables
```bash
php artisan tinker
>>> \App\Models\Prospect::count()
>>> \App\Models\CommunicationTemplate::count()
```

---

## 🧪 Testing Scenarios

### **Test 1: Quick Add Prospect (Silent Lead Capture)**

**Steps:**
1. Login to Customer Care dashboard
2. Click "+ Quick Add Prospect" button (top right)
3. Fill in the form:
   - First Name: John
   - Last Name: Doe
   - Mobile Number: 2347081114942 (Required)
   - Email: john@example.com (Optional)
   - Location: Lagos, Nigeria
   - Source: Call
   - Notes: Interested in consultation
4. Click "Save Prospect (No Account Created)"

**Expected Results:**
- ✅ Success toast: "Prospect saved successfully. No account created."
- ✅ No user account created in `users` table
- ✅ No email sent
- ✅ No notification triggered
- ✅ Prospect record created in `prospects` table with `silent_prospect = true`
- ✅ Status = "New"
- ✅ Redirected to prospects list

**Verify:**
```sql
SELECT * FROM prospects WHERE mobile_number = '2347081114942';
-- Should show: silent_prospect = 1, status = 'New'
```

---

### **Test 2: Prospect List View**

**Steps:**
1. Navigate to Prospects page (sidebar)
2. Test filters:
   - Search by name/mobile/email
   - Filter by status (New, Contacted, Converted, Closed)
3. Verify status badges:
   - New → Blue
   - Contacted → Amber
   - Converted → Green
   - Closed → Gray

**Expected Results:**
- ✅ All prospects displayed in table
- ✅ Filters work correctly
- ✅ Status badges display with correct colors
- ✅ Row actions visible (View, Mark as Contacted, Convert to Patient)

---

### **Test 3: Convert Prospect to Patient**

**Steps:**
1. Go to Prospects list
2. Click "Convert to Patient" on a prospect with status "New"
3. Review warning message
4. Check the confirmation checkbox
5. Click "Convert to Patient"

**Expected Results:**
- ✅ Warning displayed: "This will create a real patient account..."
- ✅ User account created in `users` table
- ✅ Patient profile created in `patients` table
- ✅ Onboarding email sent (check logs)
- ✅ Prospect status updated to "Converted"
- ✅ Redirected to patient profile page
- ✅ Audit log entry created

**Verify:**
```sql
SELECT * FROM users WHERE phone = '2347081114942';
SELECT * FROM patients WHERE phone = '2347081114942';
SELECT * FROM prospects WHERE mobile_number = '2347081114942';
-- Status should be 'Converted'
```

---

### **Test 4: Book Consultation on Behalf of Patient**

**Steps:**
1. Navigate to a patient profile (or converted prospect)
2. Click "New Service Request" button
3. Fill in booking form:
   - Service Type: Video Consultation
   - Select Doctor: Choose an available doctor
   - Select Date: Choose a future date
   - Select Time: Choose available time slot
   - Medical Problem: "Patient experiencing headaches"
   - Severity: Moderate
   - Age: 35
   - Gender: Male
4. Click "Book Consultation"

**Expected Results:**
- ✅ Consultation created with `booked_by_customer_service = true`
- ✅ `booked_by_agent_id` set to current agent ID
- ✅ `customer_care_id` set to current agent ID
- ✅ Consultation status = "scheduled"
- ✅ Time slot conflict prevented if already booked
- ✅ Success message with consultation reference
- ✅ Redirected to consultation details page
- ✅ Audit log entry created

**Verify:**
```sql
SELECT * FROM consultations WHERE booked_by_customer_service = 1 ORDER BY id DESC LIMIT 1;
-- Should show: booked_by_customer_service = 1, booked_by_agent_id = [agent_id]
```

---

### **Test 5: Book Consultation for Prospect (Must Convert First)**

**Steps:**
1. Try to book service for a prospect with status "New"
2. Should see warning: "Prospect must be converted to patient before booking"

**Expected Results:**
- ✅ Cannot book for non-converted prospect
- ✅ Warning message displayed
- ✅ Redirected to convert page

---

### **Test 6: Template-Based Communication (NO FREE TEXT)**

**Steps:**
1. **First, create templates as Super Admin:**
   - Login as Super Admin
   - Navigate to "Comm Templates" in sidebar
   - Click "+ New Template"
   - Create SMS template:
     - Name: Welcome SMS
     - Channel: SMS
     - Body: "Hello {{first_name}}, welcome to DoctorOnTap! Your consultation is scheduled."
   - Create Email template:
     - Name: Consultation Reminder
     - Channel: Email
     - Subject: "Reminder: Your Consultation"
     - Body: "Dear {{name}}, this is a reminder about your upcoming consultation."
   - Mark as Active

2. **Test from Customer Care Dashboard:**
   - Login to Customer Care
   - Go to Dashboard
   - Search for a patient
   - Select patient
   - Choose channel (SMS/Email/WhatsApp)
   - **Verify:** Only template dropdown appears (NO free text field)
   - Select a template
   - Preview shows with variables replaced
   - Click "Send Message"

**Expected Results:**
- ✅ No free text textarea visible
- ✅ Only template selection dropdown
- ✅ Template preview shows with sample data
- ✅ Message sent using template
- ✅ Variables replaced with actual patient data
- ✅ Audit log entry created with template_id
- ✅ Communication logged in `patient_communications` table

**Verify:**
```sql
SELECT * FROM patient_communications WHERE template_id IS NOT NULL ORDER BY id DESC LIMIT 1;
-- Should show template_id and content from template
```

---

### **Test 7: Super Admin Template Management**

**Steps:**
1. Login as Super Admin
2. Navigate to "Comm Templates" in sidebar
3. View template list with stats
4. Create new template:
   - Name: Appointment Confirmation
   - Channel: SMS
   - Body: "Hi {{first_name}}, your appointment is confirmed for {{date}}. Reference: {{reference}}"
5. Edit template
6. Toggle active/inactive status
7. View template details with preview
8. Delete template (if needed)

**Expected Results:**
- ✅ All CRUD operations work
- ✅ Variable detection works automatically
- ✅ Preview shows sample output
- ✅ Status toggle works
- ✅ Only Super Admin can access (middleware protection)

---

### **Test 8: Settings Page with Logout**

**Steps:**
1. Login to Customer Care
2. Click "Settings" in sidebar (replaces old Logout link)
3. Verify sections:
   - Profile (Name, Email, Phone)
   - Preferences
   - Security
   - Session (with Logout button)
4. Click Logout

**Expected Results:**
- ✅ Settings page displays correctly
- ✅ Logout button works
- ✅ Redirected to login page

---

### **Test 9: Sidebar Updates**

**Steps:**
1. Login to Customer Care
2. Check sidebar navigation

**Expected Results:**
- ✅ "Interactions" link removed
- ✅ "Prospects" link added
- ✅ "Settings" link added (replaces Logout)
- ✅ All other links intact

---

### **Test 10: Dashboard Updates**

**Steps:**
1. Login to Customer Care dashboard
2. Check for:
   - Quick Add Prospect button
   - Stats cards (Prospects instead of Interactions)
   - No interactions references

**Expected Results:**
- ✅ Quick Add button visible and functional
- ✅ No errors about missing interactions routes
- ✅ Dashboard loads successfully

---

## 🔍 Verification Queries

### Check Prospects
```sql
SELECT id, first_name, last_name, mobile_number, status, silent_prospect, created_by 
FROM prospects 
ORDER BY created_at DESC 
LIMIT 10;
```

### Check Booked Consultations
```sql
SELECT id, reference, patient_id, doctor_id, booked_by_customer_service, booked_by_agent_id, customer_care_id, scheduled_at
FROM consultations 
WHERE booked_by_customer_service = 1 
ORDER BY created_at DESC 
LIMIT 10;
```

### Check Templates
```sql
SELECT id, name, channel, active, created_by 
FROM communication_templates 
ORDER BY created_at DESC;
```

### Check Template-Based Communications
```sql
SELECT id, patient_id, type, template_id, content, created_by 
FROM patient_communications 
WHERE template_id IS NOT NULL 
ORDER BY created_at DESC 
LIMIT 10;
```

---

## 🐛 Common Issues & Solutions

### Issue 1: "Table 'prospects' doesn't exist"
**Solution:** Run migrations: `php artisan migrate`

### Issue 2: "Route not defined: customer-care.interactions.index"
**Solution:** Already fixed - interactions routes commented out, references removed

### Issue 3: "Template not found" when sending message
**Solution:** Create at least one active template for the channel (SMS/Email/WhatsApp)

### Issue 4: Cannot book for prospect
**Solution:** Prospect must be converted to patient first (status = "Converted")

### Issue 5: Free text still visible in communication
**Solution:** Clear browser cache, verify template selection UI is showing

---

## ✅ Success Criteria

All tests should pass:
- ✅ Prospects can be created silently (no account, no emails)
- ✅ Prospects can be converted to patients (triggers account creation)
- ✅ Consultations can be booked on behalf of patients
- ✅ Communication is template-based only (no free text)
- ✅ Super Admin can manage templates
- ✅ All audit logs are created
- ✅ Dashboard loads without errors
- ✅ Sidebar navigation updated correctly

---

## 📝 Notes

- **Silent Prospect Creation:** No user account, no emails, no notifications
- **Template Enforcement:** Customer Care cannot send free-form messages
- **Booking on Behalf:** Only works for patients (prospects must be converted first)
- **Audit Logging:** All actions are logged with agent ID and timestamp
- **Permissions:** Only Super Admin can create/edit templates

---

## 🎯 Next Steps After Testing

1. Create initial communication templates for common scenarios
2. Train Customer Care agents on new workflow
3. Monitor audit logs for compliance
4. Gather feedback for improvements

---

**Last Updated:** {{ date('Y-m-d H:i:s') }}

