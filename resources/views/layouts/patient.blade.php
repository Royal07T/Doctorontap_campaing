<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Patient Dashboard') - DoctorOnTap</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg fixed h-full z-10" x-data="{ open: true }">
            <div class="p-6 border-b">
                <h1 class="text-2xl font-bold text-gray-800">DoctorOnTap</h1>
                <p class="text-sm text-gray-600 mt-1">Patient Portal</p>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-2">
                <a href="{{ route('patient.dashboard') }}" 
                   class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('patient.dashboard') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('patient.consultations') }}" 
                   class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('patient.consultations') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Consultations
                </a>

                <a href="{{ route('patient.medical-records') }}" 
                   class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('patient.medical-records') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Medical Records
                </a>

                <a href="{{ route('patient.payments') }}" 
                   class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('patient.payments') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Payments
                </a>

                @if(Auth::guard('patient')->user()->dependents()->count() > 0)
                    <a href="{{ route('patient.dependents') }}" 
                       class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('patient.dependents') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Dependents
                    </a>
                @endif

                <a href="{{ route('patient.profile') }}" 
                   class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('patient.profile') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Profile
                </a>

                <hr class="my-4">

                <a href="{{ route('consultation.index') }}" 
                   class="flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Consultation
                </a>

                <form method="POST" action="{{ route('patient.logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-3 rounded-lg text-red-600 hover:bg-red-50 transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Logout
                    </button>
                </form>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-64">
            <!-- Top Bar -->
            <div class="bg-white shadow-sm border-b">
                <div class="px-8 py-4 flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">@yield('title', 'Dashboard')</h2>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <button class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-full transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </button>

                        <!-- User Profile -->
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                                {{ substr(Auth::guard('patient')->user()->name, 0, 1) }}
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-800">{{ Auth::guard('patient')->user()->name }}</p>
                                <p class="text-xs text-gray-500">Patient</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <div class="p-8">
                @if(session('success'))
                    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                        <p class="text-green-700">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                        <p class="text-red-700">{{ session('error') }}</p>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>

