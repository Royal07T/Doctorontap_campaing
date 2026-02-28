<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Doctor Login – {{ config('app.name') }}</title>

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

        /* ─── Image Panel (Left) ─────────────────────── */
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
                135deg,
                rgba(147, 51, 234, 0.82) 0%,
                rgba(126, 34, 206, 0.78) 40%,
                rgba(88, 28, 135, 0.88) 100%
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

        .reg-image-panel-logo {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .reg-image-panel-logo img {
            height: 4.25rem;
            width: auto;
            object-fit: contain;
        }
        @media (min-width: 1024px) {
            .reg-image-panel-logo img { height: 6rem; }
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

        /* ─── Form Panel (Right) ─────────────────────── */
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
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
            padding: 0.85rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            box-shadow: 0 4px 15px rgba(126, 34, 206, 0.3);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        @media (min-width: 640px) { .reg-topbar { padding: 0.8rem 2rem; } }
        .reg-topbar .reg-topbar-logo { height: 2rem; width: auto; opacity: 1; }
        @media (min-width: 1024px) { .reg-topbar .reg-topbar-logo { display: none; } }
        
        .reg-topbar-tagline {
            font-size: 0.8125rem;
            font-weight: 500;
            color: rgba(255,255,255,0.75);
            letter-spacing: 0.015em;
            display: none;
        }
        @media (min-width: 1024px) { .reg-topbar-tagline { display: block; } }

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
            padding: 2.75rem 1.5rem;
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
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
            position: relative;
        }
        .reg-mobile-hero p {
            font-size: 0.875rem;
            color: rgba(255,255,255,0.82);
            margin: 0;
            max-width: 28rem;
            position: relative;
        }
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

        /* ─── Form Area ───────────────────────────────── */
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

        .reg-form-inner { width: 100%; max-width: 440px; }

        @media (max-width: 1023px) {
            .login-card {
                background: #fff;
                border-radius: 1.25rem;
                box-shadow: 0 4px 24px rgba(109,40,217,0.08), 0 1px 4px rgba(0,0,0,0.05);
                border: 1px solid rgba(139,92,246,0.10);
                padding: 1.5rem 1.25rem;
            }
        }
        @media (min-width: 480px) and (max-width: 1023px) {
            .login-card { padding: 2rem 1.75rem; }
        }
        @media (min-width: 1024px) {
            .login-card {
                background: transparent;
                padding: 0;
            }
        }

        .step-hero { margin-bottom: 2rem; }
        .step-hero h2 {
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f0a2e;
            letter-spacing: -0.02em;
            margin: 0 0 0.5rem;
        }
        .step-hero p {
            font-size: 0.9375rem;
            color: #64748b;
            margin: 0;
        }

        /* ─── Inputs ──────────────────────────────────── */
        .form-label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            color: #111827;
            background: #fff;
            border: 1.5px solid #e2d9f8;
            border-radius: 0.875rem;
            outline: none;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 4px rgba(124,58,237,0.14);
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: #fff;
            font-size: 0.9375rem;
            font-weight: 600;
            border: none;
            border-radius: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(109,40,217,0.35);
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(109,40,217,0.42);
        }
    </style>
</head>
<body x-data="{ showPassword: false, isSubmitting: false }">

<div class="reg-shell">

    {{-- Left Image Panel (Desktop) --}}
    <aside class="reg-image-panel">
        <img src="{{ asset('img/Stethoscope wallpaper for doctors.jpeg') }}" alt="Doctor login" loading="eager">
        <div class="reg-image-overlay"></div>
        <div class="reg-image-content">
            <div class="reg-image-panel-logo">
                <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap">
            </div>
            <div>
                <h1 style="font-size:2.25rem;font-weight:800;color:#fff;line-height:1.2;letter-spacing:-0.02em;margin:0 0 0.875rem;">
                    Welcome back,<br>
                    <span style="color:#c4b5fd;">Doctor</span>
                </h1>
                <p style="font-size:1rem;color:rgba(255,255,255,0.75);margin:0 0 2.5rem;line-height:1.6;max-width:22rem;">
                    Access your secure portal to manage consultations, patients, and your practice efficiently.
                </p>
                <div class="reg-trust-badges">
                    <div class="reg-trust-badge">
                        <div class="reg-trust-badge-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div class="reg-trust-badge-text">
                            <strong>Secure Access</strong>
                            <span>Your data is protected and encrypted</span>
                        </div>
                    </div>
                </div>
            </div>
            <p style="font-size:0.75rem;color:rgba(255,255,255,0.4);">&copy; {{ date('Y') }} DoctorOnTap. All rights reserved.</p>
        </div>
    </aside>

    {{-- Right Form Panel --}}
    <div class="reg-form-panel">
        <header class="reg-topbar">
            <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap" class="reg-topbar-logo">
            <span class="reg-topbar-tagline"></span>
            <a href="{{ route('doctor.register') }}" class="reg-topbar-btn">
                Need an account?
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Sign Up
            </a>
        </header>

        <div class="reg-mobile-hero">
            <div class="reg-mobile-hero-badge">
                <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Secure Doctor Portal
            </div>
            <h1>Doctor Login</h1>
            <p>Sign in to your professional dashboard.</p>
            <div class="reg-mobile-trust">
                <div class="reg-mobile-trust-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Secure Access
                </div>
                <div class="reg-mobile-trust-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Encrypted
                </div>
            </div>
        </div>

        <main class="reg-form-area">
            <div class="reg-form-inner">
                <div class="login-card">
                    <div class="step-hero">
                        <h2>Sign in</h2>
                        <p>Welcome back! Please enter your credentials.</p>
                    </div>

                    {{-- Messages --}}
                    @if(session('success'))
                        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm flex items-center gap-3">
                            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm flex items-center gap-3">
                            <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('doctor.login.post') }}" @submit="isSubmitting = true">
                        @csrf

                        <div class="mb-5">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="doctor@doctorontap.com" class="form-control @error('email') border-red-500 @enderror">
                            @error('email') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-5">
                            <div class="flex justify-between items-center mb-1.5">
                                <label for="password" class="form-label">Password</label>
                                <a href="{{ route('doctor.password.request') }}" class="text-xs font-semibold text-purple-600 hover:text-purple-700">Forgot Password?</a>
                            </div>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required placeholder="Enter your password" class="form-control pr-10 @error('password') border-red-500 @enderror">
                                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                </button>
                            </div>
                            @error('password') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-6 flex items-center">
                            <input type="checkbox" id="remember" name="remember" class="w-4 h-4 text-purple-600 border-slate-300 rounded focus:ring-purple-500">
                            <label for="remember" class="ml-2 text-sm text-slate-600">Keep me signed in</label>
                        </div>

                        <button type="submit" :disabled="isSubmitting" class="btn-primary">
                            <span x-show="!isSubmitting" class="flex items-center gap-2">
                                Sign in to Dashboard
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                            <span x-show="isSubmitting" x-cloak class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                                Signing in...
                            </span>
                        </button>
                    </form>

                    <div class="mt-8 pt-6 border-t border-slate-200 text-center text-sm font-medium">
                        <a href="{{ url('/') }}" class="text-slate-500 hover:text-purple-600 inline-flex items-center gap-1.5 transition-colors">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                            Back to Website
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

</body>
</html>
