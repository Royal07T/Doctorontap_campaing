# KoraPay IP Whitelisting - Quick Guide

## 🎯 Quick Steps

### 1. Get Your Server IP
```bash
curl https://api.ipify.org
```
**Copy the IP address** (e.g., `197.210.52.123`)

---

### 2. Login to KoraPay Dashboard
- Go to: https://dashboard.korapay.com
- Login with your merchant account

---

### 3. Navigate to IP Whitelisting

**Path Option 1: Security (Most Common)**
```
Settings → General Business Settings → Security → IP Whitelisting
```

**Path Option 2: API Configuration**
```
Settings → Product & Payment Settings → API Configuration → IP Whitelisting
```

---

### 4. Add Your IP Address

1. Click **"Add IP Address"** or **"+ Add IP"** button
2. Enter your server's IP address (from step 1)
3. Add description (optional): "Production Server"
4. Click **Save** or **Add**

---

### 5. Verify & Test

- ✅ Check IP appears in whitelist
- ✅ Status shows "Active" or "Enabled"
- ✅ Retry payout from admin panel
- ✅ Should work now!

---

## 📍 Exact Location in Dashboard

Based on your KoraPay settings structure:

```
Dashboard
└── Settings ⚙️
    ├── General Business Settings
    │   ├── My Business
    │   ├── Teams
    │   ├── Security 🔒 ← IP Whitelisting is HERE
    │   ├── Notifications
    │   └── Referrals
    │
    └── Product & Payment Settings
        ├── Limits & Product Access
        └── API Configuration 🔑 ← Or possibly HERE
```

**Most Likely Location**: **Settings → General Business Settings → Security**

---

## 🔍 What to Look For

In the **Security** section, you should see:
- IP Whitelisting
- Allowed IPs
- IP Restrictions
- API IP Whitelist
- IP Address Management

---

## ⚠️ If You Can't Find It

1. **Contact KoraPay Support**:
   - Email: support@korapay.com
   - Subject: "Need to whitelist IP for payout API"
   - Include: Your merchant account email and server IP

2. **Check API Configuration**:
   - Settings → Product & Payment Settings → API Configuration
   - Look for IP-related settings

3. **Check Account Type**:
   - Some account types may have IP whitelisting in different locations
   - Business accounts usually have it in Security section

---

## ✅ After Whitelisting

Once your IP is whitelisted:
- Payouts should work immediately
- No more "Please whitelist your IP Address" error
- You can verify by checking the whitelist shows your IP as "Active"

---

## 📞 Need Help?

If you still can't find the IP whitelisting option:
1. Take a screenshot of your Settings page
2. Contact KoraPay support with:
   - Your account email
   - Server IP address
   - Screenshot of Settings page
   - Request: "Please whitelist IP [YOUR_IP] for payout API access"

