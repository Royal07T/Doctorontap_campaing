@extends('layouts.doctor')

@section('title', 'Support Tickets')
@section('header-title', 'Support Tickets')

@section('content')
@if(session('success'))
<div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-lg">
    <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
        {{ session('success') }}
    </div>
</div>
@endif

<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Support Tickets</h1>
        <p class="text-sm text-gray-600 mt-1">Contact customer care for assistance</p>
    </div>
    <a href="{{ route('doctor.support-tickets.create') }}" class="px-4 py-2 purple-gradient text-white rounded-lg hover:opacity-90 font-semibold transition-colors flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        New Ticket
    </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
    <form method="GET" action="{{ route('doctor.support-tickets.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Ticket number or subject..." 
                   class="w-full px-4 py-2 text-sm rounded-lg border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Status</label>
            <select name="status" class="w-full px-4 py-2 text-sm rounded-lg border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200">
                <option value="">All Status</option>
                <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="escalated" {{ request('status') == 'escalated' ? 'selected' : '' }}>Escalated</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Category</label>
            <select name="category" class="w-full px-4 py-2 text-sm rounded-lg border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200">
                <option value="">All Categories</option>
                <option value="billing" {{ request('category') == 'billing' ? 'selected' : '' }}>Billing</option>
                <option value="appointment" {{ request('category') == 'appointment' ? 'selected' : '' }}>Appointment</option>
                <option value="technical" {{ request('category') == 'technical' ? 'selected' : '' }}>Technical</option>
                <option value="medical" {{ request('category') == 'medical' ? 'selected' : '' }}>Medical</option>
            </select>
        </div>
        <div class="md:col-span-3 flex gap-2">
            <button type="submit" class="px-4 py-2 purple-gradient text-white rounded-lg hover:opacity-90 font-semibold">Filter</button>
            <a href="{{ route('doctor.support-tickets.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Clear</a>
        </div>
    </form>
</div>

<!-- Tickets List -->
@if($tickets->count() > 0)
<div class="space-y-4">
    @foreach($tickets as $ticket)
    <a href="{{ route('doctor.support-tickets.show', $ticket) }}" class="block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all p-5">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-sm font-semibold text-purple-600">{{ $ticket->ticket_number }}</span>
                    @if($ticket->status === 'open')
                        <span class="px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">Open</span>
                    @elseif($ticket->status === 'pending')
                        <span class="px-2 py-1 text-xs font-semibold bg-amber-100 text-amber-800 rounded-full">Pending</span>
                    @elseif($ticket->status === 'resolved')
                        <span class="px-2 py-1 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full">Resolved</span>
                    @elseif($ticket->status === 'escalated')
                        <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Escalated</span>
                    @endif
                    <span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-800 rounded-full capitalize">{{ $ticket->category }}</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $ticket->subject }}</h3>
                <p class="text-sm text-gray-600 mb-2 line-clamp-2">{{ $ticket->description }}</p>
                <div class="flex items-center gap-4 text-xs text-gray-500">
                    <span>Created: {{ $ticket->created_at->format('M d, Y') }}</span>
                    @if($ticket->agent)
                        <span>Assigned to: {{ $ticket->agent->name }}</span>
                    @endif
                </div>
            </div>
            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </div>
    </a>
    @endforeach
</div>

<!-- Pagination -->
<div class="mt-6">
    {{ $tickets->links() }}
</div>
@else
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
    </svg>
    <h3 class="text-lg font-semibold text-gray-900 mb-2">No Support Tickets</h3>
    <p class="text-gray-600 mb-4">You haven't created any support tickets yet.</p>
    <a href="{{ route('doctor.support-tickets.create') }}" class="inline-block px-4 py-2 purple-gradient text-white rounded-lg hover:opacity-90 font-semibold">
        Create Your First Ticket
    </a>
</div>
@endif
@endsection

