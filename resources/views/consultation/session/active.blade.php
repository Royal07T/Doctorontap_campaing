@extends('layouts.app-livewire')

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

            <!-- Alpine.js Vonage Integration Component -->
            <div 
                x-data="vonageConsultation({
                    consultationId: {{ $consultation->id }},
                    mode: '{{ $consultation->consultation_mode }}',
                    tokenUrl: '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.session.token" : "patient.consultations.session.token", $consultation->id) }}',
                    statusUrl: '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.session.status" : "patient.consultations.session.status", $consultation->id) }}',
                    endSessionUrl: '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.session.end" : "patient.consultations.session.end", $consultation->id) }}',
                    dashboardUrl: '{{ auth()->guard("patient")->check() ? route("patient.dashboard") : route("doctor.dashboard") }}',
                    consultationDetailsUrl: '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.view" : "patient.consultation.view", $consultation->id) }}',
                    isPatient: {{ auth()->guard('patient')->check() ? 'true' : 'false' }}
                })"
                x-init="init()"
            >
                <!-- Join Section -->
                <div x-show="state === 'idle'" class="mb-6">
                    <div class="bg-gradient-to-br from-purple-50 to-white rounded-lg border-2 border-purple-200 p-8 text-center">
                        <div class="mb-6">
                            <template x-if="mode === 'voice'">
                                <svg class="w-16 h-16 mx-auto text-purple-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                                </svg>
                            </template>
                            <template x-if="mode === 'video'">
                                <svg class="w-16 h-16 mx-auto text-purple-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </template>
                            <template x-if="mode === 'chat'">
                                <svg class="w-16 h-16 mx-auto text-purple-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </template>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-2">Ready to Join Consultation</h2>
                        <p class="text-gray-600 mb-6" x-text="`Click the button below to join the ${mode === 'voice' ? 'voice call' : (mode === 'video' ? 'video call' : 'chat session')}.`"></p>
                        <button 
                            @click="joinConsultation()"
                            :disabled="loading"
                            class="px-8 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-semibold text-lg shadow-lg hover:shadow-xl transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span x-show="!loading">Join Consultation</span>
                            <span x-show="loading">Connecting...</span>
                        </button>
                    </div>
                </div>

                <!-- Loading State -->
                <div x-show="state === 'loading'" class="mb-6">
                    <div class="bg-blue-50 rounded-lg border border-blue-200 p-8 text-center">
                        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                        <p class="text-gray-700 font-medium">Connecting to consultation...</p>
                        <p class="text-sm text-gray-500 mt-2">Please wait while we establish the connection.</p>
                    </div>
                </div>

                <!-- Error States -->
                <div x-show="state === 'error'" class="mb-6">
                    <div class="p-4 rounded-lg border" :class="{
                        'bg-red-50 border-red-200': errorType === '401' || errorType === '403' || errorType === 'generic',
                        'bg-yellow-50 border-yellow-200': errorType === '429' || errorType === '503'
                    }">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5" :class="{
                                'text-red-600': errorType === '401' || errorType === '403' || errorType === 'generic',
                                'text-yellow-600': errorType === '429' || errorType === '503'
                            }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-semibold mb-1" :class="{
                                    'text-red-800': errorType === '401' || errorType === '403' || errorType === 'generic',
                                    'text-yellow-800': errorType === '429' || errorType === '503'
                                }" x-text="errorTitle"></p>
                                <p class="text-sm mb-2" :class="{
                                    'text-red-700': errorType === '401' || errorType === '403' || errorType === 'generic',
                                    'text-yellow-700': errorType === '429' || errorType === '503'
                                }" x-text="errorMessage"></p>
                                <button 
                                    @click="joinConsultation()" 
                                    x-show="errorType === '429'"
                                    class="mt-2 text-sm underline" 
                                    :class="{
                                        'text-yellow-800 hover:text-yellow-900': errorType === '429'
                                    }"
                                >
                                    Try Again
                                </button>
                                <a 
                                    :href="consultationDetailsUrl"
                                    class="text-sm underline" 
                                    :class="{
                                        'text-red-800 hover:text-red-900': errorType === '401' || errorType === '403' || errorType === 'generic',
                                        'text-yellow-800 hover:text-yellow-900': errorType === '503'
                                    }"
                                >
                                    Return to Consultation Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Connected State -->
                <div x-show="state === 'connected'" class="mb-6">
                    <div id="vonage-container" class="bg-gray-100 rounded-lg p-8 min-h-[400px]">
                        <!-- Vonage SDK will render here -->
                    </div>
                </div>

                <!-- Action Buttons -->
                <div x-show="state === 'connected'" class="flex justify-end space-x-4">
                    <a 
                        :href="consultationDetailsUrl"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Return to Consultation
                    </a>
                    <button 
                        @click="endSession()"
                        :disabled="ending"
                        class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition disabled:opacity-50"
                    >
                        <span x-show="!ending">End Consultation</span>
                        <span x-show="ending">Ending...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal (Patient Only) -->
<template x-if="isPatient">
    <div 
        x-show="showReviewModal"
        @click.away="showReviewModal = false"
        class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center"
        style="display: none;"
    >
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 text-center">
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
                <a 
                    href="https://g.page/r/CUgGQ-i_PAOUEAI/review" 
                    target="_blank" 
                    @click="redirectToDashboard(true)"
                    class="block w-full py-3 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg transition transform hover:scale-105 shadow-md no-underline"
                >
                    ⭐⭐⭐⭐⭐ Rate Us on Google
                </a>
                <button 
                    @click="redirectToDashboard(false)" 
                    class="block w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition"
                >
                    Skip & Go to Dashboard
                </button>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
function vonageConsultation(config) {
    return {
        // Configuration
        consultationId: config.consultationId,
        mode: config.mode,
        tokenUrl: config.tokenUrl,
        statusUrl: config.statusUrl,
        endSessionUrl: config.endSessionUrl,
        dashboardUrl: config.dashboardUrl,
        consultationDetailsUrl: config.consultationDetailsUrl,
        isPatient: config.isPatient,
        
        // State
        state: 'idle', // idle, loading, connected, error, ended
        loading: false,
        ending: false,
        errorType: null,
        errorTitle: '',
        errorMessage: '',
        showReviewModal: false,
        
        // Vonage SDK
        vonageClient: null,
        session: null,
        statusPollInterval: null,
        
        init() {
            // Check if Vonage Client SDK is loaded
            // The SDK may be available as window.vonageClientSDK or window.VonageClientSDK
            if (typeof window.vonageClientSDK === 'undefined' && typeof window.VonageClientSDK === 'undefined') {
                console.error('Vonage Client SDK not loaded');
                // Don't show error immediately - wait for user to click join
            }
        },
        
        async joinConsultation() {
            this.state = 'loading';
            this.loading = true;
            this.errorType = null;
            
            try {
                // Fetch JWT token from Laravel
                const response = await fetch(this.tokenUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                });
                
                const data = await response.json();
                
                // Handle HTTP errors
                if (response.status === 401) {
                    this.showError('401', 'Unauthorized Access', 'You are not authorized to join this consultation. Please contact support if you believe this is an error.');
                    return;
                }
                
                if (response.status === 403) {
                    this.showError('403', 'Access Denied', 'You do not have permission to join this consultation session.');
                    return;
                }
                
                if (response.status === 429) {
                    this.showError('429', 'Too Many Requests', 'You have made too many requests. Please wait a moment and try again.');
                    return;
                }
                
                if (response.status === 503) {
                    this.showError('503', 'Service Unavailable', data.message || 'In-app consultation temporarily unavailable. Please contact support or use WhatsApp consultation.');
                    return;
                }
                
                if (!response.ok) {
                    this.showError('generic', 'Connection Error', data.message || `Server error (${response.status})`);
                    return;
                }
                
                if (!data.success) {
                    if (data.message && data.message.toLowerCase().includes('vonage') && data.message.toLowerCase().includes('disabled')) {
                        this.showError('503', 'Service Unavailable', data.message);
                    } else {
                        this.showError('generic', 'Connection Error', data.message || 'Failed to join consultation');
                    }
                    return;
                }
                
                // Initialize Vonage Client SDK
                await this.initializeVonage(data.token, data.session_id);
                
            } catch (error) {
                console.error('Error joining consultation:', error);
                this.showError('generic', 'Network Error', 'Network error. Please check your connection and try again.');
            } finally {
                this.loading = false;
            }
        },
        
        async initializeVonage(token, sessionId) {
            try {
                // Check if SDK is loaded
                const SDK = window.vonageClientSDK || window.VonageClientSDK;
                if (!SDK) {
                    throw new Error('Vonage Client SDK not loaded. Please refresh the page.');
                }
                
                // Create Vonage Client instance
                // Adjust constructor based on actual SDK API
                this.vonageClient = new SDK.VonageClient({
                    token: token
                });
                
                // Create session with JWT
                // Adjust method name based on actual SDK API
                this.session = await this.vonageClient.createSession(token);
                
                // Handle session errors
                if (this.session && typeof this.session.on === 'function') {
                    this.session.on('sessionError', (error) => {
                        console.error('Vonage session error:', error);
                        this.showError('generic', 'Session Error', 'An error occurred with the consultation session. Please try again.');
                        this.state = 'error';
                    });
                }
                
                // Render based on mode
                const container = document.getElementById('vonage-container');
                if (!container) {
                    throw new Error('Vonage container not found');
                }
                
                if (this.mode === 'video') {
                    await this.renderVideo(container, sessionId);
                } else if (this.mode === 'voice') {
                    await this.renderVoice(container, sessionId);
                } else if (this.mode === 'chat') {
                    await this.renderChat(container, sessionId);
                }
                
                this.state = 'connected';
                this.startStatusPolling();
                
            } catch (error) {
                console.error('Error initializing Vonage:', error);
                this.showError('generic', 'Initialization Error', error.message || 'Failed to initialize consultation. Please try again.');
            }
        },
        
        async renderVideo(container, sessionId) {
            // Video rendering logic
            // This is a placeholder - implement based on Vonage Video API
            container.innerHTML = `
                <div class="text-center py-8">
                    <p class="text-gray-600 mb-4">Video consultation session initialized</p>
                    <p class="text-sm text-gray-500">Session ID: ${sessionId}</p>
                    <p class="text-sm text-gray-500 mt-2">Video rendering will be implemented here</p>
                </div>
            `;
        },
        
        async renderVoice(container, sessionId) {
            // Voice rendering logic
            container.innerHTML = `
                <div class="text-center py-8">
                    <p class="text-gray-600 mb-4">Voice consultation session initialized</p>
                    <p class="text-sm text-gray-500">Session ID: ${sessionId}</p>
                    <p class="text-sm text-gray-500 mt-2">Voice controls will be implemented here</p>
                </div>
            `;
        },
        
        async renderChat(container, sessionId) {
            // Chat rendering logic
            container.innerHTML = `
                <div class="text-center py-8">
                    <p class="text-gray-600 mb-4">Chat consultation session initialized</p>
                    <p class="text-sm text-gray-500">Session ID: ${sessionId}</p>
                    <p class="text-sm text-gray-500 mt-2">Chat interface will be implemented here</p>
                </div>
            `;
        },
        
        showError(type, title, message) {
            this.state = 'error';
            this.errorType = type;
            this.errorTitle = title;
            this.errorMessage = message;
        },
        
        async endSession() {
            if (!confirm('Are you sure you want to end this consultation?')) {
                return;
            }
            
            this.ending = true;
            
            try {
                const response = await fetch(this.endSessionUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.cleanup();
                    if (this.isPatient) {
                        this.showReviewModal = true;
                    } else {
                        window.location.href = this.dashboardUrl;
                    }
                } else {
                    alert('Failed to end session: ' + (data.message || 'Unknown error'));
                    this.ending = false;
                }
            } catch (error) {
                console.error('Error ending session:', error);
                alert('An error occurred while ending the session');
                this.ending = false;
            }
        },
        
        startStatusPolling() {
            if (this.statusPollInterval) return;
            
            this.statusPollInterval = setInterval(async () => {
                if (this.state !== 'connected') return;
                
                try {
                    const response = await fetch(this.statusUrl, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        cache: 'no-cache'
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        const status = data.session_status || data.consultation_status;
                        
                        if (status === 'ended' || status === 'completed') {
                            this.cleanup();
                            if (this.isPatient) {
                                this.showReviewModal = true;
                            } else {
                                window.location.href = this.consultationDetailsUrl;
                            }
                        } else if (status === 'cancelled') {
                            this.cleanup();
                            window.location.href = this.consultationDetailsUrl;
                        }
                    }
                } catch (error) {
                    console.error('Error checking session status:', error);
                }
            }, 5000);
        },
        
        cleanup() {
            if (this.statusPollInterval) {
                clearInterval(this.statusPollInterval);
                this.statusPollInterval = null;
            }
            
            if (this.session) {
                try {
                    this.session.disconnect();
                } catch (error) {
                    console.error('Error disconnecting session:', error);
                }
                this.session = null;
            }
            
            this.vonageClient = null;
        },
        
        redirectToDashboard(delayed) {
            if (delayed) {
                setTimeout(() => {
                    window.location.href = this.dashboardUrl;
                }, 1000);
            } else {
                window.location.href = this.dashboardUrl;
            }
        }
    };
}
</script>
@endpush
