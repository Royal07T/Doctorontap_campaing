@extends('layouts.doctor')

@section('title', 'Doctor Dashboard')
@section('header-title', 'Doctor Dashboard')

@section('content')
                <!-- Welcome Message -->
                @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
                @endif

                <!-- Current Earnings - Prominent Display -->
                <div class="purple-gradient rounded-2xl shadow-2xl p-8 mb-6 text-white relative overflow-hidden">
                    <!-- Decorative background elements -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mr-32 -mt-32"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-white opacity-5 rounded-full -ml-24 -mb-24"></div>
                    
                    <div class="flex items-center justify-between relative z-10">
                        <div class="flex-1">
                            <p class="text-white text-opacity-90 text-sm font-semibold uppercase tracking-wider mb-3">Current Earnings</p>
                            <p class="text-5xl font-bold mb-2 drop-shadow-lg">₦{{ number_format($stats['total_earnings'], 2) }}</p>
                            <p class="text-white text-opacity-80 text-base font-medium">From {{ $stats['paid_consultations'] }} paid consultation{{ $stats['paid_consultations'] != 1 ? 's' : '' }}</p>
                        </div>
                        <div class="bg-white bg-opacity-25 backdrop-blur-sm p-6 rounded-2xl shadow-xl border border-white border-opacity-20 ml-6">
                            <svg class="w-16 h-16 text-white drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-4 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Total</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_consultations'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">Patients</p>
                            </div>
                            <div class="bg-purple-50 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-4 border-l-4 border-amber-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Pending</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['pending_consultations'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">Awaiting Action</p>
                            </div>
                            <div class="bg-amber-50 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-4 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Scheduled</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['scheduled_consultations'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">Upcoming</p>
                            </div>
                            <div class="bg-blue-50 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-4 border-l-4 border-emerald-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Completed</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['completed_consultations'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">Finished</p>
                            </div>
                            <div class="bg-emerald-50 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Statistics -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-4 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Paid</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['paid_consultations'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">Consultations</p>
                            </div>
                            <div class="bg-green-50 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-4 border-l-4 border-orange-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Pending</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['pending_payments'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">Payments</p>
                            </div>
                            <div class="bg-orange-50 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-4 border-l-4 border-indigo-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Total</p>
                                <p class="text-2xl font-bold text-gray-900">₦{{ number_format($stats['total_earnings'], 2) }}</p>
                                <p class="text-xs text-gray-500 mt-1">Earnings</p>
                            </div>
                            <div class="bg-indigo-50 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Consultations -->
                @if($recentConsultations->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-800">Recent Consultations</h2>
                        <a href="{{ route('doctor.consultations') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium">
                            View All Consultations →
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Reference</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Patient</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Problem</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Severity</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Payment</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($recentConsultations as $consultation)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-900 font-mono">{{ $consultation->reference ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $consultation->first_name }} {{ $consultation->last_name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ Str::limit($consultation->problem, 40) }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                            {{ $consultation->severity == 'mild' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $consultation->severity == 'moderate' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $consultation->severity == 'severe' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ ucfirst($consultation->severity) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                            {{ $consultation->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $consultation->status == 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $consultation->status == 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $consultation->status == 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ ucfirst($consultation->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($consultation->payment_status == 'paid')
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Paid
                                            </span>
                                            @if($consultation->payment)
                                                <div class="text-xs text-gray-500 mt-1">₦{{ number_format($consultation->payment->amount, 2) }}</div>
                                            @endif
                                        @elseif($consultation->payment_status == 'pending')
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                </svg>
                                                Pending
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                                </svg>
                                                Not Required
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $consultation->created_at->format('M d, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Welcome Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                    <div class="max-w-2xl mx-auto">
                        <div class="text-6xl mb-4">👨‍⚕️</div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">Welcome, Dr. {{ Auth::guard('doctor')->user()->name }}!</h2>
                        <p class="text-gray-600">You're logged in to your doctor dashboard. You can view your assigned consultations and patient information here.</p>
                    </div>
                </div>
@endsection

@push('scripts')
    @include('components.custom-alert-modal')
@endpush

