<!-- Sidebar -->
<aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex flex-col"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
    <!-- Sidebar Header -->
    <div class="purple-gradient p-5 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap Logo" class="h-8 w-auto">
        </div>
        <button @click="sidebarOpen = false" class="lg:hidden text-white hover:text-gray-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- User Info -->
    <div class="p-5 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-purple-100 flex-shrink-0">
        <div class="flex items-center space-x-3">
            @php
                $doctor = Auth::guard('doctor')->user();
            @endphp
            @if($doctor->photo_url)
                <img src="{{ $doctor->photo_url }}" alt="Dr. {{ $doctor->name }}" class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-md">
            @else
                <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold border-2 border-white shadow-md">
                    {{ substr($doctor->name, 0, 1) }}
                </div>
            @endif
            <div class="flex-1">
                <p class="font-semibold text-gray-800 text-sm">Dr. {{ $doctor->name }}</p>
                <p class="text-xs text-gray-500">{{ $doctor->specialization ?? 'Doctor' }}</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="p-4 space-y-2 flex-1 overflow-y-auto">
        @php
            $currentRoute = Route::currentRouteName();
        @endphp

        <a href="{{ route('doctor.dashboard') }}" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($currentRoute === 'doctor.dashboard') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('doctor.consultations') }}" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($currentRoute === 'doctor.consultations' || $currentRoute === 'doctor.consultation-details') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>My Consultations</span>
        </a>

        <a href="{{ route('doctor.bank-accounts') }}" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($currentRoute === 'doctor.bank-accounts') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <span>Bank Accounts</span>
        </a>

        <a href="{{ route('doctor.payment-history') }}" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($currentRoute === 'doctor.payment-history') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Payment History</span>
        </a>

        <a href="{{ route('doctor.profile') }}" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($currentRoute === 'doctor.profile') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>Profile</span>
        </a>

        <a href="{{ route('doctor.availability') }}" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($currentRoute === 'doctor.availability') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Availability</span>
        </a>

        <a href="{{ route('doctor.support-tickets.index') }}" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if(str_starts_with($currentRoute, 'doctor.support-tickets')) text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Customer Care</span>
        </a>

        <div class="border-t border-gray-200 my-2"></div>

        <a href="{{ url('/') }}" target="_blank" 
           class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
            </svg>
            <span>View Website</span>
        </a>

        <form method="POST" action="{{ route('doctor.logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg font-medium transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Logout</span>
            </button>
        </form>
    </nav>
</aside>

<!-- Overlay for mobile sidebar -->
<div x-show="sidebarOpen" 
     @click="sidebarOpen = false"
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
     style="display: none;"></div>

