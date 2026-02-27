<!-- Top Header -->
<header class="bg-white border-b border-gray-200 z-10">
    <div class="flex items-center justify-between px-6 py-3">
        <div class="flex items-center space-x-4 flex-1">
            <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-purple-600 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <h1 class="text-lg font-bold text-gray-800 lg:hidden">{{ $title ?? 'Dashboard' }}</h1>
            <!-- Search Bar -->
            <div class="hidden md:flex items-center flex-1 max-w-lg">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" placeholder="Search patients, caregivers or IDs..." class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:border-purple-400 focus:ring-1 focus:ring-purple-400 transition" />
                </div>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <!-- Notification Bell -->
            <button class="relative p-2 text-gray-500 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>
            <!-- Settings Gear -->
            <a href="{{ route('admin.settings') }}" class="p-2 text-gray-500 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </a>
            <!-- Divider -->
            <div class="h-8 w-px bg-gray-200"></div>
            <!-- User Info -->
            <div class="flex items-center space-x-2">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-semibold text-gray-700">{{ Auth::guard('admin')->user()->name ?? 'Admin Staff' }}</p>
                    <p class="text-[10px] text-purple-500 font-medium uppercase tracking-wide">{{ Auth::guard('admin')->user()->role === 'super_admin' ? 'Super User' : 'Admin' }}</p>
                </div>
                <div class="w-9 h-9 rounded-full purple-gradient flex items-center justify-center text-white font-bold text-sm shadow">
                    {{ substr(Auth::guard('admin')->user()->name ?? 'A', 0, 1) }}
                </div>
            </div>
            <!-- Logout -->
            <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                @csrf
                <button type="submit" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition" title="Logout">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </div>
</header>

