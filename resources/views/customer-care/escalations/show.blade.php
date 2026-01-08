<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Escalation Details - Customer Care</title>
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
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <!-- Sidebar Header -->
            <div class="purple-gradient p-5 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap Logo" class="h-8 w-auto">
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-white hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- User Info -->
            <div class="p-5 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-purple-100">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold">
                        {{ substr(Auth::guard('customer_care')->user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800 text-sm">{{ Auth::guard('customer_care')->user()->name }}</p>
                        <p class="text-xs text-gray-500">Customer Care</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-2">
                <a href="{{ route('customer-care.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('customer-care.consultations') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span>Consultations</span>
                </a>

                <a href="{{ route('customer-care.interactions.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <span>Interactions</span>
                </a>

                <a href="{{ route('customer-care.tickets.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Support Tickets</span>
                </a>

                <a href="{{ route('customer-care.escalations.index') }}" class="flex items-center space-x-3 px-4 py-3 text-white purple-gradient rounded-lg font-medium transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <span>Escalations</span>
                </a>

                <a href="{{ route('customer-care.customers.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Customers</span>
                </a>

                <div class="border-t border-gray-200 my-2"></div>

                <a href="{{ url('/') }}" target="_blank" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                    <span>View Website</span>
                </a>

                <form method="POST" action="{{ route('customer-care.logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg font-medium transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </nav>
        </aside>

        <!-- Overlay for mobile sidebar -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
             style="display: none;"></div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="purple-gradient shadow-lg z-10">
                <div class="flex items-center justify-between px-6 py-6">
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = true" class="lg:hidden text-white hover:text-purple-200 focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div class="flex items-center space-x-3">
                            <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap" class="h-8 w-auto lg:hidden">
                            <h1 class="text-xl font-bold text-white">Escalation Details</h1>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-white hidden md:block">{{ now()->format('l, F j, Y') }}</span>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Back Button -->
                <div class="mb-6">
                    <a href="{{ route('customer-care.escalations.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to Escalations
                    </a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Details Card -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Escalation Information -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                    Escalation Information
                                </h2>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Status</p>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        @if($escalation->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($escalation->status === 'in_progress') bg-blue-100 text-blue-800
                                        @elseif($escalation->status === 'resolved') bg-green-100 text-green-800
                                        @elseif($escalation->status === 'closed') bg-gray-100 text-gray-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $escalation->status)) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Escalated To</p>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                        {{ ucfirst($escalation->escalated_to_type) }}
                                    </span>
                                </div>
                                @if($escalation->escalatedTo)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">
                                        @if($escalation->escalated_to_type === 'admin') Admin
                                        @else Doctor
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-900">
                                        @if($escalation->escalated_to_type === 'admin')
                                            {{ $escalation->escalatedTo->name ?? 'N/A' }}
                                        @else
                                            {{ $escalation->escalatedTo->name ?? 'N/A' }}
                                            @if($escalation->escalatedTo->specialization ?? null)
                                                - {{ $escalation->escalatedTo->specialization }}
                                            @endif
                                        @endif
                                    </p>
                                </div>
                                @endif
                                @if($escalation->resolved_at)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Resolved At</p>
                                    <p class="text-sm text-gray-900">{{ $escalation->resolved_at->format('M d, Y h:i A') }}</p>
                                </div>
                                @endif
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Created</p>
                                    <p class="text-sm text-gray-900">{{ $escalation->created_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Reason for Escalation -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Reason for Escalation
                                </h2>
                            </div>
                            <div class="prose max-w-none">
                                <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $escalation->reason }}</p>
                            </div>
                        </div>

                        <!-- Outcome (if resolved) -->
                        @if($escalation->outcome)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Resolution Outcome
                                </h2>
                            </div>
                            <div class="prose max-w-none">
                                <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $escalation->outcome }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Related Ticket or Interaction -->
                        @if($escalation->supportTicket)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Related Ticket
                                </h2>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Ticket Number</p>
                                    <a href="{{ route('customer-care.tickets.show', $escalation->supportTicket) }}" class="text-sm font-semibold text-purple-600 hover:text-purple-800">
                                        {{ $escalation->supportTicket->ticket_number }}
                                    </a>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Subject</p>
                                    <p class="text-sm text-gray-900">{{ $escalation->supportTicket->subject }}</p>
                                </div>
                                @if($escalation->supportTicket->user)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Customer</p>
                                    <a href="{{ route('customer-care.customers.show', $escalation->supportTicket->user) }}" class="text-sm text-purple-600 hover:text-purple-800">
                                        {{ $escalation->supportTicket->user->name }}
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($escalation->customerInteraction)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    Related Interaction
                                </h2>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Summary</p>
                                    <p class="text-sm text-gray-900">{{ $escalation->customerInteraction->summary }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Channel</p>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                        {{ ucfirst($escalation->customerInteraction->channel) }}
                                    </span>
                                </div>
                                @if($escalation->customerInteraction->user)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Customer</p>
                                    <a href="{{ route('customer-care.customers.show', $escalation->customerInteraction->user) }}" class="text-sm text-purple-600 hover:text-purple-800">
                                        {{ $escalation->customerInteraction->user->name }}
                                    </a>
                                </div>
                                @endif
                                <div>
                                    <a href="{{ route('customer-care.interactions.show', $escalation->customerInteraction) }}" class="text-sm text-purple-600 hover:text-purple-800">
                                        View Interaction Details →
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Escalated By -->
                        @if($escalation->escalatedBy)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4">Escalated By</h3>
                            <div class="space-y-2">
                                <p class="text-sm font-medium text-gray-900">{{ $escalation->escalatedBy->name }}</p>
                                <p class="text-xs text-gray-500">{{ $escalation->escalatedBy->email }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Escalated To -->
                        @if($escalation->escalatedTo)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4">
                                Escalated To (@if($escalation->escalated_to_type === 'admin') Admin @else Doctor @endif)
                            </h3>
                            <div class="space-y-2">
                                <p class="text-sm font-medium text-gray-900">{{ $escalation->escalatedTo->name }}</p>
                                @if($escalation->escalatedTo->email ?? null)
                                <p class="text-xs text-gray-500">{{ $escalation->escalatedTo->email }}</p>
                                @endif
                                @if($escalation->escalated_to_type === 'doctor' && ($escalation->escalatedTo->specialization ?? null))
                                <p class="text-xs text-gray-500">{{ $escalation->escalatedTo->specialization }}</p>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Related Links -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4">Related</h3>
                            <div class="space-y-2">
                                @if($escalation->supportTicket)
                                <a href="{{ route('customer-care.tickets.show', $escalation->supportTicket) }}" class="block w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-semibold text-center">
                                    View Ticket
                                </a>
                                @endif
                                @if($escalation->customerInteraction)
                                <a href="{{ route('customer-care.interactions.show', $escalation->customerInteraction) }}" class="block w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-semibold text-center">
                                    View Interaction
                                </a>
                                @endif
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4">Timeline</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-gray-500">Created</p>
                                    <p class="text-sm text-gray-900">{{ $escalation->created_at->format('M d, Y') }}</p>
                                    <p class="text-xs text-gray-400">{{ $escalation->created_at->format('h:i A') }}</p>
                                </div>
                                @if($escalation->updated_at && $escalation->updated_at != $escalation->created_at)
                                <div>
                                    <p class="text-xs text-gray-500">Last Updated</p>
                                    <p class="text-sm text-gray-900">{{ $escalation->updated_at->format('M d, Y') }}</p>
                                    <p class="text-xs text-gray-400">{{ $escalation->updated_at->format('h:i A') }}</p>
                                </div>
                                @endif
                                @if($escalation->resolved_at)
                                <div>
                                    <p class="text-xs text-gray-500">Resolved</p>
                                    <p class="text-sm text-gray-900">{{ $escalation->resolved_at->format('M d, Y') }}</p>
                                    <p class="text-xs text-gray-400">{{ $escalation->resolved_at->format('h:i A') }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    @include('customer-care.shared.preloader-scripts')
</body>
</html>


