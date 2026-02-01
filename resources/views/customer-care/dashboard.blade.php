@extends('layouts.customer-care')

@section('title', 'Customer Care Dashboard')

@php
    $headerTitle = 'Elite Control';
@endphp

@section('content')
    <!-- Success Message -->
    @if(session('success'))
    <div class="mb-8 p-4 bg-emerald-500/10 border-l-4 border-emerald-500 text-emerald-700 rounded-2xl animate-fade-in shadow-lg shadow-emerald-500/5">
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
        <div class="clean-card p-6 border-l-4 border-l-purple-600 hover:shadow-lg transition-all duration-300 group cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Total Impact</p>
                    <p class="text-3xl font-black text-slate-800">{{ $stats['total_consultations'] }}</p>
                    <p class="text-xs text-purple-600 font-bold mt-1">Consultations</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-2xl group-hover:bg-purple-600 transition-colors duration-300">
                    <svg class="w-6 h-6 text-purple-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="clean-card p-6 border-l-4 border-l-amber-500 hover:shadow-lg transition-all duration-300 group cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Attention Required</p>
                    <p class="text-3xl font-black text-slate-800">{{ $stats['pending_consultations'] }}</p>
                    <p class="text-xs text-amber-500 font-bold mt-1">Pending Requests</p>
                </div>
                <div class="bg-amber-50 p-4 rounded-2xl group-hover:bg-amber-500 transition-colors duration-300">
                    <svg class="w-6 h-6 text-amber-500 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="clean-card p-6 border-l-4 border-l-blue-500 hover:shadow-lg transition-all duration-300 group cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Queue Management</p>
                    <p class="text-3xl font-black text-slate-800">{{ $stats['scheduled_consultations'] }}</p>
                    <p class="text-xs text-blue-500 font-bold mt-1">Scheduled Sessions</p>
                </div>
                <div class="bg-blue-50 p-4 rounded-2xl group-hover:bg-blue-500 transition-colors duration-300">
                    <svg class="w-6 h-6 text-blue-500 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="clean-card p-6 border-l-4 border-l-emerald-500 hover:shadow-lg transition-all duration-300 group cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Success Value</p>
                    <p class="text-3xl font-black text-slate-800">{{ $stats['completed_consultations'] }}</p>
                    <p class="text-xs text-emerald-500 font-bold mt-1">Completed Cases</p>
                </div>
                <div class="bg-emerald-50 p-4 rounded-2xl group-hover:bg-emerald-500 transition-colors duration-300">
                    <svg class="w-6 h-6 text-emerald-500 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div class="clean-card p-5 border-t-4 border-t-indigo-500 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between mb-3">
                    <div class="p-2 bg-indigo-50 rounded-xl text-indigo-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-slate-400">Interactions</span>
                </div>
                <p class="text-2xl font-black text-slate-800">{{ $customerCareStats['active_interactions'] ?? 0 }}</p>
                <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest mt-1">Currently Active</p>
            </div>

            <div class="clean-card p-5 border-t-4 border-t-orange-500 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between mb-3">
                    <div class="p-2 bg-orange-50 rounded-xl text-orange-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-slate-400">Tickets</span>
                </div>
                <p class="text-2xl font-black text-slate-800">{{ $customerCareStats['pending_tickets'] ?? 0 }}</p>
                <p class="text-[10px] font-bold text-orange-500 uppercase tracking-widest mt-1">Pending Resolution</p>
            </div>

            <div class="clean-card p-5 border-t-4 border-t-green-500 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between mb-3">
                    <div class="p-2 bg-green-50 rounded-xl text-green-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-slate-400">Efficiency</span>
                </div>
                <p class="text-2xl font-black text-slate-800">{{ $customerCareStats['resolved_tickets_today'] ?? 0 }}</p>
                <p class="text-[10px] font-bold text-green-500 uppercase tracking-widest mt-1">Resolved Today</p>
            </div>

            <div class="clean-card p-5 border-t-4 border-t-rose-500 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between mb-3">
                    <div class="p-2 bg-rose-50 rounded-xl text-rose-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-slate-400">Escalations</span>
                </div>
                <p class="text-2xl font-black text-slate-800">{{ $customerCareStats['escalated_cases'] ?? 0 }}</p>
                <p class="text-[10px] font-bold text-rose-500 uppercase tracking-widest mt-1">Critical Cases</p>
            </div>

            <div class="clean-card p-5 border-t-4 border-t-teal-500 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between mb-3">
                    <div class="p-2 bg-teal-50 rounded-xl text-teal-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <!-- Communication Hub -->
        <div class="xl:col-span-2 animate-slide-up" style="animation-delay: 0.2s;">
            <div class="clean-card overflow-hidden border-2 border-purple-50">
                <div class="purple-gradient p-8 text-white relative">
                    <div class="relative z-10">
                        <h3 class="text-2xl font-black tracking-tight mb-2">Communication Hub</h3>
                        <p class="text-purple-100/80 text-xs font-medium max-w-md">Connect with patients across all channels via standardized protocols.</p>
                    </div>
                    
                    <div class="mt-8 relative max-w-xl">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2.5"/></svg>
                        </div>
                        <input type="text" 
                               id="patientSearch" 
                               onkeyup="searchPatients(this.value)"
                               class="w-full bg-white/10 border border-white/20 text-white placeholder-purple-200 text-sm rounded-2xl py-4 pl-12 pr-4 focus:ring-4 focus:ring-white/10 focus:border-white/40 focus:bg-white/20 transition-all outline-none backdrop-blur-md" 
                               placeholder="Search patient record...">
                        
                        <div id="searchResults" class="absolute z-50 left-0 right-0 mt-3 bg-white rounded-3xl shadow-2xl border border-slate-100 max-h-80 overflow-y-auto hidden divide-y divide-slate-50">
                            <!-- Results populated by JS -->
                        </div>
                    </div>
                </div>

                <div id="communicationInterface" class="p-8 hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        <!-- Left: Patient Details -->
                        <div class="lg:col-span-4">
                            <div class="p-6 bg-slate-50 rounded-[2rem] border border-slate-100">
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
                            </div>
                        </div>

                        <!-- Right: Channel Action -->
                        <div class="lg:col-span-8">
                            <div class="flex p-1.5 bg-slate-100 rounded-2xl mb-6">
                                <button onclick="switchMessageType('sms')" data-type="sms" class="message-tab flex-1 py-3 px-3 rounded-xl text-xs font-bold transition-all duration-300 flex items-center justify-center space-x-2 bg-white text-purple-600 shadow-sm border border-purple-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" stroke-width="2"/></svg>
                                    <span>SMS</span>
                                </button>
                                <button onclick="switchMessageType('whatsapp')" data-type="whatsapp" class="message-tab flex-1 py-3 px-3 rounded-xl text-xs font-bold transition-all duration-300 flex items-center justify-center space-x-2 text-slate-500 hover:text-slate-800">
                                    <span>WhatsApp</span>
                                </button>
                                <button onclick="switchMessageType('voice')" data-type="voice" class="message-tab flex-1 py-3 px-3 rounded-xl text-xs font-bold transition-all duration-300 flex items-center justify-center space-x-2 text-slate-500 hover:text-slate-800">
                                    <span>Voice</span>
                                </button>
                                <button onclick="switchMessageType('video')" data-type="video" class="message-tab flex-1 py-3 px-3 rounded-xl text-xs font-bold transition-all duration-300 flex items-center justify-center space-x-2 text-slate-500 hover:text-slate-800">
                                    <span>Video</span>
                                </button>
                            </div>

                            <div id="textMessageInterface">
                                <textarea id="messageContent" 
                                          rows="5" 
                                          class="w-full bg-white border border-slate-200 rounded-[2rem] p-6 text-sm focus:ring-4 focus:ring-purple-50 transition-all outline-none resize-none mb-4"
                                          placeholder="Enter dispatch details..."></textarea>
                                
                                <button onclick="sendMessage()" class="w-full purple-gradient text-white py-5 rounded-[2rem] font-black text-sm shadow-xl shadow-purple-600/10 hover:translate-y-[-2px] active:translate-y-[0] transition-all flex items-center justify-center space-x-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" stroke-width="2"/></svg>
                                    <span>Initiate Dispatch</span>
                                </button>
                            </div>

                            <div id="callInterface" class="hidden">
                                <div class="bg-slate-50 border border-slate-100 rounded-[2.5rem] p-10 text-center">
                                    <h4 class="text-xl font-black text-slate-800 mb-2">Secure <span id="callType">Voice</span> Session</h4>
                                    <p class="text-xs text-slate-500 mb-8 max-w-sm mx-auto">Standard protocol for encrypted consultations. High-fidelity audio/video bridge initialization.</p>
                                    <button onclick="initiateCall()" class="px-10 py-4 bg-indigo-600 text-white rounded-[2rem] font-black text-sm shadow-xl shadow-indigo-600/10 hover:scale-[1.05] transition-all">
                                        Establish Bridge
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Recent Inbound Logs -->
        <div class="animate-slide-up" style="animation-delay: 0.3s;">
            <div class="flex items-center justify-between mb-6">
                 <h2 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em]">Quick Links</h2>
            </div>
            <div class="space-y-4">
                <a href="{{ route('customer-care.consultations') }}" class="clean-card p-6 flex items-center justify-between hover:border-purple-200 transition-colors group">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-purple-50 text-purple-600 rounded-2xl group-hover:bg-purple-600 group-hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-width="2"/></svg>
                        </div>
                        <span class="font-bold text-slate-700 text-sm">Consultation Registry</span>
                    </div>
                </a>
                <a href="{{ route('customer-care.tickets.index') }}" class="clean-card p-6 flex items-center justify-between hover:border-orange-200 transition-colors group">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-orange-50 text-orange-600 rounded-2xl group-hover:bg-orange-500 group-hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 5v2m0 4v2m0 4v2" stroke-width="2"/></svg>
                        </div>
                        <span class="font-bold text-slate-700 text-sm">Escalation Hub</span>
                    </div>
                </a>
               <a href="{{ route('customer-care.whatsapp-test') }}" class="clean-card p-6 flex items-center justify-between hover:border-emerald-200 transition-colors group">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke-width="2"/></svg>
                        </div>
                        <span class="font-bold text-slate-700 text-sm">Sandbox Validator</span>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Live Data Feed -->
    <div class="animate-slide-up" style="animation-delay: 0.4s;">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Recent Activity</h2>
                <p class="text-[10px] font-bold text-purple-600 uppercase tracking-[0.2em] mt-0.5">Live Consultation feed</p>
            </div>
        </div>
        
        <div class="clean-card overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Reference</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Patient</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Timeframe</th>
                            <th class="px-8 py-5 text-center text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentConsultations as $consultation)
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-8 py-6 whitespace-nowrap">
                                <span class="text-sm font-black text-slate-700 tracking-tight">{{ $consultation->reference }}</span>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 text-xs font-bold">
                                        {{ substr($consultation->patient->name ?? 'N', 0, 1) }}
                                    </div>
                                    <span class="text-sm font-bold text-slate-700">{{ $consultation->patient->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap">
                                <span class="px-4 py-1.5 inline-flex text-[10px] leading-5 font-black rounded-full uppercase tracking-widest
                                    @if($consultation->status === 'completed') bg-emerald-100 text-emerald-700
                                    @elseif($consultation->status === 'pending') bg-amber-100 text-amber-700
                                    @elseif($consultation->status === 'scheduled') bg-indigo-100 text-indigo-700
                                    @else bg-slate-100 text-slate-700
                                    @endif">
                                    {{ $consultation->status }}
                                </span>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap">
                                <span class="text-xs font-bold text-slate-500">{{ $consultation->created_at->diffForHumans() }}</span>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap text-center">
                                <a href="{{ route('customer-care.consultations.show', $consultation->id) }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-600 hover:text-purple-600 hover:border-purple-600 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2.5"/></svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-8 py-10 text-center text-slate-400 font-bold text-sm">No activity records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let selectedPatient = null;
        let currentMessageType = 'sms';

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
                            patientDiv.className = 'px-6 py-4 hover:bg-purple-50 cursor-pointer border-b border-gray-50 last:border-b-0 transition-colors';
                            patientDiv.innerHTML = `
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-black text-slate-800 text-sm tracking-tight">${patient.name}</div>
                                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">${patient.phone}</div>
                                    </div>
                                    <button onclick="selectPatient(${patient.id})" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-purple-600/20">
                                        Select
                                    </button>
                                </div>
                            `;
                            resultsDiv.appendChild(patientDiv);
                        });
                        resultsDiv.classList.remove('hidden');
                    } else {
                        resultsDiv.innerHTML = '<div class="px-6 py-4 text-slate-400 text-xs font-bold">No matches found in register</div>';
                        resultsDiv.classList.remove('hidden');
                    }
                });
        }

        // Select patient
        function selectPatient(patientId) {
            fetch(`{{ route('customer-care.patients.details', ['id' => ':id']) }}`.replace(':id', patientId))
                .then(response => response.json())
                .then(data => {
                    selectedPatient = data.patient;
                    document.getElementById('searchResults').classList.add('hidden');
                    
                    const hubInterface = document.getElementById('communicationInterface');
                    hubInterface.classList.remove('hidden');
                    hubInterface.classList.add('animate-fade-in');
                    
                    // Update patient info
                    document.getElementById('patientInitials').innerText = selectedPatient.name.charAt(0);
                    document.getElementById('selectedPatientName').innerText = selectedPatient.name;
                    
                    document.getElementById('selectedPatientInfo').innerHTML = `
                        <div class="space-y-4">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Encrypted Contact</span>
                                <span class="text-sm font-bold text-slate-700">${selectedPatient.phone}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Electronic Mail</span>
                                <span class="text-sm font-bold text-slate-700">${selectedPatient.email}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Demographics</span>
                                <span class="text-sm font-bold text-slate-700">${data.age ?? '--'} Years • <span class="capitalize">${selectedPatient.gender ?? 'Unknown'}</span></span>
                            </div>
                        </div>
                    `;
                });
        }

        // Switch message type
        function switchMessageType(type) {
            currentMessageType = type;
            
            // Update UI tabs
            document.querySelectorAll('.message-tab').forEach(tab => {
                const isActive = tab.getAttribute('data-type') === type;
                if (isActive) {
                    tab.classList.add('bg-white', 'text-purple-600', 'shadow-sm', 'border', 'border-purple-50');
                    tab.classList.remove('text-slate-500');
                } else {
                    tab.classList.remove('bg-white', 'text-purple-600', 'shadow-sm', 'border', 'border-purple-50');
                    tab.classList.add('text-slate-500');
                }
            });

            // Update interface
            if (type === 'voice' || type === 'video') {
                document.getElementById('textMessageInterface').classList.add('hidden');
                document.getElementById('callInterface').classList.remove('hidden');
                document.getElementById('callType').innerText = type.charAt(0).toUpperCase() + type.slice(1);
            } else {
                document.getElementById('textMessageInterface').classList.remove('hidden');
                document.getElementById('callInterface').classList.add('hidden');
            }
        }

        // Send message
        function sendMessage() {
            const content = document.getElementById('messageContent').value;
            if (!content || !selectedPatient) return;

            const route = currentMessageType === 'sms' 
                ? '{{ route("customer-care.send-sms") }}' 
                : '{{ route("customer-care.send-whatsapp") }}';

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
                    alert('Dispatch successful!');
                    document.getElementById('messageContent').value = '';
                } else {
                    alert('Dispatch failed: ' + data.message);
                }
            });
        }

        // Initiate call
        function initiateCall() {
            if (!selectedPatient) return;

            fetch('{{ route("customer-care.initiate-call") }}', {
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
                    alert(`${currentMessageType.charAt(0).toUpperCase() + currentMessageType.slice(1)} bridge established!`);
                } else {
                    alert('Bridge failure: ' + data.message);
                }
            });
        }
    </script>
@endpush
