<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Interaction Details - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false, pageLoading: false }">
    <div class="flex h-screen overflow-hidden">
        @include('admin.shared.sidebar', ['active' => 'customer-cares'])

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            @include('admin.shared.header', ['title' => 'Interaction Details'])

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Back Button -->
                <div class="mb-4">
                    <a href="{{ route('admin.customer-care-oversight.interactions') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white rounded-lg hover:bg-gray-50 transition border border-gray-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to Interactions
                    </a>
                </div>

                <!-- Status Badge -->
                <div class="mb-6">
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-semibold uppercase tracking-widest 
                        @if($interaction->status === 'active') bg-amber-50 text-amber-600 border border-amber-100
                        @elseif($interaction->status === 'resolved') bg-emerald-50 text-emerald-600 border border-emerald-100
                        @else bg-gray-100 text-gray-500 border border-gray-200
                        @endif">
                        @if($interaction->status === 'active')
                            <span class="w-2 h-2 bg-amber-500 rounded-full mr-2 animate-pulse"></span>
                        @endif
                        {{ ucfirst($interaction->status) }}
                    </span>
                </div>

                <!-- Info Box -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-l-blue-500">
                    <div class="flex items-start space-x-4">
                        <div class="p-2 bg-blue-100 text-blue-600 rounded-xl flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-bold text-gray-900 mb-2">Interaction Overview</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-4">
                                <div class="bg-white/60 rounded-xl p-3 border border-blue-100">
                                    <p class="text-xs font-semibold text-blue-600 uppercase tracking-widest mb-1">Channel</p>
                                    <p class="text-sm font-bold text-gray-800 capitalize">{{ $interaction->channel }}</p>
                                </div>
                                <div class="bg-white/60 rounded-xl p-3 border border-blue-100">
                                    <p class="text-xs font-semibold text-blue-600 uppercase tracking-widest mb-1">Started</p>
                                    <p class="text-sm font-bold text-gray-800">{{ $interaction->created_at->format('M d, Y • H:i') }}</p>
                                </div>
                                <div class="bg-white/60 rounded-xl p-3 border border-blue-100">
                                    <p class="text-xs font-semibold text-blue-600 uppercase tracking-widest mb-1">Duration</p>
                                    <p class="text-sm font-bold text-gray-800" id="durationDisplay">
                                        @if($interaction->status === 'active')
                                            <span class="text-amber-600">In Progress...</span>
                                        @elseif($interaction->duration_seconds)
                                            {{ round($interaction->duration_seconds / 60) }} minutes
                                        @else
                                            N/A
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content Area -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Interaction Summary -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="p-2 bg-purple-50 text-purple-600 rounded-xl">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Session Summary</h3>
                                    <p class="text-xs text-gray-500">What this conversation is about</p>
                                </div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <p class="text-sm font-semibold text-gray-700 leading-relaxed">{{ $interaction->summary }}</p>
                            </div>
                        </div>

                        <!-- Timeline / Activity Log -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Activity Timeline</h3>
                                    <p class="text-xs text-gray-500">Chronological record of events</p>
                                </div>
                            </div>

                            <div class="space-y-4 relative before:absolute before:left-[19px] before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-200">
                                <!-- Session Started -->
                                <div class="flex space-x-4 relative">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-emerald-50 border-2 border-emerald-200 flex items-center justify-center text-emerald-600 z-10">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                    <div class="flex-1 pb-4">
                                        <div class="flex items-baseline justify-between mb-1">
                                            <h4 class="text-xs font-bold text-gray-900 uppercase tracking-widest">Session Started</h4>
                                            <span class="text-xs text-gray-500">{{ $interaction->created_at->format('H:i • M d, Y') }}</span>
                                        </div>
                                        <p class="text-xs font-semibold text-gray-600">
                                            {{ $interaction->agent->name ?? 'System' }} started a {{ $interaction->channel }} session with {{ $interaction->user->name }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Notes -->
                                @forelse($interaction->notes as $note)
                                <div class="flex space-x-4 relative">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-indigo-50 border-2 border-indigo-200 flex items-center justify-center text-indigo-600 z-10">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </div>
                                    <div class="flex-1 pb-4">
                                        <div class="flex items-baseline justify-between mb-1">
                                            <h4 class="text-xs font-bold text-gray-900 uppercase tracking-widest">{{ $note->creator->name ?? 'System' }} added a note</h4>
                                            <span class="text-xs text-gray-500">{{ $note->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="p-4 rounded-xl {{ $note->is_internal ? 'bg-amber-50/80 border-2 border-amber-300' : 'bg-gray-50 border border-gray-200' }}">
                                            <p class="text-sm font-semibold text-gray-700 leading-relaxed">{{ $note->note }}</p>
                                            @if($note->is_internal)
                                            <div class="mt-3 pt-3 border-t border-amber-200 text-xs font-semibold text-amber-700 uppercase tracking-widest flex items-center">
                                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z" /></svg>
                                                Internal Note (Private - Not visible to customer)
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="flex space-x-4">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-300 z-10">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </div>
                                    <div class="flex-1 pb-4">
                                        <p class="text-xs font-semibold text-gray-400 italic">No notes added yet</p>
                                    </div>
                                </div>
                                @endforelse

                                <!-- Session Ended (if resolved) -->
                                @if($interaction->status === 'resolved' && $interaction->ended_at)
                                <div class="flex space-x-4 relative">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-emerald-50 border-2 border-emerald-200 flex items-center justify-center text-emerald-600 z-10">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-baseline justify-between mb-1">
                                            <h4 class="text-xs font-bold text-gray-900 uppercase tracking-widest">Session Completed</h4>
                                            <span class="text-xs text-gray-500">{{ $interaction->ended_at->format('H:i • M d, Y') }}</span>
                                        </div>
                                        <p class="text-xs font-semibold text-gray-600">
                                            Session resolved after {{ round($interaction->duration_seconds / 60) }} minutes
                                        </p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Escalations -->
                        @if($interaction->escalations->count() > 0)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="p-2 bg-amber-50 text-amber-600 rounded-xl">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" /></svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Escalations</h3>
                                    <p class="text-xs text-gray-500">Issues raised from this interaction</p>
                                </div>
                            </div>
                            <div class="space-y-3">
                                @foreach($interaction->escalations as $escalation)
                                <div class="p-4 bg-amber-50 rounded-xl border border-amber-200">
                                    <div class="flex items-start justify-between mb-2">
                                        <p class="text-sm font-bold text-gray-900">{{ $escalation->subject }}</p>
                                        <span class="text-xs text-gray-500">{{ $escalation->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-xs text-gray-600">{{ $escalation->description }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Sidebar Info Area -->
                    <div class="space-y-6">
                        <!-- Customer Info -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Customer Information</p>
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-50 to-purple-50 text-purple-600 flex items-center justify-center font-bold text-lg">
                                    {{ substr($interaction->user->name ?? 'P', 0, 1) }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">{{ $interaction->user->name }}</h4>
                                    <p class="text-xs text-gray-500">{{ \App\Helpers\PrivacyHelper::maskEmail($interaction->user->email) }}</p>
                                </div>
                            </div>
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                                    <span class="text-xs font-semibold text-gray-500">Phone</span>
                                    <span class="text-xs font-bold text-gray-800">{{ \App\Helpers\PrivacyHelper::maskPhone($interaction->user->phone) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Session Details -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Session Details</p>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-gray-500">Channel</span>
                                    <span class="px-2 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-bold uppercase capitalize">{{ $interaction->channel }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-gray-500">Handled By</span>
                                    <span class="text-xs font-bold text-gray-800">{{ $interaction->agent->name ?? 'SYSTEM' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-gray-500">Started</span>
                                    <span class="text-xs font-bold text-gray-800">{{ $interaction->created_at->format('H:i • d M Y') }}</span>
                                </div>
                                @if($interaction->ended_at)
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-gray-500">Ended</span>
                                    <span class="text-xs font-bold text-gray-800">{{ $interaction->ended_at->format('H:i • d M Y') }}</span>
                                </div>
                                @endif
                                @if($interaction->duration_seconds)
                                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                    <span class="text-xs font-semibold text-gray-500">Total Duration</span>
                                    <span class="text-xs font-bold text-gray-800">{{ round($interaction->duration_seconds / 60) }} minutes</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Live duration timer for active interactions
        @if($interaction->status === 'active')
        (function() {
            const startTime = new Date('{{ $interaction->created_at->toIso8601String() }}').getTime();
            const durationDisplay = document.getElementById('durationDisplay');
            
            function updateDuration() {
                const now = new Date().getTime();
                const diff = Math.floor((now - startTime) / 1000); // seconds
                const minutes = Math.floor(diff / 60);
                const seconds = diff % 60;
                durationDisplay.innerHTML = `<span class="text-amber-600 font-bold">${minutes}m ${seconds}s</span>`;
            }
            
            updateDuration();
            setInterval(updateDuration, 1000);
        })();
        @endif
    </script>
</body>
</html>

