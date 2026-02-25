# Registration Pages Documentation

This document describes the **Doctor** and **Caregiver** registration flows in the DoctorOnTap application: URLs, routes, controllers, views, form fields, validation, and post-registration behavior.

---

## Quick Reference

| Portal    | Registration URL        | Route name (GET)        | Route name (POST)            |
|-----------|--------------------------|--------------------------|------------------------------|
| Doctor    | `/doctor/register`       | `doctor.register`        | `doctor.register.post`       |
| Caregiver | `/caregiver/register`    | `caregiver.register`     | `caregiver.register.submit`  |

**Example full URLs (app at `http://localhost:8000`):**
- Doctor: `http://localhost:8000/doctor/register`
- Caregiver: `http://localhost:8000/caregiver/register`

---

## 1. Doctor Registration

### Purpose
Allows doctors to apply to join the platform. Account is created but **not approved** until admin reviews. Email verification is sent after registration.

### URLs & Routes
- **GET (show form):** `/doctor/register` — Route name: `doctor.register`
- **POST (submit):** `/doctor/register` — Route name: `doctor.register.post`
- **Success page:** `/doctor/registration-success` — Route name: `doctor.registration.success`

### Controller & View
- **Controller:** `App\Http\Controllers\Doctor\RegistrationController`
  - `showRegistrationForm()` → passes `specialties`, `states` to view
  - `getCitiesByState($stateId)` → JSON list of cities (used by AJAX for location dropdown)
  - `register(Request $request)` → validation, certificate upload, create doctor, send verification email, redirect to success
  - `success()` → registration success page
- **View:** `resources/views/doctor/register.blade.php` (standalone page, no layout)
- **Success view:** `resources/views/doctor/registration-success.blade.php`

### Form Structure (multi-step feel with progress bar)
- **Action:** `route('doctor.register.post')`
- **Method:** POST
- **Enctype:** `multipart/form-data` (for certificate upload)

### Form Sections & Fields

| Section            | Fields |
|--------------------|--------|
| **1. Personal**    | `first_name`, `last_name`, `gender`, `phone`, `email`, (password in Security section) |
| **2. Professional**| `specialization`, `is_consultant` (checkbox), `experience`, `consultation_fee`, `place_of_work`, `role` (clinical/non-clinical), `state`, `location` (city), `mdcn_license_current`, `languages`, `days_of_availability` |
| **3. Documents**   | `certificate` (file: PDF, JPG, JPEG, PNG, max 5MB) — **required** |
| **4. Security**    | `password`, `password_confirmation` |

### Validation Rules (summary)
- `first_name`, `last_name`: required, 2–255 chars, letters only (regex)
- `gender`: required, in: male, female
- `phone`: required, 10–20 chars, digits/format
- `email`: required, email, unique on `doctors`
- `password`: required, min 8, confirmed, must include uppercase, lowercase, number
- `specialization`: required, exists in `specialties.name`
- `is_consultant`: required, accepted (checkbox)
- `consultation_fee`: required, numeric, 500–1,000,000
- `place_of_work`, `role`, `state`, `location`, `mdcn_license_current`, `languages`, `days_of_availability`: required (with min/max where applicable)
- `certificate`: required, file, mimes: pdf,jpg,jpeg,png, max 5120 KB

Custom error messages are defined in the controller for all rules.

### State / City (Location)
- **State:** dropdown from `states` table (`state` = state ID).
- **City:** dropdown populated via AJAX: `GET /doctor/states/{stateId}/cities` returns JSON `[{id, name}, ...]`. Field name: `location`, value is **city name** (must exist in `locations` for selected state).

### Post-Registration Behavior
1. Certificate is stored in **private** storage (`doctor-certificates/`), with base64 copy in DB for admin viewing.
2. Doctor record is created with:
   - `is_approved` = false
   - `is_available` = false
   - `mdcn_certificate_verified` = false
3. **Email verification** is sent: `$doctor->sendEmailVerificationNotification()`.
4. User is redirected to **registration success** page with message: *"Registration successful! Please check your email to verify your account. Your application will be reviewed by our admin team."*

### Links on Registration Page
- **Already registered? Sign in:** `route('doctor.login')` → `/doctor/login`
- **Back to Website:** `url('/')`

### File Locations
- View: `resources/views/doctor/register.blade.php`
- Success: `resources/views/doctor/registration-success.blade.php`
- Controller: `app/Http/Controllers/Doctor/RegistrationController.php`
- Cities API: same controller, `getCitiesByState`; URL: `/doctor/states/{stateId}/cities` (route name: `doctor.doctor.cities-by-state`)

---

## 2. Caregiver Registration

### Purpose
Allows caregivers to apply to join the platform. Account is created with **pending approval** and **pending verification**. No email is sent on registration; approval/verification is handled separately by admin.

### URLs & Routes
- **GET (show form):** `/caregiver/register` — Route name: `caregiver.register`
- **POST (submit):** `/caregiver/register` — Route name: `caregiver.register.submit`
- **Cities API:** `/caregiver/cities/{stateId}` — Route name: `caregiver.cities`

### Controller & View
- **Controller:** `App\Http\Controllers\Auth\CareGiverAuthController`
  - `showRegistrationForm()` → passes `states` to view
  - `getCitiesByState($stateId)` → JSON list of cities for state
  - `register(Request $request)` → validation, optional file uploads, create caregiver, redirect to login
- **View:** `resources/views/auth/caregiver-register.blade.php`
- **Layout:** `resources/views/layouts/caregiver-auth.blade.php` (purple gradient, centered)

### Form Structure (4-step progress bar, same feel as Doctor)
- **Action:** `route('caregiver.register.submit')`
- **Method:** POST
- **Enctype:** `multipart/form-data` (for profile photo and CV)

### Form Sections & Fields

| Section                  | Fields |
|--------------------------|--------|
| **1. Personal**          | `first_name`, `last_name`, `email`, `phone`, `date_of_birth`, `gender` |
| **2. Professional**      | `role`, `experience_years`, `license_number` (optional), `bio` (optional) |
| **3. Address & Documents** | `address`, `state_id`, `city_id`, `profile_photo` (optional), `cv_file` (optional) |
| **4. Security**          | `password`, `password_confirmation` |

### Validation Rules (summary)
- `first_name`, `last_name`: required, 2–255 chars
- `email`: required, email, unique on `care_givers`
- `phone`: required, max 20
- `date_of_birth`: required, date
- `gender`: required, string
- `role`: required, string (e.g. Registered Nurse, Auxiliary Nurse, Caregiver, Medical Assistant)
- `experience_years`: required, integer, min 0
- `address`: required, string
- `state_id`: required, exists in `states`
- `city_id`: required, exists in `locations` and must belong to selected state
- `profile_photo`: nullable, image, max 2048 KB
- `cv_file`: nullable, mimes: pdf,doc,docx, max 5120 KB
- `password`: required, min 8, confirmed

Optional fields not in validation: `license_number`, `bio` — stored if present.

### State / City
- **State:** `state_id` — dropdown from `states`.
- **City:** `city_id` — dropdown populated via AJAX: `GET /caregiver/cities/{stateId}` returns JSON `[{id, name}, ...]`. Value is **location id** (stored as `city` name and `state` name on the caregiver record).

### Post-Registration Behavior
1. **Profile photo** (if provided): stored in `public` disk, `caregivers/photos/`.
2. **CV** (if provided): stored in `local` (private) disk, `caregivers/cvs/`.
3. Caregiver record is created with:
   - `name` = `first_name + ' ' + last_name`
   - `is_active` = false (pending approval)
   - `verification_status` = 'pending'
4. **No email** is sent on registration.
5. Redirect to **caregiver login** with message: *"Application submitted! Please wait for approval."*

### Links on Registration Page
- **Already registered? Sign in:** `route('care_giver.login')` → `/care-giver/login`
- **Back to Website:** `url('/')` (in footer)

### File Locations
- View: `resources/views/auth/caregiver-register.blade.php`
- Layout: `resources/views/layouts/caregiver-auth.blade.php`
- Controller: `app/Http/Controllers/Auth/CareGiverAuthController.php`
- Cities API: same controller; route `caregiver.cities` → `/caregiver/cities/{stateId}`

---

## 3. Comparison at a Glance

| Aspect           | Doctor                          | Caregiver                           |
|------------------|----------------------------------|-------------------------------------|
| **URL**          | `/doctor/register`               | `/caregiver/register`                |
| **Layout**       | Standalone (no layout)           | Layout: `caregiver-auth`             |
| **Steps / UX**   | 4-step progress bar              | 4-step progress bar (same style)    |
| **Required file**| Certificate (MDCN)               | None                                 |
| **Optional files** | —                             | Profile photo, CV                    |
| **After submit** | Success page + verification email | Redirect to login + success message |
| **Account state**| Unapproved, unverified           | Inactive, verification pending      |
| **Cities API**   | `/doctor/states/{id}/cities`     | `/caregiver/cities/{id}`             |

---

## 4. Using Registration Links in Code

```blade
{{-- Doctor --}}
<a href="{{ route('doctor.register') }}">Apply as Doctor</a>
<form action="{{ route('doctor.register.post') }}" method="POST" enctype="multipart/form-data">...</form>

{{-- Caregiver --}}
<a href="{{ route('caregiver.register') }}">Apply as Caregiver</a>
<form action="{{ route('caregiver.register.submit') }}" method="POST" enctype="multipart/form-data">...</form>
```

Redirect examples:
```php
return redirect()->route('doctor.register');
return redirect()->route('caregiver.register');
```

---

## 5. Security & Compliance Notes

- **Doctor:** Certificate stored privately; admin can verify MDCN later. Email verification required before full access.
- **Caregiver:** No automatic email on signup; approval and verification are admin-driven. CV stored in private disk.
- Both forms use **CSRF** protection and standard Laravel validation with custom messages where needed.
- Passwords are hashed with `Hash::make()` before storage.

---

*Last updated: 2025. Update this document if validation rules, routes, or post-registration flows change.*
