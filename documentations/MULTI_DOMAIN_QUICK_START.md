# Multi-Domain Quick Start Guide

## Quick Setup (5 Steps)

### 1. Add to `.env` file:

```env
MULTI_DOMAIN_ENABLED=true
ADMIN_DOMAIN=admin.doctorontap.com.ng
PATIENT_DOMAIN=app.doctorontap.com.ng
DOCTOR_DOMAIN=doctor.doctorontap.com.ng
CANVASSER_DOMAIN=canvasser.doctorontap.com.ng
NURSE_DOMAIN=nurse.doctorontap.com.ng
PUBLIC_DOMAIN=www.doctorontap.com.ng
SESSION_DOMAIN=.doctorontap.com.ng
COOKIE_DOMAIN=.doctorontap.com.ng
```

### 2. Configure DNS

Point all domains to your server IP:
- `admin.doctorontap.com.ng` → YOUR_SERVER_IP
- `app.doctorontap.com.ng` → YOUR_SERVER_IP
- `doctor.doctorontap.com.ng` → YOUR_SERVER_IP
- etc.

### 3. Configure Web Server

Ensure your Apache/Nginx virtual host accepts all domains (see MULTI_DOMAIN_SETUP.md for details).

### 4. Get SSL Certificate

Use a wildcard certificate (`*.doctorontap.com.ng`) or multi-domain certificate covering all domains.

### 5. Apply Domain Routing (Optional)

If you want to enforce domain-based routing, add middleware to route groups:

```php
// Admin routes
Route::prefix('admin')->name('admin.')
    ->middleware(['domain.routing:admin'])
    ->group(function () {
        // Existing admin routes
    });

// Patient routes  
Route::prefix('patient')->name('patient.')
    ->middleware(['domain.routing:patient'])
    ->group(function () {
        // Existing patient routes
    });

// Doctor routes
Route::prefix('doctor')->name('doctor.')
    ->middleware(['domain.routing:doctor'])
    ->group(function () {
        // Existing doctor routes
    });
```

**Note:** Domain routing middleware is optional. If not applied, routes will work on any domain, but you can still use helper functions to generate domain-specific URLs.

## Using Helper Functions

```php
// In your views or controllers:

// Generate URLs for specific domains
$adminUrl = admin_url('dashboard');
$patientUrl = patient_url('dashboard');
$doctorUrl = doctor_url('dashboard');

// Check current domain
if (is_domain('admin')) {
    // User is on admin domain
}

// Get current domain type
$domainType = current_domain_type();
```

## Testing

1. **Local Testing**: Use `/etc/hosts` to map domains to localhost
2. **Production**: Visit each domain and verify routing works
3. **Cross-Domain Auth**: Log in on one domain, navigate to another - should stay logged in

## Disable Multi-Domain

Set `MULTI_DOMAIN_ENABLED=false` in `.env` to disable domain routing. All routes will work on any domain.

## Full Documentation

See `MULTI_DOMAIN_SETUP.md` for complete setup instructions, troubleshooting, and advanced configuration.



