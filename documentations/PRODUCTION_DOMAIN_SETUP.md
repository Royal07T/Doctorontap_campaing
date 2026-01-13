# Production Domain Configuration Guide

**Last Updated:** January 13, 2026  
**Status:** Production Ready

---

## Overview

This guide provides step-by-step instructions for configuring the multi-domain setup in production for DoctorOnTap platform.

## Production Subdomains

Based on your server configuration, the following subdomains will be used in production:

| User Type | Subdomain | Route Prefix | Status |
|-----------|-----------|--------------|--------|
| Admin | `admin.doctorontap.com.ng` | `/admin` | ✅ Active |
| Patients | `patient.doctorontap.com.ng` | `/patient` | ✅ Active |
| Doctors | `doctor.doctorontap.com.ng` | `/doctor` | ✅ Active |
| Nurses | `nurse.doctorontap.com.ng` | `/nurse` | ✅ Active |
| Canvassers | `canvasser.doctorontap.com.ng` | `/canvasser` | ✅ Active |
| Care Givers | `caregiver.doctorontap.com.ng` | `/care-giver` | ✅ Active |
| Customer Care | `customercare.doctorontap.com.ng` | `/customer-care` | ✅ Active |

---

## Step 1: Environment Configuration

Add the following to your production `.env` file:

```env
# ============================================
# Multi-Domain Configuration
# ============================================
MULTI_DOMAIN_ENABLED=true

# Domain Configuration
ADMIN_DOMAIN=admin.doctorontap.com.ng
PATIENT_DOMAIN=patient.doctorontap.com.ng
DOCTOR_DOMAIN=doctor.doctorontap.com.ng
NURSE_DOMAIN=nurse.doctorontap.com.ng
CANVASSER_DOMAIN=canvasser.doctorontap.com.ng
CAREGIVER_DOMAIN=caregiver.doctorontap.com.ng
CUSTOMERCARE_DOMAIN=customercare.doctorontap.com.ng
PUBLIC_DOMAIN=www.doctorontap.com.ng

# Session & Cookie Domain (for cross-domain authentication)
# Use root domain with leading dot to allow cookies across subdomains
SESSION_DOMAIN=.doctorontap.com.ng
COOKIE_DOMAIN=.doctorontap.com.ng

# Application URL (base URL)
APP_URL=https://patient.doctorontap.com.ng
```

---

## Step 2: DNS Configuration

Configure DNS records to point all subdomains to your server IP address.

### A Records (IPv4)

```
admin.doctorontap.com.ng        A    YOUR_SERVER_IP
patient.doctorontap.com.ng      A    YOUR_SERVER_IP
doctor.doctorontap.com.ng       A    YOUR_SERVER_IP
nurse.doctorontap.com.ng        A    YOUR_SERVER_IP
canvasser.doctorontap.com.ng    A    YOUR_SERVER_IP
caregiver.doctorontap.com.ng    A    YOUR_SERVER_IP
customercare.doctorontap.com.ng A    YOUR_SERVER_IP
www.doctorontap.com.ng          A    YOUR_SERVER_IP
doctorontap.com.ng              A    YOUR_SERVER_IP
```

### CNAME Records (Alternative)

```
admin          CNAME   doctorontap.com.ng
patient        CNAME   doctorontap.com.ng
doctor         CNAME   doctorontap.com.ng
nurse          CNAME   doctorontap.com.ng
canvasser      CNAME   doctorontap.com.ng
caregiver      CNAME   doctorontap.com.ng
customercare   CNAME   doctorontap.com.ng
www            CNAME   doctorontap.com.ng
```

---

## Step 3: SSL Certificate Configuration

### Option 1: Wildcard Certificate (Recommended)

Use a wildcard SSL certificate for `*.doctorontap.com.ng` to cover all subdomains.

**Benefits:**
- Single certificate for all subdomains
- Easier to manage
- Lower cost

**Example:**
```
Certificate: *.doctorontap.com.ng
Valid for: admin, patient, doctor, nurse, canvasser, caregiver, customercare, www, etc.
```

### Option 2: Multi-Domain Certificate (SAN Certificate)

Use a Subject Alternative Name (SAN) certificate that includes all subdomains.

**Example:**
```
Subject: doctorontap.com.ng
SAN: admin.doctorontap.com.ng
SAN: patient.doctorontap.com.ng
SAN: doctor.doctorontap.com.ng
SAN: nurse.doctorontap.com.ng
SAN: canvasser.doctorontap.com.ng
SAN: caregiver.doctorontap.com.ng
SAN: customercare.doctorontap.com.ng
SAN: www.doctorontap.com.ng
```

---

## Step 4: Web Server Configuration

### Apache Configuration

Ensure your Apache virtual host accepts all subdomains:

```apache
<VirtualHost *:443>
    ServerName doctorontap.com.ng
    ServerAlias *.doctorontap.com.ng
    
    DocumentRoot /path/to/your/laravel/public
    
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    SSLCertificateChainFile /path/to/chain.crt
    
    <Directory /path/to/your/laravel/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Laravel specific
    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php/$1 [L]
    </IfModule>
</VirtualHost>
```

### Nginx Configuration

```nginx
server {
    listen 443 ssl http2;
    server_name doctorontap.com.ng *.doctorontap.com.ng;
    
    root /path/to/your/laravel/public;
    index index.php;
    
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_trusted_certificate /path/to/chain.crt;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## Step 5: Laravel Session & Cookie Configuration

Update `config/session.php` and `config/cookie.php` to use the root domain:

```php
// config/session.php
'domain' => env('SESSION_DOMAIN', '.doctorontap.com.ng'),

// config/cookie.php (if exists)
'domain' => env('COOKIE_DOMAIN', '.doctorontap.com.ng'),
```

---

## Step 6: Verify Configuration

### Test Each Domain

1. **Admin Domain:**
   ```
   https://admin.doctorontap.com.ng/admin/login
   ```

2. **Patient Domain:**
   ```
   https://patient.doctorontap.com.ng/patient/login
   ```

3. **Doctor Domain:**
   ```
   https://doctor.doctorontap.com.ng/doctor/login
   ```

4. **Nurse Domain:**
   ```
   https://nurse.doctorontap.com.ng/nurse/login
   ```

5. **Canvasser Domain:**
   ```
   https://canvasser.doctorontap.com.ng/canvasser/login
   ```

6. **Care Giver Domain:**
   ```
   https://caregiver.doctorontap.com.ng/care-giver/login
   ```

7. **Customer Care Domain:**
   ```
   https://customercare.doctorontap.com.ng/customer-care/login
   ```

### Test Cross-Domain Authentication

1. Log in on one domain (e.g., `patient.doctorontap.com.ng`)
2. Navigate to another domain (e.g., `doctor.doctorontap.com.ng`)
3. Verify that the session is maintained (if applicable)

---

## Step 7: Security Considerations

### 1. HTTPS Enforcement

Ensure all domains redirect HTTP to HTTPS:

```php
// In your middleware or .htaccess
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
    exit();
}
```

### 2. CORS Configuration

If you need to make cross-domain API requests, configure CORS in `config/cors.php`:

```php
'allowed_origins' => [
    'https://admin.doctorontap.com.ng',
    'https://patient.doctorontap.com.ng',
    'https://doctor.doctorontap.com.ng',
    // ... other domains
],
```

### 3. CSRF Protection

Laravel's CSRF protection works across subdomains when using the same root domain for cookies.

---

## Step 8: Helper Functions

The application includes helper functions for generating domain-specific URLs:

```php
// Generate URLs for specific domains
$adminUrl = admin_url('dashboard');
$patientUrl = patient_url('dashboard');
$doctorUrl = doctor_url('dashboard');

// Check current domain
if (is_domain('admin')) {
    // Admin-specific logic
}

// Get current domain type
$currentDomain = current_domain_type(); // Returns 'admin', 'patient', etc.
```

---

## Troubleshooting

### Issue: Sessions Not Working Across Domains

**Solution:**
1. Verify `SESSION_DOMAIN` is set to `.doctorontap.com.ng` (with leading dot)
2. Check that cookies are being set with the correct domain
3. Ensure SSL certificates are valid for all subdomains

### Issue: Redirects Not Working

**Solution:**
1. Check that `MULTI_DOMAIN_ENABLED=true` in `.env`
2. Verify domain configuration in `config/domains.php`
3. Clear config cache: `php artisan config:clear`

### Issue: 403 Forbidden on Some Domains

**Solution:**
1. Check web server configuration allows all subdomains
2. Verify DNS records are correctly configured
3. Check file permissions on Laravel directories

---

## Production Checklist

Before going live, verify:

- [ ] All subdomains are configured in `.env`
- [ ] DNS records are pointing to the correct server
- [ ] SSL certificates are installed and valid
- [ ] Web server accepts all subdomains
- [ ] Session domain is set to `.doctorontap.com.ng`
- [ ] Cookie domain is set to `.doctorontap.com.ng`
- [ ] All domains are accessible via HTTPS
- [ ] Cross-domain authentication works (if needed)
- [ ] Helper functions generate correct URLs
- [ ] Config cache is cleared: `php artisan config:clear`
- [ ] Route cache is cleared: `php artisan route:clear`
- [ ] View cache is cleared: `php artisan view:clear`

---

## Related Documentation

- [Multi-Domain Setup Guide](./MULTI_DOMAIN_SETUP.md)
- [Multi-Domain Quick Start](./MULTI_DOMAIN_QUICK_START.md)
- [Security Audit](./SECURITY_AUDIT_ROUTES_AND_ENDPOINTS.md)

---

**Document Version:** 1.0.0  
**Last Updated:** January 13, 2026

