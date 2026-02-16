@extends('layouts.customer-care')

@section('title', 'Bulk SMS Marketing')

@php
    $headerTitle = 'SMS Marketing Campaign';
@endphp

@section('content')
    @if(session('success'))
    <div class="mb-8 p-4 bg-emerald-500/10 border-l-4 border-emerald-500 text-emerald-700 rounded-2xl animate-fade-in shadow-lg shadow-emerald-500/5">
        <div class="flex items-center">
            <div class="p-2 bg-emerald-500 rounded-lg text-white mr-3">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <span class="font-semibold">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 animate-slide-up">
        <div class="clean-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Total Campaigns</p>
                    <p class="text-3xl font-black text-slate-800 mt-2">{{ $stats['total_campaigns'] }}</p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-purple-100 flex items-center justify-center">
                    <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="clean-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Total Sent</p>
                    <p class="text-3xl font-black text-emerald-600 mt-2">{{ number_format($stats['total_sent']) }}</p>
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
                    <p class="text-3xl font-black text-red-600 mt-2">{{ number_format($stats['total_failed']) }}</p>
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
                    <p class="text-3xl font-black text-blue-600 mt-2">{{ number_format($stats['success_rate'], 1) }}%</p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Templates -->
    <div class="clean-card mb-8 animate-slide-up" style="animation-delay: 0.1s;">
        <div class="p-6 border-b border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">SMS Templates</h2>
                    <p class="text-sm text-slate-500 mt-1">Select a template to create a new campaign</p>
                </div>
                <a href="{{ route('customer-care.bulk-sms.create') }}" 
                    class="px-6 py-3 bg-purple-600 text-white rounded-2xl hover:bg-purple-700 transition-all font-bold shadow-lg hover:shadow-xl inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Campaign
                </a>
            </div>
        </div>

        <div class="p-6">
            @if($templates->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($templates as $template)
                        <div class="p-5 border-2 border-slate-100 rounded-2xl hover:border-purple-300 hover:shadow-lg transition-all cursor-pointer"
                            onclick="selectTemplate({{ $template->id }}, '{{ addslashes($template->name) }}', '{{ addslashes($template->body) }}')">
                            <div class="flex items-start justify-between mb-3">
                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-purple-100 text-purple-800">
                                    SMS
                                </span>
                            </div>
                            <h3 class="font-bold text-slate-800 mb-2">{{ $template->name }}</h3>
                            <p class="text-sm text-slate-600 line-clamp-3">{{ $template->body }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                    <p class="mt-4 text-slate-500">No templates available. Contact admin to create templates.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Campaigns -->
    <div class="clean-card animate-slide-up" style="animation-delay: 0.2s;">
        <div class="p-6 border-b border-slate-100">
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Recent Campaigns</h2>
        </div>

        <div class="overflow-x-auto">
            @if($campaigns->count() > 0)
                <table class="w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Campaign Name</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Template</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Recipients</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Success Rate</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($campaigns as $campaign)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-sm font-medium text-slate-800">{{ $campaign->campaign_name }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $campaign->template->name ?? 'Custom' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $campaign->total_recipients }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-bold rounded-full 
                                        {{ $campaign->success_rate >= 90 ? 'bg-emerald-100 text-emerald-800' : 
                                           ($campaign->success_rate >= 70 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ number_format($campaign->success_rate, 1) }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-bold rounded-full 
                                        {{ $campaign->status == 'completed' ? 'bg-green-100 text-green-800' : 
                                           ($campaign->status == 'processing' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ ucfirst($campaign->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $campaign->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <a href="{{ route('customer-care.bulk-sms.show', $campaign) }}" 
                                        class="text-purple-600 hover:text-purple-800 font-semibold">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="px-6 py-4 bg-slate-50">
                    {{ $campaigns->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-4 text-slate-500">No campaigns yet. Create your first campaign!</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        function selectTemplate(id, name, content) {
            window.location.href = '{{ route("customer-care.bulk-sms.create") }}?template_id=' + id;
        }
    </script>
@endsection

