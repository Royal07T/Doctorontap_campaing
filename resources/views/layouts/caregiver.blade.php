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
        .purple-gradient { background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%); }
    </style>
    @stack('head')
</head>
<body class="bg-gray-50 min-h-screen flex flex-col" x-data="{ mobileNavOpen: false }">

    {{-- ─── Top Navigation Bar ─── --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo + Brand --}}
                <div class="flex items-center space-x-3">
                    <a href="{{ route('care_giver.dashboard') }}" class="flex items-center space-x-2">
                        <div class="w-9 h-9 rounded-lg purple-gradient flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        </div>
                        <div>
                            <span class="text-lg font-bold text-gray-900">DoctorOnTap</span>
                            <span class="ml-1 inline-flex items-center rounded bg-purple-100 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-purple-700">Caregiver Pro</span>
                        </div>
                    </a>
                </div>

                {{-- Desktop Navigation --}}
                @php $current = request()->route()?->getName() ?? ''; @endphp
                <nav class="hidden md:flex items-center space-x-1">
                    @php
                    $navItems = [
                        ['route' => 'care_giver.dashboard',       'label' => 'Daily Dashboard'],
                        ['route' => 'care_giver.patients.index',  'label' => 'My Patients'],
                        ['route' => 'care_giver.communication.index', 'label' => 'Shift History'],
                        ['route' => 'care_giver.profile.index',   'label' => 'Support'],
                    ];
                    @endphp
                    @foreach($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                       class="px-3 py-2 text-sm font-medium rounded-md transition-colors
                       {{ str_starts_with($current, $item['route']) ? 'text-purple-700 bg-purple-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                        {{ $item['label'] }}
                    </a>
                    @endforeach
                </nav>

                {{-- Right actions --}}
                <div class="flex items-center space-x-3">
                    <button type="button" class="hidden sm:inline-flex items-center rounded-full bg-red-500 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-red-600">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        Escalate / Emergency
                    </button>

                    {{-- User Avatar --}}
                    <div class="relative" x-data="{ profileOpen: false }">
                        <button @click="profileOpen = !profileOpen" class="w-9 h-9 rounded-full purple-gradient flex items-center justify-center text-white font-bold text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                            {{ substr(Auth::guard('care_giver')->user()->name, 0, 1) }}
                        </button>
                        <div x-show="profileOpen" @click.outside="profileOpen = false" x-transition
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900">{{ Auth::guard('care_giver')->user()->name }}</p>
                                <p class="text-xs text-gray-500">Caregiver</p>
                            </div>
                            <a href="{{ route('care_giver.profile.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile & Settings</a>
                            <form method="POST" action="{{ route('care_giver.logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Logout</button>
                            </form>
                        </div>
                    </div>

                    {{-- Mobile hamburger --}}
                    <button @click="mobileNavOpen = !mobileNavOpen" class="md:hidden p-2 text-gray-600 hover:text-gray-900 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Navigation --}}
        <div x-show="mobileNavOpen" x-transition class="md:hidden border-t border-gray-200 bg-white">
            <div class="px-4 py-3 space-y-1">
                @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="block px-3 py-2 rounded-md text-sm font-medium
                   {{ str_starts_with($current, $item['route']) ? 'text-purple-700 bg-purple-50' : 'text-gray-600 hover:bg-gray-50' }}">
                    {{ $item['label'] }}
                </a>
                @endforeach
                <button type="button" class="w-full mt-2 inline-flex items-center justify-center rounded-full bg-red-500 px-4 py-2 text-xs font-bold text-white">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    Escalate / Emergency
                </button>
            </div>
        </div>
    </header>

    {{-- ─── Main Content ─── --}}
    <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            {{-- Flash messages --}}
            @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                {{ session('error') }}
            </div>
            @endif

            @yield('content')
        </div>
    </main>

    {{-- ─── Footer ─── --}}
    <footer class="border-t border-gray-200 bg-white py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-xs text-gray-400">&copy; {{ date('Y') }} DoctorOnTap Healthcare Platforms. Professional Use Only.</p>
        </div>
    </footer>

    @livewireScripts
    @stack('scripts')
</body>
</html>
