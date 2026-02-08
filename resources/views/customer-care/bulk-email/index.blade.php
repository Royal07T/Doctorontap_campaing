@extends('layouts.customer-care')

@section('title', 'Bulk Email Campaigns')

@section('content')
<div class="px-6 py-8">
    <div class="flex items-center justify-between mb-10">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-600 to-indigo-600 flex items-center justify-center text-white shadow-lg">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tight">Bulk Email</h1>
                <p class="text-xs font-bold text-purple-600 uppercase tracking-[0.2em] mt-1">Send Email Campaigns to Patients</p>
            </div>
        </div>
        <a href="{{ route('customer-care.bulk-email.create') }}" class="purple-gradient text-white px-6 py-3 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center space-x-2 font-bold">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span>Send New Email Campaign</span>
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-100 border-l-4 border-l-green-600 text-green-800 px-6 py-4 rounded-xl shadow-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="clean-card p-6 border-l-4 border-l-purple-600 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500 font-bold uppercase tracking-wide">Total Campaigns</p>
                    <p class="text-3xl font-black text-slate-900 mt-2">{{ $stats['total_campaigns'] ?? 0 }}</p>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="clean-card p-6 border-l-4 border-l-green-600 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500 font-bold uppercase tracking-wide">Emails Sent</p>
                    <p class="text-3xl font-black text-slate-900 mt-2">{{ $stats['total_sent'] ?? 0 }}</p>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-green-600 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="clean-card p-6 border-l-4 border-l-blue-600 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500 font-bold uppercase tracking-wide">Success Rate</p>
                    <p class="text-3xl font-black text-slate-900 mt-2">{{ $stats['success_rate'] ?? 0 }}%</p>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="clean-card p-6 border-l-4 border-l-orange-600 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500 font-bold uppercase tracking-wide">Open Rate</p>
                    <p class="text-3xl font-black text-slate-900 mt-2">{{ $stats['open_rate'] ?? 0 }}%</p>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-orange-600 to-amber-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="clean-card p-6 mb-8">
        <form method="GET" class="flex items-center space-x-4">
            <input type="text" name="search" placeholder="Search campaigns..." value="{{ request('search') }}"
                class="flex-1 px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all font-medium">
            
            <select name="status" class="px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 font-medium">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
            </select>

            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl hover:from-purple-700 hover:to-indigo-700 shadow-lg hover:shadow-xl transition-all font-bold">
                Filter
            </button>
            <a href="{{ route('customer-care.bulk-email.index') }}" class="px-6 py-3 border-2 border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-all font-bold">
                Reset
            </a>
        </form>
    </div>

    <!-- Campaigns List -->
    <div class="clean-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-gradient-to-r from-purple-50 to-indigo-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-black text-slate-700 uppercase tracking-wider">Campaign</th>
                        <th class="px-6 py-4 text-left text-xs font-black text-slate-700 uppercase tracking-wider">Template</th>
                        <th class="px-6 py-4 text-left text-xs font-black text-slate-700 uppercase tracking-wider">Recipients</th>
                        <th class="px-6 py-4 text-left text-xs font-black text-slate-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-black text-slate-700 uppercase tracking-wider">Success/Failed</th>
                        <th class="px-6 py-4 text-left text-xs font-black text-slate-700 uppercase tracking-wider">Sent At</th>
                        <th class="px-6 py-4 text-right text-xs font-black text-slate-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @forelse($campaigns ?? [] as $campaign)
                        <tr class="hover:bg-purple-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-lg flex items-center justify-center text-white font-black shadow-md">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900">{{ $campaign->subject }}</div>
                                        <div class="text-xs text-slate-500 font-medium">ID: #{{ $campaign->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-slate-700">{{ $campaign->template->name ?? 'Custom' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <span class="px-3 py-1 bg-purple-100 text-purple-800 text-sm font-bold rounded-lg">
                                        {{ $campaign->total_recipients ?? 0 }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1.5 text-xs font-bold rounded-lg
                                    @if($campaign->status == 'completed') bg-green-100 text-green-800 border border-green-200
                                    @elseif($campaign->status == 'processing') bg-blue-100 text-blue-800 border border-blue-200
                                    @elseif($campaign->status == 'failed') bg-red-100 text-red-800 border border-red-200
                                    @else bg-yellow-100 text-yellow-800 border border-yellow-200
                                    @endif">
                                    {{ ucfirst($campaign->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-sm font-bold rounded">{{ $campaign->successful_sends ?? 0 }}</span>
                                    <span class="text-slate-400 font-bold">/</span>
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-sm font-bold rounded">{{ $campaign->failed_sends ?? 0 }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-slate-900">{{ $campaign->completed_at ? $campaign->completed_at->format('M d, Y') : 'Not sent' }}</div>
                                <div class="text-xs text-slate-500 font-medium">{{ $campaign->completed_at ? $campaign->completed_at->format('H:i A') : '' }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('customer-care.bulk-email.show', $campaign) }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-bold rounded-lg hover:bg-purple-700 transition-all shadow-md hover:shadow-lg">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-20 h-20 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-2xl flex items-center justify-center mb-4">
                                        <svg class="w-10 h-10 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <p class="text-slate-500 font-bold text-lg mb-2">No campaigns yet</p>
                                    <p class="text-slate-400 text-sm mb-6">Start by sending your first email campaign!</p>
                                    <a href="{{ route('customer-care.bulk-email.create') }}" class="purple-gradient text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all">
                                        Create Your First Campaign
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(isset($campaigns) && $campaigns->hasPages())
        <div class="mt-6">
            {{ $campaigns->links() }}
        </div>
    @endif
</div>
@endsection
