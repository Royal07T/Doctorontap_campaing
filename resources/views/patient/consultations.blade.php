@extends('layouts.patient')

@section('title', 'My Consultations')

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
    <!-- Total Consultations -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Total</p>
                <p class="text-xl font-bold text-gray-900 mb-1">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-500">Consultations</p>
            </div>
            <div class="bg-blue-50 p-3 rounded-xl flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Completed -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-emerald-500">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Completed</p>
                <p class="text-xl font-bold text-gray-900 mb-1">{{ $stats['completed'] }}</p>
                <p class="text-xs text-gray-500">Successful</p>
            </div>
            <div class="bg-emerald-50 p-3 rounded-xl flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Pending -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-amber-500">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Pending</p>
                <p class="text-xl font-bold text-gray-900 mb-1">{{ $stats['pending'] }}</p>
                <p class="text-xs text-gray-500">Awaiting</p>
            </div>
            <div class="bg-amber-50 p-3 rounded-xl flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Total Paid -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-purple-500">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Total Paid</p>
                <p class="text-xl font-bold text-gray-900 mb-1">₦{{ number_format($stats['total_paid'], 0) }}</p>
                <p class="text-xs text-gray-500">All Time</p>
            </div>
            <div class="bg-purple-50 p-3 rounded-xl flex-shrink-0">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
    <div class="mb-4 pb-4 border-b border-gray-200">
        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            Search & Filter
        </h2>
    </div>
    <form method="GET" action="{{ route('patient.consultations') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Search -->
        <div class="md:col-span-2">
            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Search</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Reference or Doctor name..." 
                       class="w-full pl-10 pr-4 py-2.5 text-sm rounded-lg border border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition">
            </div>
        </div>

        <!-- Status Filter -->
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Status</label>
            <select name="status" class="w-full py-2.5 text-sm rounded-lg border border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <!-- Payment Status Filter -->
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Payment</label>
            <select name="payment_status" class="w-full py-2.5 text-sm rounded-lg border border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition">
                <option value="">All</option>
                <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
            </select>
        </div>

        <!-- Filter Buttons -->
        <div class="md:col-span-4 flex gap-2 pt-2">
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                Apply Filters
            </button>
            <a href="{{ route('patient.consultations') }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Clear
            </a>
        </div>
    </form>
</div>

<!-- Consultations Cards -->
<div class="space-y-4">
    @if($consultations->count() > 0)
                    @foreach($consultations as $consultation)
            <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all hover:shadow-md">
                <!-- Card Header (Always Visible) -->
                <button @click="open = !open" class="w-full text-left focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                    <div class="p-5 flex items-center justify-between">
                        <div class="flex-1 flex items-center gap-4">
                            <!-- Status Indicator -->
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
                            
                            <!-- Main Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-1">
                                    <h3 class="text-sm font-semibold text-gray-900">{{ $consultation->reference }}</h3>
                                    @if($consultation->status === 'completed')
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">Completed</span>
                                    @elseif($consultation->status === 'pending')
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-700">Pending</span>
                                    @elseif($consultation->status === 'scheduled')
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700">Scheduled</span>
                                    @elseif($consultation->status === 'cancelled')
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">Cancelled</span>
                                    @endif
                                @if($consultation->payment_status === 'paid')
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">✓ Paid</span>
                                @elseif($consultation->payment_status === 'pending')
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-700">Payment Pending</span>
                                @else
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-rose-100 text-rose-700">Unpaid</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-600">Dr. {{ $consultation->doctor->name ?? 'N/A' }} • {{ $consultation->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                        
                        <!-- Dropdown Icon -->
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
                        <!-- Consultation Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Reference</p>
                                <p class="text-xs text-gray-900 font-mono">{{ $consultation->reference }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Date</p>
                                <p class="text-xs text-gray-900">{{ $consultation->created_at->format('F d, Y') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Doctor</p>
                                <p class="text-xs text-gray-900">Dr. {{ $consultation->doctor->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Contact</p>
                                <p class="text-xs text-gray-900">{{ $consultation->doctor->phone ?? 'N/A' }}</p>
                            </div>
                            @if($consultation->specialization)
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Specialization</p>
                                <p class="text-xs text-gray-900">{{ $consultation->specialization }}</p>
                            </div>
                            @endif
                            @if($consultation->fee)
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Fee</p>
                                <p class="text-xs text-gray-900 font-semibold">₦{{ number_format($consultation->fee, 2) }}</p>
                            </div>
                            @endif
                        </div>

                        <!-- Problem/Symptoms (if available) -->
                        @if($consultation->problem || $consultation->symptoms)
                        <div class="pt-3 border-t border-gray-200">
                            @if($consultation->problem)
                            <div class="mb-3">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Problem</p>
                                <p class="text-xs text-gray-700 leading-relaxed">{{ \Illuminate\Support\Str::limit($consultation->problem, 200) }}</p>
                            </div>
                            @endif
                            @if($consultation->symptoms)
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Symptoms</p>
                                <p class="text-xs text-gray-700 leading-relaxed">{{ $consultation->symptoms }}</p>
                            </div>
                            @endif
                        </div>
                                @endif

                        <!-- Action Buttons -->
                        <div class="pt-3 border-t border-gray-200 flex flex-wrap items-center gap-2">
                                    <a href="{{ route('patient.consultation.view', $consultation->id) }}" 
                               class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-white purple-gradient rounded-lg hover:opacity-90 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                        View Details
                                    </a>
                            
                                    @if($consultation->payment_status === 'paid' && $consultation->hasTreatmentPlan())
                                        <a href="{{ route('patient.consultation.view', $consultation->id) }}#treatment-plan" 
                               class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-teal-700 bg-teal-50 rounded-lg hover:bg-teal-100 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                Treatment Plan
                                        </a>
                                    @endif
                            
                                    @if($consultation->status === 'completed' && !$consultation->hasPatientReview())
                                        <button onclick="openReviewModal({{ $consultation->id }}, '{{ $consultation->reference }}', '{{ $consultation->doctor->name ?? 'N/A' }}')" 
                                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                Write Review
                                        </button>
                                    @elseif($consultation->status === 'completed' && $consultation->hasPatientReview())
                            <span class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-lg">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                Reviewed
                                        </span>
                                    @endif
                                </div>
                    </div>
                </div>
            </div>
                    @endforeach

        <!-- Pagination -->
        <div class="mt-6">
            {{ $consultations->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Consultations Found</h3>
            <p class="text-sm text-gray-500 mb-4">You haven't had any consultations yet.</p>
            <a href="{{ route('consultation.index') }}" class="inline-block purple-gradient hover:opacity-90 text-white px-6 py-2 rounded-lg font-medium transition">
                Start Your First Consultation
            </a>
        </div>
    @endif
</div>

<!-- Review Modal -->
<div x-data="reviewModal()" 
     x-show="showModal" 
     x-cloak
     @click.away="showModal = false"
     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
     style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="p-6 md:p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Rate Your Doctor</h2>
                    <p class="text-gray-600 text-sm mt-1">Share your experience with Dr. <span x-text="doctorName"></span></p>
                </div>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Consultation Info -->
            <div class="bg-purple-50 p-4 rounded-lg mb-6">
                <div class="text-sm text-gray-600 space-y-1">
                    <p><strong>Reference:</strong> <span x-text="consultationRef"></span></p>
                    <p><strong>Doctor:</strong> Dr. <span x-text="doctorName"></span></p>
                </div>
            </div>

            <!-- Review Form -->
            <form @submit.prevent="submitReview()">
                <input type="hidden" x-model="consultationId" name="consultation_id">
                
                <!-- Star Rating -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold text-gray-900 mb-3">Overall Rating *</label>
                    <div class="flex justify-center space-x-2 mb-2">
                        <template x-for="i in 5" :key="i">
                            <svg 
                                class="w-12 h-12 cursor-pointer transition-all hover:scale-110" 
                                :class="i <= selectedRating ? 'text-yellow-400' : 'text-gray-300'"
                                @click="selectedRating = i"
                                fill="currentColor" 
                                viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </template>
                    </div>
                    <p class="text-center text-gray-500 text-sm" x-text="selectedRating > 0 ? ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'][selectedRating] : 'Click on the stars to rate'"></p>
                </div>

                <!-- Comment -->
                <div class="mb-6">
                    <label for="comment" class="block text-lg font-semibold text-gray-900 mb-2">
                        Tell us about your experience
                    </label>
                    <textarea 
                        x-model="comment"
                        id="comment" 
                        name="comment" 
                        rows="5"
                        placeholder="Share your thoughts about the consultation, doctor's professionalism, and overall experience..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 resize-none"
                    ></textarea>
                </div>

                <!-- Would Recommend -->
                <div class="flex items-center mb-6">
                    <input type="checkbox" 
                           x-model="wouldRecommend"
                           id="would_recommend" 
                           name="would_recommend" 
                           value="1" 
                           class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                    <label for="would_recommend" class="ml-3 text-gray-700 font-medium">
                        I would recommend this doctor to others
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-3">
                    <button type="submit" 
                            class="flex-1 purple-gradient text-white font-bold py-3 px-6 rounded-lg hover:opacity-90 transition-all">
                        Submit Review
                    </button>
                    <button type="button" 
                            @click="showModal = false"
                            class="px-6 py-3 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function reviewModal() {
        return {
            showModal: false,
            consultationId: null,
            consultationRef: '',
            doctorName: '',
            selectedRating: 0,
            comment: '',
            wouldRecommend: true,
            
            open(consultationId, consultationRef, doctorName) {
                this.consultationId = consultationId;
                this.consultationRef = consultationRef;
                this.doctorName = doctorName;
                this.selectedRating = 0;
                this.comment = '';
                this.wouldRecommend = true;
                this.showModal = true;
            },
            
            submitReview() {
                if (!this.selectedRating) {
                    CustomAlert.warning('Please select a rating');
                    return;
                }

                const formData = {
                    consultation_id: this.consultationId,
                    rating: this.selectedRating,
                    comment: this.comment,
                    would_recommend: this.wouldRecommend
                };

                fetch('{{ route("reviews.patient.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        CustomAlert.success(data.message || 'Thank you for your feedback! Your review has been submitted successfully.');
                        this.showModal = false;
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        CustomAlert.error(data.message || 'Failed to submit review');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    CustomAlert.error('An error occurred. Please try again.');
                });
            }
        };
    }

    function openReviewModal(consultationId, consultationRef, doctorName) {
        // Find the Alpine component
        const modalElement = document.querySelector('[x-data*="reviewModal"]');
        if (modalElement && window.Alpine) {
            const component = Alpine.$data(modalElement);
            component.open(consultationId, consultationRef, doctorName);
        }
    }
</script>
@endpush
@endsection
