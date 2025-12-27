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

                <!-- Welcome Card -->
                <div class="purple-gradient rounded-xl shadow-lg p-8 mb-6 text-white relative overflow-hidden">
                    <!-- Decorative background elements -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mr-32 -mt-32"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-white opacity-5 rounded-full -ml-24 -mb-24"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h2 class="text-2xl md:text-3xl font-bold mb-2 drop-shadow-lg">Welcome back, Dr. {{ Auth::guard('doctor')->user()->name }}! 👨‍⚕️</h2>
                                <p class="text-white text-opacity-90 text-sm md:text-base mb-4">We're glad to have you here. Manage your consultations, view patient information, and stay updated with your practice.</p>
                                <div class="flex items-center gap-4 mb-4">
                                    <div class="bg-white px-4 py-2 rounded-lg shadow-md">
                                        <p class="text-xs text-gray-600 uppercase tracking-wide mb-1">Total Earnings</p>
                                        <p class="text-xl font-bold text-gray-900">₦{{ number_format($stats['total_earnings'] ?? 0, 2) }}</p>
                            </div>
                        </div>
                                <div class="flex flex-wrap gap-3 mt-4">
                                    <a href="{{ route('doctor.consultations') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white rounded-lg hover:bg-gray-50 transition text-sm font-medium text-gray-900 shadow-md">
                                        <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                        <span class="text-gray-900">View Consultations</span>
                                    </a>
                                    <a href="{{ route('doctor.profile') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white rounded-lg hover:bg-gray-50 transition text-sm font-medium text-gray-900 shadow-md">
                                        <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                        <span class="text-gray-900">Update Profile</span>
                                    </a>
                    </div>
                            </div>
                            <div class="hidden md:block ml-6">
                                <div class="bg-white p-6 rounded-xl shadow-xl">
                                    <div class="relative">
                                        <div class="absolute inset-0 bg-emerald-500 rounded-full opacity-20"></div>
                                        <svg class="w-16 h-16 text-emerald-600 drop-shadow-lg relative z-10" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Consultations -->
                @if($recentConsultations->count() > 0)
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Recent Consultations
                        </h2>
                        <a href="{{ route('doctor.consultations') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                            <span>View All</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                    <div class="space-y-4">
                                @foreach($recentConsultations as $consultation)
                            <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all hover:shadow-md">
                                <!-- Card Header -->
                                <button @click="open = !open" class="w-full text-left focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                                    <div class="p-5 flex items-center justify-between">
                                        <div class="flex-1 flex items-center gap-3">
                                            <div class="flex-shrink-0">
                                                @if($consultation->status === 'completed')
                                                    <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                                                @elseif($consultation->status === 'pending')
                                                    <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                                                @elseif($consultation->status === 'scheduled')
                                                    <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                                                @elseif($consultation->status === 'cancelled')
                                                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                                @else
                                                    <div class="w-3 h-3 rounded-full bg-gray-400"></div>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <h3 class="text-sm font-semibold text-gray-900">{{ $consultation->reference ?? 'N/A' }}</h3>
                                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                                        @if($consultation->status === 'completed') bg-emerald-100 text-emerald-700
                                                        @elseif($consultation->status === 'pending') bg-amber-100 text-amber-700
                                                        @elseif($consultation->status === 'scheduled') bg-blue-100 text-blue-700
                                                        @elseif($consultation->status === 'cancelled') bg-red-100 text-red-700
                                                        @else bg-gray-100 text-gray-700 @endif">
                                            {{ ucfirst($consultation->status) }}
                                        </span>
                                                    @if($consultation->payment_status === 'paid')
                                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">✓ Paid</span>
                                                    @elseif($consultation->payment_status === 'pending')
                                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-700">Payment Pending</span>
                                                    @endif
                                                </div>
                                                <p class="text-xs text-gray-600">{{ $consultation->first_name }} {{ $consultation->last_name }} • {{ $consultation->created_at->format('M d, Y') }}</p>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 ml-4">
                                            <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" 
                                                 :class="{ 'rotate-180': open }" 
                                                 fill="none" 
                                                 stroke="currentColor" 
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </button>

                                <!-- Dropdown Content -->
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                                     x-transition:enter-end="opacity-100 transform translate-y-0"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 transform translate-y-0"
                                     x-transition:leave-end="opacity-0 transform -translate-y-2"
                                     x-cloak
                                     class="border-t border-gray-100 bg-gray-50"
                                     style="display: none;">
                                    <div class="p-5 space-y-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Patient</p>
                                                <p class="text-xs text-gray-900 font-semibold">{{ $consultation->first_name }} {{ $consultation->last_name }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Date</p>
                                                <p class="text-xs text-gray-900">{{ $consultation->created_at->format('M d, Y H:i A') }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Problem</p>
                                                <p class="text-xs text-gray-700 leading-relaxed">{{ Str::limit($consultation->problem, 100) }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Severity</p>
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                    {{ $consultation->severity == 'mild' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                                    {{ $consultation->severity == 'moderate' ? 'bg-amber-100 text-amber-700' : '' }}
                                                    {{ $consultation->severity == 'severe' ? 'bg-red-100 text-red-700' : '' }}">
                                                    {{ ucfirst($consultation->severity ?? 'N/A') }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Payment Status</p>
                                        @if($consultation->payment_status == 'paid')
                                                    <div>
                                                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Paid
                                            </span>
                                            @if($consultation->payment)
                                                            <p class="text-xs text-gray-700 mt-1 font-semibold">₦{{ number_format($consultation->payment->amount, 2) }}</p>
                                            @endif
                                                    </div>
                                        @elseif($consultation->payment_status == 'pending')
                                                    <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                </svg>
                                                Pending
                                            </span>
                                        @else
                                                    <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                                                Not Required
                                            </span>
                                        @endif
                                            </div>
                                        </div>

                                        <!-- Action Button -->
                                        <div class="pt-3 border-t border-gray-200">
                                            <a href="{{ route('doctor.consultations.view', $consultation->id) }}" 
                                               class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-white purple-gradient rounded-lg hover:opacity-90 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                @endforeach
                    </div>
                </div>
                @endif
@endsection

@push('scripts')
    @include('components.custom-alert-modal')
@endpush

