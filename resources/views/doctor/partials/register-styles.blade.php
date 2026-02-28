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
            padding: 3rem 2.75rem;
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

        .reg-image-hero-title {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.25;
            letter-spacing: -0.02em;
            margin: 0 0 0.875rem;
        }
        .reg-image-hero-title span { color: #c4b5fd; }
        .reg-image-hero-subtitle {
            font-size: 0.9375rem;
            color: rgba(255,255,255,0.78);
            margin: 0 0 2.25rem;
            line-height: 1.65;
            max-width: 22rem;
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
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 1rem;
            padding: 0.875rem 1.125rem;
            backdrop-filter: blur(10px);
            transition: background 0.2s, border-color 0.2s;
        }
        .reg-trust-badge-icon {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.625rem;
            background: rgba(255,255,255,0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .reg-trust-badge-icon svg { width: 1.125rem; height: 1.125rem; color: #fff; }
        .reg-trust-badge-text strong { display: block; font-size: 0.8125rem; font-weight: 600; color: #fff; }
        .reg-trust-badge-text span { font-size: 0.75rem; color: rgba(255,255,255,0.65); line-height: 1.4; }

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
        /* Invert logo to white on the purple bar; hide on desktop (image panel shows it instead) */
        .reg-topbar .reg-topbar-logo { height: 2rem; width: auto; opacity: 1; }
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
            position: sticky;
            top: 3.5rem;
            z-index: 40;
            padding: 1rem 1.25rem 1rem;
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 4px 15px rgba(126, 34, 206, 0.3);
        }
        @media (min-width: 640px) { .reg-progress-wrap { padding: 1rem 2rem 1.125rem; } }

        /* Segmented coloured bar (track on purple: darker) */
        .reg-progress-track {
            height: 6px;
            background: rgba(255,255,255,0.2);
            border-radius: 99px;
            overflow: hidden;
            margin-bottom: 1rem;
            position: relative;
            transition: background 0.2s ease;
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
            background: linear-gradient(90deg, #c084fc, #e879f9);
            box-shadow: 0 0 10px rgba(192, 132, 252, 0.5);
        }
        .reg-progress-fill[data-step="1"] {
            background: linear-gradient(90deg, #c084fc, #e879f9, #f472b6);
            box-shadow: 0 0 10px rgba(232, 121, 249, 0.5);
        }
        .reg-progress-fill[data-step="2"] {
            background: linear-gradient(90deg, #c084fc, #e879f9, #f472b6, #fb7185);
            box-shadow: 0 0 10px rgba(244, 114, 182, 0.5);
        }
        .reg-progress-fill[data-step="3"] {
            background: linear-gradient(90deg, #c084fc, #e879f9, #f472b6, #fb7185, #34d399);
            box-shadow: 0 0 12px rgba(52, 211, 153, 0.5);
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
            border: 2px solid rgba(255,255,255,0.4);
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            color: rgba(255,255,255,0.9);
            transition: all 0.3s ease;
        }
        .reg-step-circle.done {
            background: linear-gradient(135deg, #059669, #10b981);
            border-color: #059669;
            color: #fff;
            box-shadow: 0 2px 8px rgba(5,150,105,0.4);
        }
        .reg-step-circle.current {
            background: rgba(255,255,255,0.95);
            border-color: #fff;
            color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(255,255,255,0.4), 0 2px 8px rgba(0,0,0,0.15);
        }
        .reg-step-label {
            font-size: 0.6875rem;
            font-weight: 500;
            color: rgba(255,255,255,0.75);
            white-space: nowrap;
            display: none;
        }
        @media (min-width: 400px) { .reg-step-label { display: block; } }
        .reg-step-label.current { color: #fff; font-weight: 700; }
        .reg-step-label.done { color: #86efac; font-weight: 600; }

        /* ─── Form area ───────────────────────────────── */
        .reg-form-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1rem 2.5rem;
        }
        @media (min-width: 480px) { .reg-form-area { padding: 2.5rem 1.25rem 3rem; } }
        @media (min-width: 640px) { .reg-form-area { padding: 3rem 2rem 3.5rem; } }
        @media (min-width: 1024px) { .reg-form-area { padding: 4rem 3.5rem 4rem; } }

        .reg-form-inner { width: 100%; max-width: 560px; }

        /* Card: rounded-2xl, soft shadow, subtle border */
        @media (max-width: 1023px) {
            .registration-step.active {
                background: #fff;
                border-radius: 1.5rem;
                box-shadow: 0 10px 40px rgba(0,0,0,0.08);
                border: 1px solid rgba(226, 232, 240, 0.9);
                overflow: hidden;
                padding: 2rem 1.5rem;
                backdrop-filter: blur(8px);
                transition: box-shadow 0.2s ease, border-color 0.2s ease;
            }
        }
        @media (min-width: 480px) and (max-width: 1023px) {
            .registration-step.active { padding: 2.25rem 2rem; }
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

        /* ─── Step header (typography hierarchy) ───────── */
        .step-hero { margin-bottom: 2.25rem; }
        .step-hero-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            background: rgba(237, 233, 254, 0.9);
            border: 1px solid rgba(196, 181, 253, 0.6);
            border-radius: 999px;
            padding: 0.3125rem 0.875rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6d28d9;
            margin-bottom: 1rem;
            transition: background 0.2s, border-color 0.2s;
        }
        .step-hero h2 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.3;
            letter-spacing: -0.02em;
            margin: 0 0 0.375rem;
        }
        @media (min-width: 640px) {
            .step-hero h2 { font-size: 2rem; }
        }
        .step-hero p {
            font-size: 0.875rem;
            color: #64748b;
            margin: 0;
            line-height: 1.6;
        }

        /* ─── Section header (inside step) ───────────── */
        .reg-section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.75rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .reg-section-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .reg-section-icon svg { width: 1.125rem; height: 1.125rem; stroke: #7c3aed; }
        .reg-section-title { font-size: 1rem; font-weight: 600; color: #1e293b; }
        .reg-section-subtitle { font-size: 0.8125rem; color: #64748b; margin-top: 0.125rem; line-height: 1.4; }

        /* ─── Inputs (fallback for any non-component fields) ─── */
        .form-group { margin-bottom: 0; }
        .form-label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.5rem;
            letter-spacing: 0.01em;
        }
        .form-label .req { color: #ef4444; margin-left: 0.1875rem; }
        .form-control {
            width: 100%;
            height: 3rem;
            padding: 0 1rem;
            font-size: 0.875rem;
            color: #0f172a;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            outline: none;
            transition: all 0.2s ease;
            appearance: none;
            -webkit-appearance: none;
        }
        .form-control::placeholder { color: #94a3b8; }
        .form-control:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
        }
        .form-hint {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.375rem;
            line-height: 1.5;
        }
        .form-error-msg {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.75rem;
            color: #ef4444;
            margin-top: 0.375rem;
        }

        /* ─── Buttons ─────────────────────────────────── */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            min-height: 3rem;
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
            color: #fff;
            font-size: 0.9375rem;
            font-weight: 600;
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(109, 40, 217, 0.3);
            letter-spacing: 0.01em;
        }
        .btn-primary:hover:not(:disabled) {
            transform: scale(1.02);
            box-shadow: 0 6px 20px rgba(109, 40, 217, 0.4);
            filter: brightness(1.05);
        }
        .btn-primary:disabled { opacity: 0.55; cursor: not-allowed; }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            min-height: 3rem;
            background: #fff;
            color: #5b21b6;
            font-size: 0.9375rem;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-secondary:hover {
            background: #f8fafc;
            border-color: #c4b5fd;
            transform: scale(1.01);
        }

        .btn-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 2.25rem;
            gap: 0.75rem;
        }
        .btn-row.end { justify-content: flex-end; }

        /* ─── Secure badge under form ─────────────────── */
        .reg-secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }
        .reg-secure-badge span {
            font-size: 0.8125rem;
            color: #64748b;
            font-weight: 500;
        }
        .reg-secure-badge svg {
            width: 1rem;
            height: 1rem;
            color: #94a3b8;
            flex-shrink: 0;
        }
    </style>
