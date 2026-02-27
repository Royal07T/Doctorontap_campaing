<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Caregiver') - DoctorOnTap</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        .brand-gradient { background: linear-gradient(180deg, #6D28D9 0%, #5B21B6 100%); }
    </style>
    @stack('head')
</head>
<body class="bg-gray-50 min-h-screen" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">

        {{-- ─── Sidebar ─── --}}
        <aside class="fixed inset-y-0 left-0 z-50 w-56 brand-gradient flex flex-col transform transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

            {{-- Logo --}}
            <div class="px-5 pt-6 pb-4 flex items-center justify-between">
                <a href="{{ route('care_giver.dashboard') }}" class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </div>
                    <div>
                        <span class="text-lg font-bold text-white">DoctorOnTap</span>
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-white/60">Caregiver Pro</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-white/70 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Navigation --}}
            @php $current = request()->route()?->getName() ?? ''; @endphp
            <nav class="flex-1 px-3 space-y-1 mt-2 overflow-y-auto">
                @php
                $navItems = [
                    ['route' => 'care_giver.dashboard',            'label' => 'Dashboard',           'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['route' => 'care_giver.patients.index',       'label' => 'My Patients',         'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                    ['route' => 'care_giver.daily-log.index',      'label' => 'Daily Health Log',    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                    ['route' => 'care_giver.communication.index',  'label' => 'Communication',       'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
                    ['route' => 'care_giver.schedules.index',      'label' => 'Schedules',           'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                    ['route' => 'care_giver.reports.index',        'label' => 'Reports',             'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ];
                @endphp

                @foreach($navItems as $item)
                @php
                    $routeExists = \Illuminate\Support\Facades\Route::has($item['route']);
                    $isActive = str_starts_with($current, $item['route']);
                @endphp
                @if($routeExists)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                   {{ $isActive ? 'bg-white/20 text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                    <span>{{ $item['label'] }}</span>
                </a>
                @else
                <span class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium text-white/40 cursor-not-allowed">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                    <span>{{ $item['label'] }}</span>
                    <span class="ml-auto text-[9px] bg-white/10 rounded px-1.5 py-0.5">Soon</span>
                </span>
                @endif
                @endforeach

            </nav>

            {{-- Emergency button --}}
            {{--
            <div class="px-3 pb-3 mt-auto">
                <button type="button" class="w-full flex items-center justify-center space-x-2 rounded-xl bg-red-500/90 hover:bg-red-500 px-4 py-3 text-xs font-bold text-white transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span>Escalate / Emergency</span>
                </button>
            </div>
            --}}

            {{-- Support card --}}
            <div class="px-3 pb-3">
                <div class="rounded-xl bg-white/10 p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-white/60">Need Help?</p>
                    <p class="text-sm text-white mt-1">Contact your supervisor or admin support.</p>
                    <button type="button" class="mt-3 w-full rounded-lg bg-orange-500 py-2 text-xs font-bold text-white transition hover:bg-orange-600">
                        Contact Support
                    </button>
                </div>
            </div>

            {{-- User info & Logout --}}
            <div class="px-3 pb-5 border-t border-white/10 pt-3">
                @php $authCaregiver = Auth::guard('care_giver')->user(); @endphp
                <div class="flex items-center space-x-3 px-2 mb-3">
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                        {{ strtoupper(substr($authCaregiver->name ?? 'C', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ $authCaregiver->name ?? 'Caregiver' }}</p>
                        <p class="text-xs text-white/50 truncate">{{ $authCaregiver->email ?? '' }}</p>
                    </div>
                </div>
                <a href="{{ route('care_giver.profile.index') }}"
                   class="flex items-center space-x-2 px-3 py-2 rounded-lg text-xs font-medium text-white/70 hover:bg-white/10 hover:text-white transition mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Profile & Account</span>
                </a>
                <a href="{{ route('care_giver.profile.index') }}?tab=security"
                   class="flex items-center space-x-2 px-3 py-2 rounded-lg text-xs font-medium text-white/70 hover:bg-white/10 hover:text-white transition mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Change Password</span>
                </a>
                <form method="POST" action="{{ route('care_giver.logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center space-x-2 px-3 py-2 rounded-lg text-xs font-medium text-red-300 hover:bg-red-500/20 hover:text-red-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Sign Out</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 bg-black/50 z-40 lg:hidden" style="display:none;"></div>

        {{-- ─── Main content area ─── --}}
        <div class="flex-1 flex flex-col overflow-hidden">

            {{-- Top bar --}}
            <header class="bg-white border-b border-gray-200 z-10">
                <div class="flex items-center justify-between px-4 sm:px-6 h-16">
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>

                        <div>
                            <h1 class="text-lg font-bold text-gray-900">@yield('page-title', 'Caregiver Portal')</h1>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        {{-- Search --}}
                        <div class="hidden sm:flex items-center bg-gray-100 rounded-lg px-3 py-1.5">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" placeholder="Search patients, logs..." class="bg-transparent border-0 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-0 w-40 lg:w-56">
                        </div>

                        @yield('header-actions')

                        {{-- Notifications bell --}}
                        <button type="button" class="relative p-2 text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>

                        {{-- User dropdown --}}
                        <div class="relative" x-data="{ userOpen: false }">
                            @php $headerCaregiver = Auth::guard('care_giver')->user(); @endphp
                            <button @click="userOpen = !userOpen" @click.outside="userOpen = false"
                                    class="flex items-center space-x-2 rounded-lg px-2 py-1.5 hover:bg-gray-100 transition">
                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-700 font-bold text-xs">
                                    {{ strtoupper(substr($headerCaregiver->name ?? 'C', 0, 1)) }}
                                </div>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div x-show="userOpen" x-transition
                                 class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50" style="display:none;">
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-sm font-semibold text-gray-900">{{ $headerCaregiver->name ?? 'Caregiver' }}</p>
                                    <p class="text-xs text-gray-500">{{ $headerCaregiver->email ?? '' }}</p>
                                </div>
                                <a href="{{ route('care_giver.profile.index') }}" class="flex items-center space-x-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span>Profile Settings</span>
                                </a>
                                <a href="{{ route('care_giver.profile.index') }}?tab=security" class="flex items-center space-x-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <span>Password & PIN</span>
                                </a>
                                <div class="border-t border-gray-100 mt-1 pt-1">
                                    <form method="POST" action="{{ route('care_giver.logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center space-x-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            <span>Sign Out</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Content --}}
            <main class="flex-1 overflow-y-auto bg-gray-50 p-4 sm:p-6">
                {{-- Flash messages --}}
                @if(session('success'))
                <div class="mb-4 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        {{ session('success') }}
                    </div>
                </div>
                @endif
                @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">{{ session('error') }}</div>
                @endif

                @yield('content')
            </main>

            {{-- Footer --}}
            <footer class="border-t border-gray-200 bg-white py-4">
                <div class="px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-2">
                    <p class="text-xs text-gray-400">&copy; {{ date('Y') }} DoctorOnTap Healthcare Platforms. Professional Use Only.</p>
                    <div class="flex items-center space-x-4 text-xs text-gray-400">
                        <a href="#" class="hover:text-gray-600">Privacy Policy</a>
                        <a href="#" class="hover:text-gray-600">Help Center</a>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
