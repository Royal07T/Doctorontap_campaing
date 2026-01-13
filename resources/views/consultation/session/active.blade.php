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

            <!-- Join Consultation Section -->
            <div id="joinSection" class="mb-6">
                <div class="bg-gradient-to-br from-purple-50 to-white rounded-lg border-2 border-purple-200 p-8 text-center">
                    <div class="mb-6">
                        @if($consultation->consultation_mode === 'voice')
                            <svg class="w-16 h-16 mx-auto text-purple-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                            </svg>
                        @elseif($consultation->consultation_mode === 'video')
                            <svg class="w-16 h-16 mx-auto text-purple-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        @elseif($consultation->consultation_mode === 'chat')
                            <svg class="w-16 h-16 mx-auto text-purple-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        @endif
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Ready to Join Consultation</h2>
                    <p class="text-gray-600 mb-6">Click the button below to join the {{ $consultation->consultation_mode === 'voice' ? 'voice call' : ($consultation->consultation_mode === 'video' ? 'video call' : 'chat session') }}.</p>
                    <button 
                        id="joinButton"
                        onclick="joinConsultation()" 
                        class="px-8 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-semibold text-lg shadow-lg hover:shadow-xl transform hover:scale-105"
                    >
                        Join Consultation
                    </button>
                </div>
            </div>

            <!-- Loading State (hidden by default) -->
            <div id="loadingState" class="hidden mb-6">
                <div class="bg-blue-50 rounded-lg border border-blue-200 p-8 text-center">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                    <p class="text-gray-700 font-medium">Connecting to consultation...</p>
                    <p class="text-sm text-gray-500 mt-2">Please wait while we establish the connection.</p>
                </div>
            </div>

            <!-- Error States (hidden by default) -->
            <div id="error401" class="hidden mb-6 p-4 bg-red-50 rounded-lg border border-red-200">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-600 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-red-800 mb-1">Unauthorized Access</p>
                        <p class="text-sm text-red-700 mb-2">You are not authorized to join this consultation. Please contact support if you believe this is an error.</p>
                        <a href="{{ route(auth()->guard('doctor')->check() ? 'doctor.consultations.view' : 'patient.consultation.view', $consultation->id) }}" 
                           class="text-sm text-red-800 underline hover:text-red-900">Return to Consultation Details</a>
                    </div>
                </div>
            </div>

            <div id="error403" class="hidden mb-6 p-4 bg-red-50 rounded-lg border border-red-200">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-600 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-red-800 mb-1">Access Denied</p>
                        <p class="text-sm text-red-700 mb-2">You do not have permission to join this consultation session.</p>
                        <a href="{{ route(auth()->guard('doctor')->check() ? 'doctor.consultations.view' : 'patient.consultation.view', $consultation->id) }}" 
                           class="text-sm text-red-800 underline hover:text-red-900">Return to Consultation Details</a>
                    </div>
                </div>
            </div>

            <div id="error429" class="hidden mb-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-yellow-600 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-yellow-800 mb-1">Too Many Requests</p>
                        <p class="text-sm text-yellow-700 mb-2">You have made too many requests. Please wait a moment and try again.</p>
                        <button onclick="joinConsultation()" class="mt-2 text-sm text-yellow-800 underline hover:text-yellow-900">Try Again</button>
                    </div>
                </div>
            </div>

            <div id="error503" class="hidden mb-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-yellow-600 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-yellow-800 mb-1">In-App Consultation Temporarily Unavailable</p>
                        <p class="text-sm text-yellow-700 mb-2">The consultation service is currently unavailable. Please contact support or use WhatsApp consultation.</p>
                        <a href="{{ route(auth()->guard('doctor')->check() ? 'doctor.consultations.view' : 'patient.consultation.view', $consultation->id) }}" 
                           class="text-sm text-yellow-800 underline hover:text-yellow-900">Return to Consultation Details</a>
                    </div>
                </div>
            </div>

            <div id="errorGeneric" class="hidden mb-6 p-4 bg-red-50 rounded-lg border border-red-200">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-600 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-red-800 mb-1">Connection Error</p>
                        <p id="genericErrorMessage" class="text-sm text-red-700 mb-2"></p>
                        <button onclick="joinConsultation()" class="mt-2 text-sm text-red-800 underline hover:text-red-900">Try Again</button>
                    </div>
                </div>
            </div>

            <!-- Connected State (hidden by default) -->
            <div id="connectedState" class="hidden mb-6">
                <div id="vonage-container" class="bg-gray-100 rounded-lg p-8 text-center">
                    <p class="text-gray-600">Vonage SDK integration will be initialized here</p>
                    <p class="text-sm text-gray-500 mt-2">Session ID: <span id="session-id">--</span></p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div id="actionButtons" class="flex justify-end space-x-4 hidden">
                <a href="{{ route(auth()->guard('doctor')->check() ? 'doctor.consultations.view' : 'patient.consultation.view', $consultation->id) }}" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Return to Consultation
                </a>
                <button 
                    id="endSessionButton"
                    onclick="endSession()" 
                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                >
                    End Consultation
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal (Patient Only) -->
@if(auth()->guard('patient')->check())
<div id="reviewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden" style="display: none;">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 text-center transform transition-all scale-100">
        <div class="mb-4">
            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-800 mb-2">Consultation Completed</h2>
            <p class="text-gray-600 mb-6">How was your experience? Please take a moment to rate us on Google.</p>
        </div>
        
        <div class="space-y-3">
            <a href="https://g.page/r/CUgGQ-i_PAOUEAI/review" 
               target="_blank" 
               onclick="redirectToDashboard(true)"
               class="block w-full py-3 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg transition transform hover:scale-105 shadow-md no-underline">
                ⭐⭐⭐⭐⭐ Rate Us on Google
            </a>
            
            <button onclick="redirectToDashboard(false)" 
                    class="block w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition">
                Skip & Go to Dashboard
            </button>
        </div>
    </div>
</div>
@endif

<script>
(function() {
    'use strict';
    
    const isPatient = {{ auth()->guard('patient')->check() ? 'true' : 'false' }};
    const dashboardUrl = '{{ auth()->guard("patient")->check() ? route("patient.dashboard") : route("doctor.dashboard") }}';
    const checkStatusUrl = '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.session.status" : "patient.consultations.session.status", $consultation->id) }}';
    const tokenUrl = '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.session.token" : "patient.consultations.session.token", $consultation->id) }}';
    const endSessionUrl = '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.session.end" : "patient.consultations.session.end", $consultation->id) }}';
    const consultationDetailsUrl = '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.view" : "patient.consultation.view", $consultation->id) }}';
    
    let isConnected = false;
    let statusPollInterval = null;
    
    // Hide all error states
    function hideAllErrors() {
        ['error401', 'error403', 'error429', 'error503', 'errorGeneric'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        });
    }
    
    // Show specific error state
    function showError(errorType, message) {
        hideAllErrors();
        
        if (errorType === '401') {
            document.getElementById('error401').classList.remove('hidden');
        } else if (errorType === '403') {
            document.getElementById('error403').classList.remove('hidden');
        } else if (errorType === '429') {
            document.getElementById('error429').classList.remove('hidden');
        } else if (errorType === '503') {
            document.getElementById('error503').classList.remove('hidden');
        } else {
            const genericError = document.getElementById('errorGeneric');
            const genericMessage = document.getElementById('genericErrorMessage');
            if (genericError) genericError.classList.remove('hidden');
            if (genericMessage) genericMessage.textContent = message || 'An unexpected error occurred. Please try again.';
        }
        
        // Show join section again
        document.getElementById('joinSection').classList.remove('hidden');
        document.getElementById('loadingState').classList.add('hidden');
        document.getElementById('connectedState').classList.add('hidden');
        document.getElementById('actionButtons').classList.add('hidden');
    }
    
    // Join consultation - fetch token and initialize
    window.joinConsultation = async function() {
        // Hide join section and show loading
        document.getElementById('joinSection').classList.add('hidden');
        document.getElementById('loadingState').classList.remove('hidden');
        hideAllErrors();
        
        try {
            // Fetch token via POST (secure)
            const response = await fetch(tokenUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            });
            
            const data = await response.json();
            
            // Handle different HTTP status codes
            if (response.status === 401) {
                showError('401', 'Unauthorized access');
                return;
            }
            
            if (response.status === 403) {
                showError('403', 'Access denied');
                return;
            }
            
            if (response.status === 429) {
                showError('429', 'Too many requests');
                return;
            }
            
            if (response.status === 503) {
                showError('503', 'Service unavailable');
                return;
            }
            
            if (!response.ok) {
                showError('generic', data.message || `Server error (${response.status})`);
                return;
            }
            
            if (!data.success) {
                if (data.message && data.message.toLowerCase().includes('vonage') && data.message.toLowerCase().includes('disabled')) {
                    showError('503', data.message);
                } else {
                    showError('generic', data.message || 'Failed to join consultation');
                }
                return;
            }
            
            // Success - hide loading, show connected state
            document.getElementById('loadingState').classList.add('hidden');
            document.getElementById('connectedState').classList.remove('hidden');
            document.getElementById('actionButtons').classList.remove('hidden');
            
            // Update session ID display
            const sessionIdEl = document.getElementById('session-id');
            if (sessionIdEl && data.session_id) {
                sessionIdEl.textContent = data.session_id;
            }
            
            isConnected = true;
            
            // Initialize Vonage SDK here (placeholder)
            const mode = '{{ $consultation->consultation_mode }}';
            console.log('Vonage SDK initialization would happen here');
            console.log('Mode:', mode);
            console.log('Session ID:', data.session_id);
            // TODO: Initialize Vonage Video SDK, Conversations SDK, or Voice SDK based on mode
            // DO NOT log tokens or sensitive data
            
            // Start polling for session status
            startStatusPolling();
            
        } catch (error) {
            console.error('Error joining consultation:', error);
            showError('generic', 'Network error. Please check your connection and try again.');
        }
    };
    
    // End session
    window.endSession = async function() {
        if (!confirm('Are you sure you want to end this consultation?')) {
            return;
        }
        
        const endButton = document.getElementById('endSessionButton');
        if (endButton) {
            endButton.disabled = true;
            endButton.textContent = 'Ending...';
        }
        
        try {
            const response = await fetch(endSessionUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                if (isPatient) {
                    showReviewModal();
                } else {
                    window.location.href = dashboardUrl;
                }
            } else {
                alert('Failed to end session: ' + (data.message || 'Unknown error'));
                if (endButton) {
                    endButton.disabled = false;
                    endButton.textContent = 'End Consultation';
                }
            }
        } catch (error) {
            console.error('Error ending session:', error);
            alert('An error occurred while ending the session');
            if (endButton) {
                endButton.disabled = false;
                endButton.textContent = 'End Consultation';
            }
        }
    };
    
    // Start polling for session status
    function startStatusPolling() {
        if (statusPollInterval) return; // Already polling
        
        statusPollInterval = setInterval(() => {
            if (!isConnected) return;
            
            fetch(checkStatusUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                cache: 'no-cache'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const status = data.session_status || data.consultation_status;
                    
                    if (status === 'ended' || status === 'completed') {
                        clearInterval(statusPollInterval);
                        statusPollInterval = null;
                        
                        if (isPatient) {
                            showReviewModal();
                        } else {
                            window.location.href = consultationDetailsUrl;
                        }
                    } else if (status === 'cancelled') {
                        clearInterval(statusPollInterval);
                        statusPollInterval = null;
                        window.location.href = consultationDetailsUrl;
                    }
                }
            })
            .catch(error => {
                console.error('Error checking session status:', error);
                // Don't show error to user, just log it
            });
        }, 5000);
    }
    
    // Review modal functions
    function showReviewModal() {
        const modal = document.getElementById('reviewModal');
        if (modal && isPatient) {
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
        } else {
            window.location.href = dashboardUrl;
        }
    }
    
    window.redirectToDashboard = function(delayed) {
        if (delayed) {
            setTimeout(() => {
                window.location.href = dashboardUrl;
            }, 1000);
        } else {
            window.location.href = dashboardUrl;
        }
    };
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (statusPollInterval) {
            clearInterval(statusPollInterval);
        }
    });
})();
</script>
@endsection

