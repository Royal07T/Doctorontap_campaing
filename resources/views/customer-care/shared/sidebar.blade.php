<!-- Sidebar -->
<aside class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-right border-gray-200 shadow-xl transform transition-transform duration-500 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
    <!-- Sidebar Header -->
    <div class="purple-gradient p-8 flex items-center justify-between rounded-br-[3rem] shadow-lg mb-6">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap Logo" class="h-10 w-auto filter drop-shadow-md">
        </div>
        <button @click="sidebarOpen = false" class="lg:hidden text-white/80 hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- User Info Container -->
    <div class="px-6 mb-8 group">
        <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 transition-all duration-300 group-hover:shadow-md">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-white font-bold shadow-lg ring-4 ring-purple-100">
                    {{ substr(auth()->guard('customer_care')->user()->name ?? 'CC', 0, 1) }}
                </div>
                <div class="flex-1 overflow-hidden">
                    <p class="font-bold text-slate-800 text-sm truncate">{{ auth()->guard('customer_care')->user()->name ?? 'Customer Care' }}</p>
                    <p class="text-[10px] font-bold text-purple-600 uppercase tracking-widest">Support Elite</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="px-6 space-y-1.5 overflow-y-auto max-h-[calc(100vh-320px)] custom-scrollbar">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-3">Main Menu</p>
        
        <a href="{{ route('customer-care.dashboard') }}" class="flex items-center space-x-3 px-4 py-3.5 {{ request()->routeIs('customer-care.dashboard') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200' : 'text-slate-600 hover:text-purple-600 hover:bg-purple-50' }} rounded-2xl font-semibold transition-all duration-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('customer-care.consultations') }}" class="flex items-center space-x-3 px-4 py-3.5 {{ request()->routeIs('customer-care.consultations*') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200' : 'text-slate-600 hover:text-purple-600 hover:bg-purple-50' }} rounded-2xl font-medium transition-all duration-200 group">
            <div class="{{ request()->routeIs('customer-care.consultations*') ? 'bg-white/20' : 'bg-slate-100 group-hover:bg-purple-100' }} p-1 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <span>Consultations</span>
        </a>

        <a href="{{ route('customer-care.interactions.index') }}" class="flex items-center space-x-3 px-4 py-3.5 text-slate-600 hover:text-purple-600 hover:bg-purple-50 rounded-2xl font-medium transition-all duration-200 group">
            <div class="p-1 rounded-lg bg-slate-100 group-hover:bg-purple-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </div>
            <span>Interactions</span>
        </a>

        <a href="{{ route('customer-care.tickets.index') }}" class="flex items-center space-x-3 px-4 py-3.5 text-slate-600 hover:text-purple-600 hover:bg-purple-50 rounded-2xl font-medium transition-all duration-200 group">
            <div class="p-1 rounded-lg bg-slate-100 group-hover:bg-purple-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <span>Support Tickets</span>
        </a>

        <div class="pt-4 mt-4 border-t border-slate-100">
            <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-3">Resources</p>
            
            <a href="{{ url('/') }}" target="_blank" class="flex items-center justify-between px-4 py-3 text-slate-600 hover:text-indigo-600 hover:bg-slate-50 rounded-2xl font-medium transition-all group">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                    <span>Live Website</span>
                </div>
                <svg class="w-3.5 h-3.5 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
            </a>

            <form method="POST" action="{{ route('customer-care.logout') }}" class="mt-4">
                @csrf
                <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3.5 text-red-500 hover:bg-red-50 rounded-2xl font-bold transition-all group">
                    <div class="p-1 rounded-lg bg-red-50 group-hover:bg-red-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </div>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </nav>
</aside>
