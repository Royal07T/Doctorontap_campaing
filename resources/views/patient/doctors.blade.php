@extends('layouts.patient')

@section('title', 'Find a Doctor')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-1">
                <a href="{{ route('patient.dashboard') }}" class="hover:text-purple-600">Home</a>
                <span class="mx-2">/</span>
                <span class="text-purple-600">Find Doctors</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Find Your Specialist</h1>
            <p class="text-gray-500 text-sm mt-1">Book appointments with top-rated doctors across various specialties.</p>
        </div>
        
        <!-- Search & Filter Actions -->
        <div class="w-full md:w-auto">
             <form method="GET" action="{{ route('patient.doctors') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="relative min-w-[200px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                       <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                       </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search doctor or specialty..." class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-shadow">
                </div>
                
                <div class="relative min-w-[200px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                       <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                       </svg>
                    </div>
                    <select name="specialization" class="w-full pl-10 pr-8 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-shadow appearance-none">
                        <option value="">All Specializations</option>
                        @foreach($specializations as $spec)
                            <option value="{{ $spec }}" {{ request('specialization') == $spec ? 'selected' : '' }}>
                                {{ $spec }}
                            </option>
                        @endforeach
                    </select>
                     <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                       <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                       </svg>
                    </div>
                </div>
                
                <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm shadow-purple-200">
                    Search
                </button>
             </form>
        </div>
    </div>

    <!-- Results Grid -->
    @if($doctors->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($doctors as $doctor)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col group h-full">
                    <!-- Card Header -->
                    <div class="p-5 flex items-start justify-between relative">
                        <div class="relative">
                            <div class="w-16 h-16 rounded-2xl bg-gray-50 p-1 border border-gray-100">
                                @if($doctor->photo_url)
                                    <img src="{{ $doctor->photo_url }}" alt="{{ $doctor->name }}" class="w-full h-full object-cover rounded-xl">
                                @else
                                    <div class="w-full h-full bg-purple-100 rounded-xl flex items-center justify-center text-purple-600 font-bold text-xl">
                                        {{ substr($doctor->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <!-- Online Indicator -->
                            @if($doctor->is_available)
                                <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white rounded-full" title="Available Now"></span>
                            @else
                                <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-gray-300 border-2 border-white rounded-full" title="Offline"></span>
                            @endif
                        </div>
                        
                         <!-- Rating Badge -->
                        @php
                            $avgRating = $doctor->average_rating ?? 0;
                            $reviewsCount = $doctor->published_reviews_count ?? 0;
                        @endphp
                         <div class="flex flex-col items-end gap-1">
                            <div class="flex items-center gap-1 bg-yellow-50 px-2 py-1 rounded-lg">
                                <svg class="w-3.5 h-3.5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <span class="text-xs font-bold text-gray-700">{{ number_format($avgRating, 1) }}</span>
                                <span class="text-[10px] text-gray-400">({{ $reviewsCount }})</span>
                            </div>
                            @php
                                // Get consultation fee - always use doctor-set prices
                                // For fee ranges, show only the minimum fee
                                if ($doctor->min_consultation_fee && $doctor->max_consultation_fee) {
                                    $feeDisplay = '₦' . number_format($doctor->min_consultation_fee, 0);
                                } elseif ($doctor->consultation_fee) {
                                    $feeDisplay = '₦' . number_format($doctor->consultation_fee, 0);
                                } else {
                                    $feeDisplay = 'Contact for pricing';
                                }
                            @endphp
                            <div class="text-sm font-bold text-purple-600">
                                {{ $feeDisplay }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Card Body -->
                    <div class="px-5 flex-1">
                        <h3 class="text-base font-bold text-gray-900 line-clamp-1 mb-0.5">{{ $doctor->name }}</h3>
                        <p class="text-sm text-purple-600 font-medium mb-3 line-clamp-1">{{ $doctor->specialization ?? 'General Practitioner' }}</p>
                        
                         <!-- Meta Info -->
                         <div class="space-y-2 mb-4">
                            @if($doctor->experience)
                            <div class="flex items-center text-xs text-gray-500">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                {{ $doctor->experience }} Experience
                            </div>
                            @endif
                            @if($doctor->location)
                            <div class="flex items-center text-xs text-gray-500">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ $doctor->location }}
                            </div>
                            @endif
                            @if($doctor->languages)
                            <div class="flex items-center text-xs text-gray-500">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                                </svg>
                                {{ $doctor->languages }}
                            </div>
                            @endif
                         </div>
                         
                         <!-- Badges -->
                         <div class="flex flex-wrap gap-1.5 mb-3">
                            @if($doctor->can_provide_second_opinion ?? true)
                                <span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-700 text-[10px] font-semibold rounded-full">
                                    <svg class="w-2.5 h-2.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Second Opinion
                                </span>
                            @endif
                            
                            @if($doctor->is_international ?? false)
                                <span class="inline-flex items-center px-2 py-1 bg-purple-100 text-purple-700 text-[10px] font-semibold rounded-full">
                                    <svg class="w-2.5 h-2.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    International
                                </span>
                            @endif
                         </div>
                    </div>
                    
                    <!-- Card Footer -->
                    <div class="p-4 bg-gray-50 border-t border-gray-100 group-hover:bg-purple-50 transition-colors">
                        <a href="{{ route('patient.doctors.book', $doctor->id) }}" 
                           class="w-full py-2.5 bg-purple-600 border border-purple-600 text-white font-bold text-sm rounded-xl hover:bg-purple-700 hover:border-purple-700 transition-all shadow-md hover:shadow-lg text-center block">
                            Book Appointment
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pt-6">
            {{ $doctors->withQueryString()->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-20 bg-white rounded-3xl border border-gray-100">
            <div class="w-20 h-20 bg-purple-50 rounded-full flex items-center justify-center mx-auto mb-4">
                 <svg class="w-10 h-10 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">No Doctors Found</h3>
            <p class="text-gray-500 max-w-sm mx-auto mb-6">We couldn't find any doctors matching your criteria. Try adjusting your filters.</p>
            <a href="{{ route('patient.doctors') }}" class="inline-flex items-center px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors">
                Clear Filters
            </a>
        </div>
    @endif
</div>

@endsection
