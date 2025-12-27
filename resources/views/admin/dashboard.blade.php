<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - DoctorOnTap</title>
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
        @include('admin.shared.sidebar', ['active' => 'dashboard'])

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            @include('admin.shared.header', ['title' => 'Dashboard'])

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
            <!-- Total Consultations -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Total</p>
                        <p class="text-xl font-bold text-gray-900 mb-1">{{ $stats['total_consultations'] }}</p>
                        <p class="text-xs text-gray-500">Consultations</p>
                    </div>
                    <div class="bg-blue-50 p-3 rounded-xl flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          

            <!-- Pending Consultations -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-amber-500">
                <div class="flex items-center justify-between">
                    <div class="flex-1">                    
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Pending</p>
                        <p class="text-xl font-bold text-gray-900 mb-1">{{ $stats['pending_consultations'] }}</p>
                        <p class="text-xs text-gray-500">Awaiting Action</p>
                    </div>
                    <div class="bg-amber-50 p-3 rounded-xl flex-shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Completed Consultations -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-emerald-500">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Completed</p>
                        <p class="text-xl font-bold text-gray-900 mb-1">{{ $stats['completed_consultations'] }}</p>
                        <p class="text-xs text-gray-500">Successful</p>
                    </div>
                    <div class="bg-emerald-50 p-3 rounded-xl flex-shrink-0">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Unpaid Consultations -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Unpaid</p>
                        <p class="text-xl font-bold text-gray-900 mb-1">{{ $stats['unpaid_consultations'] }}</p>
                        <p class="text-xs text-gray-500">Awaiting Payment</p>
                    </div>
                    <div class="bg-red-50 p-3 rounded-xl flex-shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Paid Consultations -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Paid</p>
                        <p class="text-xl font-bold text-gray-900 mb-1">{{ $stats['paid_consultations'] }}</p>
                        <p class="text-xs text-gray-500">Payment Complete</p>
                    </div>
                    <div class="bg-purple-50 p-3 rounded-xl flex-shrink-0">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Revenue - Spans 2 columns on larger screens -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-teal-500 sm:col-span-2 lg:col-span-3 xl:col-span-2">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Total Revenue</p>
                        <p class="text-xl font-bold text-gray-900 mb-1">₦{{ number_format($stats['total_revenue'], 2) }}</p>
                        <p class="text-xs text-gray-500">Cumulative Earnings</p>
                    </div>
                    <div class="bg-teal-50 p-3 rounded-xl flex-shrink-0">
                        <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div>
            <div class="mb-4 pb-4 border-b border-gray-200">
                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Quick Actions
                </h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Manage Consultations -->
                <a href="{{ route('admin.consultations') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 hover:border-purple-400 group">
                    <div class="flex items-center space-x-3">
                        <div class="purple-gradient p-3 rounded-xl group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-gray-900 group-hover:text-purple-700 transition-colors">Consultations</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Manage & update status</p>
                        </div>
                    </div>
                </a>

                <!-- View Payments -->
                <a href="{{ route('admin.payments') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 hover:border-purple-400 group">
                    <div class="flex items-center space-x-3">
                        <div class="purple-gradient p-3 rounded-xl group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-gray-900 group-hover:text-purple-700 transition-colors">Payments</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Track transactions</p>
                        </div>
                    </div>
                </a>

                <!-- View Doctors -->
                <a href="{{ route('admin.doctors') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 hover:border-purple-400 group">
                    <div class="flex items-center space-x-3">
                        <div class="purple-gradient p-3 rounded-xl group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-gray-900 group-hover:text-purple-700 transition-colors">Doctors</h3>
                            <p class="text-xs text-gray-500 mt-0.5">View all providers</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
            </main>
        </div>
    </div>
    
    @include('admin.shared.preloader')
</body>
</html>

