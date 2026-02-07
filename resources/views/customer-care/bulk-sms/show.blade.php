@extends('layouts.customer-care')

@section('title', 'Campaign Details')

@php
    $headerTitle = 'Campaign Details';
@endphp

@section('content')
    <div class="mb-6">
        <a href="{{ route('customer-care.bulk-sms.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-800 font-semibold">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Campaigns
        </a>
    </div>

    <!-- Campaign Header -->
    <div class="clean-card mb-6">
        <div class="p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-3xl font-black text-slate-800 tracking-tight">{{ $campaign->campaign_name }}</h1>
                    <p class="text-sm text-slate-500 mt-2">
                        Created on {{ $campaign->created_at->format('F d, Y \a\t H:i') }}
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="px-4 py-2 rounded-xl font-bold text-sm
                        {{ $campaign->status == 'completed' ? 'bg-green-100 text-green-800' : 
                           ($campaign->status == 'processing' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ ucfirst($campaign->status) }}
                    </span>
                    @if($campaign->status == 'completed')
                        <a href="{{ route('customer-care.bulk-sms.export', $campaign) }}" 
                            class="px-4 py-2 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-all font-bold inline-flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export CSV
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="clean-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Total Recipients</p>
                    <p class="text-3xl font-black text-slate-800 mt-2">{{ $campaign->total_recipients }}</p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-purple-100 flex items-center justify-center">
                    <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="clean-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Successful</p>
                    <p class="text-3xl font-black text-emerald-600 mt-2">{{ $campaign->successful_sends }}</p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="clean-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Failed</p>
                    <p class="text-3xl font-black text-red-600 mt-2">{{ $campaign->failed_sends }}</p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center">
                    <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="clean-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Success Rate</p>
                    <p class="text-3xl font-black text-blue-600 mt-2">{{ number_format($campaign->success_rate, 1) }}%</p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaign Details -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Message Content -->
        <div class="clean-card">
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-xl font-black text-slate-800">Message Content</h2>
            </div>
            <div class="p-6">
                @if($campaign->template)
                    <div class="mb-4">
                        <p class="text-sm font-semibold text-slate-500 mb-1">Template Used</p>
                        <p class="font-bold text-slate-800">{{ $campaign->template->name }}</p>
                    </div>
                @endif
                <div class="p-4 bg-slate-50 rounded-xl border-2 border-slate-200">
                    <p class="text-sm whitespace-pre-wrap">{{ $campaign->message_content }}</p>
                </div>
                <p class="mt-2 text-xs text-slate-500">
                    {{ strlen($campaign->message_content) }} characters (~{{ ceil(strlen($campaign->message_content) / 160) }} SMS)
                </p>
            </div>
        </div>

        <!-- Campaign Info -->
        <div class="clean-card">
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-xl font-black text-slate-800">Campaign Information</h2>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Sent By</p>
                    <p class="font-bold text-slate-800">{{ $campaign->sender->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-500">Created At</p>
                    <p class="font-bold text-slate-800">{{ $campaign->created_at->format('F d, Y \a\t H:i:s') }}</p>
                </div>
                @if($campaign->completed_at)
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Completed At</p>
                        <p class="font-bold text-slate-800">{{ $campaign->completed_at->format('F d, Y \a\t H:i:s') }}</p>
                    </div>
                @endif
                @if($campaign->cost)
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Estimated Cost</p>
                        <p class="font-bold text-slate-800">₦{{ number_format($campaign->cost, 2) }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Send Results -->
    @if($campaign->send_results && count($campaign->send_results) > 0)
        <div class="clean-card">
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-xl font-black text-slate-800">Detailed Results</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">#</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Phone Number</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Message ID</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Error (if any)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($campaign->send_results as $index => $result)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-800">{{ $result['phone'] ?? 'N/A' }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-bold rounded-full
                                        {{ $result['status'] == 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($result['status'] ?? 'unknown') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 font-mono">{{ $result['message_id'] ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-red-600">{{ $result['error'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection

