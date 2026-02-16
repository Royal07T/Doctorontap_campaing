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
                <p class="text-xs text-gray-500">Super Administrator</p>
            </div>
        </div>
    </div>

    <!-- Navigation - Scrollable -->
    <nav class="p-4 space-y-2 flex-1 overflow-y-auto">
        <a href="{{ route('super-admin.dashboard') }}" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'dashboard') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('super-admin.users.index') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'users') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>User Management</span>
        </a>

        <a href="{{ route('super-admin.activity-logs.index') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'activity-logs') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <span>Activity Logs</span>
        </a>

        <a href="{{ route('super-admin.services.index') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'services') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>Services</span>
        </a>

        <a href="{{ route('super-admin.system-health.index') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'system-health') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span>System Health</span>
        </a>

        <a href="{{ route('super-admin.communication-templates.index') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all @if($active === 'communication-templates') text-white purple-gradient @else text-gray-700 hover:bg-purple-50 hover:text-purple-600 @endif">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V16a2 2 0 01-2 2h-5z" />
            </svg>
            <span>Comm Templates</span>
        </a>

        <div class="border-t border-gray-200 my-2"></div>

        <a href="{{ route('admin.dashboard') }}"
           class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Admin</span>
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

