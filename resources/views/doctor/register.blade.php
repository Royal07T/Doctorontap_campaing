<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Doctor Registration – {{ config('app.name') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }

        /* ─── Layout ─────────────────────────────────── */
        html, body { height: 100%; margin: 0; padding: 0; }
        body { display: flex; flex-direction: column; min-height: 100vh; background: #f8f7ff; }

        .reg-shell {
            display: flex;
            flex: 1;
            min-height: 100vh;
        }

        /* ─── Image Panel ─────────────────────────────── */
        .reg-image-panel {
            display: none;
            position: sticky;
            top: 0;
            height: 100vh;
            width: 42%;
            flex-shrink: 0;
            overflow: hidden;
        }
        @media (min-width: 1024px) {
            .reg-image-panel { display: flex; flex-direction: column; }
        }

        .reg-image-panel img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center top;
        }

        .reg-image-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                160deg,
                rgba(109, 40, 217, 0.82) 0%,
                rgba(79, 16, 171, 0.75) 40%,
                rgba(15, 10, 40, 0.88) 100%
            );
        }

        .reg-image-content {
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            padding: 2.5rem 2.75rem;
        }

        .reg-trust-badges {
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
        }
        .reg-trust-badge {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 0.875rem;
            padding: 0.75rem 1rem;
            backdrop-filter: blur(8px);
        }
        .reg-trust-badge-icon {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.625rem;
            background: rgba(255,255,255,0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .reg-trust-badge-icon svg { width: 1.125rem; height: 1.125rem; color: #fff; }
        .reg-trust-badge-text strong { display: block; font-size: 0.8125rem; font-weight: 600; color: #fff; }
        .reg-trust-badge-text span { font-size: 0.75rem; color: rgba(255,255,255,0.7); }

        /* ─── Form Panel ──────────────────────────────── */
        .reg-form-panel {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            background: #f8f7ff;
        }

        /* ─── Topbar ──────────────────────────────────── */
        .reg-topbar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 50%, #8b5cf6 100%);
            padding: 0.7rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            box-shadow: 0 4px 20px rgba(91,33,182,0.45);
        }
        @media (min-width: 640px) { .reg-topbar { padding: 0.8rem 2rem; } }
        /* Invert logo to white on the purple bar; hide on desktop (image panel shows it instead) */
        .reg-topbar .reg-topbar-logo { height: 3.25rem; width: auto; filter: brightness(0) invert(1); opacity: 0.95; }
        @media (min-width: 1024px) { .reg-topbar .reg-topbar-logo { display: none; } }
        /* Centre tagline (desktop only) */
        .reg-topbar-tagline {
            font-size: 0.8125rem;
            font-weight: 500;
            color: rgba(255,255,255,0.75);
            letter-spacing: 0.015em;
            display: none;
        }
        @media (min-width: 1024px) { .reg-topbar-tagline { display: block; } }
        /* Sign-in button */
        .reg-topbar-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 1.1rem;
            background: rgba(255,255,255,0.15);
            border: 1.5px solid rgba(255,255,255,0.45);
            border-radius: 999px;
            font-size: 0.8125rem;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
            white-space: nowrap;
            backdrop-filter: blur(6px);
            transition: background 0.2s, border-color 0.2s, transform 0.18s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }
        .reg-topbar-btn:hover {
            background: rgba(255,255,255,0.28);
            border-color: rgba(255,255,255,0.75);
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.18);
        }
        .reg-topbar-btn svg { width: 0.875rem; height: 0.875rem; flex-shrink: 0; }

        /* ─── Mobile hero banner (small screens only) ─── */
        .reg-mobile-hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem 1.5rem 1.75rem;
            background: linear-gradient(135deg, #6d28d9 0%, #7c3aed 45%, #a855f7 80%, #ec4899 100%);
            position: relative;
            overflow: hidden;
        }
        .reg-mobile-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
        }
        @media (min-width: 1024px) { .reg-mobile-hero { display: none; } }
        .reg-mobile-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 999px;
            padding: 0.25rem 0.875rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 0.75rem;
            backdrop-filter: blur(6px);
        }
        .reg-mobile-hero h1 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.22;
            letter-spacing: -0.02em;
            margin: 0 0 0.5rem;
        }
        .reg-mobile-hero p {
            font-size: 0.875rem;
            color: rgba(255,255,255,0.82);
            margin: 0;
            max-width: 28rem;
        }
        /* Mobile trust strip */
        .reg-mobile-trust {
            display: flex;
            gap: 0.625rem;
            margin-top: 1.25rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        .reg-mobile-trust-item {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 999px;
            padding: 0.25rem 0.75rem;
            font-size: 0.6875rem;
            font-weight: 600;
            color: #fff;
        }
        .reg-mobile-trust-item svg { width: 0.75rem; height: 0.75rem; }

        /* ─── Progress bar ────────────────────────────── */
        .reg-progress-wrap {
            padding: 0.75rem 1.25rem 0.875rem;
            background: #fff;
            border-bottom: 1px solid rgba(139,92,246,0.10);
        }
        @media (min-width: 640px) { .reg-progress-wrap { padding: 0.75rem 2rem 0.9rem; } }

        /* Segmented coloured bar */
        .reg-progress-track {
            height: 6px;
            background: #ede9fe;
            border-radius: 99px;
            overflow: hidden;
            margin-bottom: 0.9rem;
            position: relative;
        }
        .reg-progress-fill {
            height: 100%;
            border-radius: 99px;
            transition: width 0.55s cubic-bezier(0.4,0,0.2,1),
                        background 0.55s ease;
            position: relative;
        }
        /* Step-specific bar colours (set from JS via data-step attr on fill) */
        .reg-progress-fill[data-step="0"] {
            background: linear-gradient(90deg, #7c3aed, #8b5cf6);
            box-shadow: 0 0 10px rgba(124,58,237,0.5);
        }
        .reg-progress-fill[data-step="1"] {
            background: linear-gradient(90deg, #7c3aed, #a855f7, #ec4899);
            box-shadow: 0 0 10px rgba(168,85,247,0.5);
        }
        .reg-progress-fill[data-step="2"] {
            background: linear-gradient(90deg, #7c3aed, #a855f7, #ec4899, #f97316);
            box-shadow: 0 0 10px rgba(249,115,22,0.45);
        }
        .reg-progress-fill[data-step="3"] {
            background: linear-gradient(90deg, #7c3aed, #a855f7, #ec4899, #f97316, #10b981);
            box-shadow: 0 0 12px rgba(16,185,129,0.45);
        }
        /* Shimmer animation on the bar */
        .reg-progress-fill::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.30) 50%, transparent 100%);
            background-size: 200% 100%;
            animation: shimmer 2.2s infinite linear;
        }
        @keyframes shimmer {
            0%   { background-position: -200% 0; }
            100% { background-position:  200% 0; }
        }

        .reg-steps-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            position: relative;
        }
        .reg-step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
            flex: 1;
        }
        .reg-step-circle {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            border: 2px solid #e2d8f7;
            background: #f5f3ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            color: #c4b5fd;
            transition: all 0.3s ease;
        }
        .reg-step-circle.done {
            background: linear-gradient(135deg, #059669, #10b981);
            border-color: #059669;
            color: #fff;
            box-shadow: 0 2px 8px rgba(5,150,105,0.35);
        }
        .reg-step-circle.current {
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            border-color: #7c3aed;
            color: #fff;
            box-shadow: 0 0 0 4px rgba(124,58,237,0.18), 0 2px 8px rgba(124,58,237,0.3);
        }
        .reg-step-label {
            font-size: 0.6875rem;
            font-weight: 500;
            color: #c4b5fd;
            white-space: nowrap;
            display: none;
        }
        @media (min-width: 400px) { .reg-step-label { display: block; } }
        .reg-step-label.current { color: #7c3aed; font-weight: 700; }
        .reg-step-label.done { color: #059669; font-weight: 600; }

        /* ─── Form area ───────────────────────────────── */
        .reg-form-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.5rem 1rem 2rem;
        }
        @media (min-width: 480px) { .reg-form-area { padding: 2rem 1.25rem; } }
        @media (min-width: 640px) { .reg-form-area { padding: 2.5rem 2rem; } }
        @media (min-width: 1024px) { .reg-form-area { padding: 3rem 3.5rem; } }

        .reg-form-inner { width: 100%; max-width: 560px; }

        /* Mobile card wrapper around each step */
        @media (max-width: 1023px) {
            .registration-step.active {
                background: #fff;
                border-radius: 1.25rem;
                box-shadow: 0 4px 24px rgba(109,40,217,0.08), 0 1px 4px rgba(0,0,0,0.05);
                border: 1px solid rgba(139,92,246,0.10);
                overflow: hidden;
                padding: 1.5rem 1.25rem;
            }
        }
        @media (min-width: 480px) and (max-width: 1023px) {
            .registration-step.active { padding: 2rem 1.75rem; }
        }

        /* ─── Step sections ───────────────────────────── */
        .registration-step { display: none; }
        .registration-step.active {
            display: block;
            animation: stepIn 0.35s cubic-bezier(0.4,0,0.2,1);
        }
        @keyframes stepIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ─── Step header ─────────────────────────────── */
        .step-hero { margin-bottom: 2rem; }
        .step-hero-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            background: linear-gradient(135deg, #f3edff, #ede9fe);
            border: 1px solid #c4b5fd;
            border-radius: 999px;
            padding: 0.25rem 0.875rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6d28d9;
            margin-bottom: 0.875rem;
        }
        .step-hero h2 {
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f0a2e;
            line-height: 1.22;
            letter-spacing: -0.02em;
            margin: 0 0 0.5rem;
        }
        .step-hero p {
            font-size: 0.9375rem;
            color: #64748b;
            margin: 0;
            line-height: 1.6;
        }

        /* ─── Section header (inside step) ───────────── */
        .reg-section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #ede9fe;
        }
        .reg-section-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            background: linear-gradient(135deg, #ede9fe, #f3edff);
            border: 1px solid #c4b5fd;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .reg-section-icon svg { width: 1.125rem; height: 1.125rem; stroke: #7c3aed; }
        .reg-section-title { font-size: 1.0625rem; font-weight: 700; color: #1e1340; }
        .reg-section-subtitle { font-size: 0.8125rem; color: #7c85a2; margin-top: 0.125rem; }

        /* ─── Inputs ──────────────────────────────────── */
        .form-group { margin-bottom: 0; }
        .form-label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.4375rem;
            letter-spacing: 0.01em;
        }
        .form-label .req { color: #ef4444; margin-left: 0.1875rem; }
        .form-control {
            width: 100%;
            padding: 0.6875rem 0.9375rem;
            font-size: 0.875rem;
            color: #111827;
            background: #fff;
            border: 1.5px solid #e2d9f8;
            border-radius: 0.75rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            appearance: none;
            -webkit-appearance: none;
        }
        .form-control::placeholder { color: #a8b3cf; }
        .form-control:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 4px rgba(124,58,237,0.14);
        }
        .form-hint {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 0.375rem;
            line-height: 1.5;
        }
        .form-error-msg {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.75rem;
            color: #ef4444;
            margin-top: 0.4rem;
        }

        /* ─── Buttons ─────────────────────────────────── */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6875rem 1.5rem;
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: #fff;
            font-size: 0.9375rem;
            font-weight: 600;
            border: none;
            border-radius: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(109,40,217,0.35);
            letter-spacing: 0.01em;
        }
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(109,40,217,0.42);
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }
        .btn-primary:disabled { opacity: 0.55; cursor: not-allowed; }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6875rem 1.25rem;
            background: #f1edff;
            color: #5b21b6;
            font-size: 0.9375rem;
            font-weight: 600;
            border: 1.5px solid #ddd6fe;
            border-radius: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-secondary:hover {
            background: #ede9fe;
            border-color: #c4b5fd;
            transform: translateY(-1px);
        }

        .btn-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 2rem;
            gap: 0.75rem;
        }
        .btn-row.end { justify-content: flex-end; }
    </style>

    <script>
        window.toggleDoctorPasswordVisibility = function(inputId, buttonId) {
            var passwordInput = document.getElementById(inputId);
            var eyeOpen = document.getElementById(buttonId + '-open');
            var eyeClosed = document.getElementById(buttonId + '-closed');
            if (!passwordInput || !eyeOpen || !eyeClosed) return;
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        };
    </script>
</head>
<body x-data="{ isSubmitting: false }">

<div class="reg-shell">

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- IMAGE PANEL (left, sticky, full height) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <aside class="reg-image-panel">
        <img src="{{ asset('img/Stethoscope wallpaper for doctors.jpeg') }}" alt="Doctor registration" loading="eager">
        <div class="reg-image-overlay"></div>

        <div class="reg-image-content">
            {{-- Brand logo --}}
            <div>
                <img src="{{ asset('img/dashlogo.png') }}" alt="DoctorOnTap" style="height:4.25rem;width:auto;object-fit:contain;">
            </div>

            {{-- Headline --}}
            <div>
                <h1 style="font-size:2rem;font-weight:800;color:#fff;line-height:1.2;letter-spacing:-0.02em;margin:0 0 0.875rem;">
                    Join Nigeria's leading<br>
                    <span style="color:#c4b5fd;">telehealth platform</span>
                </h1>
                <p style="font-size:0.9375rem;color:rgba(255,255,255,0.72);margin:0 0 2.25rem;line-height:1.65;max-width:22rem;">
                    Connect with patients across Nigeria, manage consultations, and grow your practice — all from one place.
                </p>

                {{-- Trust badges --}}
                <div class="reg-trust-badges">
                    <div class="reg-trust-badge">
                        <div class="reg-trust-badge-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div class="reg-trust-badge-text">
                            <strong>MDCN Verified</strong>
                            <span>All doctors undergo KYC verification</span>
                        </div>
                    </div>
                    <div class="reg-trust-badge">
                        <div class="reg-trust-badge-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="reg-trust-badge-text">
                            <strong>Fast Payments</strong>
                            <span>Receive consultation fees directly</span>
                        </div>
                    </div>
                    <div class="reg-trust-badge">
                        <div class="reg-trust-badge-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div class="reg-trust-badge-text">
                            <strong>Quick Approval</strong>
                            <span>Applications reviewed in 1–2 business days</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <p style="font-size:0.75rem;color:rgba(255,255,255,0.4);">&copy; {{ date('Y') }} DoctorOnTap. All rights reserved.</p>
        </div>
    </aside>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- FORM PANEL (right, scrollable) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="reg-form-panel">

        {{-- Topbar --}}
        <header class="reg-topbar">
            {{-- Logo: always visible, white-filtered on purple bg --}}
            <img src="{{ asset('img/sitelogo.png') }}" alt="DoctorOnTap" class="reg-topbar-logo">

            {{-- Tagline, desktop only --}}
            <span class="reg-topbar-tagline"></span>

            {{-- Sign-in button --}}
            <a href="{{ route('doctor.login') }}" class="reg-topbar-btn">
                Already registered?
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                Sign In
            </a>
        </header>

        {{-- Mobile hero banner (only visible < 1024px) --}}
        <div class="reg-mobile-hero">
            <div class="reg-mobile-hero-badge">
                <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Nigeria's #1 Telehealth Platform
            </div>
            <h1>Join DoctorOnTap</h1>
            <p>Connect with patients, manage consultations &amp; grow your practice — all from one place.</p>
            <div class="reg-mobile-trust">
                <div class="reg-mobile-trust-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    MDCN Verified
                </div>
                <div class="reg-mobile-trust-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Fast Payments
                </div>
                <div class="reg-mobile-trust-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    1–2 Day Approval
                </div>
            </div>
        </div>

        {{-- Progress bar --}}
        <div class="reg-progress-wrap">
            <div class="reg-progress-track">
                <div id="progress-bar" class="reg-progress-fill" data-step="0" style="width:25%;" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="4"></div>
            </div>
            <div class="reg-steps-row">
                @foreach(['Personal', 'Professional', 'Documents', 'Security'] as $i => $stepLabel)
                    <div class="reg-step-item" data-progress-step="{{ $i }}">
                        <div class="reg-step-circle {{ $i === 0 ? 'current' : '' }}" data-step-circle="{{ $i }}">
                            {{ $i + 1 }}
                        </div>
                        <span class="reg-step-label {{ $i === 0 ? 'current' : '' }}" data-step-label="{{ $i }}">{{ $stepLabel }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Form area --}}
        <div class="reg-form-area">
            <div class="reg-form-inner">

                <form method="POST" action="{{ route('doctor.register.post') }}" enctype="multipart/form-data" id="doctor-registration-form" @submit="isSubmitting = true">
                    @csrf

                    <x-doctor-registration.form-errors :errors="$errors" />

                    {{-- ── Step 1: Personal ── --}}
                    <section class="registration-step active" data-step="0">
                        <div class="step-hero">
                            <div class="step-hero-pill">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Step 1 of 4
                            </div>
                            <h2>Create your account</h2>
                            <p>Let's start with your personal details so we can identify you.</p>
                        </div>

                        <div class="reg-section-header">
                            <div class="reg-section-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div>
                                <div class="reg-section-title">Personal Information</div>
                                <div class="reg-section-subtitle">Your basic details</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <x-doctor-registration.input label="A. First Name" name="first_name" id="first_name" :value="old('first_name')" required type="text" minlength="2" maxlength="255" pattern="[a-zA-Z\s'\-]+" placeholder="e.g., Glory" title="First name should only contain letters, spaces, hyphens, or apostrophes" />
                            <x-doctor-registration.input label="B. Last Name" name="last_name" id="last_name" :value="old('last_name')" required type="text" minlength="2" maxlength="255" pattern="[a-zA-Z\s'\-]+" placeholder="e.g., Iniabasi" title="Last name should only contain letters, spaces, hyphens, or apostrophes" />
                            <x-doctor-registration.select label="C. Gender" name="gender" id="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            </x-doctor-registration.select>
                            <x-doctor-registration.input label="D. Phone Number" name="phone" id="phone" :value="old('phone')" required type="tel" minlength="10" maxlength="20" pattern="[0-9+\s\-\(\)]+" placeholder="e.g., 09067726381" title="Please enter a valid phone number (at least 10 digits)" />
                            <x-doctor-registration.input label="E. Email Address" name="email" id="email" :value="old('email')" required type="email" maxlength="255" placeholder="e.g., gloryiniabasi2000@gmail.com" title="Please enter a valid email address" class="sm:col-span-2" />
                        </div>
                        <div class="btn-row end">
                            <button type="button" class="registration-next btn-primary">
                                Next
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </section>

                    {{-- ── Step 2: Professional ── --}}
                    <section class="registration-step" data-step="1">
                        <div class="step-hero">
                            <div class="step-hero-pill">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Step 2 of 4
                            </div>
                            <h2>Professional details</h2>
                            <p>Tell us about your medical expertise and work environment.</p>
                        </div>

                        <div class="reg-section-header">
                            <div class="reg-section-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <div class="reg-section-title">Professional Details</div>
                                <div class="reg-section-subtitle">Your medical expertise</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <x-doctor-registration.select label="F. Specialty" name="specialization" id="specialization" required class="sm:col-span-2" hint="Select your area of medical expertise from the list.">
                                <option value="">Select your medical specialty</option>
                                @foreach($specialties as $specialty)
                                    <option value="{{ $specialty->name }}" {{ old('specialization') == $specialty->name ? 'selected' : '' }}>
                                        {{ $specialty->name }}
                                        @if($specialty->description) — {{ Str::limit($specialty->description, 50) }} @endif
                                    </option>
                                @endforeach
                            </x-doctor-registration.select>
                            <x-doctor-registration.checkbox name="is_consultant" id="is_consultant" label="I am a consultant in this specialization *" class="sm:col-span-2"
                                :description="'Only consultants can provide second opinion services. Please check this box if you are a consultant in your selected specialization.'" />
                            <x-doctor-registration.input label="G. Years of Experience" name="experience" id="experience" :value="old('experience')" required type="text" placeholder="e.g., 2 years" />
                            <x-doctor-registration.input label="H. Consultation Fee (₦)" name="consultation_fee" id="consultation_fee" :value="old('consultation_fee')" required type="number" step="0.01" min="0" placeholder="e.g., 5000" hint="Your suggested fee. Admin may adjust during approval." />
                            <x-doctor-registration.input label="I. Present Place of Work" name="place_of_work" id="place_of_work" :value="old('place_of_work')" required type="text" placeholder="e.g., Capitol hill hospital, Warri, Delta state" class="sm:col-span-2" />
                            <x-doctor-registration.select label="J. Your Role" name="role" id="role" required>
                                <option value="">Select Role</option>
                                <option value="clinical" {{ old('role') == 'clinical' ? 'selected' : '' }}>Clinical</option>
                                <option value="non-clinical" {{ old('role') == 'non-clinical' ? 'selected' : '' }}>Non-Clinical</option>
                            </x-doctor-registration.select>
                            <x-doctor-registration.select label="L. State" name="state" id="state" required>
                                <option value="">Select your state</option>
                                @foreach($states as $state)
                                    <option value="{{ $state->id }}" {{ old('state') == $state->id ? 'selected' : '' }}>{{ $state->name }}</option>
                                @endforeach
                            </x-doctor-registration.select>
                            <x-doctor-registration.select label="City" name="location" id="location" required disabled hint="Select your city from the list.">
                                <option value="">Select state first</option>
                            </x-doctor-registration.select>
                            <x-doctor-registration.select label="M. MDCN License Status" name="mdcn_license_current" id="mdcn_license_current" required class="sm:col-span-2" hint="To practice on DoctorOnTap, your MDCN license must be up to date for KYC purposes.">
                                <option value="">Select Status</option>
                                <option value="yes" {{ old('mdcn_license_current') == 'yes' ? 'selected' : '' }}>✓ Yes, up to date</option>
                                <option value="processing" {{ old('mdcn_license_current') == 'processing' ? 'selected' : '' }}>⏳ Still processing / Awaiting update</option>
                                <option value="no" {{ old('mdcn_license_current') == 'no' ? 'selected' : '' }}>✗ No</option>
                            </x-doctor-registration.select>
                            <x-doctor-registration.input label="N. Languages Spoken" name="languages" id="languages" :value="old('languages')" required type="text" placeholder="e.g., English" />
                            <x-doctor-registration.textarea name="days_of_availability" id="days_of_availability" label="O. Days of Availability" required rows="3" placeholder="e.g., Two weeks of day shifts and one week of night shift, in that order."
                                :hint="'Describe your availability schedule'" />
                        </div>
                        <div class="btn-row">
                            <button type="button" class="registration-prev btn-secondary">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                                Previous
                            </button>
                            <button type="button" class="registration-next btn-primary">
                                Next
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </section>

                    {{-- ── Step 3: Documents ── --}}
                    <section class="registration-step" data-step="2">
                        <div class="step-hero">
                            <div class="step-hero-pill">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Step 3 of 4
                            </div>
                            <h2>Upload credentials</h2>
                            <p>We need your official documents to verify your medical license.</p>
                        </div>

                        <div class="reg-section-header">
                            <div class="reg-section-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <div class="reg-section-title">Documentation</div>
                                <div class="reg-section-subtitle">Upload your credentials for verification</div>
                            </div>
                        </div>

                        <x-doctor-registration.document-upload name="certificate" label="Upload MDCN License or Medical Certificate" accept=".pdf,.jpg,.jpeg,.png" :required="true" />

                        <div class="btn-row">
                            <button type="button" class="registration-prev btn-secondary">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                                Previous
                            </button>
                            <button type="button" class="registration-next btn-primary">
                                Next
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </section>

                    {{-- ── Step 4: Security ── --}}
                    <section class="registration-step" data-step="3">
                        <div class="step-hero">
                            <div class="step-hero-pill">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Step 4 of 4
                            </div>
                            <h2>Secure your account</h2>
                            <p>Create a strong password. We'll send a verification email after you submit.</p>
                        </div>

                        <div class="reg-section-header">
                            <div class="reg-section-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <div>
                                <div class="reg-section-title">Account Security</div>
                                <div class="reg-section-subtitle">Create a secure password</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <x-doctor-registration.password-with-toggle name="password" id="password" label="Password" toggleId="password-eye" />
                            <x-doctor-registration.password-with-toggle name="password_confirmation" id="password_confirmation" label="Confirm Password" toggleId="password-confirmation-eye" />
                        </div>

                        {{-- Completion note --}}
                        <div style="display:flex;align-items:flex-start;gap:0.625rem;background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1.5px solid #86efac;border-radius:0.875rem;padding:1rem 1.125rem;margin-top:1.5rem;">
                            <svg width="18" height="18" fill="none" stroke="#16a34a" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:0.1rem;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <div>
                                <p style="font-size:0.8125rem;font-weight:600;color:#15803d;margin:0 0 0.25rem;">Almost done!</p>
                                <p style="font-size:0.8125rem;color:#16a34a;margin:0;line-height:1.5;">Applications are reviewed within 1–2 business days. You'll receive a confirmation email once approved.</p>
                            </div>
                        </div>

                        <div class="btn-row">
                            <button type="button" class="registration-prev btn-secondary">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                                Previous
                            </button>
                            <button type="submit" :disabled="isSubmitting" class="btn-primary" style="min-width:11rem;">
                                <span x-show="!isSubmitting" style="display:flex;align-items:center;gap:0.5rem;">
                                    Complete Registration
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span x-show="isSubmitting" x-cloak style="display:flex;align-items:center;gap:0.5rem;">
                                    <svg class="animate-spin" style="width:1rem;height:1rem;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Submitting…
                                </span>
                            </button>
                        </div>
                    </section>

                </form>
            </div>
        </div>
    </div>{{-- end form panel --}}
</div>{{-- end shell --}}

<x-system-preloader x-show="isSubmitting" message="Submitting your registration..." />

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var sections      = document.querySelectorAll('.registration-step');
        var progressBar   = document.getElementById('progress-bar');
        var currentStep   = 0;
        var totalSteps    = 4;
        var oldState      = @json(old('state'));
        var oldLocation   = @json(old('location', ''));
        var stateSelect   = document.getElementById('state');
        var locationSelect = document.getElementById('location');

        function getCircle(i) { return document.querySelector('[data-step-circle="' + i + '"]'); }
        function getLabel(i)  { return document.querySelector('[data-step-label="' + i + '"]'); }

        function goToStep(step) {
            currentStep = step;
            sections.forEach(function (sec) {
                var s = parseInt(sec.dataset.step, 10);
                sec.classList.toggle('active', s === step);
            });
            updateProgress(step);
            // Scroll form panel back to top
            var panel = document.querySelector('.reg-form-panel');
            if (panel) panel.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function updateProgress(step) {
            var pct = ((step + 1) / totalSteps) * 100;
            if (progressBar) {
                progressBar.style.width = pct + '%';
                progressBar.setAttribute('aria-valuenow', step + 1);
                progressBar.setAttribute('data-step', step); // drives CSS colour gradient
            }
            for (var i = 0; i < totalSteps; i++) {
                var circle = getCircle(i);
                var label  = getLabel(i);
                if (!circle) continue;

                circle.classList.remove('done', 'current');
                if (label) label.classList.remove('done', 'current');

                if (i < step) {
                    circle.classList.add('done');
                    circle.innerHTML = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>';
                    if (label) label.classList.add('done');
                } else if (i === step) {
                    circle.classList.add('current');
                    circle.textContent = i + 1;
                    if (label) label.classList.add('current');
                } else {
                    circle.textContent = i + 1;
                }
            }
        }

        document.querySelectorAll('.registration-next').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (currentStep < totalSteps - 1) goToStep(currentStep + 1);
            });
        });
        document.querySelectorAll('.registration-prev').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (currentStep > 0) goToStep(currentStep - 1);
            });
        });

        goToStep(0);

        // State → City cascade
        if (stateSelect && locationSelect) {
            stateSelect.addEventListener('change', function () {
                var stateId = this.value;
                locationSelect.innerHTML = '<option value="">Loading cities…</option>';
                locationSelect.disabled = true;
                if (stateId) {
                    fetch('/doctor/states/' + stateId + '/cities')
                        .then(function (r) { return r.json(); })
                        .then(function (cities) {
                            locationSelect.innerHTML = '<option value="">Select your city</option>';
                            cities.forEach(function (city) {
                                var opt = document.createElement('option');
                                opt.value = city.name;
                                opt.textContent = city.name;
                                if (oldLocation === city.name) opt.selected = true;
                                locationSelect.appendChild(opt);
                            });
                            locationSelect.disabled = false;
                        })
                        .catch(function () {
                            locationSelect.innerHTML = '<option value="">Error loading cities</option>';
                            locationSelect.disabled = false;
                        });
                } else {
                    locationSelect.innerHTML = '<option value="">Select state first</option>';
                    locationSelect.disabled = true;
                }
            });
            if (oldState) {
                stateSelect.value = oldState;
                stateSelect.dispatchEvent(new Event('change'));
            }
        }
    });
</script>
</body>
</html>
