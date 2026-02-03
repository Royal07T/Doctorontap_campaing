@extends('layouts.customer-care')

@section('title', 'Escalation Details - Customer Care')

@section('content')
<div class="px-6 py-8">
    <!-- Header & Status Brackets -->
    <div class="flex items-center justify-between mb-10">
        <div class="flex items-center space-x-4">
            <a href="{{ route('customer-care.escalations.index') }}" class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-purple-600 hover:text-white transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Escalation Dossier</h1>
                <p class="text-[10px] font-bold text-purple-600 uppercase tracking-[0.2em] mt-1">High-level resolution & executive oversight</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
             <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest 
                @if($escalation->status === 'pending') bg-amber-50 text-amber-600 border border-amber-100
                @elseif($escalation->status === 'resolved') bg-emerald-50 text-emerald-600 border border-emerald-100
                @else bg-slate-100 text-slate-500 border border-slate-200
                @endif">
                {{ str_replace('_', ' ', $escalation->status) }}
            </span>
             <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-purple-50 text-purple-600 border border-purple-100">
                To: {{ $escalation->escalated_to_type }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Main Escalation Narrative -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Escalation Context -->
            <div class="clean-card p-8 animate-slide-up">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-rose-50 text-rose-600 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 tracking-tight">Executive Summary</h3>
                    </div>
                </div>
                <div class="p-8 bg-slate-50 rounded-3xl border border-slate-100 italic">
                    <p class="text-sm font-bold text-slate-700 leading-relaxed">"{{ $escalation->reason }}"</p>
                </div>
            </div>

            <!-- Resolution Data -->
            @if($escalation->outcome)
            <div class="clean-card p-8 animate-slide-up border-t-4 border-t-emerald-500" style="animation-delay: 0.1s;">
                 <div class="flex items-center space-x-3 mb-8">
                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 tracking-tight">Resolution Outcome</h3>
                </div>
                <p class="text-sm font-bold text-slate-600 leading-relaxed bg-slate-50 p-6 rounded-2xl border border-slate-100">{{ $escalation->outcome }}</p>
            </div>
            @endif

            <!-- Related Assets -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($escalation->supportTicket)
                <div class="clean-card p-6 animate-slide-up" style="animation-delay: 0.2s;">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Origin Ticket</p>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <h4 class="text-xs font-black text-slate-800 uppercase mb-2">{{ $escalation->supportTicket->subject }}</h4>
                        <a href="{{ route('customer-care.tickets.show', $escalation->supportTicket) }}" class="text-[10px] font-black text-purple-600 uppercase tracking-widest hover:text-purple-800 transition-colors">
                            View #{{ $escalation->supportTicket->ticket_number }} →
                        </a>
                    </div>
                </div>
                @endif

                @if($escalation->customerInteraction)
                <div class="clean-card p-6 animate-slide-up" style="animation-delay: 0.25s;">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Interaction Node</p>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <p class="text-sm font-bold text-slate-600 truncate mb-2">{{ $escalation->customerInteraction->summary }}</p>
                        <a href="{{ route('customer-care.interactions.show', $escalation->customerInteraction) }}" class="text-[10px] font-black text-purple-600 uppercase tracking-widest hover:text-purple-800 transition-colors">
                            View Session Detail →
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Meta Sidebar -->
        <div class="space-y-8">
            <!-- Assignment Dossier -->
            <div class="clean-card p-6 animate-slide-up">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Personnel Oversight</p>
                <div class="space-y-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 font-black text-xs">
                            {{ substr($escalation->escalatedBy->name ?? '?', 0, 1) }}
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase">Initiated By</p>
                            <p class="text-xs font-black text-slate-800">{{ $escalation->escalatedBy->name ?? 'System' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-black text-xs shadow-inner">
                            {{ substr($escalation->escalatedTo->name ?? '?', 0, 1) }}
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-purple-400 uppercase">Handled By</p>
                            <p class="text-xs font-black text-slate-800">{{ $escalation->escalatedTo->name ?? 'Pending Resource' }}</p>
                            @if($escalation->escalated_to_type === 'doctor')
                            <p class="text-[8px] font-bold text-slate-400 uppercase">{{ $escalation->escalatedTo->specialization ?? 'Clinical Lead' }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline Registry -->
            <div class="clean-card p-6 animate-slide-up" style="animation-delay: 0.1s;">
                 <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Event Timeline</p>
                 <div class="space-y-4">
                    <div class="flex justify-between items-center text-[10px] font-bold">
                        <span class="text-slate-400 uppercase">Commenced</span>
                        <span class="text-slate-800">{{ $escalation->created_at->format('d M, H:i') }}</span>
                    </div>
                    @if($escalation->resolved_at)
                    <div class="flex justify-between items-center text-[10px] font-bold">
                        <span class="text-emerald-500 uppercase">Concluded</span>
                        <span class="text-emerald-600">{{ $escalation->resolved_at->format('d M, H:i') }}</span>
                    </div>
                    @endif
                 </div>
            </div>

            <!-- Patient Quick-View -->
            @php 
                $patient = $escalation->supportTicket->user ?? $escalation->customerInteraction->user ?? null;
            @endphp
            @if($patient)
            <div class="clean-card p-6 animate-slide-up" style="animation-delay: 0.2s;">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Subject Profile</p>
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-black text-xs">
                        {{ substr($patient->name, 0, 1) }}
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-slate-800 truncate">{{ $patient->name }}</h4>
                        <p class="text-[9px] font-bold text-slate-400 tracking-tighter">{{ $patient->email }}</p>
                    </div>
                </div>
                <a href="{{ route('customer-care.customers.show', $patient) }}" class="block w-full py-2 bg-slate-100 text-slate-500 text-center rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-purple-600 hover:text-white transition-all">
                    Profile Dossier
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
