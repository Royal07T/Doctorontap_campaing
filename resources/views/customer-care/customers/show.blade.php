@extends('layouts.customer-care')

@section('title', ($patient->name ?? 'Patient') . ' - Patient Profile')

@section('content')
<div class="px-6 py-8">
    <!-- Breadcrumbs & Header -->
    <div class="flex items-center justify-between mb-10">
        <div class="flex items-center space-x-4">
            <a href="{{ route('customer-care.customers.index') }}" class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-purple-600 hover:text-white transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Patient Profile</h1>
                <p class="text-[10px] font-bold text-purple-600 uppercase tracking-[0.2em] mt-1">Customer support & interaction history</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $patient->is_verified ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-amber-50 text-amber-600 border border-amber-100' }}">
                {{ $patient->is_verified ? 'Verified Identity' : 'Pending Verification' }}
            </span>
        </div>
    </div>

    <!-- Central Dossier Profile -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Profile Sidebar Card -->
        <div class="lg:col-span-1 space-y-8">
            <div class="clean-card p-8 text-center animate-slide-up">
                <div class="relative inline-block mb-6">
                    @if($patient->photo)
                    <img src="{{ asset('storage/' . $patient->photo) }}" alt="{{ $patient->name }}" class="w-32 h-32 rounded-[2.5rem] object-cover border-4 border-white shadow-2xl mx-auto">
                    @else
                    <div class="w-32 h-32 rounded-[2.5rem] bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-white text-4xl font-black shadow-2xl mx-auto">
                        {{ substr($patient->name ?? 'P', 0, 1) }}
                    </div>
                    @endif
                    <div class="absolute -bottom-2 -right-2 bg-emerald-500 text-white p-2 rounded-xl border-4 border-white shadow-lg">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M5 13l4 4L19 7" /></svg>
                    </div>
                </div>
                
                <h2 class="text-2xl font-black text-slate-800 mb-1">{{ $patient->name }}</h2>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-4">Patient ID: #DT-{{ str_pad($patient->id, 5, '0', STR_PAD_LEFT) }}</p>
                
                <div class="pt-6 mt-6 border-t border-slate-50 space-y-4">
                    <div class="flex items-center justify-between text-left">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Email Address</span>
                        <span class="text-sm font-bold text-slate-800">{{ $patient->email }}</span>
                    </div>
                    <div class="flex items-center justify-between text-left">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Phone Number</span>
                        <span class="text-sm font-bold text-slate-800">{{ $patient->phone ?? 'NR' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-left">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Joined On</span>
                        <span class="text-sm font-bold text-slate-800">{{ $patient->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Basic Info -->
            <div class="clean-card p-6 animate-slide-up" style="animation-delay: 0.1s;">
                 <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Basic Information</p>
                 <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter mb-1">Age</p>
                        <p class="text-sm font-black text-slate-800">{{ $patient->age ?? 'NA' }} Yrs</p>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter mb-1">Gender</p>
                        <p class="text-sm font-black text-slate-800">{{ ucfirst($patient->gender ?? 'NA') }}</p>
                    </div>
                 </div>
            </div>
        </div>

        <!-- Detailed Dossier Info -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Information Notice -->
            <div class="clean-card p-8 animate-slide-up border-l-4 border-l-amber-500">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="p-2 bg-amber-50 text-amber-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 tracking-tight">Medical Information Restricted</h3>
                </div>
                <p class="text-sm font-bold text-slate-600 leading-relaxed">
                    Medical information is restricted to licensed medical professionals only. To communicate with this patient, please use the consultation details page where you can send messages via email, SMS, or WhatsApp.
                </p>
                <div class="mt-6 pt-6 border-t border-slate-100">
                    <a href="{{ route('customer-care.consultations') }}" class="inline-flex items-center text-xs font-black text-purple-600 uppercase tracking-widest hover:text-purple-800 transition-colors">
                        View Consultations
                        <svg class="w-3.5 h-3.5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke-width="2.5"/></svg>
                    </a>
                </div>
            </div>

            <!-- Stats & Engagement -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-slide-up" style="animation-delay: 0.2s;">
                <div class="clean-card p-6 flex items-center space-x-4 border-l-4 border-l-blue-500">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-slate-800 leading-none">{{ $patient->customerInteractions->count() }}</p>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Interactions</p>
                    </div>
                </div>
                <div class="clean-card p-6 flex items-center space-x-4 border-l-4 border-l-orange-500">
                    <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2" /></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-slate-800 leading-none">{{ $patient->supportTickets->count() }}</p>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Tickets</p>
                    </div>
                </div>
                <div class="clean-card p-6 flex items-center space-x-4 border-l-4 border-l-purple-500">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-slate-800 leading-none">{{ $patient->consultations->count() }}</p>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Visits</p>
                    </div>
                </div>
            </div>

            <!-- Tabbed Activity History -->
            <div class="clean-card overflow-hidden animate-slide-up" style="animation-delay: 0.3s;" x-data="{ tab: 'interactions' }">
                <div class="flex bg-slate-50 border-b border-slate-100">
                    <button @click="tab = 'interactions'" :class="tab === 'interactions' ? 'bg-white text-purple-600 border-b-2 border-purple-600' : 'text-slate-400 hover:text-slate-600'" class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] transition-all outline-none">Recent Interactions</button>
                    <button @click="tab = 'tickets'" :class="tab === 'tickets' ? 'bg-white text-purple-600 border-b-2 border-purple-600' : 'text-slate-400 hover:text-slate-600'" class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] transition-all outline-none">Support Queue</button>
                    <button @click="tab = 'consultations'" :class="tab === 'consultations' ? 'bg-white text-purple-600 border-b-2 border-purple-600' : 'text-slate-400 hover:text-slate-600'" class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] transition-all outline-none">Clinical Visits</button>
                </div>

                <!-- Interactions Tab -->
                <div x-show="tab === 'interactions'" class="p-0 animate-fade-in">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Channel</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Identity/Agent</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Summary</th>
                                <th class="px-6 py-4 text-right text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Finalize</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($patient->customerInteractions->take(5) as $interaction)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-5">
                                    <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest 
                                        {{ $interaction->channel == 'chat' ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600' }}">
                                        {{ $interaction->channel }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-[10px] font-bold text-slate-800">{{ $interaction->agent->name ?? 'System' }}</div>
                                    <div class="text-[9px] font-medium text-slate-400">{{ $interaction->created_at->format('d M - H:i') }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-[11px] font-bold text-slate-600 truncate max-w-[200px]">{{ $interaction->summary }}</div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <a href="{{ route('customer-care.interactions.show', $interaction) }}" class="text-purple-600 hover:text-purple-800 transition-colors">
                                        <svg class="w-5 h-5 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-6 py-12 text-center text-slate-300 font-bold text-sm italic">No interaction records found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Tickets Tab -->
                <div x-show="tab === 'tickets'" class="p-0 animate-fade-in" style="display: none;">
                    <table class="w-full text-left">
                         <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Ticket #</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">State</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Issue Summary</th>
                                <th class="px-6 py-4 text-right text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($patient->supportTickets->take(5) as $ticket)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-5 font-black text-slate-800 text-xs">#{{ $ticket->ticket_number }}</td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest 
                                        {{ $ticket->status == 'open' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                                        {{ $ticket->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-[11px] font-bold text-slate-600 truncate max-w-[200px]">{{ $ticket->subject }}</div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <a href="{{ route('customer-care.tickets.show', $ticket) }}" class="text-purple-600 hover:text-purple-800 transition-colors">
                                        <svg class="w-5 h-5 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-6 py-12 text-center text-slate-300 font-bold text-sm italic">No support tickets issued</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Consultations Tab -->
                <div x-show="tab === 'consultations'" class="p-0 animate-fade-in" style="display: none;">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Session Date</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Medical Pro</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Status</th>
                                <th class="px-6 py-4 text-right text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Records</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($patient->consultations->take(5) as $consultation)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-5 text-xs font-black text-slate-800">{{ $consultation->created_at->format('d M Y') }}</td>
                                <td class="px-6 py-5">
                                    <div class="text-[10px] font-bold text-slate-600 uppercase tracking-widest">{{ $consultation->doctor->name ?? 'Assigned MD' }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest 
                                        {{ $consultation->status == 'completed' ? 'bg-indigo-50 text-indigo-600' : 'bg-slate-100 text-slate-400' }}">
                                        {{ $consultation->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <a href="{{ route('customer-care.consultations.show', $consultation->id) }}" class="text-purple-600 hover:text-purple-800 transition-colors">
                                        <svg class="w-5 h-5 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-6 py-12 text-center text-slate-300 font-bold text-sm italic">No clinical records found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
