@extends('layouts.customer-care')

@section('title', 'Prospect Details - Customer Care')

@php
    $headerTitle = 'Prospect Details';
@endphp

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('customer-care.prospects.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Prospects
        </a>
        <div class="flex items-center gap-3">
            @if($prospect->status !== 'Converted' && $prospect->status !== 'Closed')
            <form method="POST" action="{{ route('customer-care.prospects.mark-contacted', $prospect) }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-sm font-semibold transition-colors">
                    Mark as Contacted
                </button>
            </form>
            <a href="{{ route('customer-care.prospects.convert', $prospect) }}" 
               class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold transition-colors">
                Convert to Patient
            </a>
            @endif
            @if($prospect->status === 'Converted')
            <a href="{{ route('customer-care.booking.create', ['prospect_id' => $prospect->id]) }}" 
               class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition-colors">
                Book Service
            </a>
            @endif
            <a href="{{ route('customer-care.prospects.edit', $prospect) }}" 
               class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition-colors">
                Edit
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-1">Name</h3>
                <p class="text-lg font-bold text-gray-900">{{ $prospect->full_name }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-1">Status</h3>
                @if($prospect->status === 'New')
                    <span class="px-3 py-1 text-sm font-bold rounded-full bg-blue-100 text-blue-700 border border-blue-200 uppercase">New</span>
                @elseif($prospect->status === 'Contacted')
                    <span class="px-3 py-1 text-sm font-bold rounded-full bg-amber-100 text-amber-700 border border-amber-200 uppercase">Contacted</span>
                @elseif($prospect->status === 'Converted')
                    <span class="px-3 py-1 text-sm font-bold rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200 uppercase">Converted</span>
                @else
                    <span class="px-3 py-1 text-sm font-bold rounded-full bg-gray-100 text-gray-700 border border-gray-200 uppercase">Closed</span>
                @endif
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-1">Mobile Number</h3>
                <p class="text-lg font-mono text-gray-900">{{ $prospect->mobile_number }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-1">Email</h3>
                <p class="text-lg text-gray-900">{{ $prospect->email ?? '—' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-1">Location</h3>
                <p class="text-lg text-gray-900">{{ $prospect->location ?? '—' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-1">Source</h3>
                <p class="text-lg text-gray-900 capitalize">{{ $prospect->source ?? '—' }}</p>
            </div>
            <div class="md:col-span-2">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-1">Notes</h3>
                <p class="text-gray-900 whitespace-pre-wrap">{{ $prospect->notes ?? '—' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-1">Created By</h3>
                <p class="text-lg text-gray-900">{{ $prospect->createdBy->name ?? '—' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-1">Created At</h3>
                <p class="text-lg text-gray-900">{{ $prospect->created_at->format('M d, Y h:i A') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

