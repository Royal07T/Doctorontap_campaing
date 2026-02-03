@extends('layouts.customer-care')

@section('title', 'Interaction Details - Customer Care')

@section('content')
<div class="px-6 py-8">
    <!-- Header & Actions -->
    <div class="flex items-center justify-between mb-10">
        <div class="flex items-center space-x-4">
            <a href="{{ route('customer-care.interactions.index') }}" class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-purple-600 hover:text-white transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Interaction Details</h1>
                <p class="text-[10px] font-bold text-purple-600 uppercase tracking-[0.2em] mt-1">Live session log & resolution management</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest 
                @if($interaction->status === 'active') bg-amber-50 text-amber-600 border border-amber-100
                @elseif($interaction->status === 'resolved') bg-emerald-50 text-emerald-600 border border-emerald-100
                @else bg-slate-100 text-slate-500 border border-slate-200
                @endif">
                {{ $interaction->status }} Status
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Main Content Area -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Interaction Summary -->
            <div class="clean-card p-8 animate-slide-up">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-purple-50 text-purple-600 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 tracking-tight">Executive Summary</h3>
                    </div>
                </div>
                <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100">
                    <p class="text-sm font-bold text-slate-700 leading-relaxed italic">"{{ $interaction->summary }}"</p>
                </div>
            </div>

            <!-- Notes Section -->
            <div class="clean-card p-8 animate-slide-up" style="animation-delay: 0.1s;">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 tracking-tight">Case Notes</h3>
                    </div>
                </div>

                <div class="space-y-6 mb-8">
                    @forelse($interaction->notes as $note)
                    <div class="flex space-x-4 group">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 font-black text-xs">
                            {{ substr($note->creator->name ?? '?', 0, 1) }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-baseline justify-between mb-1">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest">{{ $note->creator->name ?? 'System' }}</h4>
                                <span class="text-[10px] font-bold text-slate-400">{{ $note->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="p-4 rounded-2xl {{ $note->is_internal ? 'bg-amber-50/50 border border-amber-100' : 'bg-slate-50 border border-slate-100' }}">
                                <p class="text-sm font-bold text-slate-600 leading-relaxed">{{ $note->note }}</p>
                                @if($note->is_internal)
                                <div class="mt-2 text-[8px] font-black text-amber-600 uppercase tracking-widest flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z" /></svg>
                                    Internal Personnel Only
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-12">
                        <p class="text-xs font-black text-slate-300 uppercase tracking-widest">No anecdotal data available</p>
                    </div>
                    @endforelse
                </div>

                @if($interaction->status === 'active')
                <div class="mt-10 pt-8 border-t border-slate-50">
                    <form method="POST" action="{{ route('customer-care.interactions.add-note', $interaction) }}">
                        @csrf
                        <div class="mb-4">
                            <textarea name="note" rows="3" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 text-sm font-bold focus:ring-4 focus:ring-purple-50 transition-all outline-none" placeholder="Append medical or logistic observation..."></textarea>
                        </div>
                        <div class="flex items-center justify-between">
                            <label class="flex items-center space-x-3 cursor-pointer group">
                                <div class="relative">
                                    <input type="checkbox" name="is_internal" value="1" checked class="sr-only peer">
                                    <div class="w-10 h-5 bg-slate-200 rounded-full peer peer-checked:bg-purple-600 transition-colors"></div>
                                    <div class="absolute left-1 top-1 w-3 h-3 bg-white rounded-full peer-checked:translate-x-5 transition-transform"></div>
                                </div>
                                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest group-hover:text-purple-600 transition-colors">Internal Privacy</span>
                            </label>
                            <button type="submit" class="bg-slate-800 text-white px-8 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-900 transition-all shadow-lg shadow-slate-200">
                                Commit Note
                            </button>
                        </div>
                    </form>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar Info Area -->
        <div class="space-y-8">
            <!-- Patient Mini-Profile -->
            <div class="clean-card p-6 animate-slide-up">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Patient Entity</p>
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-50 text-purple-600 flex items-center justify-center font-black text-lg shadow-inner">
                        {{ substr($interaction->user->name ?? 'P', 0, 1) }}
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-800 leading-tight">{{ $interaction->user->name }}</h4>
                        <p class="text-[10px] font-bold text-slate-400 tracking-tighter">{{ $interaction->user->email }}</p>
                    </div>
                </div>
                <div class="space-y-2 mb-6">
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-[9px] font-black text-slate-400 uppercase">Phone</span>
                        <span class="text-[11px] font-bold text-slate-800">{{ $interaction->user->phone ?? 'Unlisted' }}</span>
                    </div>
                </div>
                <a href="{{ route('customer-care.customers.show', $interaction->user) }}" class="block w-full text-center py-3 bg-slate-100 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-purple-600 hover:text-white transition-all">
                    View Full Dossier
                </a>
            </div>

            <!-- Interaction Metadata -->
            <div class="clean-card p-6 animate-slide-up" style="animation-delay: 0.1s;">
                 <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Session Metrics</p>
                 <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Comm. Channel</span>
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black uppercase tracking-widest">{{ $interaction->channel }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Assigned Agent</span>
                        <span class="text-[10px] font-black text-slate-800 uppercase">{{ $interaction->agent->name ?? 'SYSTEM' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Start Time</span>
                        <span class="text-[10px] font-black text-slate-800">{{ $interaction->created_at->format('H:i | d M') }}</span>
                    </div>
                 </div>
            </div>

            <!-- Resolution Actions -->
            <div class="clean-card p-6 animate-slide-up border-l-4 border-l-purple-600" style="animation-delay: 0.2s;">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Workflow Triggers</p>
                <div class="space-y-3">
                    @if($interaction->status === 'active')
                    <form method="POST" action="{{ route('customer-care.interactions.end', $interaction) }}" id="endInteractionForm">
                        @csrf
                        <button type="button" onclick="handleEndInteraction()" class="w-full py-3.5 bg-emerald-500 text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-100">
                            Finalize Session
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('customer-care.escalations.create-from-interaction', $interaction) }}" class="block w-full text-center py-3.5 bg-slate-800 text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-slate-900 transition-all">
                        Escalate Case
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function handleEndInteraction() {
        if (confirm('Verify: Are you ready to finalize this interaction and commit it to historical logs?')) {
            document.getElementById('endInteractionForm').submit();
        }
    }
</script>
@endsection
