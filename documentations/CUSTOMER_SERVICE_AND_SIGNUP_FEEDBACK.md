# Customer Service Platform & Sign-up Feedback

**Date:** February 2025  
**Context:** Senior developer feedback on customer service issues and Doctor/Caregiver sign-up deliverables.

---

## 1. Approach: Incremental Improvement

Agreed. Starting from the current customer service platform and improving incrementally is the right approach. The items below address the immediate issues first.

---

## 2. Issue C – Bulk SMS: No Free-Text Entry (Implemented)

**Requirement:** Customer service staff must not be able to manually enter SMS text. There must be no field for free-text input; only pre-approved templates may be used.

**Changes made:**

- **UI (Create SMS Campaign):**
  - Template selection is now **required** (no “optional” and no “write custom message below”).
  - The **Message Content** textarea has been **removed**. Staff can only:
    1. Choose a template (required).
    2. Fill in template variables (if the template has placeholders).
    3. See a read-only preview and character/segment counts.
  - Copy updated to: “Only pre-approved SMS templates may be used. Free-text entry is not allowed.”
  - Send button is disabled until a template is selected and the preview has content.

- **Backend (`BulkSmsController::send()`):**
  - `template_id` is **required** (validated as `required|exists:communication_templates,id`).
  - `message` is **no longer accepted** from the request. The message is built **only** from the selected template:
    - Load the SMS template by `template_id` (and ensure it’s active).
    - Call `$template->render($request->variables ?? [])` to get the message.
  - If the rendered message exceeds 1000 characters, the request is rejected with a validation error.
  - Campaign is stored and processed using this template-derived message only.

**Result:** Staff cannot type or paste free text for bulk SMS; they can only send messages from templates (with optional variable values). This meets the “no field for staff to input free text” requirement.

---

## 3. Doctor & Caregiver Sign-up – Current Version for Review

Below is what is in the codebase today so you can review the current Doctor and Caregiver sign-up flows.

### 3.1 Doctor Sign-up

| Item | Details |
|------|--------|
| **URL** | `/doctor/register` |
| **Route name** | `doctor.register` (GET), `doctor.register.post` (POST) |
| **Controller** | `App\Http\Controllers\Doctor\RegistrationController` |
| **View** | `resources/views/doctor/register.blade.php` |
| **Layout** | Standalone page (no layout extend; full HTML with Instrument Sans, Vite assets). |

**Current flow:**

- **Step 1 – Personal:** First name, last name, gender, phone, email, password (with strength rules).
- **Step 2 – Professional:** Specialization (from `specialties`), consultant confirmation (`is_consultant`), experience, consultation fee, place of work, role (clinical/non-clinical).
- **Step 3 – Location & credentials:** State → city (cities loaded via `/doctor/states/{stateId}/cities`), MDCN license status, languages, days of availability, **certificate upload** (required; PDF/image, max 5MB).
- **Step 4 – Review & submit.**

**Validation (high level):** Name regex, email unique on `doctors`, password min 8 + uppercase + lowercase + number + confirmed, fee range (e.g. 500–1,000,000), certificate required. Custom error messages are defined.

**Post-registration:** Doctor is created; certificate is stored in private storage; redirect to success page with message to verify email and wait for admin review.

**What’s in place:** Multi-step form with progress indicator, state/city dropdowns, file upload, validation, and success page. Suitable for review and then incremental polish (copy, validations, or UX).

---

### 3.2 Caregiver Sign-up

| Item | Details |
|------|--------|
| **URL** | `/caregiver/register` |
| **Route name** | `caregiver.register` (GET), `caregiver.register.submit` (POST) |
| **Controller** | `App\Http\Controllers\Auth\CareGiverAuthController` |
| **View** | `resources/views/auth/caregiver-register.blade.php` |
| **Layout** | `layouts.caregiver-auth` (purple gradient, minimal auth layout). |

**Current flow (as implemented):**

- **Personal:** Full name, email, phone, date of birth, gender.
- **Professional:** Role (Registered Nurse, Auxiliary Nurse, Caregiver, Medical Assistant), years of experience, license number (optional), short bio.
- **Address:** Street address, state (dropdown from `states`), city (dropdown loaded via `/caregiver/cities/{stateId}` when state is selected).
- **Documents (optional):** Profile photo, CV (PDF/DOC/DOCX).
- **Password:** Password + confirm (min 8 characters).
- **Submit:** “Submit Application” → creates caregiver with `is_active = false`, `verification_status = 'pending'`, redirect to login with success message to wait for approval.

**Fixes applied in this pass:**

- Layout was broken (`@extends('layouts.')`). It now extends `layouts.caregiver-auth`.
- Form was incomplete (address input truncated, no state/city/password/submit). Completed with: full address field, state and city dropdowns, cities loaded by AJAX, optional profile photo and CV, password + confirmation, validation error display, submit button, and a small script for city loading.

**What’s in place:** Single-page form with all required and optional fields wired to the existing controller. Ready for your review and any further design/validation tweaks.

---

## 4. Suggested Next Steps

1. **Review** the live Doctor and Caregiver sign-up pages (`/doctor/register`, `/caregiver/register`) and share any copy, validation, or UX changes you want.
2. **Bulk SMS:** Test the create campaign flow with template-only behaviour (no free-text field) in your environment and confirm it matches policy.
3. **After Doctor & Caregiver are signed off:** Proceed to Patient, Pharmacy, and Laboratory sign-up flows as planned.

If you want, the next iteration can focus on specific fields, validations, or success/verification messaging for Doctor and Caregiver sign-up.
