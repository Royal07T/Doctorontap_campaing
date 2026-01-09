# KoraPay IP Whitelisting Guide

## 🔒 Error: "Please whitelist your IP Address to continue"

This error occurs because **KoraPay requires your server's IP address to be whitelisted** before allowing payout/disbursement operations. This is a security feature to prevent unauthorized API access.

---

## 🔍 What This Means

KoraPay's API has IP whitelisting enabled for your account, which means:
- Only requests from whitelisted IP addresses are allowed
- Your production server's IP is not currently whitelisted
- You need to add it to KoraPay dashboard

---

## ✅ How to Fix

### Step 1: Find Your Server's IP Address

#### Option A: From Your Server (Recommended)

```bash
# Get public IP address
curl -s https://api.ipify.org
# or
curl -s https://ifconfig.me
# or
curl -s https://icanhazip.com
```

#### Option B: From DirectAdmin/cPanel

1. Login to your hosting control panel (DirectAdmin/cPanel)
2. Look for **Server Information** or **IP Address** section
3. Note the **Public IP Address** or **Dedicated IP**

#### Option C: From Laravel Application

Create a temporary route to check:

```php
// In routes/web.php (temporary - remove after use)
Route::get('/check-ip', function() {
    return [
        'server_ip' => request()->server('SERVER_ADDR'),
        'public_ip' => file_get_contents('https://api.ipify.org'),
        'remote_ip' => request()->ip(),
    ];
});
```

Then visit: `https://yourdomain.com/check-ip`

**⚠️ Remove this route after getting the IP!**

---

### Step 2: Whitelist IP in KoraPay Dashboard

1. **Login to KoraPay Dashboard**
   - Go to: https://dashboard.korapay.com
   - Login with your merchant account

2. **Navigate to Settings**
   - Click on **Settings** (gear icon or from menu)
   - You'll see these sections:
     - General Business Settings
     - Product & Payment Settings
     - Appearance Settings

3. **Find IP Whitelisting**
   
   **Option A: Security Section (Most Likely)**
   - Under **General Business Settings**
   - Click on **Security**
   - Look for **IP Whitelisting**, **Allowed IPs**, or **IP Restrictions**
   - This is where you manage IP addresses for API access

   **Option B: API Configuration**
   - Under **Product & Payment Settings**
   - Click on **API Configuration**
   - Look for **IP Whitelisting** or **API IP Restrictions**
   - This section manages API keys and IP restrictions

4. **Add Your Server IP**
   - Click **Add IP Address**, **Whitelist IP**, or **+ Add IP**
   - Enter your server's public IP address (from Step 1)
   - Optionally add a description (e.g., "Production Server")
   - Click **Save** or **Add**

5. **Wait for Activation**
   - IP whitelisting usually takes effect immediately
   - Some accounts may require a few minutes
   - Check KoraPay dashboard for confirmation
   - You should see your IP listed in the whitelist

---

## 🔧 Alternative: Disable IP Whitelisting (Not Recommended)

If you have a dynamic IP or multiple servers, you can:

1. **Contact KoraPay Support**
   - Email: support@korapay.com
   - Request to disable IP whitelisting for your account
   - Explain your use case (multiple servers, dynamic IP, etc.)

2. **Use API Key Restrictions Instead**
   - Some accounts allow disabling IP whitelist
   - Rely on API key security only
   - Requires KoraPay approval

---

## 📋 Step-by-Step Instructions

### For Production Server:

1. **SSH into your server**:
   ```bash
   ssh user@your-server-ip
   ```

2. **Get public IP**:
   ```bash
   curl https://api.ipify.org
   ```
   Example output: `197.210.52.123`

3. **Login to KoraPay Dashboard**:
   - URL: https://dashboard.korapay.com
   - Click **Settings** (gear icon)

4. **Navigate to IP Whitelisting**:
   
   **Path 1: Security (Recommended)**
   - **Settings** → **General Business Settings** → **Security**
   - Look for **IP Whitelisting** or **Allowed IPs**
   
   **Path 2: API Configuration (Alternative)**
   - **Settings** → **Product & Payment Settings** → **API Configuration**
   - Look for **IP Whitelisting** or **API IP Restrictions**

5. **Add IP Address**:
   - Click **Add IP Address**, **+ Add IP**, or **Whitelist IP**
   - Enter: `197.210.52.123` (your actual IP from step 2)
   - Add description (optional): "Production Server"
   - Click **Save** or **Add**

6. **Verify IP is Added**:
   - Check that your IP appears in the whitelist
   - Status should show as "Active" or "Enabled"

7. **Test Payout Again**:
   - Go back to your admin panel
   - Try initiating the payout again
   - Should work now! ✅

---

## 🌐 Multiple Servers / Load Balancers

If you have multiple servers or use a load balancer:

### Option 1: Whitelist All Server IPs
- Get IPs from all servers
- Add each IP to KoraPay whitelist
- This works if you have a few static servers

### Option 2: Whitelist Load Balancer IP
- If using a load balancer, whitelist the load balancer's IP
- All requests will appear to come from the load balancer

### Option 3: Contact KoraPay
- Request to disable IP whitelisting
- Use API key authentication only
- May require business justification

---

## 🔍 Verify IP Whitelisting

### Test from Server:

```bash
# Test if your IP is whitelisted
curl -X POST https://api.korapay.com/merchant/api/v1/transactions/disburse \
  -H "Authorization: Bearer YOUR_SECRET_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "reference": "TEST-REF",
    "destination": {
      "type": "bank_account",
      "amount": "100.00",
      "currency": "NGN",
      "bank_account": {
        "bank": "033",
        "account": "1234567890"
      },
      "customer": {
        "name": "Test",
        "email": "test@example.com"
      }
    }
  }'
```

If you get the IP whitelist error, the IP is not whitelisted yet.

---

## ⚠️ Important Notes

1. **IP Whitelisting is Required**
   - KoraPay enforces this for security
   - Cannot be bypassed without whitelisting
   - Applies to payout/disbursement operations

2. **IP Changes**
   - If your server IP changes, update whitelist
   - Some hosting providers use dynamic IPs
   - Consider using a static IP or contacting KoraPay

3. **Development vs Production**
   - Development server IP may be different
   - Whitelist both if testing from different servers
   - Or use KoraPay test mode (if available)

4. **Multiple Environments**
   - Staging server needs separate IP whitelist
   - Production server needs separate IP whitelist
   - Each environment's IP must be whitelisted

---

## 🆘 Still Having Issues?

### Check KoraPay Dashboard:
1. Verify IP is actually added
2. Check if there's a confirmation/approval needed
3. Look for any error messages in dashboard

### Contact KoraPay Support:
- Email: support@korapay.com
- Include:
  - Your merchant account email
  - Server IP address(es)
  - Error message screenshot
  - Transaction reference (if any)

### Check Laravel Logs:
```bash
tail -f storage/logs/laravel.log | grep -i korapay
```

---

## 📝 Quick Checklist

- [ ] Found server's public IP address
- [ ] Logged into KoraPay dashboard
- [ ] Navigated to IP Whitelisting settings
- [ ] Added server IP address
- [ ] Saved/Confirmed IP whitelist
- [ ] Waited a few minutes (if needed)
- [ ] Tested payout again
- [ ] Verified payout works

---

## 🔐 Security Best Practices

1. **Only Whitelist Production IPs**
   - Don't whitelist development/local IPs in production
   - Use separate KoraPay accounts for dev/staging

2. **Monitor IP Changes**
   - Set up alerts if server IP changes
   - Update whitelist immediately if IP changes

3. **Use Static IPs**
   - Request static IP from hosting provider
   - Prevents whitelist issues from IP changes

4. **Regular Audits**
   - Review whitelisted IPs periodically
   - Remove unused/old IPs

---

Once you've whitelisted your server's IP address in the KoraPay dashboard, the payout should work successfully!

