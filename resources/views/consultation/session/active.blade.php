@extends('layouts.app')

@section('title', 'Active Consultation')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Active Consultation</h1>
            
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

            <div class="mb-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                <p class="text-sm text-yellow-800">
                    <strong>Placeholder View:</strong> This is a placeholder for the active consultation interface.
                </p>
                <p class="text-sm text-yellow-800 mt-2">
                    In production, this view will integrate with Vonage SDK to provide:
                </p>
                <ul class="text-sm text-yellow-800 mt-2 list-disc list-inside">
                    @if($consultation->consultation_mode === 'video')
                    <li>Video call interface with camera/microphone controls</li>
                    <li>Screen sharing capabilities</li>
                    <li>Chat sidebar</li>
                    @elseif($consultation->consultation_mode === 'voice')
                    <li>Audio call interface with microphone controls</li>
                    <li>Call quality indicators</li>
                    @elseif($consultation->consultation_mode === 'chat')
                    <li>Real-time chat interface</li>
                    <li>Message history</li>
                    <li>File sharing</li>
                    @endif
                </ul>
            </div>

            <div id="vonage-container" class="mb-6">
                <!-- Vonage SDK will be initialized here -->
                <div class="bg-gray-100 rounded-lg p-8 text-center">
                    <p class="text-gray-600">Vonage SDK integration will be implemented here</p>
                    <p class="text-sm text-gray-500 mt-2">Session ID: <span id="session-id">{{ $sessionData['session_id'] ?? 'N/A' }}</span></p>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <button 
                    onclick="endSession()" 
                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                >
                    End Consultation
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Placeholder: Session management functions
// In production, these will use Vonage SDK

async function endSession() {
    if (confirm('Are you sure you want to end this consultation?')) {
        try {
            const response = await fetch('{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.session.end" : "patient.consultations.session.end", $consultation->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                window.location.href = '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.view" : "patient.consultation.view", $consultation->id) }}';
            } else {
                alert('Failed to end session: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error ending session:', error);
            alert('An error occurred while ending the session');
        }
    }
}

// Placeholder: Initialize Vonage SDK
// In production, this will initialize the appropriate Vonage SDK based on consultation mode
document.addEventListener('DOMContentLoaded', function() {
    const mode = '{{ $consultation->consultation_mode }}';
    const token = '{{ $sessionData["token"] ?? "" }}';
    const sessionId = '{{ $sessionData["session_id"] ?? "" }}';
    
    if (token && sessionId) {
        console.log('Vonage SDK initialization would happen here');
        console.log('Mode:', mode);
        console.log('Session ID:', sessionId);
        // TODO: Initialize Vonage Video SDK, Conversations SDK, or Voice SDK based on mode
    }
});
</script>
@endsection

