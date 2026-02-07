<!-- Sidebar -->
<aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex flex-col sidebar-scrollable"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       style="scrollbar-width: thin; scrollbar-color: #a78bfa #f3f4f6;">
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
    <div class="p-5 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-blue-50 flex-shrink-0">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold">
                {{ substr(Auth::guard('admin')->user()->name, 0, 1) }}
            </div>
            <div class="flex-1">
                <p class="font-semibold text-gray-800 text-sm">{{ Auth::guard('admin')->user()->name }}</p>
                <p class="text-xs text-gray-500">Administrator</p>
            </div>
        </div>
    </div>

    <!-- Navigation - Scrollable -->
    <nav class="p-4 space-y-2 flex-1 overflow-y-auto">
        <a href="{{ route('admin.dashboard') }}" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'dashboard') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('admin.consultations') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'consultations') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Consultations</span>
        </a>

        <a href="{{ route('admin.patients') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'patients') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>Patient Records</span>
        </a>

        <a href="{{ route('admin.payments') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'payments') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span>Payments</span>
        </a>

        <a href="{{ route('admin.doctor-payments') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'doctor-payments') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Doctor Payments</span>
        </a>

        <a href="{{ route('admin.doctors') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'doctors') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span>Doctors</span>
        </a>

        <a href="{{ route('admin.most-consulted-doctors') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'most-consulted-doctors') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span>Most Consulted</span>
        </a>

        <a href="{{ route('admin.doctor-registrations') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'doctor-registrations') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Doctor Registrations</span>
        </a>

        <a href="{{ route('admin.admin-users') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'admin-users') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>Admin Users</span>
        </a>

        <a href="{{ route('admin.canvassers') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'canvassers') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span>Canvassers</span>
        </a>

        <a href="{{ route('admin.canvasser-patients') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'canvasser-patients') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>Canvasser Patients</span>
        </a>

        <a href="{{ route('admin.nurses') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'nurses') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <span>Nurses</span>
        </a>

        <a href="{{ route('admin.customer-cares') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'customer-cares') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>Customer Care</span>
        </a>

        <a href="{{ route('admin.care-givers') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'care-givers') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <span>Care Givers</span>
        </a>

        <a href="{{ route('admin.vital-signs') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'vital-signs') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <span>Vital Signs Records</span>
        </a>

        <a href="{{ route('admin.reviews') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'reviews') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
            </svg>
            <span>Reviews & Feedback</span>
        </a>

        <a href="{{ route('admin.sms-templates.index') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'sms-templates') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
            </svg>
            <span>SMS Templates</span>
        </a>

        <a href="{{ route('admin.email-templates.index') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'email-templates') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <span>Email Templates</span>
        </a>

        <div class="border-t border-gray-200 my-2"></div>

        @if(Auth::guard('admin')->user()->isSuperAdmin())
        <a href="{{ route('super-admin.dashboard') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all text-yellow-700 hover:bg-yellow-50 hover:text-yellow-800 border-l-4 border-yellow-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span>Super Admin</span>
        </a>
        @endif

        <a href="{{ route('admin.security') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'security') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span>Security Monitoring</span>
        </a>

        <a href="{{ route('admin.settings') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'settings') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>Settings</span>
        </a>

        <a href="{{ url('/') }}" target="_blank"
           class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
            </svg>
            <span>View Website</span>
        </a>

        <form method="POST" action="{{ route('admin.logout') }}">
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

<style>
/* Custom scrollbar styling for sidebar navigation */
.sidebar-scrollable nav::-webkit-scrollbar {
    width: 6px;
}

.sidebar-scrollable nav::-webkit-scrollbar-track {
    background: #f3f4f6;
    border-radius: 10px;
}

.sidebar-scrollable nav::-webkit-scrollbar-thumb {
    background: #a78bfa;
    border-radius: 10px;
}

.sidebar-scrollable nav::-webkit-scrollbar-thumb:hover {
    background: #8b5cf6;
}
</style>

