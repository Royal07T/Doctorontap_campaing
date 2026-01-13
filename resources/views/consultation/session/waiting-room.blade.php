@extends('layouts.app')

@section('title', 'Waiting Room - Consultation')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Waiting Room</h1>
            
            <div class="mb-6">
                <p class="text-gray-600 mb-2">
                    <strong>Consultation Reference:</strong> {{ $consultation->reference }}
                </p>
                <p class="text-gray-600 mb-2">
                    <strong>Mode:</strong> 
                    <span class="capitalize">{{ $consultation->consultation_mode }}</span>
                </p>
                @if($consultation->doctor)
                <p class="text-gray-600 mb-2">
                    <strong>Doctor:</strong> Dr. {{ $consultation->doctor->name }}
                </p>
                @endif
            </div>

            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600 mb-4"></div>
                <p class="text-gray-600">Waiting for the consultation to start...</p>
            </div>

            <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-blue-800">
                    <strong>Note:</strong> This is a placeholder view. The actual waiting room UI will be implemented with Vonage SDK integration.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Placeholder: Auto-refresh session status
// In production, this will use WebSockets or polling to check session status
setInterval(function() {
    fetch('{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.session.status" : "patient.consultations.session.status", $consultation->id) }}')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.session_status === 'active') {
                // Redirect to active consultation view
                window.location.href = '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.session.active" : "patient.consultations.session.active", $consultation->id) }}';
            }
        })
        .catch(error => console.error('Error checking session status:', error));
}, 5000); // Check every 5 seconds
</script>
@endsection

