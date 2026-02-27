<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lead Management - DoctorOnTap Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>.purple-gradient { background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%); }</style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false, showCreate: false, editLead: null }">
<div class="flex h-screen overflow-hidden">
    @include('admin.shared.sidebar', ['active' => 'leads'])
    <div class="flex-1 flex flex-col overflow-hidden">
        @include('admin.shared.header', ['title' => 'Lead Management'])
        <main class="flex-1 overflow-y-auto bg-gray-100 p-6">

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">{{ session('error') }}</div>
        @endif

        {{-- Stats cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
            @foreach([
                ['label' => 'Total Leads',    'value' => $stats['total'],        'color' => 'blue',   'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                ['label' => 'Active',         'value' => $stats['active'],       'color' => 'green',  'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label' => 'Converted',      'value' => $stats['converted'],    'color' => 'purple', 'icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'],
                ['label' => 'Lost',           'value' => $stats['lost'],         'color' => 'red',    'icon' => 'M6 18L18 6M6 6l12 12'],
                ['label' => 'Due Follow-Up',  'value' => $stats['due_followup'], 'color' => 'amber',  'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ] as $stat)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 border-l-4 border-{{ $stat['color'] }}-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ $stat['label'] }}</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stat['value'] }}</p>
                    </div>
                    <div class="bg-{{ $stat['color'] }}-50 p-2.5 rounded-xl">
                        <svg class="w-5 h-5 text-{{ $stat['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/></svg>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Funnel --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <h3 class="font-semibold text-gray-800 mb-4">Lead Funnel</h3>
            <div class="flex items-end space-x-4 h-32">
                @foreach([
                    ['label' => 'New',       'count' => $funnel['new'],       'color' => 'bg-blue-500'],
                    ['label' => 'Day 1',     'count' => $funnel['day1'],      'color' => 'bg-indigo-500'],
                    ['label' => 'Day 3',     'count' => $funnel['day3'],      'color' => 'bg-purple-500'],
                    ['label' => 'Day 7',     'count' => $funnel['day7'],      'color' => 'bg-amber-500'],
                    ['label' => 'Converted', 'count' => $funnel['converted'], 'color' => 'bg-green-500'],
                    ['label' => 'Lost',      'count' => $funnel['lost'],      'color' => 'bg-red-500'],
                ] as $stage)
                    @php
                        $max = max(array_values($funnel)) ?: 1;
                        $height = max(10, ($stage['count'] / $max) * 100);
                    @endphp
                    <div class="flex-1 flex flex-col items-center">
                        <span class="text-xs font-bold text-gray-700 mb-1">{{ $stage['count'] }}</span>
                        <div class="{{ $stage['color'] }} rounded-t-lg w-full transition-all" style="height: {{ $height }}%"></div>
                        <span class="text-[10px] text-gray-500 mt-1 font-medium">{{ $stage['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Toolbar --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search leads..."
                       class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 w-48">
                <select name="status" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">All Status</option>
                    <option value="active"       {{ request('status') === 'active'       ? 'selected' : '' }}>Active</option>
                    <option value="converted"    {{ request('status') === 'converted'    ? 'selected' : '' }}>Converted</option>
                    <option value="lost"         {{ request('status') === 'lost'         ? 'selected' : '' }}>Lost</option>
                    <option value="unresponsive" {{ request('status') === 'unresponsive' ? 'selected' : '' }}>Unresponsive</option>
                </select>
                <select name="stage" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">All Stages</option>
                    <option value="new"       {{ request('stage') === 'new'       ? 'selected' : '' }}>New</option>
                    <option value="day1"      {{ request('stage') === 'day1'      ? 'selected' : '' }}>Day 1</option>
                    <option value="day3"      {{ request('stage') === 'day3'      ? 'selected' : '' }}>Day 3</option>
                    <option value="day7"      {{ request('stage') === 'day7'      ? 'selected' : '' }}>Day 7</option>
                    <option value="converted" {{ request('stage') === 'converted' ? 'selected' : '' }}>Converted</option>
                    <option value="lost"      {{ request('stage') === 'lost'      ? 'selected' : '' }}>Lost</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">Filter</button>
            </form>
            <button @click="showCreate = true" class="px-4 py-2 purple-gradient text-white text-sm font-medium rounded-lg hover:opacity-90 flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>New Lead</span>
            </button>
        </div>

        {{-- Leads Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Name</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Contact</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Source</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Stage</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Last Contact</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($leads as $lead)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3">
                                <p class="font-medium text-sm text-gray-800">{{ $lead->name }}</p>
                                @if($lead->interest_type)
                                    <p class="text-xs text-gray-500">{{ $lead->interest_type }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <p class="text-sm text-gray-700">{{ $lead->email ?? '—' }}</p>
                                <p class="text-xs text-gray-500">{{ $lead->phone ?? '—' }}</p>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-blue-50 text-blue-700 rounded-full">{{ $lead->source ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3">
                                @php
                                    $stageColors = [
                                        'new' => 'bg-blue-100 text-blue-700',
                                        'day1' => 'bg-indigo-100 text-indigo-700',
                                        'day3' => 'bg-purple-100 text-purple-700',
                                        'day7' => 'bg-amber-100 text-amber-700',
                                        'converted' => 'bg-green-100 text-green-700',
                                        'lost' => 'bg-red-100 text-red-700',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full {{ $stageColors[$lead->followup_stage] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst(str_replace('_', ' ', $lead->followup_stage)) }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                @php
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-700',
                                        'converted' => 'bg-purple-100 text-purple-700',
                                        'lost' => 'bg-red-100 text-red-700',
                                        'unresponsive' => 'bg-gray-100 text-gray-700',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full {{ $statusColors[$lead->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($lead->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-600">
                                {{ $lead->last_contacted_at?->diffForHumans() ?? 'Never' }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end space-x-1">
                                    @if($lead->status === 'active')
                                        <form method="POST" action="{{ route('admin.leads.follow-up', $lead) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Send Follow-up">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.leads.convert', $lead) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg" title="Mark Converted">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.leads.mark-lost', $lead) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg" title="Mark Lost">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.leads.destroy', $lead) }}" class="inline"
                                          onsubmit="return confirm('Delete this lead?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:bg-gray-50 hover:text-red-600 rounded-lg" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <p class="font-medium">No leads found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($leads->hasPages())
                <div class="px-5 py-3 border-t border-gray-200">{{ $leads->withQueryString()->links() }}</div>
            @endif
        </div>

        {{-- Create Lead Modal --}}
        <div x-show="showCreate" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showCreate = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Create New Lead</h3>
                <form method="POST" action="{{ route('admin.leads.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Name *</label>
                            <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                            <input type="text" name="phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                        <input type="email" name="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Source *</label>
                            <select name="source" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                <option value="website">Website</option>
                                <option value="referral">Referral</option>
                                <option value="social_media">Social Media</option>
                                <option value="canvasser">Canvasser</option>
                                <option value="phone_inquiry">Phone Inquiry</option>
                                <option value="walk_in">Walk-in</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Interest</label>
                            <select name="interest_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                <option value="">Select...</option>
                                <option value="standard_plan">Standard Plan</option>
                                <option value="executive_plan">Executive Plan</option>
                                <option value="sovereign_plan">Sovereign Plan</option>
                                <option value="general_inquiry">General Inquiry</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                        <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" @click="showCreate = false" class="px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-sm text-white purple-gradient rounded-lg hover:opacity-90 font-medium">Create Lead</button>
                    </div>
                </form>
            </div>
        </div>

        </main>
    </div>
</div>
</body>
</html>
