# Multi-Domain Setup Guide

This guide explains how to configure different domains for different user types in production (e.g., admin domain, patient domain, doctor domain).

## Overview

The multi-domain feature allows you to:
- Separate admin, patient, doctor, and other interfaces on different domains
- Improve security by isolating different user types
- Better organize your application structure
- Use domain-specific branding if needed

## Example Domain Structure

```
- Admin:     admin.doctorontap.com.ng
- Patients:  app.doctorontap.com.ng (or patient.doctorontap.com.ng)
- Doctors:   doctor.doctorontap.com.ng
- Canvassers: canvasser.doctorontap.com.ng
- Nurses:    nurse.doctorontap.com.ng
- Public:    www.doctorontap.com.ng (or doctorontap.com.ng)
```

## Step 1: Configure Environment Variables

Add the following to your `.env` file:

```env
# Enable Multi-Domain Routing
MULTI_DOMAIN_ENABLED=true

# Domain Configuration
ADMIN_DOMAIN=admin.doctorontap.com.ng
PATIENT_DOMAIN=app.doctorontap.com.ng
DOCTOR_DOMAIN=doctor.doctorontap.com.ng
CANVASSER_DOMAIN=canvasser.doctorontap.com.ng
NURSE_DOMAIN=nurse.doctorontap.com.ng
PUBLIC_DOMAIN=www.doctorontap.com.ng

# Session & Cookie Domain (for cross-domain authentication)
# Use root domain with leading dot to allow cookies across subdomains
SESSION_DOMAIN=.doctorontap.com.ng
COOKIE_DOMAIN=.doctorontap.com.ng
```

## Step 2: DNS Configuration

Configure your DNS to point all domains to the same server:

### A Records (for IPv4)
```
admin.doctorontap.com.ng     A    YOUR_SERVER_IP
app.doctorontap.com.ng       A    YOUR_SERVER_IP
doctor.doctorontap.com.ng    A    YOUR_SERVER_IP
canvasser.doctorontap.com.ng A    YOUR_SERVER_IP
nurse.doctorontap.com.ng     A    YOUR_SERVER_IP
www.doctorontap.com.ng       A    YOUR_SERVER_IP
doctorontap.com.ng           A    YOUR_SERVER_IP
```

### CNAME Records (Alternative)
```
admin     CNAME   doctorontap.com.ng
app       CNAME   doctorontap.com.ng
doctor    CNAME   doctorontap.com.ng
canvasser CNAME   doctorontap.com.ng
nurse     CNAME   doctorontap.com.ng
www       CNAME   doctorontap.com.ng
```

## Step 3: Web Server Configuration

### Apache (.htaccess)

The existing `.htaccess` file should work, but ensure it's configured to handle all domains:

```apache
<VirtualHost *:80>
    ServerName admin.doctorontap.com.ng
    ServerAlias app.doctorontap.com.ng doctor.doctorontap.com.ng canvasser.doctorontap.com.ng nurse.doctorontap.com.ng www.doctorontap.com.ng doctorontap.com.ng
    DocumentRoot /path/to/your/project/public
    
    <Directory /path/to/your/project/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

<VirtualHost *:443>
    ServerName admin.doctorontap.com.ng
    ServerAlias app.doctorontap.com.ng doctor.doctorontap.com.ng canvasser.doctorontap.com.ng nurse.doctorontap.com.ng www.doctorontap.com.ng doctorontap.com.ng
    DocumentRoot /path/to/your/project/public
    
    SSLEngine on
    SSLCertificateFile /path/to/ssl/certificate.crt
    SSLCertificateKeyFile /path/to/ssl/private.key
    SSLCertificateChainFile /path/to/ssl/chain.crt
    
    <Directory /path/to/your/project/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name admin.doctorontap.com.ng app.doctorontap.com.ng doctor.doctorontap.com.ng canvasser.doctorontap.com.ng nurse.doctorontap.com.ng www.doctorontap.com.ng doctorontap.com.ng;
    
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name admin.doctorontap.com.ng app.doctorontap.com.ng doctor.doctorontap.com.ng canvasser.doctorontap.com.ng nurse.doctorontap.com.ng www.doctorontap.com.ng doctorontap.com.ng;
    
    ssl_certificate /path/to/ssl/certificate.crt;
    ssl_certificate_key /path/to/ssl/private.key;
    ssl_certificate_chain /path/to/ssl/chain.crt;
    
    root /path/to/your/project/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Step 4: SSL Certificate

You'll need an SSL certificate that covers all your domains. Options:

### Option 1: Wildcard Certificate
```
*.doctorontap.com.ng
```

### Option 2: Multi-Domain (SAN) Certificate
```
admin.doctorontap.com.ng
app.doctorontap.com.ng
doctor.doctorontap.com.ng
canvasser.doctorontap.com.ng
nurse.doctorontap.com.ng
www.doctorontap.com.ng
doctorontap.com.ng
```

### Option 3: Let's Encrypt (Free)
```bash
# Install certbot
sudo apt-get install certbot python3-certbot-nginx  # For Nginx
sudo apt-get install certbot python3-certbot-apache # For Apache

# Get certificate for all domains
sudo certbot --nginx -d admin.doctorontap.com.ng -d app.doctorontap.com.ng -d doctor.doctorontap.com.ng -d canvasser.doctorontap.com.ng -d nurse.doctorontap.com.ng -d www.doctorontap.com.ng -d doctorontap.com.ng

# Auto-renewal
sudo certbot renew --dry-run
```

## Step 5: Using Domain Routing in Routes

### Option 1: Using Middleware (Recommended)

Apply domain routing middleware to route groups:

```php
// In routes/web.php

// Admin routes - only accessible on admin domain
Route::prefix('admin')->name('admin.')
    ->middleware(['domain.routing:admin'])
    ->group(function () {
        // Admin routes here
    });

// Patient routes - only accessible on patient domain
Route::prefix('patient')->name('patient.')
    ->middleware(['domain.routing:patient'])
    ->group(function () {
        // Patient routes here
    });

// Doctor routes - only accessible on doctor domain
Route::prefix('doctor')->name('doctor.')
    ->middleware(['domain.routing:doctor'])
    ->group(function () {
        // Doctor routes here
    });
```

### Option 2: Using Route Domain Constraints

```php
// In routes/web.php

Route::domain('admin.doctorontap.com.ng')->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        // Admin routes
    });
});

Route::domain('app.doctorontap.com.ng')->group(function () {
    Route::prefix('patient')->name('patient.')->group(function () {
        // Patient routes
    });
});
```

## Step 6: Using Helper Functions

Use the provided helper functions to generate domain-aware URLs:

```php
// Generate admin URL
$adminLoginUrl = admin_url('login');
// Returns: https://admin.doctorontap.com.ng/login

// Generate patient URL
$patientDashboardUrl = patient_url('dashboard');
// Returns: https://app.doctorontap.com.ng/dashboard

// Generate doctor URL
$doctorProfileUrl = doctor_url('profile');
// Returns: https://doctor.doctorontap.com.ng/profile

// Check current domain
if (is_domain('admin')) {
    // User is on admin domain
}

// Get current domain type
$domainType = current_domain_type(); // Returns: 'admin', 'patient', 'doctor', etc.
```

## Step 7: Cross-Domain Authentication

Since sessions and cookies are configured to use the root domain (`.doctorontap.com.ng`), authentication will work across all subdomains. Users can log in on one domain and remain authenticated when navigating to another.

### Important Notes:
- Session domain must be set to root domain with leading dot (`.doctorontap.com.ng`)
- Cookie domain must match session domain
- HTTPS is required for secure cookies across domains
- SameSite cookie attribute should be set to `lax` or `none` for cross-domain requests

## Step 8: Testing

### Local Testing

For local testing, you can use `/etc/hosts` to simulate domains:

```bash
# Add to /etc/hosts (Linux/Mac) or C:\Windows\System32\drivers\etc\hosts (Windows)
127.0.0.1 admin.doctorontap.local
127.0.0.1 app.doctorontap.local
127.0.0.1 doctor.doctorontap.local
```

Then update your `.env`:
```env
MULTI_DOMAIN_ENABLED=true
ADMIN_DOMAIN=admin.doctorontap.local
PATIENT_DOMAIN=app.doctorontap.local
DOCTOR_DOMAIN=doctor.doctorontap.local
SESSION_DOMAIN=.doctorontap.local
COOKIE_DOMAIN=.doctorontap.local
```

### Production Testing

1. Test each domain individually:
   - Visit `https://admin.doctorontap.com.ng/admin/login`
   - Visit `https://app.doctorontap.com.ng/patient/login`
   - Visit `https://doctor.doctorontap.com.ng/doctor/login`

2. Test cross-domain authentication:
   - Log in on admin domain
   - Navigate to patient domain
   - Verify you're still authenticated

3. Test domain routing:
   - Try accessing admin routes on patient domain (should redirect)
   - Try accessing patient routes on admin domain (should redirect)

## Troubleshooting

### Issue: Sessions not working across domains

**Solution:**
- Ensure `SESSION_DOMAIN` is set to root domain with leading dot
- Ensure `COOKIE_DOMAIN` matches `SESSION_DOMAIN`
- Verify HTTPS is enabled (required for secure cookies)
- Check browser console for cookie errors

### Issue: Redirect loops

**Solution:**
- Verify domain configuration in `.env`
- Check middleware is correctly applied
- Ensure web server is configured to handle all domains

### Issue: 403 Forbidden errors

**Solution:**
- Check domain routing middleware configuration
- Verify domain names match exactly (case-sensitive)
- Check if multi-domain is enabled in `.env`

## Security Considerations

1. **HTTPS Required**: Always use HTTPS in production for secure cookie transmission
2. **Domain Validation**: The middleware validates domains to prevent unauthorized access
3. **Session Security**: Sessions are configured with secure, HTTP-only cookies
4. **CSRF Protection**: Ensure CSRF tokens work across domains if needed

## Disabling Multi-Domain

To disable multi-domain routing:

```env
MULTI_DOMAIN_ENABLED=false
```

When disabled, all routes work on any domain and helper functions fall back to standard `url()` function.

## Additional Resources

- [Laravel Domain Routing Documentation](https://laravel.com/docs/routing#route-group-domain-routing)
- [Session Configuration](https://laravel.com/docs/session)
- [Cookie Security Best Practices](https://developer.mozilla.org/en-US/docs/Web/HTTP/Cookies)



