@extends('layouts.patient')

@section('title', 'Ticket Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('patient.support-tickets.index') }}" class="text-purple-600 hover:text-purple-700 font-semibold flex items-center gap-2 mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Tickets
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Ticket Details</h1>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-lg">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">{{ $supportTicket->subject }}</h2>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-purple-600">{{ $supportTicket->ticket_number }}</span>
                    @if($supportTicket->status === 'open')
                        <span class="px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">Open</span>
                    @elseif($supportTicket->status === 'pending')
                        <span class="px-3 py-1 text-xs font-semibold bg-amber-100 text-amber-800 rounded-full">Pending</span>
                    @elseif($supportTicket->status === 'resolved')
                        <span class="px-3 py-1 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full">Resolved</span>
                    @elseif($supportTicket->status === 'escalated')
                        <span class="px-3 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Escalated</span>
                    @endif
                    <span class="px-3 py-1 text-xs font-semibold bg-gray-100 text-gray-800 rounded-full capitalize">{{ $supportTicket->category }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 pb-6 border-b border-gray-200">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Priority</p>
                <span class="px-3 py-1 text-xs font-semibold rounded-full
                    @if($supportTicket->priority === 'urgent') bg-red-100 text-red-800
                    @elseif($supportTicket->priority === 'high') bg-orange-100 text-orange-800
                    @elseif($supportTicket->priority === 'medium') bg-yellow-100 text-yellow-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    {{ ucfirst($supportTicket->priority) }}
                </span>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Created</p>
                <p class="text-sm text-gray-900">{{ $supportTicket->created_at->format('M d, Y h:i A') }}</p>
            </div>
            @if($supportTicket->agent)
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Assigned Agent</p>
                <p class="text-sm text-gray-900">{{ $supportTicket->agent->name }}</p>
            </div>
            @endif
            @if($supportTicket->resolved_at)
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Resolved</p>
                <p class="text-sm text-gray-900">{{ $supportTicket->resolved_at->format('M d, Y h:i A') }}</p>
            </div>
            @endif
        </div>

        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Description</p>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $supportTicket->description }}</p>
            </div>
        </div>
    </div>

    @if($supportTicket->escalations->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Escalations</h3>
        <div class="space-y-4">
            @foreach($supportTicket->escalations as $escalation)
            <div class="border-l-4 border-orange-500 pl-4 py-2">
                <p class="text-sm font-semibold text-gray-900">{{ $escalation->reason }}</p>
                <p class="text-xs text-gray-600 mt-1">{{ $escalation->created_at->format('M d, Y h:i A') }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection

