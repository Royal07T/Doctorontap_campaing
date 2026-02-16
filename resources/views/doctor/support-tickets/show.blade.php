@extends('layouts.doctor')

@section('title', 'Ticket Details')
@section('header-title', 'Ticket Details')

@section('content')
<div class="max-w-5xl mx-auto p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('doctor.support-tickets.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800 mb-4 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Tickets
        </a>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $supportTicket->subject }}</h1>
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="text-sm font-mono font-semibold text-indigo-600">{{ $supportTicket->ticket_number }}</span>
                    @if($supportTicket->status === 'open')
                        <span class="px-3 py-1 text-xs font-bold bg-blue-100 text-blue-700 rounded-full uppercase tracking-wider border border-blue-200">Open</span>
                    @elseif($supportTicket->status === 'pending')
                        <span class="px-3 py-1 text-xs font-bold bg-amber-100 text-amber-700 rounded-full uppercase tracking-wider border border-amber-200">Pending</span>
                    @elseif($supportTicket->status === 'resolved')
                        <span class="px-3 py-1 text-xs font-bold bg-emerald-100 text-emerald-700 rounded-full uppercase tracking-wider border border-emerald-200">Resolved</span>
                    @elseif($supportTicket->status === 'escalated')
                        <span class="px-3 py-1 text-xs font-bold bg-red-100 text-red-700 rounded-full uppercase tracking-wider border border-red-200">Escalated</span>
                    @endif
                    <span class="px-3 py-1 text-xs font-bold bg-gray-100 text-gray-700 rounded-full uppercase tracking-wider border border-gray-200">{{ $supportTicket->category }}</span>
                    <span class="px-3 py-1 text-xs font-bold rounded-full uppercase tracking-wider
                        @if($supportTicket->priority === 'urgent') bg-red-100 text-red-700 border border-red-200
                        @elseif($supportTicket->priority === 'high') bg-orange-100 text-orange-700 border border-orange-200
                        @elseif($supportTicket->priority === 'medium') bg-yellow-100 text-yellow-700 border border-yellow-200
                        @else bg-gray-100 text-gray-700 border border-gray-200 @endif">
                        {{ ucfirst($supportTicket->priority) }} Priority
                    </span>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-lg">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            {{ session('success') }}
        </div>
    </div>
    @endif

    <!-- Chat-Style Thread -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <!-- Ticket Info Header -->
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Created</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $supportTicket->created_at->format('M d, Y h:i A') }}</p>
                </div>
                @if($supportTicket->agent)
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Assigned Agent</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $supportTicket->agent->name }}</p>
                </div>
                @endif
                @if($supportTicket->resolved_at)
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Resolved</p>
                    <p class="text-sm font-semibold text-emerald-600">{{ $supportTicket->resolved_at->format('M d, Y h:i A') }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Conversation Thread -->
        <div class="p-6 space-y-4 max-h-[600px] overflow-y-auto">
            <!-- Initial Message (Doctor) -->
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <span class="text-indigo-600 font-bold text-sm">{{ substr($supportTicket->doctor->name ?? 'D', 0, 1) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-sm font-bold text-gray-900">You</span>
                        <span class="text-xs text-gray-500">{{ $supportTicket->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                    <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-100">
                        <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $supportTicket->description }}</p>
                    </div>
                </div>
            </div>

            <!-- Timeline Marker -->
            <div class="flex items-center gap-4 my-6">
                <div class="flex-1 border-t border-gray-200"></div>
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Ticket Created</span>
                <div class="flex-1 border-t border-gray-200"></div>
            </div>

            <!-- Escalations (if any) -->
            @if($supportTicket->escalations->count() > 0)
                @foreach($supportTicket->escalations as $escalation)
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0 border-2 border-red-200">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-bold text-red-700">Escalation</span>
                            <span class="text-xs text-gray-500">{{ $escalation->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                        <div class="bg-red-50 rounded-lg p-4 border-2 border-red-200">
                            <p class="text-sm font-semibold text-red-900 mb-1">Reason:</p>
                            <p class="text-sm text-red-800 whitespace-pre-wrap leading-relaxed">{{ $escalation->reason }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            @endif

            <!-- Empty State for Future Replies -->
            <div class="text-center py-8 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <p class="text-sm">Waiting for agent response...</p>
            </div>
        </div>

        <!-- Sticky Reply Box (Placeholder for future implementation) -->
        @if($supportTicket->status !== 'resolved')
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-200">
                <div class="flex-1">
                    <p class="text-xs text-gray-500 mb-1">Reply functionality coming soon</p>
                    <p class="text-xs text-gray-400">For now, please contact support directly if you need to add information.</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

