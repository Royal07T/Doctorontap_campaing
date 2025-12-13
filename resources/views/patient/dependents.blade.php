@extends('layouts.patient')

@section('title', 'My Dependents')

@section('content')
@if($dependents->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($dependents as $dependent)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow p-6">
                    <!-- Avatar -->
                    <div class="flex items-center mb-4">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                            <span class="text-2xl font-bold text-blue-600">{{ substr($dependent->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">{{ $dependent->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $dependent->age }} years old</p>
                        </div>
                    </div>

                    <!-- Information -->
                    <div class="space-y-3 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Gender:</span>
                            <span class="font-medium text-gray-800">{{ ucfirst($dependent->gender ?? 'N/A') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Email:</span>
                            <span class="font-medium text-gray-800">{{ $dependent->email ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Phone:</span>
                            <span class="font-medium text-gray-800">{{ $dependent->phone ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Consultations:</span>
                            <span class="font-medium text-gray-800">{{ $dependent->consultations->count() }}</span>
                        </div>
                    </div>

                <!-- Actions -->
                <div class="pt-4 border-t border-gray-200">
                    <a href="{{ route('patient.consultations') }}?patient={{ $dependent->id }}" 
                       class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                        View Consultations →
                    </a>
                </div>
                </div>
            @endforeach
        </div>
@else
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Dependents</h3>
        <p class="text-sm text-gray-500 mb-4">You don't have any dependents registered yet.</p>
        <a href="{{ route('consultation.index') }}" class="inline-block purple-gradient hover:opacity-90 text-white px-6 py-2 rounded-lg font-medium transition">
            Book Consultation
        </a>
    </div>
@endif
</div>
@endsection

