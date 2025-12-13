# Doctor Payment Percentage Configuration

## ✅ Feature Added: Configurable Payment Percentage

The admin can now set and adjust the default payment percentage split between doctors and the platform.

---

## 📍 Where to Configure

**Admin Dashboard → Settings → Pricing Settings**

---

## 🎯 How It Works

### 1. Default Percentage Setting

In the **Settings** page, you'll find:

**Doctor Payment Percentage (%)**
- Input field with a percentage value (0-100)
- Default value: **70%** (doctor gets 70%, platform gets 30%)
- Real-time preview showing the split
- Applies to all new payments by default

### 2. Per-Payment Override

When creating a payment in **Doctor Payments** page:
- The system pre-fills with the default percentage from settings
- Admin can override this for specific payments
- Useful for special cases or negotiations

---

## 💡 Configuration Examples

### Example 1: Standard Split (70/30)
```
Doctor Payment Percentage: 70%
Result:
- Doctor receives: 70%
- Platform fee: 30%

On ₦10,000 consultation:
- Doctor: ₦7,000
- Platform: ₦3,000
```

### Example 2: Higher Doctor Share (80/20)
```
Doctor Payment Percentage: 80%
Result:
- Doctor receives: 80%
- Platform fee: 20%

On ₦10,000 consultation:
- Doctor: ₦8,000
- Platform: ₦2,000
```

### Example 3: Equal Split (50/50)
```
Doctor Payment Percentage: 50%
Result:
- Doctor receives: 50%
- Platform fee: 50%

On ₦10,000 consultation:
- Doctor: ₦5,000
- Platform: ₦5,000
```

---

## 📋 Step-by-Step Guide

### Setting the Default Percentage

1. **Login to Admin Dashboard**
   ```
   Navigate to: Admin → Settings
   ```

2. **Find Pricing Settings Section**
   - Look for "Doctor Payment Percentage (%)"
   - Currently shows: 70% (default)

3. **Adjust the Percentage**
   - Enter your desired percentage (0-100)
   - See real-time preview of the split
   - Example: Enter 75 for 75% doctor / 25% platform

4. **Save Settings**
   - Click "Save Settings" button
   - New percentage applies to all future payments

### Using Custom Percentage for Specific Payment

1. **Navigate to Doctor Payments**
   ```
   Admin → Doctor Payments → Create Payment
   ```

2. **Select Doctor and Consultations**
   - Choose the doctor
   - Select consultations to include

3. **Adjust Percentage (Optional)**
   - The field shows the default percentage
   - Modify if needed for this specific payment
   - System recalculates amounts in real-time

4. **Create Payment**
   - Review the payment summary
   - Click "Create Payment"

---

## 🔍 Real-Time Calculations

### In Settings Page

When you change the percentage, you'll see:
```
Doctor Share: 75%
Platform Fee: 25%
```

### In Payment Creation

The payment summary updates automatically:
```
Selected Consultations: 10
Total Amount: ₦50,000
Doctor Share (75%): ₦37,500
Platform Fee (25%): ₦12,500
```

---

## 📊 Where the Percentage is Used

### 1. Settings Page
- ✅ Configure default percentage
- ✅ See real-time preview

### 2. Payment Creation
- ✅ Pre-filled with default
- ✅ Overridable per payment
- ✅ Real-time calculations

### 3. Payment Records
- ✅ Stored per payment
- ✅ Visible in payment history
- ✅ Audit trail maintained

---

## 🔒 Permission & Access

**Only Admin users can:**
- Configure the default percentage
- Override percentage per payment
- View all payment breakdowns

**Doctors can:**
- See their payment share in history
- View percentage used for each payment
- Cannot modify percentages

---

## 💰 Business Scenarios

### Scenario 1: Standard Practice
```
Setting: 70% doctor / 30% platform
Use for: Regular doctors
```

### Scenario 2: Senior Specialists
```
Override to: 80% doctor / 20% platform
Use for: Highly experienced doctors
Create payment with custom percentage
```

### Scenario 3: New Doctors
```
Override to: 60% doctor / 40% platform
Use for: Probation period
Revert to standard after evaluation
```

### Scenario 4: Promotional Period
```
Temporarily set to: 75% doctor / 25% platform
Update in settings
All new payments use new rate
Revert when promotion ends
```

---

## 🎨 UI Features

### Settings Page
- Clean input field with percentage indicator
- Real-time split preview
- Color-coded display (purple theme)
- Help text explaining the split

### Payment Creation
- Pre-filled with default
- Easy to modify
- Live calculation updates
- Clear summary display

---

## 📈 Tracking & Reports

### For Each Payment
The system records:
- Doctor percentage used
- Platform percentage
- Exact amounts calculated
- Date and time
- Admin who created it

### Statistics
View in payment history:
- Total paid to doctors
- Platform fees collected
- Average percentage used
- Payment trends

---

## 🛠️ Technical Details

### Database
- Setting key: `doctor_payment_percentage`
- Type: decimal
- Default: 70
- Range: 0-100

### Validation
- Minimum: 0%
- Maximum: 100%
- Step: 0.01 (allows decimals)
- Required field

### Calculations
```
doctor_amount = total × (doctor_percentage / 100)
platform_fee = total × (platform_percentage / 100)
platform_percentage = 100 - doctor_percentage
```

---

## 🚀 Quick Actions

### To Change Default Split
```
1. Settings → Scroll to "Doctor Payment Percentage"
2. Change value (e.g., 75)
3. Save Settings
4. Done! All new payments use 75%
```

### To Override for One Payment
```
1. Doctor Payments → Create Payment
2. Select doctor & consultations
3. Modify "Doctor Percentage" field
4. Create Payment
5. This payment uses custom percentage
```

### To View Payment Split
```
1. Doctor Payments → Find payment
2. Click "View"
3. See breakdown with percentages
```

---

## 📝 Best Practices

1. **Set a Fair Default**
   - Consider industry standards
   - Balance doctor satisfaction with platform costs
   - Review periodically

2. **Document Special Cases**
   - Note why custom percentages used
   - Use payment notes field
   - Maintain consistency

3. **Communicate Clearly**
   - Inform doctors of their percentage
   - Explain platform fees
   - Be transparent

4. **Review Regularly**
   - Monitor doctor satisfaction
   - Track platform costs
   - Adjust if needed

---

## ✨ Benefits

### For Platform
- ✅ Flexible pricing model
- ✅ Easy adjustment without code changes
- ✅ Special deals for VIP doctors
- ✅ Promotional campaigns

### For Doctors
- ✅ Clear understanding of earnings
- ✅ See exact breakdown
- ✅ Transparent system
- ✅ Predictable income

### For Admins
- ✅ Easy configuration
- ✅ No technical knowledge needed
- ✅ Real-time preview
- ✅ Complete control

---

## 📞 Summary

**Default Configuration**: 70% doctor / 30% platform
**Adjustable**: Yes, in Settings
**Per-Payment Override**: Yes, in payment creation
**Range**: 0-100%
**Decimal Support**: Yes (e.g., 72.5%)

**The system is now fully flexible for any business model!** 🎉

---

*Configuration guide updated: December 13, 2025*

