<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Consultation Details - Customer Care</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
        .medical-info-blur {
            position: relative;
            filter: blur(8px);
            pointer-events: none;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }
        .medical-info-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: 0.75rem;
        }
        .medical-info-restricted {
            text-align: center;
            padding: 2rem;
        }
        .medical-info-restricted svg {
            margin: 0 auto 1rem;
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

                <a href="{{ route('customer-care.consultations') }}" class="flex items-center space-x-3 px-4 py-3 text-white purple-gradient rounded-lg font-medium transition-all">
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

                <a href="{{ route('customer-care.escalations.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
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
                            <h1 class="text-xl font-bold text-white">Consultation Details</h1>
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
                    <a href="{{ route('customer-care.consultations') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to Consultations
                    </a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Details Card -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Patient Information -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Patient Information
                                </h2>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Reference</p>
                                    <p class="text-sm font-mono text-gray-900">{{ $consultation->reference }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Status</p>
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                        @if($consultation->status === 'completed') bg-green-100 text-green-800
                                        @elseif($consultation->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($consultation->status === 'scheduled') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($consultation->status) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Full Name</p>
                                    <p class="text-sm text-gray-900">{{ $consultation->full_name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Email</p>
                                    <p class="text-sm text-gray-900">{{ $consultation->email }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Phone</p>
                                    <p class="text-sm text-gray-900">{{ $consultation->mobile ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Age</p>
                                    <p class="text-sm text-gray-900">{{ $consultation->age ?? 'N/A' }} years</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Gender</p>
                                    <p class="text-sm text-gray-900">{{ ucfirst($consultation->gender ?? 'N/A') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Created Date</p>
                                    <p class="text-sm text-gray-900">{{ $consultation->created_at->format('M d, Y h:i A') }}</p>
                                </div>
                                @if($consultation->patient)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Patient Record</p>
                                    <a href="{{ route('customer-care.customers.show', $consultation->patient) }}" class="text-sm text-purple-600 hover:text-purple-800">
                                        View Patient Profile
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Medical Information -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 relative">
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Medical Information
                                </h2>
                            </div>
                            <div class="space-y-4 medical-info-blur">
                                @if($consultation->problem)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Problem/Complaint</p>
                                    <p class="text-sm text-gray-900">{{ $consultation->problem }}</p>
                                </div>
                                @endif
                                @if($consultation->presenting_complaint)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Presenting Complaint</p>
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $consultation->presenting_complaint }}</p>
                                </div>
                                @endif
                                @if($consultation->severity)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Severity</p>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        @if($consultation->severity === 'critical' || $consultation->severity === 'urgent') bg-red-100 text-red-800
                                        @elseif($consultation->severity === 'high') bg-orange-100 text-orange-800
                                        @elseif($consultation->severity === 'medium') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($consultation->severity) }}
                                    </span>
                                </div>
                                @endif
                                @if($consultation->emergency_symptoms && is_array($consultation->emergency_symptoms) && count($consultation->emergency_symptoms) > 0)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Emergency Symptoms</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($consultation->emergency_symptoms as $symptom)
                                        <span class="px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-800">{{ $symptom }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                @if($consultation->history_of_complaint)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">History of Complaint</p>
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $consultation->history_of_complaint }}</p>
                                </div>
                                @endif
                                @if($consultation->past_medical_history)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Past Medical History</p>
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $consultation->past_medical_history }}</p>
                                </div>
                                @endif
                                @if($consultation->family_history)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Family History</p>
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $consultation->family_history }}</p>
                                </div>
                                @endif
                                @if($consultation->drug_history)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Drug History</p>
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $consultation->drug_history }}</p>
                                </div>
                                @endif
                            </div>
                            <div class="medical-info-overlay">
                                <div class="medical-info-restricted">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <h3 class="text-sm font-semibold text-gray-700 mb-1">Medical Information Restricted</h3>
                                    <p class="text-xs text-gray-500">This information is confidential and only accessible to authorized medical personnel.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Consultation Details -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    Consultation Details
                                </h2>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @if($consultation->doctor)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Doctor</p>
                                    <p class="text-sm text-gray-900">{{ $consultation->doctor->full_name }}</p>
                                    <p class="text-xs text-gray-600">{{ $consultation->doctor->specialization ?? 'N/A' }}</p>
                                </div>
                                @endif
                                @if($consultation->consult_mode)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Consultation Mode</p>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                        {{ ucfirst($consultation->consult_mode) }}
                                    </span>
                                </div>
                                @endif
                                @if($consultation->scheduled_at)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Scheduled Date</p>
                                    <p class="text-sm text-gray-900">{{ $consultation->scheduled_at->format('M d, Y h:i A') }}</p>
                                </div>
                                @endif
                                @if($consultation->consultation_completed_at)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Completed Date</p>
                                    <p class="text-sm text-gray-900">{{ $consultation->consultation_completed_at->format('M d, Y h:i A') }}</p>
                                </div>
                                @endif
                                @if($consultation->consultation_type)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Consultation Type</p>
                                    <p class="text-sm text-gray-900">{{ ucfirst($consultation->consultation_type) }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Treatment Plan -->
                        @if($consultation->diagnosis || $consultation->treatment_plan || $consultation->prescribed_medications)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 relative">
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Treatment Plan
                                </h2>
                            </div>
                            <div class="space-y-4 medical-info-blur">
                                @if($consultation->diagnosis)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Diagnosis</p>
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $consultation->diagnosis }}</p>
                                </div>
                                @endif
                                @if($consultation->investigation)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Investigation</p>
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $consultation->investigation }}</p>
                                </div>
                                @endif
                                @if($consultation->treatment_plan)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Treatment Plan</p>
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $consultation->treatment_plan }}</p>
                                </div>
                                @endif
                                @if($consultation->prescribed_medications && is_array($consultation->prescribed_medications) && count($consultation->prescribed_medications) > 0)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Prescribed Medications</p>
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach($consultation->prescribed_medications as $medication)
                                        <li class="text-sm text-gray-900">{{ $medication }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                                @if($consultation->follow_up_instructions)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Follow-up Instructions</p>
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $consultation->follow_up_instructions }}</p>
                                </div>
                                @endif
                                @if($consultation->lifestyle_recommendations)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Lifestyle Recommendations</p>
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $consultation->lifestyle_recommendations }}</p>
                                </div>
                                @endif
                                @if($consultation->next_appointment_date)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Next Appointment</p>
                                    <p class="text-sm text-gray-900">{{ $consultation->next_appointment_date->format('M d, Y') }}</p>
                                </div>
                                @endif
                                @if($consultation->doctor_notes)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Doctor Notes</p>
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $consultation->doctor_notes }}</p>
                                </div>
                                @endif
                                @if($consultation->additional_notes)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Additional Notes</p>
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $consultation->additional_notes }}</p>
                                </div>
                                @endif
                            </div>
                            <div class="medical-info-overlay">
                                <div class="medical-info-restricted">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <h3 class="text-sm font-semibold text-gray-700 mb-1">Treatment Plan Restricted</h3>
                                    <p class="text-xs text-gray-500">This information is confidential and only accessible to authorized medical personnel.</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Payment Status -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4">Payment Status</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600">Status</span>
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                        @if($consultation->payment_status === 'paid') bg-green-100 text-green-800
                                        @elseif($consultation->payment_status === 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($consultation->payment_status ?? 'Unpaid') }}
                                    </span>
                                </div>
                                @if($consultation->payment)
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600">Amount</span>
                                    <span class="text-sm font-semibold text-gray-900">₦{{ number_format($consultation->payment->amount, 2) }}</span>
                                </div>
                                @if($consultation->payment_completed_at)
                                <div>
                                    <p class="text-xs text-gray-500">Paid On</p>
                                    <p class="text-sm text-gray-900">{{ $consultation->payment_completed_at->format('M d, Y h:i A') }}</p>
                                </div>
                                @endif
                                @endif
                                @if($consultation->payment_request_sent)
                                <div>
                                    <p class="text-xs text-gray-500">Payment Request Sent</p>
                                    <p class="text-sm text-gray-900">{{ $consultation->payment_request_sent_at ? $consultation->payment_request_sent_at->format('M d, Y h:i A') : 'Yes' }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Assigned Staff -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4">Assigned Staff</h3>
                            <div class="space-y-3">
                                @if($consultation->doctor)
                                <div>
                                    <p class="text-xs text-gray-500">Doctor</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $consultation->doctor->full_name }}</p>
                                </div>
                                @endif
                                @if($consultation->customerCare)
                                <div>
                                    <p class="text-xs text-gray-500">Customer Care</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $consultation->customerCare->name }}</p>
                                </div>
                                @endif
                                @if($consultation->nurse)
                                <div>
                                    <p class="text-xs text-gray-500">Nurse</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $consultation->nurse->name }}</p>
                                </div>
                                @endif
                                @if($consultation->canvasser)
                                <div>
                                    <p class="text-xs text-gray-500">Canvasser</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $consultation->canvasser->name }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Quick Info -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4">Timeline</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-gray-500">Created</p>
                                    <p class="text-sm text-gray-900">{{ $consultation->created_at->format('M d, Y') }}</p>
                                    <p class="text-xs text-gray-400">{{ $consultation->created_at->format('h:i A') }}</p>
                                </div>
                                @if($consultation->updated_at && $consultation->updated_at != $consultation->created_at)
                                <div>
                                    <p class="text-xs text-gray-500">Last Updated</p>
                                    <p class="text-sm text-gray-900">{{ $consultation->updated_at->format('M d, Y') }}</p>
                                    <p class="text-xs text-gray-400">{{ $consultation->updated_at->format('h:i A') }}</p>
                                </div>
                                @endif
                                @if($consultation->treatment_plan_created_at)
                                <div>
                                    <p class="text-xs text-gray-500">Treatment Plan Created</p>
                                    <p class="text-sm text-gray-900">{{ $consultation->treatment_plan_created_at->format('M d, Y') }}</p>
                                    <p class="text-xs text-gray-400">{{ $consultation->treatment_plan_created_at->format('h:i A') }}</p>
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

