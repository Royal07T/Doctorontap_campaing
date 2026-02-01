<header class="bg-white/80 backdrop-blur-md border-b border-gray-200 z-10 sticky top-0">
    <div class="flex items-center justify-between px-8 py-6">
        <div class="flex items-center space-x-6">
            <button @click="sidebarOpen = true" class="lg:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">{{ $title ?? 'Dashboard' }}</h1>
                <p class="text-[11px] font-bold text-purple-600 uppercase tracking-[0.2em] mt-0.5">Control Center</p>
            </div>
        </div>
        <div class="flex items-center space-x-6">
            <div class="hidden xl:flex items-center space-x-2 bg-slate-100 px-4 py-2 rounded-2xl border border-slate-200 shadow-inner">
                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="text-xs font-bold text-slate-600">{{ now()->format('l, F j, Y') }}</span>
            </div>
            
            <div class="flex items-center space-x-3">
                <div class="relative group">
                    <x-notification-icon />
                </div>
                <div class="w-px h-8 bg-slate-200 mx-2"></div>
                <button class="p-2.5 bg-slate-50 hover:bg-purple-100 text-slate-600 hover:text-purple-600 rounded-2xl transition-all duration-300 border border-slate-200 group relative">
                    <svg class="w-5 h-5 transform group-hover:rotate-45 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
        </div>
    </div>
</header>
