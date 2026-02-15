{{-- Consultation Lifecycle Banner --}}
@props(['consultation', 'userType' => 'patient']) {{-- userType: 'patient' or 'doctor' --}}

@php
    $isInApp = in_array($consultation->consultation_mode ?? 'whatsapp', ['voice', 'video', 'chat']);
    $sessionStatus = $consultation->session_status ?? 'pending';
    $isDoctor = $userType === 'doctor';
@endphp

@if($isInApp)
<div class="mb-6">
    <div class="bg-white rounded-xl shadow-sm border-2 
        @if($sessionStatus === 'scheduled') border-blue-200 bg-blue-50
        @elseif($sessionStatus === 'waiting') border-purple-200 bg-purple-50
        @elseif($sessionStatus === 'active') border-green-200 bg-green-50
        @elseif($sessionStatus === 'completed') border-gray-200 bg-gray-50
        @elseif($sessionStatus === 'cancelled') border-red-200 bg-red-50
        @else border-gray-200 bg-gray-50
        @endif p-5">
        
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                    @if($sessionStatus === 'scheduled')
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-lg font-bold text-blue-900">Consultation Scheduled</h3>
                    @elseif($sessionStatus === 'waiting')
                        <svg class="w-6 h-6 text-purple-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-lg font-bold text-purple-900">Waiting for Consultation</h3>
                    @elseif($sessionStatus === 'active')
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-lg font-bold text-green-900">Consultation Active</h3>
                    @elseif($sessionStatus === 'completed')
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-lg font-bold text-gray-900">Consultation Completed</h3>
                    @elseif($sessionStatus === 'cancelled')
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <h3 class="text-lg font-bold text-red-900">Consultation Cancelled</h3>
                    @else
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-lg font-bold text-gray-900">Consultation Status</h3>
                    @endif
                </div>
                
                <div class="space-y-2 mb-4">
                    <div class="flex items-center gap-2 text-sm">
                        <span class="font-semibold text-gray-700">Reference:</span>
                        <span class="text-gray-900 font-mono">{{ $consultation->reference }}</span>
                    </div>
                    
                    @if($consultation->scheduled_at)
                    <div class="flex items-center gap-2 text-sm">
                        <span class="font-semibold text-gray-700">Scheduled:</span>
                        <span class="text-gray-900">{{ $consultation->scheduled_at->format('M d, Y h:i A') }}</span>
                    </div>
                    @endif
                    
                    <div class="flex items-center gap-2 text-sm">
                        <span class="font-semibold text-gray-700">Mode:</span>
                        <span class="text-gray-900 capitalize">
                            @php
                                $mode = $consultation->consultation_mode ?? $consultation->consult_mode ?? 'whatsapp';
                            @endphp
                            @if($mode === 'voice')
                                🎤 Voice Call
                            @elseif($mode === 'video')
                                🎥 Video Call
                            @elseif($mode === 'chat')
                                💬 Chat
                            @else
                                📱 WhatsApp
                            @endif
                        </span>
                        @if($isDoctor)
                            <span class="text-xs text-gray-500 italic">(Selected by Patient)</span>
                        @endif
                    </div>
                </div>
                
                {{-- Status-specific messages and actions --}}
                <div class="mt-4">
                    @if($sessionStatus === 'scheduled')
                        <p class="text-sm text-blue-800 mb-3">
                            <strong>Waiting for start time.</strong> The consultation will begin at the scheduled time.
                        </p>
                        @if($consultation->scheduled_at && $consultation->scheduled_at->isFuture())
                            <p class="text-xs text-blue-700">
                                Starts in: <span id="countdown-{{ $consultation->id }}" class="font-semibold">--:--</span>
                            </p>
                        @endif
                        
                    @elseif($sessionStatus === 'waiting')
                        <p class="text-sm text-purple-800 mb-3">
                            <strong>Waiting for other participant.</strong> 
                            @if($isDoctor)
                                Waiting for patient to join...
                            @else
                                Waiting for doctor to join...
                            @endif
                        </p>
                        <a href="{{ route($isDoctor ? 'doctor.consultations.view' : 'patient.consultation.view', $consultation->id) }}" 
                           class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-semibold">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Go to Waiting Room
                        </a>
                        
                    @elseif($sessionStatus === 'active')
                        <p class="text-sm text-green-800 mb-3">
                            <strong>Consultation is active.</strong> Click below to join the session.
                        </p>
                        <a href="{{ route($isDoctor ? 'doctor.consultations.view' : 'patient.consultation.view', $consultation->id) }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-semibold">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Join Consultation
                        </a>
                        
                    @elseif($sessionStatus === 'completed')
                        <p class="text-sm text-gray-800 mb-3">
                            <strong>Consultation has ended.</strong> You can view the treatment plan and consultation details below.
                        </p>
                        
                    @elseif($sessionStatus === 'cancelled')
                        <p class="text-sm text-red-800 mb-3">
                            <strong>Consultation has been cancelled.</strong> Please contact support if you have any questions.
                        </p>
                        
                    @else
                        <p class="text-sm text-gray-800">
                            Current status: <span class="font-semibold capitalize">{{ $sessionStatus }}</span>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($sessionStatus === 'scheduled' && $consultation->scheduled_at)
<script>
(function() {
    const consultationId = {{ $consultation->id }};
    const scheduledAt = new Date('{{ $consultation->scheduled_at->toIso8601String() }}').getTime();
    const countdownEl = document.getElementById('countdown-' + consultationId);
    
    if (!countdownEl) return;
    
    let hasReloaded = false; // Prevent multiple reloads
    let reloadTimeout = null;
    
    // Check if time has already passed on page load
    const now = new Date().getTime();
    if (scheduledAt <= now) {
        // Time has already passed, reload once to get updated status
        if (!sessionStorage.getItem('countdown-reloaded-' + consultationId)) {
            sessionStorage.setItem('countdown-reloaded-' + consultationId, 'true');
            setTimeout(() => {
                location.reload();
            }, 1000);
        }
        return;
    }
    
    const updateCountdown = function() {
        const now = new Date().getTime();
        const distance = scheduledAt - now;
        
        if (distance < 0) {
            countdownEl.textContent = 'Starting now...';
            
            // Only reload once, and add a delay to allow status update
            if (!hasReloaded && !sessionStorage.getItem('countdown-reloaded-' + consultationId)) {
                hasReloaded = true;
                sessionStorage.setItem('countdown-reloaded-' + consultationId, 'true');
                // Clear any existing timeout
                if (reloadTimeout) clearTimeout(reloadTimeout);
                // Wait 2 seconds before reloading to allow backend to update status
                reloadTimeout = setTimeout(() => {
                    // Check if we're still on the same page (prevent reload loop)
                    if (window.location.pathname.includes('/consultations/' + consultationId)) {
                        location.reload();
                    }
                }, 2000);
            }
            return;
        }
        
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        countdownEl.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    };
    
    updateCountdown();
    setInterval(updateCountdown, 1000);
})();
</script>
@endif
@endif

