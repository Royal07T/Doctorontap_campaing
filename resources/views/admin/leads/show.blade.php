<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lead: {{ $lead->name }} - DoctorOnTap Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>.purple-gradient { background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%); }</style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false, editing: false }">
<div class="flex h-screen overflow-hidden">
    @include('admin.shared.sidebar', ['active' => 'leads'])
    <div class="flex-1 flex flex-col overflow-hidden">
        @include('admin.shared.header', ['title' => 'Lead Details'])
        <main class="flex-1 overflow-y-auto bg-gray-100 p-6">

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">{{ session('success') }}</div>
        @endif

        {{-- Back link --}}
        <a href="{{ route('admin.leads.index') }}" class="inline-flex items-center text-sm text-purple-600 hover:text-purple-800 mb-4">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Leads
        </a>

        <div class="grid lg:grid-cols-3 gap-6">
            {{-- Left: Lead info card --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center space-x-4">
                            <div class="w-14 h-14 rounded-full purple-gradient flex items-center justify-center text-white text-xl font-bold">
                                {{ strtoupper(substr($lead->name, 0, 1)) }}
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">{{ $lead->name }}</h2>
                                <p class="text-sm text-gray-500">Created {{ $lead->created_at->format('M j, Y') }}</p>
                            </div>
                        </div>
                        <button @click="editing = !editing" class="px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700 font-medium">
                            <span x-text="editing ? 'Cancel' : 'Edit'"></span>
                        </button>
                    </div>

                    {{-- View mode --}}
                    <div x-show="!editing" class="grid sm:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-500 font-medium uppercase mb-1">Email</p>
                            <p class="text-sm font-medium text-gray-800">{{ $lead->email ?? '—' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-500 font-medium uppercase mb-1">Phone</p>
                            <p class="text-sm font-medium text-gray-800">{{ $lead->phone ?? '—' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-500 font-medium uppercase mb-1">Source</p>
                            <p class="text-sm font-medium text-gray-800 capitalize">{{ $lead->source ?? '—' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-500 font-medium uppercase mb-1">Interest</p>
                            <p class="text-sm font-medium text-gray-800 capitalize">{{ str_replace('_', ' ', $lead->interest_type ?? '—') }}</p>
                        </div>
                        @if($lead->notes)
                        <div class="sm:col-span-2 bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-500 font-medium uppercase mb-1">Notes</p>
                            <p class="text-sm text-gray-700">{{ $lead->notes }}</p>
                        </div>
                        @endif
                    </div>

                    {{-- Edit form --}}
                    <form x-show="editing" method="POST" action="{{ route('admin.leads.update', $lead) }}" class="space-y-4">
                        @csrf @method('PUT')
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Name</label>
                                <input type="text" name="name" value="{{ $lead->name }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                                <input type="email" name="email" value="{{ $lead->email }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                                <input type="text" name="phone" value="{{ $lead->phone }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Source</label>
                                <input type="text" name="source" value="{{ $lead->source }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Interest</label>
                                <input type="text" name="interest_type" value="{{ $lead->interest_type }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                    <option value="active"       {{ $lead->status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="converted"    {{ $lead->status === 'converted' ? 'selected' : '' }}>Converted</option>
                                    <option value="lost"         {{ $lead->status === 'lost' ? 'selected' : '' }}>Lost</option>
                                    <option value="unresponsive" {{ $lead->status === 'unresponsive' ? 'selected' : '' }}>Unresponsive</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                            <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">{{ $lead->notes }}</textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-5 py-2 text-sm text-white purple-gradient rounded-lg hover:opacity-90 font-medium">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Right: Status / Timeline --}}
            <div class="space-y-6">
                {{-- Status card --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-800 mb-4">Status</h3>
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
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Stage</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full {{ $stageColors[$lead->followup_stage] ?? 'bg-gray-100' }}">
                                {{ ucfirst(str_replace('_', ' ', $lead->followup_stage)) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Status</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full
                                {{ $lead->status === 'active' ? 'bg-green-100 text-green-700' : ($lead->status === 'converted' ? 'bg-purple-100 text-purple-700' : 'bg-red-100 text-red-700') }}">
                                {{ ucfirst($lead->status) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Last Contacted</span>
                            <span class="text-sm text-gray-800">{{ $lead->last_contacted_at?->diffForHumans() ?? 'Never' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Next Channel</span>
                            <span class="text-sm text-gray-800 capitalize">{{ $lead->getFollowUpChannel() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Due for Follow-up?</span>
                            <span class="text-sm {{ $lead->isDueForFollowUp() ? 'text-amber-600 font-bold' : 'text-gray-500' }}">
                                {{ $lead->isDueForFollowUp() ? 'Yes' : 'No' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                @if($lead->status === 'active')
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-800 mb-4">Quick Actions</h3>
                    <div class="space-y-2">
                        <form method="POST" action="{{ route('admin.leads.follow-up', $lead) }}">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                                Send Follow-up Now
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.leads.convert', $lead) }}">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2.5 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                                Mark as Converted
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.leads.mark-lost', $lead) }}">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2.5 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                                Mark as Lost
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Follow-up Timeline --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-800 mb-4">Follow-up Pipeline</h3>
                    @php
                        $stages = [
                            ['key' => 'new',       'label' => 'New Lead',       'channel' => 'WhatsApp (Day 1)'],
                            ['key' => 'day1',      'label' => 'Day 1 Sent',     'channel' => 'Email (Day 3)'],
                            ['key' => 'day3',      'label' => 'Day 3 Sent',     'channel' => 'SMS (Day 7)'],
                            ['key' => 'day7',      'label' => 'Day 7 Sent',     'channel' => 'Final attempt'],
                            ['key' => 'converted', 'label' => 'Converted',      'channel' => 'Success! 🎉'],
                        ];
                        $currentIdx = array_search($lead->followup_stage, array_column($stages, 'key'));
                    @endphp
                    <div class="space-y-3">
                        @foreach($stages as $idx => $stage)
                            @php
                                $done = $currentIdx !== false && $idx < $currentIdx;
                                $active = $idx === $currentIdx;
                            @endphp
                            <div class="flex items-start space-x-3">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5
                                    {{ $done ? 'bg-green-500' : ($active ? 'bg-purple-500 ring-4 ring-purple-100' : 'bg-gray-200') }}">
                                    @if($done)
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    @elseif($active)
                                        <div class="w-2 h-2 bg-white rounded-full"></div>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-medium {{ $done ? 'text-green-700' : ($active ? 'text-purple-700' : 'text-gray-400') }}">{{ $stage['label'] }}</p>
                                    <p class="text-xs {{ $active ? 'text-purple-500' : 'text-gray-400' }}">{{ $stage['channel'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        </main>
    </div>
</div>
</body>
</html>
