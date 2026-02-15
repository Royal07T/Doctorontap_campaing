@extends('layouts.customer-care')

@section('title', 'Customer Interactions - Customer Care')

@section('content')
<div class="px-6 py-8">
    <div class="flex items-center justify-between mb-10">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Customer Interactions</h1>
            <p class="text-[10px] font-bold text-purple-600 uppercase tracking-[0.2em] mt-1">Full interaction history & tracking</p>
        </div>
        <a href="{{ route('customer-care.interactions.create') }}" class="purple-gradient text-white px-8 py-4 rounded-[1.5rem] font-black text-sm shadow-xl shadow-purple-600/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
            <span>New Interaction</span>
        </a>
    </div>

    @if(session('success'))
    <div class="mb-8 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center space-x-3 animate-fade-in">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        <p class="text-sm font-bold">{{ session('success') }}</p>
    </div>
    @endif

    <!-- Search & Filters -->
    <div class="clean-card p-6 mb-8 animate-slide-up">
        <form method="GET" action="{{ route('customer-care.interactions.index') }}" class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <div class="lg:col-span-2">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Search Database</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Customer name or email..." 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-4 focus:ring-purple-50 transition-all outline-none">
                    <svg class="absolute right-4 top-3.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2"/></svg>
                </div>
            </div>
            
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Current Status</label>
                <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-4 focus:ring-purple-50 transition-all outline-none appearance-none">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Channel</label>
                <select name="channel" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-4 focus:ring-purple-50 transition-all outline-none appearance-none">
                    <option value="">All Channels</option>
                    <option value="chat" {{ request('channel') == 'chat' ? 'selected' : '' }}>Chat</option>
                    <option value="call" {{ request('channel') == 'call' ? 'selected' : '' }}>Call</option>
                    <option value="email" {{ request('channel') == 'email' ? 'selected' : '' }}>Email</option>
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 bg-slate-800 text-white rounded-xl px-4 py-3 text-sm font-bold hover:bg-slate-900 transition-all">
                    Filter Results
                </button>
                @if(request()->hasAny(['search', 'status', 'channel']))
                <a href="{{ route('customer-care.interactions.index') }}" class="bg-slate-100 text-slate-600 rounded-xl px-4 py-3 hover:bg-slate-200 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2"/></svg>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Results Table -->
    <div class="clean-card overflow-hidden animate-slide-up" style="animation-delay: 0.1s;">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50">
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Customer Profile</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Channel</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Summary</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Created</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($interactions as $interaction)
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        <td class="px-6 py-5">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-black text-xs shadow-inner">
                                    {{ substr($interaction->user->name ?? 'NA', 0, 1) }}
                                </div>
                                <div>
                                    <div class="text-sm font-black text-slate-800">{{ $interaction->user->name ?? 'N/A' }}</div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $interaction->user ? \App\Helpers\PrivacyHelper::maskEmail($interaction->user->email) : '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest 
                                {{ $interaction->channel == 'chat' ? 'bg-blue-50 text-blue-600' : '' }}
                                {{ $interaction->channel == 'call' ? 'bg-emerald-50 text-emerald-600' : '' }}
                                {{ $interaction->channel == 'email' ? 'bg-purple-50 text-purple-600' : '' }}">
                                {{ $interaction->channel }}
                            </span>
                        </td>
                        <td class="px-6 py-5">
                            <div class="text-xs font-bold text-slate-600 max-w-xs truncate">
                                {{ $interaction->summary }}
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex flex-col space-y-1.5">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest w-fit
                                    {{ $interaction->status == 'active' ? 'bg-amber-50 text-amber-600' : '' }}
                                    {{ $interaction->status == 'resolved' ? 'bg-emerald-50 text-emerald-600' : '' }}
                                    {{ $interaction->status == 'pending' ? 'bg-slate-100 text-slate-500' : '' }}">
                                    {{ $interaction->status }}
                                </span>
                                @if($interaction->status == 'active')
                                <span class="text-[9px] font-black text-amber-600 uppercase tracking-widest">Action: Continue or End</span>
                                @elseif($interaction->status == 'pending')
                                <span class="text-[9px] font-black text-slate-600 uppercase tracking-widest">Action: Respond</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ $interaction->created_at->format('d M Y') }}</div>
                            <div class="text-[10px] font-medium text-slate-400">{{ $interaction->created_at->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <a href="{{ route('customer-care.interactions.show', $interaction) }}" 
                               class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-purple-600 hover:text-white transition-all">
                                View Full Profile
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="w-20 h-20 bg-slate-50 text-slate-200 rounded-[2rem] flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                            </div>
                            <h3 class="text-lg font-black text-slate-800">No Interactions Found</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-2 mb-4">Try adjusting your filters or search terms</p>
                            <div class="flex items-center justify-center space-x-3">
                                @if(request()->hasAny(['search', 'status', 'channel']))
                                <a href="{{ route('customer-care.interactions.index') }}" class="px-6 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-all">
                                    Clear Filters
                                </a>
                                @endif
                                <a href="{{ route('customer-care.interactions.create') }}" class="px-6 py-2 bg-purple-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-purple-700 transition-all">
                                    Create New Interaction
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($interactions->hasPages())
        <div class="px-6 py-6 border-t border-slate-50 bg-slate-50/30">
            {{ $interactions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
