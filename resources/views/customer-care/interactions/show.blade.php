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
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Customer Support Session</h1>
                <p class="text-[10px] font-bold text-purple-600 uppercase tracking-[0.2em] mt-1">Real-time communication log & case management</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest 
                @if($interaction->status === 'active') bg-amber-50 text-amber-600 border border-amber-100
                @elseif($interaction->status === 'resolved') bg-emerald-50 text-emerald-600 border border-emerald-100
                @else bg-slate-100 text-slate-500 border border-slate-200
                @endif">
                @if($interaction->status === 'active')
                    <span class="w-2 h-2 bg-amber-500 rounded-full mr-2 animate-pulse"></span>
                @endif
                {{ ucfirst($interaction->status) }}
            </span>
        </div>
    </div>

    <!-- What is this? Info Box -->
    <div class="clean-card p-6 mb-8 bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-l-blue-500 animate-slide-up">
        <div class="flex items-start space-x-4">
            <div class="p-2 bg-blue-100 text-blue-600 rounded-xl flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-black text-slate-800 mb-2">What is this page?</h3>
                <p class="text-xs font-bold text-slate-600 leading-relaxed mb-3">
                    This is a <strong>customer support session</strong> - a record of a real-time conversation between you (the agent) and a customer. 
                    Use this page to track ongoing support, add notes about the conversation, and manage the resolution process.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-4">
                    <div class="bg-white/60 rounded-xl p-3 border border-blue-100">
                        <p class="text-[9px] font-black text-blue-600 uppercase tracking-widest mb-1">Channel</p>
                        <p class="text-xs font-bold text-slate-800 capitalize">{{ $interaction->channel }}</p>
                    </div>
                    <div class="bg-white/60 rounded-xl p-3 border border-blue-100">
                        <p class="text-[9px] font-black text-blue-600 uppercase tracking-widest mb-1">Started</p>
                        <p class="text-xs font-bold text-slate-800">{{ $interaction->created_at->format('M d, Y • H:i') }}</p>
                    </div>
                    <div class="bg-white/60 rounded-xl p-3 border border-blue-100">
                        <p class="text-[9px] font-black text-blue-600 uppercase tracking-widest mb-1">Duration</p>
                        <p class="text-xs font-bold text-slate-800" id="durationDisplay">
                            @if($interaction->status === 'active')
                                <span class="text-amber-600">In Progress...</span>
                            @elseif($interaction->duration_seconds)
                                {{ round($interaction->duration_seconds / 60) }} minutes
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Main Content Area -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Interaction Summary -->
            <div class="clean-card p-8 animate-slide-up">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-purple-50 text-purple-600 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-800 tracking-tight">Session Summary</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">What this conversation is about</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100">
                    <p class="text-sm font-bold text-slate-700 leading-relaxed">{{ $interaction->summary }}</p>
                </div>
            </div>

            <!-- Timeline / Activity Log -->
            <div class="clean-card p-8 animate-slide-up" style="animation-delay: 0.1s;">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-800 tracking-tight">Activity Timeline</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Chronological record of events</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 relative before:absolute before:left-[19px] before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    <!-- Session Started -->
                    <div class="flex space-x-4 relative">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-emerald-50 border-2 border-emerald-200 flex items-center justify-center text-emerald-600 z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="flex-1 pb-4">
                            <div class="flex items-baseline justify-between mb-1">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest">Session Started</h4>
                                <span class="text-[10px] font-bold text-slate-400">{{ $interaction->created_at->format('H:i • M d, Y') }}</span>
                            </div>
                            <p class="text-xs font-bold text-slate-600">
                                {{ $interaction->agent->name ?? 'System' }} started a {{ $interaction->channel }} session with {{ $interaction->user->name }}
                            </p>
                        </div>
                    </div>

                    <!-- Notes -->
                    @forelse($interaction->notes as $note)
                    <div class="flex space-x-4 relative">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-indigo-50 border-2 border-indigo-200 flex items-center justify-center text-indigo-600 z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </div>
                        <div class="flex-1 pb-4">
                            <div class="flex items-baseline justify-between mb-1">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest">{{ $note->creator->name ?? 'System' }} added a note</h4>
                                <span class="text-[10px] font-bold text-slate-400">{{ $note->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="p-4 rounded-2xl {{ $note->is_internal ? 'bg-amber-50/50 border border-amber-200' : 'bg-slate-50 border border-slate-200' }}">
                                <p class="text-sm font-bold text-slate-700 leading-relaxed">{{ $note->note }}</p>
                                @if($note->is_internal)
                                <div class="mt-2 text-[8px] font-black text-amber-600 uppercase tracking-widest flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z" /></svg>
                                    Internal Note (Not visible to customer)
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="flex space-x-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-300 z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </div>
                        <div class="flex-1 pb-4">
                            <p class="text-xs font-bold text-slate-400 italic">No notes added yet</p>
                        </div>
                    </div>
                    @endforelse

                    <!-- Session Ended (if resolved) -->
                    @if($interaction->status === 'resolved' && $interaction->ended_at)
                    <div class="flex space-x-4 relative">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-emerald-50 border-2 border-emerald-200 flex items-center justify-center text-emerald-600 z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-baseline justify-between mb-1">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest">Session Completed</h4>
                                <span class="text-[10px] font-bold text-slate-400">{{ $interaction->ended_at->format('H:i • M d, Y') }}</span>
                            </div>
                            <p class="text-xs font-bold text-slate-600">
                                Session resolved after {{ round($interaction->duration_seconds / 60) }} minutes
                            </p>
                        </div>
                    </div>
                    @endif
                </div>

                @if($interaction->status === 'active')
                <div class="mt-8 pt-8 border-t border-slate-100">
                    <h4 class="text-sm font-black text-slate-800 mb-4">Add Note to Timeline</h4>
                    <form method="POST" action="{{ route('customer-care.interactions.add-note', $interaction) }}">
                        @csrf
                        <div class="mb-4">
                            <textarea name="note" rows="3" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 text-sm font-bold focus:ring-4 focus:ring-purple-50 transition-all outline-none" placeholder="Document what happened, customer's concern, or resolution steps..."></textarea>
                        </div>
                        <div class="flex items-center justify-between">
                            <label class="flex items-center space-x-3 cursor-pointer group">
                                <div class="relative">
                                    <input type="checkbox" name="is_internal" value="1" checked class="sr-only peer">
                                    <div class="w-10 h-5 bg-slate-200 rounded-full peer peer-checked:bg-purple-600 transition-colors"></div>
                                    <div class="absolute left-1 top-1 w-3 h-3 bg-white rounded-full peer-checked:translate-x-5 transition-transform"></div>
                                </div>
                                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest group-hover:text-purple-600 transition-colors">Internal Note</span>
                            </label>
                            <button type="submit" class="bg-purple-600 text-white px-8 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-purple-700 transition-all shadow-lg shadow-purple-200">
                                Add Note
                            </button>
                        </div>
                    </form>
                </div>
                @endif
            </div>

            <!-- Related Items -->
            @if($relatedInteractions->count() > 0 || $relatedTickets->count() > 0 || $relatedConsultations->count() > 0)
            <div class="clean-card p-8 animate-slide-up" style="animation-delay: 0.2s;">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-800 tracking-tight">Related Activity</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Other interactions, tickets, and consultations</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @if($relatedInteractions->count() > 0)
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Previous Sessions</p>
                        <div class="space-y-2">
                            @foreach($relatedInteractions->take(3) as $related)
                            <a href="{{ route('customer-care.interactions.show', $related) }}" class="block p-3 bg-slate-50 rounded-xl border border-slate-100 hover:bg-purple-50 hover:border-purple-200 transition-all">
                                <p class="text-xs font-bold text-slate-800 truncate">{{ Str::limit($related->summary, 40) }}</p>
                                <p class="text-[9px] font-bold text-slate-400 mt-1">{{ $related->created_at->diffForHumans() }}</p>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($relatedTickets->count() > 0)
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Support Tickets</p>
                        <div class="space-y-2">
                            @foreach($relatedTickets->take(3) as $ticket)
                            <a href="{{ route('customer-care.tickets.show', $ticket) }}" class="block p-3 bg-slate-50 rounded-xl border border-slate-100 hover:bg-orange-50 hover:border-orange-200 transition-all">
                                <p class="text-xs font-bold text-slate-800 truncate">#{{ $ticket->ticket_number }}</p>
                                <p class="text-[9px] font-bold text-slate-400 mt-1">{{ $ticket->created_at->diffForHumans() }}</p>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($relatedConsultations->count() > 0)
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Consultations</p>
                        <div class="space-y-2">
                            @foreach($relatedConsultations->take(3) as $consultation)
                            <a href="{{ route('customer-care.consultations.show', $consultation->id) }}" class="block p-3 bg-slate-50 rounded-xl border border-slate-100 hover:bg-indigo-50 hover:border-indigo-200 transition-all">
                                <p class="text-xs font-bold text-slate-800 truncate">#{{ $consultation->reference }}</p>
                                <p class="text-[9px] font-bold text-slate-400 mt-1">{{ $consultation->created_at->diffForHumans() }}</p>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar Info Area -->
        <div class="space-y-8">
            <!-- Customer Info -->
            <div class="clean-card p-6 animate-slide-up">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Customer Information</p>
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-50 text-purple-600 flex items-center justify-center font-black text-lg shadow-inner">
                        {{ substr($interaction->user->name ?? 'P', 0, 1) }}
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-800 leading-tight">{{ $interaction->user->name }}</h4>
                        <p class="text-[10px] font-bold text-slate-400 tracking-tighter">{{ \App\Helpers\PrivacyHelper::maskEmail($interaction->user->email) }}</p>
                    </div>
                </div>
                <div class="space-y-2 mb-6">
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-[9px] font-black text-slate-400 uppercase">Phone</span>
                        <span class="text-[11px] font-bold text-slate-800">{{ \App\Helpers\PrivacyHelper::maskPhone($interaction->user->phone) }}</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <a href="{{ route('customer-care.customers.show', $interaction->user) }}" class="block w-full text-center py-3 bg-slate-100 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-purple-600 hover:text-white transition-all mb-2">
                        View Full Profile
                    </a>
                    @include('components.customer-care.communication-modal', [
                        'userName' => $interaction->user->name,
                        'userId' => $interaction->user->id,
                        'userType' => 'patient'
                    ])
                </div>
            </div>

            <!-- Session Details -->
            <div class="clean-card p-6 animate-slide-up" style="animation-delay: 0.1s;">
                 <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Session Details</p>
                 <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Channel</span>
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black uppercase tracking-widest capitalize">{{ $interaction->channel }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Handled By</span>
                        <span class="text-[10px] font-black text-slate-800">{{ $interaction->agent->name ?? 'SYSTEM' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Started</span>
                        <span class="text-[10px] font-black text-slate-800">{{ $interaction->created_at->format('H:i • d M Y') }}</span>
                    </div>
                    @if($interaction->ended_at)
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Ended</span>
                        <span class="text-[10px] font-black text-slate-800">{{ $interaction->ended_at->format('H:i • d M Y') }}</span>
                    </div>
                    @endif
                    @if($interaction->duration_seconds)
                    <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Total Duration</span>
                        <span class="text-xs font-black text-slate-800">{{ round($interaction->duration_seconds / 60) }} minutes</span>
                    </div>
                    @endif
                 </div>
            </div>

            <!-- Quick Actions -->
            <div class="clean-card p-6 animate-slide-up border-l-4 border-l-purple-600" style="animation-delay: 0.2s;">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Quick Actions</p>
                <div class="space-y-3">
                    @if($interaction->status === 'active')
                    <form method="POST" action="{{ route('customer-care.interactions.end', $interaction) }}" id="endInteractionForm">
                        @csrf
                        <button type="button" onclick="handleEndInteraction()" class="w-full py-3.5 bg-emerald-500 text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-100 flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <span>Mark as Resolved</span>
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('customer-care.escalations.create-from-interaction', $interaction) }}" class="block w-full text-center py-3.5 bg-slate-800 text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-slate-900 transition-all flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" /></svg>
                        <span>Escalate to Admin</span>
                    </a>
                    <a href="{{ route('customer-care.interactions.index', ['search' => $interaction->user->name]) }}" class="block w-full text-center py-3.5 bg-indigo-50 text-indigo-600 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-indigo-100 transition-all border border-indigo-200">
                        View All Sessions
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function handleEndInteraction() {
        if (confirm('Are you sure you want to mark this session as resolved? This will end the interaction and calculate the total duration.')) {
            document.getElementById('endInteractionForm').submit();
        }
    }

    // Live duration timer for active interactions
    @if($interaction->status === 'active')
    (function() {
        const startTime = new Date('{{ $interaction->created_at->toIso8601String() }}').getTime();
        const durationDisplay = document.getElementById('durationDisplay');
        
        function updateDuration() {
            const now = new Date().getTime();
            const diff = Math.floor((now - startTime) / 1000); // seconds
            const minutes = Math.floor(diff / 60);
            const seconds = diff % 60;
            durationDisplay.innerHTML = `<span class="text-amber-600 font-bold">${minutes}m ${seconds}s</span>`;
        }
        
        updateDuration();
        setInterval(updateDuration, 1000);
    })();
    @endif
</script>
@endsection

