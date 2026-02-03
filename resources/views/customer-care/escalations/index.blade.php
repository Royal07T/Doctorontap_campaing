@extends('layouts.customer-care')

@section('title', 'Escalations - Customer Care')

@section('content')
<div class="px-6 py-8">
    <div class="flex items-center justify-between mb-10">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Escalations</h1>
            <p class="text-[10px] font-bold text-purple-600 uppercase tracking-[0.2em] mt-1">High-priority case management & oversight</p>
        </div>
        <div class="flex items-center space-x-3">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ now()->format('l, F j, Y') }}</span>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-8 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center space-x-3 animate-fade-in">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        <p class="text-sm font-bold">{{ session('success') }}</p>
    </div>
    @endif

    <!-- Search & Filters -->
    <div class="clean-card p-6 mb-8 animate-slide-up">
        <form method="GET" action="{{ route('customer-care.escalations.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Current Status</label>
                <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-4 focus:ring-purple-50 transition-all outline-none appearance-none">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Escalated To</label>
                <select name="escalated_to_type" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-4 focus:ring-purple-50 transition-all outline-none appearance-none">
                    <option value="">All Oversight Groups</option>
                    <option value="admin" {{ request('escalated_to_type') == 'admin' ? 'selected' : '' }}>Administrative Board</option>
                    <option value="doctor" {{ request('escalated_to_type') == 'doctor' ? 'selected' : '' }}>Medical Directors</option>
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 bg-slate-800 text-white rounded-xl px-4 py-3 text-sm font-bold hover:bg-slate-900 transition-all">
                    Filter Queue
                </button>
                @if(request()->hasAny(['status', 'escalated_to_type']))
                <a href="{{ route('customer-care.escalations.index') }}" class="bg-slate-100 text-slate-600 rounded-xl px-4 py-3 hover:bg-slate-200 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2"/></svg>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Escalations Registry -->
    <div class="clean-card overflow-hidden animate-slide-up" style="animation-delay: 0.1s;">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50">
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Source Entity</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Escalation Target</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Primary Reason</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Current Status</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Timeline</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($escalations as $escalation)
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        <td class="px-6 py-5">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                </div>
                                <div>
                                    <div class="text-xs font-black text-slate-800 uppercase tracking-widest">
                                        @if($escalation->support_ticket_id)
                                            Ticket #{{ $escalation->supportTicket->ticket_number ?? 'N/A' }}
                                        @elseif($escalation->customer_interaction_id)
                                            Interaction #{{ $escalation->customer_interaction_id }}
                                        @else
                                            General Escalation
                                        @endif
                                    </div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Reference ID: {{ $escalation->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest 
                                {{ $escalation->escalated_to_type == 'admin' ? 'bg-indigo-50 text-indigo-600' : 'bg-rose-50 text-rose-600' }}">
                                {{ $escalation->escalated_to_type }}
                            </span>
                        </td>
                        <td class="px-6 py-5">
                            <div class="text-xs font-bold text-slate-600 max-w-xs truncate">
                                {{ $escalation->reason }}
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest 
                                {{ $escalation->status == 'pending' ? 'bg-amber-50 text-amber-600' : '' }}
                                {{ $escalation->status == 'in_progress' ? 'bg-blue-50 text-blue-600' : '' }}
                                {{ $escalation->status == 'resolved' ? 'bg-emerald-50 text-emerald-600' : '' }}
                                {{ $escalation->status == 'closed' ? 'bg-slate-100 text-slate-500' : '' }}">
                                {{ str_replace('_', ' ', $escalation->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-5">
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ $escalation->created_at->format('d M Y') }}</div>
                            <div class="text-[10px] font-medium text-slate-400">{{ $escalation->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <a href="{{ route('customer-care.escalations.show', $escalation) }}" 
                               class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-purple-600 hover:text-white transition-all">
                                Review Case
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="w-20 h-20 bg-slate-50 text-slate-200 rounded-[2rem] flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                            </div>
                            <h3 class="text-lg font-black text-slate-800">Queue is Clear</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-2">No pending escalations require attention</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($escalations->hasPages())
        <div class="px-6 py-6 border-t border-slate-50 bg-slate-50/30">
            {{ $escalations->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
