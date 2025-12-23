<!-- Top Header -->
<header class="purple-gradient shadow-lg z-10">
    <div class="flex items-center justify-between px-6 py-6">
        <div class="flex items-center space-x-4">
            <button @click="sidebarOpen = true" class="lg:hidden text-white hover:text-purple-200 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <div class="flex items-center space-x-3">
                <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap" class="h-8 w-auto lg:hidden">
                <h1 class="text-xl font-bold text-white">{{ $title ?? 'Dashboard' }}</h1>
            </div>
        </div>
        <div class="flex items-center space-x-4">
            <!-- Notification Icon -->
            <x-notification-icon />
            <span class="text-sm text-white">{{ now()->format('l, F j, Y') }}</span>
        </div>
    </div>
</header>

