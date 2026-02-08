@extends('layouts.customer-care')

@section('title', 'Email Campaign Details')

@section('content')
<div class="px-6 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header Actions -->
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('customer-care.bulk-email.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-900 font-bold transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Campaigns
            </a>
            <a href="{{ route('customer-care.bulk-email.export', $campaign) }}" 
                class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 font-bold shadow-lg hover:shadow-xl transition-all flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Report
            </a>
        </div>

        <!-- Campaign Header -->
        <div class="clean-card p-8 mb-6 border-l-4 border-l-purple-600">
            <div class="flex items-start justify-between mb-6">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-3xl font-black text-slate-800">{{ $campaign->subject }}</h2>
                            <p class="text-sm text-slate-500 font-medium mt-1">Campaign ID: #{{ $campaign->id }} • {{ $campaign->campaign_name }}</p>
                        </div>
                    </div>
                </div>
                <span class="px-5 py-2.5 text-sm font-bold rounded-xl shadow-lg
                    @if($campaign->status == 'completed') bg-green-100 text-green-800 border-2 border-green-200
                    @elseif($campaign->status == 'processing') bg-blue-100 text-blue-800 border-2 border-blue-200
                    @elseif($campaign->status == 'failed') bg-red-100 text-red-800 border-2 border-red-200
                    @else bg-yellow-100 text-yellow-800 border-2 border-yellow-200
                    @endif">
                    {{ ucfirst($campaign->status) }}
                </span>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="p-5 bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl border-2 border-purple-200 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-sm text-purple-700 font-bold uppercase tracking-wide">Total Recipients</div>
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="text-4xl font-black text-purple-900">{{ $campaign->total_recipients }}</div>
                </div>

                <div class="p-5 bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl border-2 border-green-200 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-sm text-green-700 font-bold uppercase tracking-wide">Successful</div>
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="text-4xl font-black text-green-900">{{ $campaign->successful_sends }}</div>
                </div>

                <div class="p-5 bg-gradient-to-br from-red-50 to-rose-50 rounded-xl border-2 border-red-200 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-sm text-red-700 font-bold uppercase tracking-wide">Failed</div>
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="text-4xl font-black text-red-900">{{ $campaign->failed_sends }}</div>
                </div>

                <div class="p-5 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border-2 border-blue-200 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-sm text-blue-700 font-bold uppercase tracking-wide">Success Rate</div>
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div class="text-4xl font-black text-blue-900">
                        {{ $campaign->total_recipients > 0 ? round(($campaign->successful_sends / $campaign->total_recipients) * 100, 1) : 0 }}%
                    </div>
                </div>
            </div>

            <!-- Campaign Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8 p-6 bg-slate-50 rounded-xl border border-slate-200">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500 font-medium">Sent By</div>
                        <div class="font-bold text-slate-900">{{ $campaign->sender->name ?? 'N/A' }}</div>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500 font-medium">Sent At</div>
                        <div class="font-bold text-slate-900">{{ $campaign->completed_at ? $campaign->completed_at->format('M d, Y - g:i A') : 'Not sent yet' }}</div>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500 font-medium">Open Rate</div>
                        <div class="font-bold text-slate-900">{{ $campaign->opened_count }} opens ({{ $campaign->successful_sends > 0 ? round(($campaign->opened_count / $campaign->successful_sends) * 100, 1) : 0 }}%)</div>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500 font-medium">Template Used</div>
                        <div class="font-bold text-slate-900">{{ $campaign->template->name ?? 'Custom' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Content Preview -->
        <div class="clean-card p-8 mb-6">
            <h3 class="text-xl font-black text-slate-800 mb-6 flex items-center">
                <span class="w-10 h-10 bg-purple-600 text-white rounded-xl flex items-center justify-center mr-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </span>
                Email Content Preview
            </h3>
            
            <div class="p-6 bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl border-2 border-slate-200">
                <div class="mb-4 pb-4 border-b-2 border-purple-200">
                    <div class="text-sm text-slate-600 font-medium mb-1">Subject:</div>
                    <div class="text-lg font-bold text-slate-900">{{ $campaign->subject }}</div>
                </div>
                <div class="prose max-w-none">
                    {!! $campaign->message_content !!}
                </div>
            </div>
        </div>

        <!-- Recipients List -->
        <div class="clean-card p-8">
            <h3 class="text-xl font-black text-slate-800 mb-6 flex items-center">
                <span class="w-10 h-10 bg-indigo-600 text-white rounded-xl flex items-center justify-center mr-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </span>
                Recipients ({{ count($campaign->send_results ?? []) }})
            </h3>
            
            <div class="overflow-x-auto rounded-xl border-2 border-slate-200">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-gradient-to-r from-purple-50 to-indigo-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-700 uppercase tracking-wider">Email Address</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-700 uppercase tracking-wider">Delivered At</th>
                            <th class="px-6 py-4 text-center text-xs font-black text-slate-700 uppercase tracking-wider">Opened</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        @forelse($campaign->send_results ?? [] as $result)
                            <tr class="hover:bg-purple-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-lg flex items-center justify-center text-white font-bold text-sm mr-3">
                                            {{ substr($result['email'] ?? '', 0, 1) }}
                                        </div>
                                        <div class="text-sm font-medium text-slate-900">{{ $result['email'] ?? 'N/A' }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1.5 text-xs font-bold rounded-lg
                                        @if(($result['status'] ?? '') == 'success') bg-green-100 text-green-800 border border-green-200
                                        @else bg-red-100 text-red-800 border border-red-200
                                        @endif">
                                        {{ ucfirst($result['status'] ?? 'pending') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 font-medium">
                                    {{ $campaign->completed_at ? $campaign->completed_at->format('M d, H:i') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="w-6 h-6 mx-auto bg-slate-200 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-16 h-16 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                        <p class="text-slate-500 font-medium">No recipients found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
