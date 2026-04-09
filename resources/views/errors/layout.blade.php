<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - DoctorOnTap</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen font-sans text-gray-900">
    <div class="relative min-h-screen overflow-hidden">
        <div class="absolute -top-32 -right-24 h-72 w-72 rounded-full bg-purple-300/30 blur-3xl"></div>
        <div class="absolute -bottom-20 -left-20 h-72 w-72 rounded-full bg-rose-200/30 blur-3xl"></div>

        <div class="relative z-10 flex min-h-screen items-center justify-center px-4 py-12">
            <div class="w-full max-w-3xl">
                <div class="mb-6 flex items-center gap-3">
                    <div class="h-11 w-11 rounded-xl purple-gradient flex items-center justify-center shadow-md">
                        <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap" class="h-6 w-auto" onerror="this.onerror=null; this.style.display='none';">
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.2em] text-gray-500">DoctorOnTap</p>
                        <h1 class="text-lg font-semibold text-gray-900">@yield('title')</h1>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8 md:p-10">
                    <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-xs font-semibold text-purple-600 uppercase tracking-[0.2em]">@yield('message')</p>
                            <h2 class="mt-2 text-4xl md:text-5xl font-extrabold text-gray-900">@yield('code')</h2>
                            <p class="mt-3 text-sm md:text-base text-gray-600 max-w-xl">@yield('description')</p>
                        </div>
                        <div class="rounded-2xl bg-purple-50 border border-purple-100 p-6 text-center">
                            <svg class="mx-auto h-10 w-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6l4 2" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="mt-3 text-xs font-semibold uppercase tracking-[0.2em] text-purple-700">Need help?</p>
                            <p class="mt-1 text-xs text-purple-600">If this keeps happening, contact support.</p>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-3">
                        @yield('actions')
                    </div>
                </div>

                <p class="mt-6 text-xs text-gray-500">Request: {{ request()->getRequestUri() }}</p>
            </div>
        </div>
    </div>
</body>
</html>
