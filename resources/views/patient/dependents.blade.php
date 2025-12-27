@extends('layouts.patient')

@section('title', 'My Dependents')

@section('content')
@if($dependents->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($dependents as $dependent)
            <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all overflow-hidden">
                <!-- Card Header -->
                <button @click="open = !open" class="w-full text-left focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                    <div class="p-5">
                        <div class="flex items-center mb-3">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                <span class="text-lg font-bold text-blue-600">{{ substr($dependent->name, 0, 1) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $dependent->name }}</h3>
                                <p class="text-xs text-gray-500">{{ $dependent->age }} years old</p>
                            </div>
                            <div class="flex-shrink-0 ml-3">
                                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" 
                                     :class="{ 'rotate-180': open }" 
                                     fill="none" 
                                     stroke="currentColor" 
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                        </div>
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
                    <div class="p-5 space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Gender</p>
                                <p class="text-xs text-gray-900">{{ ucfirst($dependent->gender ?? 'N/A') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Consultations</p>
                                <p class="text-xs text-gray-900 font-semibold">{{ $dependent->consultations->count() }}</p>
                            </div>
                            @if($dependent->email)
                            <div class="col-span-2">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Email</p>
                                <p class="text-xs text-gray-900">{{ $dependent->email }}</p>
                        </div>
                            @endif
                            @if($dependent->phone)
                            <div class="col-span-2">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Phone</p>
                                <p class="text-xs text-gray-900">{{ $dependent->phone }}</p>
                        </div>
                            @endif
                        </div>

                        <!-- Action Button -->
                        <div class="pt-3 border-t border-gray-200">
                            <a href="{{ route('patient.consultations') }}?patient={{ $dependent->id }}" 
                               class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-white purple-gradient rounded-lg hover:opacity-90 transition w-full justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View Consultations
                            </a>
                        </div>
                    </div>
                </div>
                </div>
            @endforeach
        </div>
@else
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <h3 class="text-sm font-semibold text-gray-900 mb-2">No Dependents</h3>
        <p class="text-xs text-gray-500 mb-4">You don't have any dependents registered yet.</p>
        <a href="{{ route('consultation.index') }}" class="inline-block purple-gradient hover:opacity-90 text-white px-5 py-2.5 text-xs font-medium rounded-lg transition">
            Book Consultation
        </a>
    </div>
@endif
</div>
@endsection

