<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Multi-Domain Configuration
    |--------------------------------------------------------------------------
    |
    | Configure different domains for different user types in production.
    | This allows you to separate admin, patient, doctor, and other
    | interfaces on different domains for better security and organization.
    |
    | Example:
    | - Admin: admin.doctorontap.com.ng
    | - Patients: patient.doctorontap.com.ng or patient.doctorontap.com.ng
    | - Doctors: doctor.doctorontap.com.ng
    | - Public: www.doctorontap.com.ng or doctorontap.com.ng
    |
    */

    'enabled' => env('MULTI_DOMAIN_ENABLED', false),

    'domains' => [
        'admin' => env('ADMIN_DOMAIN', 'admin.doctorontap.com.ng'),
        'patient' => env('PATIENT_DOMAIN', 'patient.doctorontap.com.ng'),
        'doctor' => env('DOCTOR_DOMAIN', 'doctor.doctorontap.com.ng'),
        'canvasser' => env('CANVASSER_DOMAIN', 'canvasser.doctorontap.com.ng'),
        'nurse' => env('NURSE_DOMAIN', 'nurse.doctorontap.com.ng'),
        'caregiver' => env('CAREGIVER_DOMAIN', 'caregiver.doctorontap.com.ng'),
        'customercare' => env('CUSTOMERCARE_DOMAIN', 'customercare.doctorontap.com.ng'),
        'public' => env('PUBLIC_DOMAIN', 'www.doctorontap.com.ng'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Domain Mappings
    |--------------------------------------------------------------------------
    |
    | Map route prefixes to their respective domains.
    | This is used by the DomainRouting middleware to enforce
    | domain-based access control.
    |
    */

    'route_mappings' => [
        'admin' => env('ADMIN_DOMAIN', 'admin.doctorontap.com.ng'),
        'patient' => env('PATIENT_DOMAIN', 'patient.doctorontap.com.ng'),
        'doctor' => env('DOCTOR_DOMAIN', 'doctor.doctorontap.com.ng'),
        'canvasser' => env('CANVASSER_DOMAIN', 'canvasser.doctorontap.com.ng'),
        'nurse' => env('NURSE_DOMAIN', 'nurse.doctorontap.com.ng'),
        'caregiver' => env('CAREGIVER_DOMAIN', 'caregiver.doctorontap.com.ng'),
        'customercare' => env('CUSTOMERCARE_DOMAIN', 'customercare.doctorontap.com.ng'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Domain Configuration
    |--------------------------------------------------------------------------
    |
    | Configure session domain for cross-domain authentication.
    | Set to the root domain (e.g., '.doctorontap.com.ng') to allow
    | sessions to work across all subdomains.
    |
    */

    'session_domain' => env('SESSION_DOMAIN', '.doctorontap.com.ng'),

    /*
    |--------------------------------------------------------------------------
    | Cookie Domain Configuration
    |--------------------------------------------------------------------------
    |
    | Configure cookie domain for cross-domain authentication.
    | Set to the root domain (e.g., '.doctorontap.com.ng') to allow
    | cookies to work across all subdomains.
    |
    */

    'cookie_domain' => env('COOKIE_DOMAIN', '.doctorontap.com.ng'),

    /*
    |--------------------------------------------------------------------------
    | Fallback Domain
    |--------------------------------------------------------------------------
    |
    | The default domain to use when multi-domain is disabled
    | or when no specific domain is configured.
    |
    */

    'fallback' => env('APP_URL', 'http://localhost'),
];



