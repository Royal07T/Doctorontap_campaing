@extends(auth()->guard('doctor')->check() ? 'layouts.doctor' : 'layouts.patient')

@section('title', 'Waiting Room - Consultation')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Waiting Room</h1>
            
            <!-- Consultation Info -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Consultation Reference</p>
                        <p class="text-sm font-medium text-gray-900">{{ $consultation->reference }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Consultation Mode</p>
                        <p class="text-sm font-medium text-gray-900 capitalize">
                            @if($consultation->consultation_mode === 'voice')
                                🎤 Voice Call
                            @elseif($consultation->consultation_mode === 'video')
                                🎥 Video Call
                            @elseif($consultation->consultation_mode === 'chat')
                                💬 Chat
                            @else
                                {{ $consultation->consultation_mode }}
                            @endif
                        </p>
                    </div>
                    @if($consultation->scheduled_at)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Scheduled Time</p>
                        <p class="text-sm font-medium text-gray-900">{{ $consultation->scheduled_at->format('M d, Y h:i A') }}</p>
                    </div>
                    @endif
                    @if($consultation->doctor)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Doctor</p>
                        <p class="text-sm font-medium text-gray-900">Dr. {{ $consultation->doctor->name }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Status Display -->
            <div id="waitingRoomStatus" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600 mb-4"></div>
                <p id="statusMessage" class="text-gray-600 font-medium">Waiting for the consultation to start...</p>
                <p id="statusSubMessage" class="text-sm text-gray-500 mt-2"></p>
            </div>

            <!-- Countdown (if scheduled) -->
            @if($consultation->scheduled_at && $consultation->scheduled_at->isFuture())
            <div id="countdownContainer" class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200 text-center">
                <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide mb-2">Consultation Starts In</p>
                <p id="countdown" class="text-2xl font-bold text-blue-900">--:--</p>
            </div>
            @endif

            <!-- Error State (hidden by default) -->
            <div id="errorState" class="hidden mb-6 p-4 bg-red-50 rounded-lg border border-red-200">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-600 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-red-800 mb-1">Unable to Check Session Status</p>
                        <p id="errorMessage" class="text-sm text-red-700"></p>
                        <button onclick="location.reload()" class="mt-2 text-sm text-red-800 underline hover:text-red-900">Refresh Page</button>
                    </div>
                </div>
            </div>

            <!-- Degraded State (Vonage Disabled) -->
            <div id="degradedState" class="hidden mb-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
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

            <!-- Join Button (hidden by default, shown when ready) -->
            <div id="joinButtonContainer" class="hidden mb-4">
                <div class="text-center">
                    <a href="{{ route(auth()->guard('doctor')->check() ? 'doctor.consultations.session.active' : 'patient.consultations.session.active', $consultation->id) }}" 
                       class="inline-flex items-center px-8 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-semibold text-lg shadow-lg hover:shadow-xl transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Join Consultation Now
                    </a>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-center gap-4 mt-6">
                <a href="{{ route(auth()->guard('doctor')->check() ? 'doctor.consultations.view' : 'patient.consultation.view', $consultation->id) }}" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Return to Consultation
                </a>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    
    const consultationId = {{ $consultation->id }};
    const isDoctor = {{ auth()->guard('doctor')->check() ? 'true' : 'false' }};
    const statusUrl = '{{ $consultation->consultation_mode === 'video' ? route(auth()->guard('doctor')->check() ? 'doctor.consultations.video.status' : 'patient.consultations.video.status', $consultation->id) : route(auth()->guard('doctor')->check() ? 'doctor.consultations.session.status' : 'patient.consultations.session.status', $consultation->id) }}';
    const activeUrl = '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.session.active" : "patient.consultations.session.active", $consultation->id) }}';
    const scheduledAt = @json($consultation->scheduled_at ? $consultation->scheduled_at->toIso8601String() : null);
    
    let pollInterval = null;
    let consecutiveErrors = 0;
    const MAX_CONSECUTIVE_ERRORS = 3;
    
    // Countdown timer
    let countdownInterval = null;
    if (scheduledAt) {
        const startTime = new Date(scheduledAt).getTime();
        countdownInterval = setInterval(function() {
            const now = new Date().getTime();
            const distance = startTime - now;
            
            if (distance < 0) {
                clearInterval(countdownInterval);
                const countdownContainer = document.getElementById('countdownContainer');
                if (countdownContainer) {
                    countdownContainer.classList.add('hidden');
                }
                // When countdown reaches zero, check status and show join button if ready
                checkSessionStatus();
            } else {
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                const countdownEl = document.getElementById('countdown');
                if (countdownEl) {
                    countdownEl.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                }
            }
        }, 1000);
    }
    
    // Update status display
    function updateStatusDisplay(status, message, subMessage) {
        const statusMessage = document.getElementById('statusMessage');
        const statusSubMessage = document.getElementById('statusSubMessage');
        
        if (statusMessage) {
            statusMessage.textContent = message;
        }
        if (statusSubMessage) {
            statusSubMessage.textContent = subMessage || '';
        }
    }
    
    // Show error state
    function showError(message) {
        const errorState = document.getElementById('errorState');
        const errorMessage = document.getElementById('errorMessage');
        const waitingRoomStatus = document.getElementById('waitingRoomStatus');
        
        if (errorState) {
            errorState.classList.remove('hidden');
        }
        if (errorMessage) {
            errorMessage.textContent = message || 'An error occurred while checking session status.';
        }
        if (waitingRoomStatus) {
            waitingRoomStatus.classList.add('hidden');
        }
    }
    
    // Hide error state
    function hideError() {
        const errorState = document.getElementById('errorState');
        const waitingRoomStatus = document.getElementById('waitingRoomStatus');
        
        if (errorState) {
            errorState.classList.add('hidden');
        }
        if (waitingRoomStatus) {
            waitingRoomStatus.classList.remove('hidden');
        }
        consecutiveErrors = 0;
    }
    
    // Show degraded state (Vonage disabled)
    function showDegradedState() {
        const degradedState = document.getElementById('degradedState');
        const waitingRoomStatus = document.getElementById('waitingRoomStatus');
        
        if (degradedState) {
            degradedState.classList.remove('hidden');
        }
        if (waitingRoomStatus) {
            waitingRoomStatus.classList.add('hidden');
        }
        
        // Stop polling
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }
    
    // Show join button
    function showJoinButton() {
        const joinButtonContainer = document.getElementById('joinButtonContainer');
        if (joinButtonContainer) {
            joinButtonContainer.classList.remove('hidden');
        }
    }
    
    // Hide join button
    function hideJoinButton() {
        const joinButtonContainer = document.getElementById('joinButtonContainer');
        if (joinButtonContainer) {
            joinButtonContainer.classList.add('hidden');
        }
    }
    
    // Check session status
    function checkSessionStatus() {
        fetch(statusUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            cache: 'no-cache'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            consecutiveErrors = 0;
            hideError();
            
            if (!data.success) {
                if (data.message && data.message.toLowerCase().includes('vonage') && data.message.toLowerCase().includes('disabled')) {
                    showDegradedState();
                    return;
                }
                updateStatusDisplay('error', 'Unable to check session status', data.message || '');
                return;
            }
            
            const sessionStatus = data.session_status || data.consultation_status || 'unknown';
            
            // Check if scheduled time has passed (client-side check)
            const now = new Date().getTime();
            const scheduledTime = scheduledAt ? new Date(scheduledAt).getTime() : null;
            const timeHasPassed = scheduledTime && now >= scheduledTime;
            
            // Handle different states
            switch(sessionStatus) {
                case 'scheduled':
                    // If scheduled time has passed, allow joining
                    if (timeHasPassed) {
                        updateStatusDisplay('waiting', 'Consultation is ready', 'You can now join the consultation.');
                        showJoinButton();
                    } else {
                        updateStatusDisplay('scheduled', 'Waiting for start time', isDoctor ? 'Patient will join when consultation starts' : 'Doctor will join when consultation starts');
                        hideJoinButton();
                    }
                    break;
                    
                case 'waiting':
                    // Show appropriate message based on user type
                    const waitingMessage = isDoctor 
                        ? 'Waiting for patient to join...' 
                        : 'Waiting for doctor to join...';
                    updateStatusDisplay('waiting', 'Waiting for other participant', waitingMessage);
                    showJoinButton();
                    break;
                    
                case 'active':
                    // Redirect to active consultation
                    clearInterval(pollInterval);
                    if (countdownInterval) clearInterval(countdownInterval);
                    window.location.href = activeUrl;
                    return;
                    
                case 'completed':
                case 'ended':
                    updateStatusDisplay('ended', 'Consultation has ended', 'Redirecting to consultation details...');
                    clearInterval(pollInterval);
                    if (countdownInterval) clearInterval(countdownInterval);
                    setTimeout(() => {
                        window.location.href = '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.view" : "patient.consultation.view", $consultation->id) }}';
                    }, 2000);
                    return;
                    
                case 'cancelled':
                    updateStatusDisplay('cancelled', 'Consultation has been cancelled', 'Redirecting to consultation details...');
                    clearInterval(pollInterval);
                    if (countdownInterval) clearInterval(countdownInterval);
                    setTimeout(() => {
                        window.location.href = '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.view" : "patient.consultation.view", $consultation->id) }}';
                    }, 2000);
                    return;
                    
                default:
                    updateStatusDisplay('unknown', 'Checking session status...', '');
            }
        })
        .catch(error => {
            console.error('Error checking session status:', error);
            consecutiveErrors++;
            
            if (consecutiveErrors >= MAX_CONSECUTIVE_ERRORS) {
                showError('Unable to connect to the server. Please check your internet connection and try refreshing the page.');
                clearInterval(pollInterval);
            } else {
                updateStatusDisplay('error', 'Checking session status...', 'Connection issue, retrying...');
            }
        });
    }
    
            // Start polling
            checkSessionStatus(); // Initial check
            // Poll every 15 seconds (reduced from 5 to avoid security alerts)
            // Both doctor and patient use the same polling interval
            pollInterval = setInterval(checkSessionStatus, 15000);
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (pollInterval) clearInterval(pollInterval);
        if (countdownInterval) clearInterval(countdownInterval);
    });
})();
</script>
@endsection

