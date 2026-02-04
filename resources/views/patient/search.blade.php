@extends('layouts.patient')

@section('title', 'Search Results')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Search Header -->
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-gray-900 mb-2">Search Results</h2>
        <form action="{{ route('patient.search') }}" method="GET" class="flex gap-2">
            <input type="text" name="q" value="{{ $query }}" placeholder="Search records, doctors..." class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium">
                Search
            </button>
        </form>
        @if($query)
            <p class="text-gray-600 mt-2">Found results for: <strong>"{{ $query }}"</strong></p>
        @endif
    </div>

    @if($query)
        <!-- Consultations Results -->
        @if($results['consultations']->isNotEmpty())
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Consultations</h3>
            <div class="space-y-3">
                @foreach($results['consultations'] as $consultation)
                <a href="{{ route('patient.consultation.view', $consultation->id) }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-900">
                                @if($consultation->doctor)
                                    Dr. {{ $consultation->doctor->name }}
                                @else
                                    Consultation #{{ $consultation->reference }}
                                @endif
                            </p>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ $consultation->problem ?? 'No description' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $consultation->created_at->format('M d, Y') }}
                            </p>
                        </div>
                        <span class="px-3 py-1 text-xs font-medium rounded-full
                            @if($consultation->status === 'completed') bg-green-100 text-green-700
                            @elseif($consultation->status === 'pending') bg-yellow-100 text-yellow-700
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ ucfirst($consultation->status) }}
                        </span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Doctors Results -->
        @if($results['doctors']->isNotEmpty())
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Doctors</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($results['doctors'] as $doctor)
                <a href="{{ route('patient.doctors', ['search' => $query]) }}" class="flex items-center gap-4 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="w-16 h-16 rounded-lg bg-gray-200 overflow-hidden flex-shrink-0">
                        @if($doctor->photo_url)
                            <img src="{{ $doctor->photo_url }}" class="w-full h-full object-cover" alt="{{ $doctor->name }}">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white font-bold text-xl">
                                {{ substr($doctor->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900">{{ $doctor->name }}</p>
                        <p class="text-sm text-gray-600">{{ $doctor->specialization ?? 'General Physician' }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $doctor->is_available ? 'Online' : 'Offline' }}
                        </p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Medical Records Results -->
        @if($results['medical_records']->isNotEmpty())
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Medical Records</h3>
            <div class="space-y-3">
                @foreach($results['medical_records'] as $record)
                <a href="{{ route('patient.medical-records') }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900">
                                {{ $record->diagnosis ?? 'Medical Record' }}
                            </p>
                            @if($record->treatment_plan)
                            <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                {{ Str::limit($record->treatment_plan, 100) }}
                            </p>
                            @endif
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $record->consultation_date->format('M d, Y') }}
                            </p>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- No Results -->
        @if($results['consultations']->isEmpty() && $results['doctors']->isEmpty() && $results['medical_records']->isEmpty())
        <div class="bg-white rounded-2xl p-12 shadow-lg border border-gray-200 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <h3 class="text-xl font-bold text-gray-900 mb-2">No results found</h3>
            <p class="text-gray-600">Try searching with different keywords</p>
        </div>
        @endif
    @else
        <div class="bg-white rounded-2xl p-12 shadow-lg border border-gray-200 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Start searching</h3>
            <p class="text-gray-600">Enter a search term to find consultations, doctors, or medical records</p>
        </div>
    @endif
</div>
@endsection

