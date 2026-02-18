<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page-title', 'Family Portal') – DoctorOnTap</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    <style>
        .teal-gradient { background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        {{-- Sidebar --}}
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl transform transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="teal-gradient p-5 flex items-center justify-between">
                <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap" class="h-8 w-auto">
                <button @click="sidebarOpen = false" class="lg:hidden text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>

            <div class="p-5 border-b border-gray-200 bg-gradient-to-r from-teal-50 to-teal-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-teal-600 flex items-center justify-center text-white font-bold">{{ substr(Auth::guard('family')->user()->name ?? '', 0, 1) }}</div>
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">{{ Auth::guard('family')->user()->name }}</p>
                        <p class="text-xs text-gray-500">Family Portal</p>
                    </div>
                </div>
            </div>

            <nav class="p-4 space-y-2">
                <a href="{{ route('family.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition-all {{ request()->routeIs('family.dashboard') ? 'teal-gradient text-white' : 'text-gray-700 hover:bg-teal-50 hover:text-teal-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('family.reports') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition-all {{ request()->routeIs('family.reports*') ? 'teal-gradient text-white' : 'text-gray-700 hover:bg-teal-50 hover:text-teal-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Reports</span>
                </a>
                <div class="border-t border-gray-200 my-2"></div>
                <form method="POST" action="{{ route('family.logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg font-medium transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Logout</span>
                    </button>
                </form>
            </nav>
        </aside>

        {{-- Overlay for mobile --}}
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 bg-black/50 z-40 lg:hidden" style="display:none;"></div>

        {{-- Main --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="teal-gradient shadow-lg z-10">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center gap-4">
                        <button @click="sidebarOpen = true" class="lg:hidden text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
                        <div>
                            <h1 class="text-xl font-bold text-white">@yield('page-title', 'Family Portal')</h1>
                            <p class="text-sm text-teal-200">Read-only access for family members</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">@yield('header-actions')</div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                @if(session('success'))
                    <div class="mb-4 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">{{ session('error') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
