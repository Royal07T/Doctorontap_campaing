# Login Pages Documentation

This document describes all login portals in the DoctorOnTap application: URLs, routes, controllers, views, and behavior.

---

## Quick Reference

| Portal        | Login URL              | Route name (GET)   | Guard       |
|---------------|------------------------|--------------------|-------------|
| Admin         | `/admin/login`         | `admin.login`      | `admin`     |
| Doctor        | `/doctor/login`        | `doctor.login`     | `doctor`    |
| Caregiver     | `/care-giver/login`    | `care_giver.login` | `care_giver`|
| Patient       | `/patient/login`       | `patient.login`    | `patient`   |
| Nurse         | `/nurse/login`         | `nurse.login`      | `nurse`     |
| Canvasser     | `/canvasser/login`     | `canvasser.login`  | `canvasser`|
| Customer Care | `/customer-care/login` | `customer-care.login` | `customer_care` |

**Base URL example:** If the app runs at `http://localhost:8000`, then:
- Doctor login: `http://localhost:8000/doctor/login`
- Caregiver login: `http://localhost:8000/care-giver/login`

---

## 1. Doctor Login

### Purpose
Allows doctors to sign in to the Doctor Portal (dashboard, consultations, profile, etc.).

### URLs & Routes
- **GET (show form):** `/doctor/login` — Route name: `doctor.login`
- **POST (submit):** `/doctor/login` — Route name: `doctor.login.post`

### Controller & View
- **Controller:** `App\Http\Controllers\Doctor\AuthController`
  - `showLogin()` → returns view, redirects to dashboard if already logged in
  - `login(Request $request)` → validates credentials, checks approval/availability, handles email verification redirect
- **View:** `resources/views/doctor/login.blade.php` (standalone page, no layout)

### Form
- **Action:** `route('doctor.login.post')`
- **Method:** POST
- **Fields:** `email` (required), `password` (required), `remember` (optional checkbox)
- **CSRF:** `@csrf` token required

### Behavior
- If already authenticated as doctor → redirect to `doctor.dashboard`.
- Validates email + password.
- Pre-login checks:
  - If doctor exists but **not approved** → error: "Your account is pending admin approval..."
  - If doctor exists but **not available** (deactivated) → error: "Your account has been deactivated..."
- On success: session regenerate, `last_login_at` updated, then:
  - If email **not verified** → redirect to `doctor.verification.notice`.
  - Else → redirect to `doctor.dashboard` with welcome message.
- On failure: back with error "The provided credentials do not match our records."

### Links on Page
- **Forgot Password:** `route('doctor.password.request')` → `/doctor/forgot-password`
- **Create Account:** `route('doctor.register')` → `/doctor/register`
- **Back to Website:** `url('/')`

### Middleware
- POST route uses `login.rate.limit` middleware.
- No auth required to view or submit the login page.

### Post-Login
- Guard: `doctor`
- After login, middleware `doctor.auth` and `doctor.verified` protect dashboard routes.
- Logout: `route('doctor.logout')` (POST), redirects to `doctor.login`.

---

## 2. Caregiver Login

### Purpose
Allows caregivers to sign in to the Care Giver Portal (dashboard, assigned patients, etc.).

### URLs & Routes
- **GET (show form):** `/care-giver/login` — Route name: `care_giver.login`
- **POST (submit):** `/care-giver/login` — Route name: `care_giver.login.post`

### Controller & View
- **Controller:** `App\Http\Controllers\CareGiver\AuthController`
  - `showLogin()` → returns view, redirects to dashboard if already logged in
  - `login(Request $request)` → validates credentials, checks active + email verified
- **View:** `resources/views/care-giver/login.blade.php`
- **Layout:** `layouts.caregiver-auth` (purple gradient, centered content)

### Form
- **Action:** `route('care_giver.login.post')`
- **Method:** POST
- **Fields:** `email` (required), `password` (required), `remember` (optional checkbox)
- **CSRF:** `@csrf` token required

### Behavior
- If already authenticated as caregiver → redirect to `care_giver.dashboard`.
- Validates email + password.
- Pre-login checks:
  - If caregiver exists but **not active** → error: "Your account has been deactivated..."
  - If caregiver exists but **email not verified** → back with error + `verification_required` and `verification_email` in session (view can show resend/notice).
- On success: session regenerate, `last_login_at` updated → redirect to `care_giver.dashboard` with welcome message.
- On failure: back with error "The provided credentials do not match our records."

### Links on Page
- **Apply Here (Register):** `route('caregiver.register')` → `/caregiver/register`
- **Back to Website:** `url('/')`
- **Forgot Password:** Not present; no caregiver forgot-password routes in the app.

### Session / Verification
- View shows success, error, and `verification_required` (with `verification_email`) messages.
- After login, caregiver may be required to verify PIN (`care_giver.pin` middleware) before dashboard.

### Middleware
- POST route uses `login.rate.limit` middleware.
- No auth required to view or submit the login page.

### Post-Login
- Guard: `care_giver`
- Protected routes use `auth:care_giver`, then optionally `care_giver.pin` and `session.management`.
- Logout: `route('care_giver.logout')` (POST), redirects to `care_giver.login`.

---

## 3. Other Login Portals (Summary)

| Portal        | Path              | Route Name (GET)   | View (typical)           | Controller (typical)        |
|---------------|-------------------|--------------------|---------------------------|-----------------------------|
| Admin         | `/admin/login`    | `admin.login`      | `admin.login`             | `Admin\AuthController`      |
| Patient       | `/patient/login`  | `patient.login`    | `patient.login`           | `Patient\AuthController`   |
| Nurse         | `/nurse/login`    | `nurse.login`      | `nurse.login`             | `Nurse\AuthController`      |
| Canvasser     | `/canvasser/login`| `canvasser.login`  | `canvasser.login`         | `Canvasser\AuthController`  |
| Customer Care | `/customer-care/login` | `customer-care.login` | `customer-care.login` | `CustomerCare\AuthController` |

All of these use the same pattern: GET shows the form, POST submits to a `*.login.post` route with `login.rate.limit` applied. Each uses its own guard and post-login redirect.

---

## 4. Using Login Links in Code

Use route names so URLs stay correct if the path changes:

```blade
{{-- Doctor --}}
<a href="{{ route('doctor.login') }}">Doctor Login</a>
<form action="{{ route('doctor.login.post') }}" method="POST">...</form>

{{-- Caregiver --}}
<a href="{{ route('care_giver.login') }}">Caregiver Login</a>
<form action="{{ route('care_giver.login.post') }}" method="POST">...</form>

{{-- Others --}}
<a href="{{ route('admin.login') }}">Admin Login</a>
<a href="{{ route('patient.login') }}">Patient Login</a>
```

Redirecting to the correct login from middleware or exception handler:

```php
// Example: redirect to doctor login
return redirect()->route('doctor.login');

// Example: redirect to caregiver login
return redirect()->route('care_giver.login');
```

---

## 5. Security Notes

- All login POST routes are protected by **rate limiting** (`login.rate.limit`).
- **CSRF** token is required on every login form.
- **Session regeneration** is performed on successful login.
- **Guards** are separate per portal (no cross-guard login).
- Doctor: approval and availability checks before allowing login.
- Caregiver: active flag and email verification check before allowing full access.

---

## 6. File Locations

| Item        | Doctor                    | Caregiver                         |
|------------|---------------------------|------------------------------------|
| View       | `resources/views/doctor/login.blade.php` | `resources/views/care-giver/login.blade.php` |
| Layout     | None (standalone)         | `resources/views/layouts/caregiver-auth.blade.php` |
| Controller | `app/Http/Controllers/Doctor/AuthController.php` | `app/Http/Controllers/CareGiver/AuthController.php` |
| Guard      | `config/auth.php` → `guards.doctor` | `config/auth.php` → `guards.care_giver` |

---

*Last updated: 2025. For route or view changes, update this document accordingly.*
