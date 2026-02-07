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

    <!-- User Info -->
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

    <!-- Navigation (Scrollable) -->
    <nav class="px-6 space-y-1 overflow-y-auto flex-1 custom-scrollbar">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-3">Main Navigation</p>
        
        <!-- Dashboard -->
        <a href="{{ route('customer-care.dashboard') }}" class="flex items-center space-x-3 px-4 py-3.5 {{ request()->routeIs('customer-care.dashboard') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200' : 'text-slate-600 hover:text-purple-600 hover:bg-purple-50' }} rounded-2xl font-bold transition-all duration-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
            <span>Dashboard</span>
        </a>

        <!-- Consultations -->
        <a href="{{ route('customer-care.consultations') }}" class="flex items-center space-x-3 px-4 py-3.5 {{ request()->routeIs('customer-care.consultations*') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200' : 'text-slate-600 hover:text-purple-600 hover:bg-purple-50' }} rounded-2xl font-bold transition-all duration-300 group">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
            <span>Consultations</span>
        </a>

        <!-- Interactions -->
        <a href="{{ route('customer-care.interactions.index') }}" class="flex items-center space-x-3 px-4 py-3.5 {{ request()->routeIs('customer-care.interactions*') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200' : 'text-slate-600 hover:text-purple-600 hover:bg-purple-50' }} rounded-2xl font-bold transition-all duration-300 group">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
            <span>Interactions</span>
        </a>

        <!-- Support Tickets -->
        <a href="{{ route('customer-care.tickets.index') }}" class="flex items-center space-x-3 px-4 py-3.5 {{ request()->routeIs('customer-care.tickets*') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200' : 'text-slate-600 hover:text-purple-600 hover:bg-purple-50' }} rounded-2xl font-bold transition-all duration-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2" /></svg>
            <span>Support Tickets</span>
        </a>

        <!-- Escalations -->
        <a href="{{ route('customer-care.escalations.index') }}" class="flex items-center space-x-3 px-4 py-3.5 {{ request()->routeIs('customer-care.escalations*') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200' : 'text-slate-600 hover:text-purple-600 hover:bg-purple-50' }} rounded-2xl font-bold transition-all duration-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <span>Escalations</span>
        </a>

        <!-- Customers -->
        <a href="{{ route('customer-care.customers.index') }}" class="flex items-center space-x-3 px-4 py-3.5 {{ request()->routeIs('customer-care.customers*') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200' : 'text-slate-600 hover:text-purple-600 hover:bg-purple-50' }} rounded-2xl font-bold transition-all duration-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            <span>Patients</span>
        </a>

        <!-- Doctor Directory -->
        <a href="{{ route('customer-care.doctors.index') }}" class="flex items-center space-x-3 px-4 py-3.5 {{ request()->routeIs('customer-care.doctors*') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200' : 'text-slate-600 hover:text-purple-600 hover:bg-purple-50' }} rounded-2xl font-bold transition-all duration-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            <span>Doctors</span>
        </a>

        <!-- Bulk SMS Marketing -->
        <a href="{{ route('customer-care.bulk-sms.index') }}" class="flex items-center space-x-3 px-4 py-3.5 {{ request()->routeIs('customer-care.bulk-sms*') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200' : 'text-slate-600 hover:text-purple-600 hover:bg-purple-50' }} rounded-2xl font-bold transition-all duration-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>
            <span>Bulk SMS</span>
        </a>

        <!-- Bulk Email Marketing -->
        <a href="{{ route('customer-care.bulk-email.index') }}" class="flex items-center space-x-3 px-4 py-3.5 {{ request()->routeIs('customer-care.bulk-email*') ? 'bg-purple-600 text-white shadow-lg shadow-purple-200' : 'text-slate-600 hover:text-purple-600 hover:bg-purple-50' }} rounded-2xl font-bold transition-all duration-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
            <span>Bulk Email</span>
        </a>
    </nav>

    <!-- Sidebar Footer (Fixed Bottom) -->
    <div class="px-6 py-4 mt-auto border-t border-slate-100 bg-white">
        <!-- View Website -->
        <a href="{{ url('/') }}" target="_blank" class="flex items-center justify-between px-4 py-3 text-slate-600 hover:text-indigo-600 hover:bg-slate-50 rounded-2xl font-bold transition-all group">
            <div class="flex items-center space-x-3 text-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                <span>Live Portal</span>
            </div>
            <svg class="w-3.5 h-3.5 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
        </a>

        <!-- Logout -->
        <form method="POST" action="{{ route('customer-care.logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 text-red-600 hover:bg-red-50 rounded-2xl font-black transition-all group">
                <div class="p-2 rounded-xl bg-red-50 group-hover:bg-red-600 group-hover:text-white transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                </div>
                <span class="text-xs uppercase tracking-widest">Logout Session</span>
            </button>
        </form>
    </div>
</aside>
