@extends('layouts.customer-care')

@section('title', 'Support Tickets - Customer Care')

@section('content')
<div class="px-6 py-8">
    <div class="flex items-center justify-between mb-10">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Support Tickets</h1>
            <p class="text-[10px] font-bold text-purple-600 uppercase tracking-[0.2em] mt-1">Resolution tracking & escalation management</p>
        </div>
        <a href="{{ route('customer-care.tickets.create') }}" class="purple-gradient text-white px-8 py-4 rounded-[1.5rem] font-black text-sm shadow-xl shadow-purple-600/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
            <span>New Ticket</span>
        </a>
    </div>

    @if(session('success'))
    <div class="mb-8 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center space-x-3 animate-fade-in">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        <p class="text-sm font-bold">{{ session('success') }}</p>
    </div>
    @endif

    <!-- Filters -->
    <div class="clean-card p-6 mb-8 animate-slide-up">
        <form method="GET" action="{{ route('customer-care.tickets.index') }}" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="lg:col-span-2">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Search Tickets</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Ticket #, subject, or customer..." 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-4 focus:ring-purple-50 transition-all outline-none">
                    <svg class="absolute right-4 top-3.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2"/></svg>
                </div>
            </div>
            
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Status</label>
                <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-4 focus:ring-purple-50 transition-all outline-none appearance-none">
                    <option value="">All Status</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="escalated" {{ request('status') == 'escalated' ? 'selected' : '' }}>Escalated</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Category</label>
                <select name="category" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-4 focus:ring-purple-50 transition-all outline-none appearance-none">
                    <option value="">All Categories</option>
                    <option value="billing" {{ request('category') == 'billing' ? 'selected' : '' }}>Billing</option>
                    <option value="appointment" {{ request('category') == 'appointment' ? 'selected' : '' }}>Appointment</option>
                    <option value="technical" {{ request('category') == 'technical' ? 'selected' : '' }}>Technical</option>
                    <option value="medical" {{ request('category') == 'medical' ? 'selected' : '' }}>Medical</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Priority</label>
                <select name="priority" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-4 focus:ring-purple-50 transition-all outline-none appearance-none">
                    <option value="">All Priorities</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-slate-800 text-white rounded-xl px-4 py-3 text-sm font-bold hover:bg-slate-900 transition-all">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Tickets Registry -->
    <div class="clean-card overflow-hidden animate-slide-up" style="animation-delay: 0.1s;">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50">
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Ticket Identity</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Customer / User</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Subject & Description</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Priority & Status</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Assignment</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($tickets as $ticket)
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        <td class="px-6 py-5">
                            <div class="text-sm font-black text-slate-800">#{{ $ticket->ticket_number }}</div>
                            <div class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest">{{ $ticket->created_at->format('d M Y') }}</div>
                        </td>
                        <td class="px-6 py-5">
                            @if($ticket->user_type === 'doctor' && $ticket->doctor)
                                <div class="text-sm font-black text-slate-800">Dr. {{ $ticket->doctor->name }}</div>
                                <div class="text-[10px] font-bold text-purple-600 uppercase tracking-widest mt-1">Medical Staff</div>
                            @elseif($ticket->user_type === 'patient' && $ticket->user)
                                <div class="text-sm font-black text-slate-800">{{ $ticket->user->name }}</div>
                                <div class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest mt-1">Patient Profile</div>
                            @else
                                <div class="text-sm font-black text-slate-400">System Internal</div>
                            @endif
                        </td>
                        <td class="px-6 py-5">
                            <div class="text-xs font-bold text-slate-700 mb-1 max-w-xs truncate">{{ $ticket->subject }}</div>
                            <span class="inline-flex px-2 py-0.5 rounded bg-slate-100 text-[9px] font-black text-slate-500 uppercase tracking-widest">
                                {{ $ticket->category }}
                            </span>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex flex-col space-y-1.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest w-fit
                                    {{ $ticket->priority == 'urgent' ? 'bg-red-50 text-red-600' : '' }}
                                    {{ $ticket->priority == 'high' ? 'bg-orange-50 text-orange-600' : '' }}
                                    {{ $ticket->priority == 'medium' ? 'bg-amber-50 text-amber-600' : '' }}
                                    {{ $ticket->priority == 'low' ? 'bg-slate-100 text-slate-500' : '' }}">
                                    {{ $ticket->priority }} Priority
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest w-fit
                                    {{ $ticket->status == 'open' ? 'bg-emerald-50 text-emerald-600' : '' }}
                                    {{ $ticket->status == 'pending' ? 'bg-amber-50 text-amber-600' : '' }}
                                    {{ $ticket->status == 'resolved' ? 'bg-slate-50 text-slate-400' : '' }}
                                    {{ $ticket->status == 'escalated' ? 'bg-purple-50 text-purple-600' : '' }}">
                                    Status: {{ $ticket->status }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            @if($ticket->agent_id && $ticket->agent)
                                <div class="flex items-center space-x-2">
                                    <div class="w-6 h-6 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-500">
                                        {{ substr($ticket->agent->name, 0, 1) }}
                                    </div>
                                    <div class="text-[10px] font-bold text-slate-600">{{ $ticket->agent->name }}</div>
                                </div>
                            @else
                                <span class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Awaiting Assignee</span>
                            @endif
                        </td>
                        <td class="px-6 py-5 text-right">
                            <a href="{{ route('customer-care.tickets.show', $ticket) }}" 
                               class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-purple-600 hover:text-white transition-all">
                                Manage Ticket
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="w-20 h-20 bg-slate-50 text-slate-200 rounded-[2rem] flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            </div>
                            <h3 class="text-lg font-black text-slate-800">No Tickets Logged</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-2">The support queue is currently clear</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())
        <div class="px-6 py-6 border-t border-slate-50 bg-slate-50/30">
            {{ $tickets->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
