<!-- Sidebar -->
<aside class="fixed inset-y-0 left-0 z-50 w-56 bg-white shadow-xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex flex-col sidebar-scrollable"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       style="scrollbar-width: thin; scrollbar-color: #a78bfa #f3f4f6;">
    <!-- Sidebar Header -->
    <div class="purple-gradient px-4 py-5 flex items-center justify-between flex-shrink-0">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2">
            <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
            </div>
            <div>
                <span class="text-sm font-extrabold text-white tracking-wide">DOCTORONTAP</span>
                <p class="text-[10px] text-white/60 -mt-0.5">{{ $subtitle ?? 'Ops Dashboard' }}</p>
            </div>
        </a>
        <button @click="sidebarOpen = false" class="lg:hidden text-white/70 hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Navigation - Scrollable -->
    <nav class="p-3 space-y-1 flex-1 overflow-y-auto">
        @php
        $navItems = [
            ['route' => 'admin.dashboard', 'key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['route' => 'admin.patients', 'key' => 'clients', 'label' => 'Client Management', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
            ['route' => 'admin.care-givers', 'key' => 'caregivers', 'label' => 'Caregiver Database', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
            ['route' => 'admin.financial-hub.index', 'key' => 'financial-hub', 'label' => 'Financial Hub', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['route' => 'admin.admin-reports', 'key' => 'admin-reports', 'label' => 'Admin Reports', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['route' => 'admin.comms-center', 'key' => 'comms-center', 'label' => 'Comms Center', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
        ];
        @endphp

        @foreach($navItems as $item)
        <a href="{{ route($item['route']) }}"
           class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
           @if($active === $item['key']) text-white purple-gradient shadow-md @else text-gray-600 hover:bg-purple-50 hover:text-purple-700 @endif">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
            </svg>
            <span>{{ $item['label'] }}</span>
        </a>
        @endforeach

        {{-- Divider --}}
        <div class="border-t border-gray-100 my-2"></div>

        {{-- Secondary nav items (existing pages kept accessible) --}}
        @php
        $secondaryItems = [
            ['route' => 'admin.consultations', 'key' => 'consultations', 'label' => 'Consultations', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['route' => 'admin.doctors', 'key' => 'doctors', 'label' => 'Doctors', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
            ['route' => 'admin.nurses', 'key' => 'nurses', 'label' => 'Nurses', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
            ['route' => 'admin.family-members', 'key' => 'family-members', 'label' => 'Family Members', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['route' => 'admin.payments', 'key' => 'payments', 'label' => 'Payments', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
            ['route' => 'admin.admin-users', 'key' => 'admin-users', 'label' => 'Admin Users', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
            ['route' => 'admin.settings', 'key' => 'settings', 'label' => 'Settings', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
        ];
        @endphp

        @foreach($secondaryItems as $item)
        <a href="{{ route($item['route']) }}"
           class="flex items-center space-x-3 px-3 py-2 rounded-lg text-xs font-medium transition-all
           @if($active === $item['key']) text-purple-700 bg-purple-50 @else text-gray-500 hover:bg-gray-50 hover:text-gray-700 @endif">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
            </svg>
            <span>{{ $item['label'] }}</span>
        </a>
        @endforeach

        @if(Auth::guard('admin')->user()->isSuperAdmin())
        <div class="border-t border-gray-100 my-2"></div>
        <a href="{{ route('super-admin.dashboard') }}"
           class="flex items-center space-x-3 px-3 py-2 rounded-lg text-xs font-medium text-amber-700 hover:bg-amber-50 border-l-3 border-amber-400 transition-all">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span>Super Admin</span>
        </a>
        @endif
    </nav>

    {{-- Bottom: CTA Button --}}
    <div class="p-3 flex-shrink-0 border-t border-gray-100">
        @hasSection('sidebar-cta')
            @yield('sidebar-cta')
        @else
        <a href="{{ route('admin.patients') }}" class="flex items-center justify-center gap-2 w-full py-2.5 rounded-lg purple-gradient text-white text-sm font-semibold shadow-md hover:shadow-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            New Intake
        </a>
        @endif
    </div>
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
.sidebar-scrollable nav::-webkit-scrollbar { width: 4px; }
.sidebar-scrollable nav::-webkit-scrollbar-track { background: transparent; }
.sidebar-scrollable nav::-webkit-scrollbar-thumb { background: #ddd6fe; border-radius: 10px; }
.sidebar-scrollable nav::-webkit-scrollbar-thumb:hover { background: #a78bfa; }
</style>

