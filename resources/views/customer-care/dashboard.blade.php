<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Customer Care Dashboard - DoctorOnTap</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary-glow: conic-gradient(from 180deg at 50% 50%, #9333ea33 0deg, #7e22ce33 55deg, #6366f133 120deg, #a855f733 160deg, transparent 360deg);
            --secondary-glow: radial-gradient(circle at center, #9333ea22 0%, #7e22ce11 100%);
        }
        
        .purple-gradient {
            background: linear-gradient(135deg, #6B21A8 0%, #4C1D95 100%);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }

        .glass-sidebar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
        }

        .nav-link-active {
            background: linear-gradient(90deg, #9333EA 0%, #7E22CE 100%);
            box-shadow: 0 4px 15px rgba(147, 51, 234, 0.3);
            transform: translateX(5px);
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .animate-slide-up {
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stat-card-glow {
            position: relative;
            overflow: hidden;
        }

        .stat-card-glow::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at center, rgba(147, 51, 234, 0.05) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #9333ea;
        }
    </style>
</head>
<body class="bg-[#F8FAFC] min-h-screen font-sans antialiased text-slate-900" x-data="{ sidebarOpen: false, pageLoading: false }">
    <!-- Background Decoration -->
    <div class="fixed top-0 left-0 w-full h-full pointer-events-none -z-10 overflow-hidden">
        <div class="absolute top-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-purple-100/50 blur-[120px]"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[30%] h-[30%] rounded-full bg-blue-100/50 blur-[100px]"></div>
    </div>
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 w-72 glass-sidebar shadow-2xl transform transition-transform duration-500 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
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
                <div class="p-4 rounded-2xl bg-gradient-to-br from-purple-50/50 to-indigo-50/50 border border-purple-100/50 transition-all duration-300 group-hover:shadow-md">
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
                
                <a href="{{ route('customer-care.dashboard') }}" class="flex items-center space-x-3 px-4 py-3.5 text-white nav-link-active rounded-2xl font-semibold transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('customer-care.consultations') }}" class="flex items-center space-x-3 px-4 py-3.5 text-slate-600 hover:text-purple-600 hover:bg-purple-50/50 rounded-2xl font-medium transition-all duration-200 group">
                    <div class="p-1 rounded-lg bg-slate-100 group-hover:bg-purple-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <span>Consultations</span>
                </a>

                <a href="{{ route('customer-care.interactions.index') }}" class="flex items-center space-x-3 px-4 py-3.5 text-slate-600 hover:text-purple-600 hover:bg-purple-50/50 rounded-2xl font-medium transition-all duration-200 group">
                    <div class="p-1 rounded-lg bg-slate-100 group-hover:bg-purple-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <span>Interactions</span>
                </a>

                <a href="{{ route('customer-care.tickets.index') }}" class="flex items-center space-x-3 px-4 py-3.5 text-slate-600 hover:text-purple-600 hover:bg-purple-50/50 rounded-2xl font-medium transition-all duration-200 group">
                    <div class="p-1 rounded-lg bg-slate-100 group-hover:bg-purple-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <span>Support Tickets</span>
                </a>

                <div class="pt-4 mt-4 border-t border-slate-100">
                    <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-3">Resources</p>
                    
                    <a href="{{ url('/') }}" target="_blank" class="flex items-center justify-between px-4 py-3 text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50 rounded-2xl font-medium transition-all group">
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

        <!-- Overlay for mobile sidebar -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 lg:hidden"
             style="display: none;"></div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white/80 backdrop-blur-md border-b border-slate-200 z-10 sticky top-0">
                <div class="flex items-center justify-between px-8 py-6">
                    <div class="flex items-center space-x-6">
                        <button @click="sidebarOpen = true" class="lg:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div>
                            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Elite Control</h1>
                            <p class="text-[11px] font-bold text-purple-600 uppercase tracking-[0.2em] mt-0.5">System Dashboard</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-6">
                        <div class="hidden xl:flex items-center space-x-2 bg-slate-100 px-4 py-2 rounded-2xl border border-slate-200">
                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span class="text-xs font-bold text-slate-600">{{ now()->format('l, F j, Y') }}</span>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <div class="relative group">
                                <x-notification-icon />
                            </div>
                            <div class="w-px h-8 bg-slate-200 mx-2"></div>
                            <button class="p-2.5 bg-slate-100 hover:bg-purple-100 text-slate-600 hover:text-purple-600 rounded-2xl transition-all duration-300 border border-slate-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                <!-- Success Message -->
                @if(session('success'))
                <div class="mb-8 p-4 bg-emerald-500/10 border-l-4 border-emerald-500 text-emerald-700 rounded-2xl backdrop-blur-md animate-fade-in shadow-lg shadow-emerald-500/10">
                    <div class="flex items-center">
                        <div class="p-2 bg-emerald-500 rounded-lg text-white mr-3">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <span class="font-semibold">{{ session('success') }}</span>
                    </div>
                </div>
                @endif

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 animate-slide-up">
                    <div class="glass-card rounded-[2rem] p-6 stat-card-glow transition-all duration-300 hover:shadow-2xl hover:shadow-purple-500/10 group cursor-pointer border-t-4 border-t-purple-600">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Total Impact</p>
                                <p class="text-3xl font-black text-slate-800">{{ $stats['total_consultations'] }}</p>
                                <p class="text-xs text-purple-600 font-bold mt-1">Consultations</p>
                            </div>
                            <div class="bg-purple-600 p-4 rounded-2xl shadow-lg ring-4 ring-purple-100 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card rounded-[2rem] p-6 stat-card-glow transition-all duration-300 hover:shadow-2xl hover:shadow-amber-500/10 group cursor-pointer border-t-4 border-t-amber-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Attention Required</p>
                                <p class="text-3xl font-black text-slate-800">{{ $stats['pending_consultations'] }}</p>
                                <p class="text-xs text-amber-500 font-bold mt-1">Pending Requests</p>
                            </div>
                            <div class="bg-amber-500 p-4 rounded-2xl shadow-lg ring-4 ring-amber-100 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card rounded-[2rem] p-6 stat-card-glow transition-all duration-300 hover:shadow-2xl hover:shadow-blue-500/10 group cursor-pointer border-t-4 border-t-blue-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Queue Management</p>
                                <p class="text-3xl font-black text-slate-800">{{ $stats['scheduled_consultations'] }}</p>
                                <p class="text-xs text-blue-500 font-bold mt-1">Scheduled Sessions</p>
                            </div>
                            <div class="bg-blue-500 p-4 rounded-2xl shadow-lg ring-4 ring-blue-100 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card rounded-[2rem] p-6 stat-card-glow transition-all duration-300 hover:shadow-2xl hover:shadow-emerald-500/10 group cursor-pointer border-t-4 border-t-emerald-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Success Value</p>
                                <p class="text-3xl font-black text-slate-800">{{ $stats['completed_consultations'] }}</p>
                                <p class="text-xs text-emerald-500 font-bold mt-1">Completed Cases</p>
                            </div>
                            <div class="bg-emerald-500 p-4 rounded-2xl shadow-lg ring-4 ring-emerald-100 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Care Module Statistics -->
                @if(isset($customerCareStats))
                <div class="mb-10 animate-slide-up" style="animation-delay: 0.1s;">
                    <h2 class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] mb-6 flex items-center">
                        <span class="w-8 h-px bg-purple-200 mr-4"></span>
                        Module Performance
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                        <div class="glass-card rounded-3xl p-5 border-l-4 border-l-indigo-500 hover:translate-y-[-5px] transition-transform duration-300">
                            <div class="flex items-center justify-between mb-3">
                                <div class="p-2 bg-indigo-500/10 rounded-xl">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-bold text-slate-400">Interactions</span>
                            </div>
                            <p class="text-2xl font-black text-slate-800">{{ $customerCareStats['active_interactions'] ?? 0 }}</p>
                            <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest mt-1">Currently Active</p>
                        </div>

                        <div class="glass-card rounded-3xl p-5 border-l-4 border-l-orange-500 hover:translate-y-[-5px] transition-transform duration-300">
                            <div class="flex items-center justify-between mb-3">
                                <div class="p-2 bg-orange-500/10 rounded-xl">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-bold text-slate-400">Tickets</span>
                            </div>
                            <p class="text-2xl font-black text-slate-800">{{ $customerCareStats['pending_tickets'] ?? 0 }}</p>
                            <p class="text-[10px] font-bold text-orange-500 uppercase tracking-widest mt-1">Pending Resolution</p>
                        </div>

                        <div class="glass-card rounded-3xl p-5 border-l-4 border-l-green-500 hover:translate-y-[-5px] transition-transform duration-300">
                            <div class="flex items-center justify-between mb-3">
                                <div class="p-2 bg-green-500/10 rounded-xl">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-bold text-slate-400">Efficiency</span>
                            </div>
                            <p class="text-2xl font-black text-slate-800">{{ $customerCareStats['resolved_tickets_today'] ?? 0 }}</p>
                            <p class="text-[10px] font-bold text-green-500 uppercase tracking-widest mt-1">Resolved Today</p>
                        </div>

                        <div class="glass-card rounded-3xl p-5 border-l-4 border-l-rose-500 hover:translate-y-[-5px] transition-transform duration-300">
                            <div class="flex items-center justify-between mb-3">
                                <div class="p-2 bg-rose-500/10 rounded-xl">
                                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </div>
                                <span class="text-xs font-bold text-slate-400">Escalations</span>
                            </div>
                            <p class="text-2xl font-black text-slate-800">{{ $customerCareStats['escalated_cases'] ?? 0 }}</p>
                            <p class="text-[10px] font-bold text-rose-500 uppercase tracking-widest mt-1">Critical Cases</p>
                        </div>

                        <div class="glass-card rounded-3xl p-5 border-l-4 border-l-teal-500 hover:translate-y-[-5px] transition-transform duration-300">
                            <div class="flex items-center justify-between mb-3">
                                <div class="p-2 bg-teal-500/10 rounded-xl">
                                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-bold text-slate-400">Response</span>
                            </div>
                            <p class="text-2xl font-black text-slate-800">{{ $customerCareStats['avg_response_time'] ?? 0 }}m</p>
                            <p class="text-[10px] font-bold text-teal-500 uppercase tracking-widest mt-1">Average S.L.A</p>
                        </div>
                    </div>
                </div>
                @endif

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-10">
                    <!-- Omni-Channel Communication Hub -->
                    <div class="xl:col-span-2 animate-slide-up" style="animation-delay: 0.2s;">
                        <div class="glass-card rounded-[2.5rem] overflow-hidden border border-purple-100/50 shadow-2xl shadow-purple-500/5">
                            <div class="purple-gradient p-8 text-white relative overflow-hidden">
                                <div class="absolute top-0 right-0 p-8 opacity-10">
                                    <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 9h12v2H6V9zm8 5H6v-2h8v2zm4-6H6V6h12v2z"/></svg>
                                </div>
                                <div class="relative z-10">
                                    <h3 class="text-2xl font-black tracking-tight mb-2">Communication Hub</h3>
                                    <p class="text-purple-100/80 text-xs font-medium max-w-md">Connect with patients across SMS, WhatsApp, and Secure Voice/Video directly from this control center.</p>
                                </div>
                                
                                <div class="mt-8 relative max-w-xl">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2.5"/></svg>
                                    </div>
                                    <input type="text" 
                                           id="patientSearch" 
                                           onkeyup="searchPatients(this.value)"
                                           class="w-full bg-white/10 border border-white/20 text-white placeholder-purple-200 text-sm rounded-2xl py-4 pl-12 pr-4 focus:ring-4 focus:ring-white/10 focus:border-white/40 focus:bg-white/20 transition-all outline-none backdrop-blur-md" 
                                           placeholder="Search patient by name, email or phone...">
                                    
                                    <!-- Search Results Dropdown -->
                                    <div id="searchResults" class="absolute z-50 left-0 right-0 mt-3 bg-white rounded-3xl shadow-2xl border border-slate-100 max-h-80 overflow-y-auto hidden divide-y divide-slate-50 animate-fade-in custom-scrollbar">
                                        <!-- Results populated by JS -->
                                    </div>
                                </div>
                            </div>

                            <div id="communicationInterface" class="p-8 hidden">
                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                                    <!-- Left: Patient Detail Card -->
                                    <div class="lg:col-span-4">
                                        <div class="p-6 bg-slate-50 rounded-[2rem] border border-slate-100 sticky top-4">
                                            <div class="flex items-center space-x-4 mb-6">
                                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-white text-xl font-black shadow-lg">
                                                    <span id="patientInitials">PT</span>
                                                </div>
                                                <div>
                                                    <h4 id="selectedPatientName" class="font-black text-slate-800 tracking-tight">Select Patient</h4>
                                                    <div class="flex items-center space-x-2 mt-1">
                                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Active File</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div id="selectedPatientInfo" class="space-y-4">
                                                <!-- Info populated by JS -->
                                            </div>

                                            <div class="mt-8 pt-6 border-t border-slate-200">
                                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Channel History</p>
                                                <div id="communicationHistory" class="space-y-3 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                                                    <p class="text-xs text-slate-400 italic">No activity selected</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right: Action Center -->
                                    <div class="lg:col-span-8">
                                        <div class="flex p-1.5 bg-slate-100 rounded-2xl mb-6">
                                            <button onclick="switchMessageType('sms')" data-type="sms" class="message-tab flex-1 py-3 px-3 rounded-xl text-xs font-bold transition-all duration-300 flex items-center justify-center space-x-2 bg-white text-purple-600 shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" stroke-width="2"/></svg>
                                                <span>SMS</span>
                                            </button>
                                            <button onclick="switchMessageType('whatsapp')" data-type="whatsapp" class="message-tab flex-1 py-3 px-3 rounded-xl text-xs font-bold transition-all duration-300 flex items-center justify-center space-x-2 text-slate-500 hover:text-slate-800">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.414 0 .004 5.408 0 12.044c0 2.123.543 4.191 1.577 6.01L0 24l6.112-1.608a11.811 11.811 0 005.933 1.604h.005c6.634 0 12.043-5.408 12.048-12.047a11.82 11.82 0 00-3.486-8.451"/></svg>
                                                <span>WhatsApp</span>
                                            </button>
                                            <button onclick="switchMessageType('voice')" data-type="voice" class="message-tab flex-1 py-3 px-3 rounded-xl text-xs font-bold transition-all duration-300 flex items-center justify-center space-x-2 text-slate-500 hover:text-slate-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke-width="2"/></svg>
                                                <span>Voice</span>
                                            </button>
                                            <button onclick="switchMessageType('video')" data-type="video" class="message-tab flex-1 py-3 px-3 rounded-xl text-xs font-bold transition-all duration-300 flex items-center justify-center space-x-2 text-slate-500 hover:text-slate-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" stroke-width="2"/></svg>
                                                <span>Video</span>
                                            </button>
                                        </div>

                                        <div id="textMessageInterface">
                                            <div class="mb-5">
                                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Quick Template</label>
                                                <select id="messageTemplate" 
                                                        onchange="updateMessageFromTemplate()"
                                                        class="w-full bg-white border border-slate-200 rounded-2xl py-3.5 px-4 text-sm focus:ring-4 focus:ring-purple-100 transition-all outline-none">
                                                    <option value="">Select a helpful template...</option>
                                                    <option value="appointment_reminder">Appointment Reminder</option>
                                                    <option value="appointment_confirmation">Appointment Confirmation</option>
                                                    <option value="prescription_ready">Prescription Ready</option>
                                                    <option value="test_results">Test Results Available</option>
                                                    <option value="payment_reminder">Payment Outstanding Reminder</option>
                                                    <option value="follow_up">Patient Well-being Follow-up</option>
                                                </select>
                                            </div>

                                            <div class="relative">
                                                <textarea id="messageContent" 
                                                          rows="5" 
                                                          class="w-full bg-white border border-slate-200 rounded-[2rem] p-6 text-sm focus:ring-4 focus:ring-purple-100 transition-all outline-none resize-none"
                                                          placeholder="Craft your message here..."></textarea>
                                                <div class="absolute bottom-6 right-6 flex items-center space-x-4">
                                                    <span class="text-[10px] font-bold text-slate-400"><span id="charCount">0</span> characters</span>
                                                    <button onclick="clearMessage()" class="text-xs font-bold text-slate-400 hover:text-rose-500 transition-colors">Clear</button>
                                                </div>
                                            </div>

                                            <button onclick="sendMessage()" class="w-full mt-6 purple-gradient text-white py-5 rounded-[2rem] font-black text-sm shadow-xl shadow-purple-600/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center space-x-3">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" stroke-width="2"/></svg>
                                                <span>Deploy Message</span>
                                            </button>
                                        </div>

                                        <div id="callInterface" class="hidden">
                                            <div class="bg-slate-50 border border-indigo-100/50 rounded-[2.5rem] p-10 text-center">
                                                <div class="w-24 h-24 bg-indigo-100 rounded-[2.5rem] flex items-center justify-center text-indigo-600 mx-auto mb-6 shadow-inner">
                                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                                <h4 class="text-xl font-black text-slate-800 mb-2">Initialize <span id="callType">Voice</span> Session</h4>
                                                <p class="text-xs text-slate-500 mb-8 max-w-sm mx-auto">You are about to start a secure <span id="callTypeDesc">voice</span> consultation. All sessions are encrypted for patient privacy.</p>
                                                <button id="callTypeButton" onclick="initiateCall()" class="px-10 py-4 bg-indigo-600 text-white rounded-[2rem] font-black text-sm shadow-xl shadow-indigo-600/20 hover:scale-[1.05] transition-all">
                                                    Start Session
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Side Column: Action Cards -->
                    <div class="space-y-8 animate-slide-up" style="animation-delay: 0.3s;">
                        <h3 class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] flex items-center">
                            <span class="w-4 h-px bg-purple-200 mr-3"></span>
                            Quick Actions
                        </h3>
                        
                        <div class="grid grid-cols-1 gap-4">
                            <a href="{{ route('customer-care.consultations') }}" class="glass-card p-6 rounded-[2rem] border border-white hover:border-purple-200 group transition-all duration-300 hover:shadow-xl hover:shadow-purple-500/5">
                                <div class="flex items-center space-x-4">
                                    <div class="p-3 bg-purple-100 rounded-2xl text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-all duration-300 shadow-sm">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-width="2"/></svg>
                                    </div>
                                    <div>
                                        <h4 class="font-black text-slate-800 text-sm group-hover:text-purple-600 transition-colors">Master Registry</h4>
                                        <p class="text-[11px] font-bold text-slate-400 mt-0.5">Global Consultation Feed</p>
                                    </div>
                                </div>
                            </a>

                            <a href="{{ route('customer-care.consultations', ['status' => 'pending']) }}" class="glass-card p-6 rounded-[2rem] border border-white hover:border-amber-200 group transition-all duration-300 hover:shadow-xl hover:shadow-amber-500/5">
                                <div class="flex items-center space-x-4">
                                    <div class="p-3 bg-amber-100 rounded-2xl text-amber-600 group-hover:bg-amber-500 group-hover:text-white transition-all duration-300 shadow-sm">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2"/></svg>
                                    </div>
                                    <div>
                                        <h4 class="font-black text-slate-800 text-sm group-hover:text-amber-600 transition-colors">Active Alerts</h4>
                                        <p class="text-[11px] font-bold text-slate-400 mt-0.5">Requires Response</p>
                                    </div>
                                </div>
                            </a>

                            <a href="{{ route('customer-care.tickets.index') }}" class="glass-card p-6 rounded-[2rem] border border-white hover:border-indigo-200 group transition-all duration-300 hover:shadow-xl hover:shadow-indigo-500/5">
                                <div class="flex items-center space-x-4">
                                    <div class="p-3 bg-indigo-100 rounded-2xl text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 shadow-sm">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke-width="2"/></svg>
                                    </div>
                                    <div>
                                        <h4 class="font-black text-slate-800 text-sm group-hover:text-indigo-600 transition-colors">Elite Support</h4>
                                        <p class="text-[11px] font-bold text-slate-400 mt-0.5">Open Support Tickets</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Feed -->
                <div class="animate-slide-up" style="animation-delay: 0.4s;">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Recent Activity</h2>
                            <p class="text-[10px] font-bold text-purple-600 uppercase tracking-[0.2em] mt-0.5">Live Consultation feed</p>
                        </div>
                        <a href="{{ route('customer-care.consultations') }}" class="px-6 py-2.5 bg-white border border-slate-200 rounded-2xl text-xs font-black text-slate-600 hover:bg-slate-50 transition-all shadow-sm">View Archive</a>
                    </div>
                    
                    @if($recentConsultations->count() > 0)
                    <div class="glass-card rounded-[2.5rem] overflow-hidden border border-white/40 shadow-2xl shadow-slate-200/50">
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="bg-slate-50/50">
                                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Reference</th>
                                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Patient Profile</th>
                                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Assigned Elite</th>
                                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Priority Status</th>
                                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Timeframe</th>
                                        <th class="px-8 py-5 text-center text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($recentConsultations as $consultation)
                                    <tr class="hover:bg-purple-50/30 transition-colors group">
                                        <td class="px-8 py-6 whitespace-nowrap">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-500 group-hover:bg-purple-600 group-hover:text-white transition-all">#</div>
                                                <span class="text-sm font-black text-slate-700 tracking-tight">{{ $consultation->reference }}</span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 whitespace-nowrap">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-slate-100 to-slate-200 flex items-center justify-center text-slate-400 font-bold group-hover:ring-2 group-hover:ring-purple-200 transition-all">
                                                    {{ substr($consultation->patient->name ?? 'N', 0, 1) }}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-slate-800 group-hover:text-purple-600 transition-colors">{{ $consultation->patient->name ?? 'N/A' }}</p>
                                                    <p class="text-[10px] text-slate-400 uppercase font-bold tracking-tighter">Verified Member</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 whitespace-nowrap">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs font-bold text-slate-600">Dr. {{ $consultation->doctor->name ?? 'Assigning...' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 whitespace-nowrap">
                                            <span class="px-4 py-1.5 inline-flex text-[10px] leading-5 font-black rounded-full uppercase tracking-widest shadow-sm
                                                @if($consultation->status === 'completed') bg-emerald-100 text-emerald-700 border border-emerald-200
                                                @elseif($consultation->status === 'pending') bg-amber-100 text-amber-700 border border-amber-200
                                                @elseif($consultation->status === 'scheduled') bg-indigo-100 text-indigo-700 border border-indigo-200
                                                @else bg-slate-100 text-slate-700 border border-slate-200
                                                @endif">
                                                {{ $consultation->status }}
                                            </span>
                                        </td>
                                        <td class="px-8 py-6 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-slate-700 underline decoration-purple-200 decoration-2 underline-offset-4">{{ $consultation->created_at->format('d M, Y') }}</span>
                                                <span class="text-[10px] font-bold text-slate-400 mt-1 uppercase">{{ $consultation->created_at->diffForHumans() }}</span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 whitespace-nowrap text-center">
                                            <a href="{{ route('customer-care.consultations.show', $consultation->id) }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-600 hover:text-purple-600 hover:border-purple-600 hover:shadow-lg transition-all">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-width="2"/></svg>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @else
                    <div class="glass-card rounded-[3rem] p-20 text-center border-dashed border-2 border-slate-200">
                        <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center text-slate-200 mx-auto mb-6">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-width="2"/></svg>
                        </div>
                        <h4 class="text-xl font-black text-slate-400 tracking-tight">System Registry Clear</h4>
                        <p class="text-xs text-slate-400 mt-2">No consultation records were found for the current cycle.</p>
                    </div>
                    @endif
                </div>
            </main>
        </div>
    </div>

    @include('customer-care.shared.preloader-scripts')

    <!-- Customer Care Communication Hub JavaScript -->
    <script>
        let selectedPatient = null;
        let currentMessageType = 'sms';

        // Message templates
        const messageTemplates = {
            appointment_reminder: 'Hi {patient_name}, this is a reminder about your appointment with Dr. Specialist on {date} at {time}. Reply CANCEL to reschedule.',
            appointment_confirmation: 'Hi {patient_name}, your appointment with Dr. Specialist is confirmed for {date} at {time}. Reply HELP for assistance.',
            prescription_ready: 'Hi {patient_name}, your prescription is ready for pickup at our pharmacy. Order ID: {order_id}',
            test_results: 'Hi {patient_name}, your test results are available. Please login to your DoctorOnTap account to view them.',
            payment_reminder: 'Hi {patient_name}, this is a reminder about your outstanding payment for consultation on {date}. Please pay to avoid service interruption.',
            follow_up: 'Hi {patient_name}, this is a follow-up regarding your recent consultation. How are you feeling? Reply if you need any assistance.'
        };

        // Search patients
        function searchPatients(query) {
            if (query.length < 2) {
                document.getElementById('searchResults').classList.add('hidden');
                return;
            }

            fetch(`{{ route('customer-care.patients.search') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    const resultsDiv = document.getElementById('searchResults');
                    resultsDiv.innerHTML = '';
                    
                    if (data.patients && data.patients.length > 0) {
                        data.patients.forEach(patient => {
                            const patientDiv = document.createElement('div');
                            patientDiv.className = 'px-4 py-3 hover:bg-purple-50 cursor-pointer border-b border-gray-200 last:border-b-0';
                            patientDiv.innerHTML = `
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium text-gray-900">${patient.name}</div>
                                        <div class="text-sm text-gray-500">${patient.email} • ${patient.phone}</div>
                                    </div>
                                    <button onclick="selectPatient(${patient.id})" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm">
                                        Select
                                    </button>
                                </div>
                            `;
                            resultsDiv.appendChild(patientDiv);
                        });
                        resultsDiv.classList.remove('hidden');
                    } else {
                        resultsDiv.innerHTML = '<div class="px-4 py-3 text-gray-500">No patients found</div>';
                        resultsDiv.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                });
        }

        // Select patient
        function selectPatient(patientId) {
            fetch(`{{ route('customer-care.patients.details', ['id' => ':id']) }}`.replace(':id', patientId))
                .then(response => response.json())
                .then(data => {
                    selectedPatient = data.patient;
                    document.getElementById('searchResults').classList.add('hidden');
                    document.getElementById('patientSearch').value = '';
                    
                    const hubInterface = document.getElementById('communicationInterface');
                    hubInterface.classList.remove('hidden');
                    hubInterface.classList.add('animate-fade-in');
                    
                    // Update patient info
                    document.getElementById('patientInitials').innerText = selectedPatient.name.charAt(0);
                    document.getElementById('selectedPatientName').innerText = selectedPatient.name;
                    
                    document.getElementById('selectedPatientInfo').innerHTML = `
                        <div class="space-y-4">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 font-bold">Encrypted Contact</span>
                                <span class="text-sm font-bold text-slate-700 underline decoration-purple-200 decoration-2 underline-offset-4">${selectedPatient.phone}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 font-bold">Mailbox</span>
                                <span class="text-sm font-bold text-slate-700">${selectedPatient.email}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 font-bold">Patient Bio</span>
                                <span class="text-sm font-bold text-slate-700">${data.age ?? '--'} Years • <span class="capitalize">${selectedPatient.gender ?? 'Unknown'}</span></span>
                            </div>
                        </div>
                    `;
                    
                    // Load communication history
                    loadCommunicationHistory(patientId);
                    
                    // Track for template population
                    window.currentPatientName = selectedPatient.name;
                })
                .catch(error => {
                    console.error('Error loading patient:', error);
                });
        }

        // Load communication history
        function loadCommunicationHistory(patientId) {
            fetch(`{{ route('customer-care.communications.history', ['patientId' => ':id']) }}`.replace(':id', patientId))
                .then(response => response.json())
                .then(data => {
                    const historyDiv = document.getElementById('communicationHistory');
                    historyDiv.innerHTML = '';
                    
                    if (data.communications && data.communications.length > 0) {
                        data.communications.forEach(comm => {
                            const commDiv = document.createElement('div');
                            commDiv.className = 'p-4 bg-white rounded-2xl border border-slate-100 shadow-sm animate-fade-in mb-3';
                            
                            const statusClass = comm.status === 'sent' ? 'bg-emerald-50 text-emerald-600' : 
                                               comm.status === 'failed' ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-600';
                            
                            const typeLabel = comm.type.toUpperCase();
                            
                            commDiv.innerHTML = `
                                <div class="flex flex-col">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">${typeLabel}</span>
                                        <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full ${statusClass}">${comm.status}</span>
                                    </div>
                                    <p class="text-xs text-slate-600 line-clamp-2">${comm.content}</p>
                                    <span class="text-[8px] font-bold text-slate-400 mt-2 uppercase tracking-tight text-right">${new Date(comm.created_at).toLocaleDateString()} at ${new Date(comm.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                                </div>
                            `;
                            historyDiv.appendChild(commDiv);
                        });
                    } else {
                        historyDiv.innerHTML = '<div class="text-center py-6"><p class="text-[10px] font-bold text-slate-400 italic">Historical data is clean</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading history:', error);
                });
        }

        // Switch message type (Tabs)
        function switchMessageType(type) {
            currentMessageType = type;
            
            // Update tabs UI
            document.querySelectorAll('.message-tab').forEach(tab => {
                if (tab.getAttribute('data-type') === type) {
                    tab.className = 'message-tab flex-1 py-3 px-3 rounded-xl text-xs font-bold transition-all duration-300 flex items-center justify-center space-x-2 bg-white text-purple-600 shadow-sm ring-1 ring-slate-200';
                } else {
                    tab.className = 'message-tab flex-1 py-3 px-3 rounded-xl text-xs font-bold transition-all duration-300 flex items-center justify-center space-x-2 text-slate-500 hover:text-slate-800 hover:bg-slate-200/50';
                }
            });

            const textInterface = document.getElementById('textMessageInterface');
            const callInterface = document.getElementById('callInterface');
            const callTypeLabel = document.getElementById('callType');
            const callTypeDesc = document.getElementById('callTypeDesc');
            const callTypeButton = document.getElementById('callTypeButton');

            if (type === 'sms' || type === 'whatsapp') {
                textInterface.classList.remove('hidden');
                callInterface.classList.add('hidden');
            } else {
                textInterface.classList.add('hidden');
                callInterface.classList.remove('hidden');
                callInterface.classList.add('animate-fade-in');
                
                const isVoice = (type === 'voice');
                callTypeLabel.innerText = isVoice ? 'Voice' : 'Video';
                callTypeDesc.innerText = isVoice ? 'voice' : 'video';
                callTypeButton.className = `px-10 py-4 ${isVoice ? 'bg-indigo-600' : 'bg-purple-600'} text-white rounded-[2rem] font-black text-sm shadow-xl transition-all hover:scale-[1.05] active:scale-[0.95]`;
                callTypeButton.innerText = `Initialize ${type.charAt(0).toUpperCase() + type.slice(1)} Session`;
            }
        }

        // Update message from template
        function updateMessageFromTemplate() {
            const templateKey = document.getElementById('messageTemplate').value;
            if (!templateKey) return;
            
            let message = messageTemplates[templateKey];
            
            // Replace placeholders
            if (window.currentPatientName) {
                message = message.replace('{patient_name}', window.currentPatientName);
            }
            
            // Add other mock data replacements
            message = message.replace('{doctor_name}', 'Specialist');
            message = message.replace('{date}', 'Today');
            message = message.replace('{time}', 'the scheduled time');
            
            document.getElementById('messageContent').value = message;
            updateCharCount();
        }

        // Update character count
        function updateCharCount() {
            const content = document.getElementById('messageContent').value;
            document.getElementById('charCount').textContent = content.length;
        }

        // Clear message
        function clearMessage() {
            document.getElementById('messageContent').value = '';
            document.getElementById('messageTemplate').value = '';
            updateCharCount();
        }

        // Send message (Modified to maintain routing fixes)
        function sendMessage() {
            if (!selectedPatient) {
                alert('Establish a patient connection first.');
                return;
            }
            
            const content = document.getElementById('messageContent').value;
            if (!content) {
                alert('Buffer is empty. Write something.');
                return;
            }
            
            const route = currentMessageType === 'sms' 
                ? '{{ route('customer-care.communications.send-sms') }}' 
                : '{{ route('customer-care.communications.send-whatsapp') }}';
            
            const sendButton = event.currentTarget;
            const originalText = sendButton.innerHTML;
            sendButton.disabled = true;
            sendButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span>Deploying...</span>
            `;
            
            fetch(route, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    patient_id: selectedPatient.id,
                    message: content
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Command Successful: Message deployed to ' + currentMessageType.toUpperCase());
                    clearMessage();
                    loadCommunicationHistory(selectedPatient.id);
                } else {
                    alert('Relay Error: ' + (data.message || 'System rejection'));
                }
            })
            .catch(error => {
                console.error('Relay catastrophic failure:', error);
                alert('Relay failure. Check system logs.');
            })
            .finally(() => {
                sendButton.disabled = false;
                sendButton.innerHTML = originalText;
            });
        }

        // Initiate call
        function initiateCall() {
            if (!selectedPatient) {
                alert('Please select a patient first');
                return;
            }
            
            const button = event.target;
            button.disabled = true;
            button.textContent = 'Initiating...';
            
            fetch(`{{ route('customer-care.communications.initiate-call') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    patient_id: selectedPatient.id,
                    call_type: currentMessageType
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.type === 'video') {
                        // Open video call interface
                        window.open(`/video-call/${data.session_id}/${data.agent_token}`, '_blank');
                    } else {
                        alert('Voice call initiated successfully!');
                    }
                    loadCommunicationHistory(selectedPatient.id);
                } else {
                    alert('Failed to initiate call: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error initiating call');
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = `Start ${currentMessageType === 'voice' ? 'Voice' : 'Video'} Call`;
            });
        }

        // Character count listener
        document.addEventListener('DOMContentLoaded', function() {
            const messageTextarea = document.getElementById('messageContent');
            if (messageTextarea) {
                messageTextarea.addEventListener('input', updateCharCount);
            }
        });

        // Close search results when clicking outside
        document.addEventListener('click', function(event) {
            const searchContainer = document.querySelector('.max-w-xl');
            if (searchContainer && !searchContainer.contains(event.target)) {
                const searchResults = document.getElementById('searchResults');
                if (searchResults) searchResults.classList.add('hidden');
            }
        });
    </script>
</body>
</html>

