@extends('layouts.customer-care')

@section('title', 'Ticket Details - Customer Care')

@section('content')
<div class="px-6 py-8">
    <!-- Header & Breadcrumbs -->
    <div class="flex items-center justify-between mb-10">
        <div class="flex items-center space-x-4">
            <a href="{{ route('customer-care.tickets.index') }}" class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-purple-600 hover:text-white transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Ticket #{{ $ticket->ticket_number }}</h1>
                <p class="text-[10px] font-bold text-purple-600 uppercase tracking-[0.2em] mt-1">Support incident diagnostic & resolution</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
             <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest 
                @if($ticket->status === 'open') bg-emerald-50 text-emerald-600 border border-emerald-100
                @elseif($ticket->status === 'resolved') bg-slate-100 text-slate-500 border border-slate-200
                @else bg-amber-50 text-amber-600 border border-amber-100
                @endif">
                {{ $ticket->status }}
            </span>
            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest 
                @if($ticket->priority === 'urgent') bg-rose-50 text-rose-600 border border-rose-100 animate-pulse
                @elseif($ticket->priority === 'high') bg-orange-50 text-orange-600 border border-orange-100
                @else bg-blue-50 text-blue-600 border border-blue-100
                @endif">
                {{ $ticket->priority }} Priority
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Main Case Data -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Subject & Description -->
            <div class="clean-card p-8 animate-slide-up">
                <div class="mb-8 p-6 bg-slate-50 rounded-3xl border border-slate-100">
                    <h2 class="text-lg font-black text-slate-800 mb-2">{{ $ticket->subject }}</h2>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Case Category: {{ $ticket->category }}</p>
                </div>
                
                <div class="relative group">
                    <div class="absolute -left-4 top-0 bottom-0 w-1 bg-purple-600 rounded-full opacity-20 group-hover:opacity-100 transition-opacity"></div>
                    <p class="text-sm font-bold text-slate-600 leading-relaxed whitespace-pre-wrap pl-4">{{ $ticket->description }}</p>
                </div>
            </div>

            <!-- Escalation Log -->
            @if($ticket->escalations->count() > 0)
            <div class="clean-card p-8 animate-slide-up" style="animation-delay: 0.1s;">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="p-2 bg-orange-50 text-orange-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 tracking-tight">Oversight & Escalations</h3>
                </div>

                <div class="space-y-6">
                    @foreach($ticket->escalations as $escalation)
                    <div class="flex space-x-4">
                        <div class="flex-shrink-0 w-1 pt-2">
                            <div class="w-2 h-2 rounded-full bg-orange-500 shadow-lg shadow-orange-200"></div>
                        </div>
                        <div class="flex-1 p-5 rounded-2xl bg-orange-50/30 border border-orange-100/50">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-black text-orange-600 uppercase tracking-widest">Escalated to {{ $escalation->escalated_to_type }}</span>
                                <span class="text-[10px] font-bold text-slate-400">{{ $escalation->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm font-bold text-slate-700 leading-relaxed">{{ $escalation->reason }}</p>
                            <div class="mt-3 flex items-center text-[9px] font-black text-slate-400 uppercase tracking-tighter">
                                Authored by: {{ $escalation->escalatedBy->name ?? 'System Internal' }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Meta Dossier Sidebar -->
        <div class="space-y-8">
            <!-- Patient Mini-Dossier -->
            <div class="clean-card p-6 animate-slide-up">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Patient Entity</p>
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-50 text-purple-600 flex items-center justify-center font-black text-lg shadow-inner">
                        {{ substr($ticket->user->name ?? 'P', 0, 1) }}
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-800 leading-tight">{{ $ticket->user->name }}</h4>
                        <p class="text-[10px] font-bold text-slate-400 tracking-tighter">{{ $ticket->user->email }}</p>
                    </div>
                </div>
                <div class="space-y-2 mb-6 text-[11px] font-bold text-slate-600">
                    <div class="flex justify-between p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-400 uppercase tracking-widest text-[9px]">Mobile</span>
                        <span>{{ $ticket->user->phone ?? 'NR' }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-2">
                    <a href="{{ route('customer-care.customers.show', $ticket->user) }}" class="block w-full text-center py-3 bg-slate-800 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-900 transition-all">
                        Full Patient Records
                    </a>
                </div>
            </div>

            <!-- Workflow Management -->
            <div class="clean-card p-6 animate-slide-up border-l-4 border-l-purple-600" style="animation-delay: 0.1s;">
                 <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Workflow Triggers</p>
                 <div class="space-y-4">
                    @if(!$ticket->agent_id)
                    <form method="POST" action="{{ route('customer-care.tickets.assign-to-me', $ticket) }}">
                        @csrf
                        <button type="submit" class="w-full py-3.5 bg-emerald-500 text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-100">
                            Claim Ticket Responsibility
                        </button>
                    </form>
                    @endif

                    @if($ticket->status !== 'resolved')
                    <form method="POST" action="{{ route('customer-care.tickets.update-status', $ticket) }}" class="space-y-3">
                        @csrf
                        <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-[10px] font-black uppercase tracking-widest outline-none transition-all focus:ring-4 focus:ring-purple-50">
                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Mark: OPEN</option>
                            <option value="pending" {{ $ticket->status === 'pending' ? 'selected' : '' }}>Mark: PENDING</option>
                            <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Mark: RESOLVED</option>
                            <option value="escalated" {{ $ticket->status === 'escalated' ? 'selected' : '' }}>Mark: ESCALATED</option>
                        </select>
                        <button type="submit" class="w-full py-3 bg-slate-100 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-purple-600 hover:text-white transition-all">
                            Commit Status Change
                        </button>
                    </form>
                    @endif

                    <div class="pt-4 border-t border-slate-50">
                         <a href="{{ route('customer-care.escalations.create-from-ticket', $ticket) }}" class="block w-full text-center py-3.5 bg-rose-50 text-rose-600 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-rose-600 hover:text-white transition-all">
                            Executive Escalation
                        </a>
                    </div>
                 </div>
            </div>

            <!-- Ownership Dossier -->
            <div class="clean-card p-6 animate-slide-up" style="animation-delay: 0.2s;">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Ownership Dossier</p>
                <div class="space-y-4">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase mb-1">Assigned Liaison</p>
                        <p class="text-xs font-black text-slate-800 uppercase tracking-widest">{{ $ticket->agent->name ?? 'Unassigned Asset' }}</p>
                    </div>
                    @if($ticket->resolved_at)
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase mb-1">Resolution Closure</p>
                        <p class="text-xs font-black text-emerald-600 uppercase tracking-widest">{{ $ticket->resolved_at->format('d M Y | H:i') }}</p>
                        <p class="text-[9px] font-bold text-slate-400 mt-1">Closed by: {{ $ticket->resolvedBy->name ?? 'N/A' }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
