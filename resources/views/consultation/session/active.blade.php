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
                    vonageApiKey: '{{ config('services.vonage.api_key') }}', // Legacy fallback
                    applicationId: '{{ config('services.vonage.application_id') }}', // JWT Application ID (preferred)
                    tokenUrl: '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.session.token" : "patient.consultations.session.token", $consultation->id) }}',
                    statusUrl: '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.session.status" : "patient.consultations.session.status", $consultation->id) }}',
                    endSessionUrl: '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.session.end" : "patient.consultations.session.end", $consultation->id) }}',
                    videoCreateUrl: '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.video.create" : "patient.consultations.video.create", $consultation->id) }}',
                    videoJoinUrl: '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.video.join" : "patient.consultations.video.join", $consultation->id) }}',
                    videoRefreshUrl: '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.video.refresh" : "patient.consultations.video.refresh", $consultation->id) }}',
                    videoStatusUrl: '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.video.status" : "patient.consultations.video.status", $consultation->id) }}',
                    videoEndUrl: '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.video.end" : "patient.consultations.video.end", $consultation->id) }}',
                    videoRecordingStartUrl: '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.video.recording.start" : "patient.consultations.video.recording.start", $consultation->id) }}',
                    videoRecordingStopUrl: '{{ route(auth()->guard("doctor")->check() ? "doctor.consultations.video.recording.stop" : "patient.consultations.video.recording.stop", $consultation->id) }}',
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
        vonageApiKey: config.vonageApiKey, // Legacy fallback
        applicationId: config.applicationId, // JWT Application ID (preferred)
        tokenUrl: config.tokenUrl,
        statusUrl: config.statusUrl,
        endSessionUrl: config.endSessionUrl,
        videoCreateUrl: config.videoCreateUrl,
        videoJoinUrl: config.videoJoinUrl,
        videoRefreshUrl: config.videoRefreshUrl,
        videoStatusUrl: config.videoStatusUrl,
        videoEndUrl: config.videoEndUrl,
        videoRecordingStartUrl: config.videoRecordingStartUrl,
        videoRecordingStopUrl: config.videoRecordingStopUrl,
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
        
        // OpenTok SDK
        session: null,
        publisher: null,
        subscribers: [], // Array to track all subscribers
        statusPollInterval: null,
        currentArchiveId: null,
        
        // Chat SDK
        conversationClient: null,
        conversation: null,
        chatMessages: [],
        typingUsers: new Set(),
        isTyping: false,
        typingTimeout: null,
        
        // Controls state
        isMuted: false,
        isVideoEnabled: true,
        isScreenSharing: false,
        isRecording: false,
        connectionQuality: 'good', // good, fair, poor
        participants: [],
        
        // File upload
        fileInput: null,
        
        init() {
            // Check if OpenTok.js SDK is loaded
            // OpenTok.js is the official SDK for Vonage Video API
            if (typeof OT === 'undefined') {
                console.warn('OpenTok.js SDK not loaded. Video/voice consultations may not work.');
                // Don't show error immediately - wait for user to click join
            }

            window.addEventListener('beforeunload', () => {
                this.cleanupVonage();
            });
        },
        
        async joinConsultation() {
            if (this.mode === 'video') {
                return this.joinVideoRoom();
            }

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
                
                // Store token for chat
                this.chatToken = data.token;
                
                // Initialize Vonage Client SDK
                await this.initializeVonage(this.vonageApiKey, data.token, data.session_id);
                
            } catch (error) {
                console.error('Error joining consultation:', error);
                this.showError('generic', 'Network Error', 'Network error. Please check your connection and try again.');
            } finally {
                this.loading = false;
            }
        },

        async joinVideoRoom() {
            this.state = 'loading';
            this.loading = true;
            this.errorType = null;

            try {
                if (!this.isPatient) {
                    await fetch(this.videoCreateUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({})
                    });
                }

                const response = await fetch(this.videoJoinUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                });

                const data = await response.json();

                if (response.status === 404 && this.isPatient) {
                    this.showError('generic', 'Waiting for Doctor', 'The doctor has not started the video room yet. Please wait a moment and try again.');
                    return;
                }

                if (response.status === 401) {
                    this.showError('401', 'Unauthorized Access', 'You are not authorized to join this consultation. Please contact support if you believe this is an error.');
                    return;
                }

                if (response.status === 403) {
                    this.showError('403', 'Access Denied', 'You do not have permission to join this video room.');
                    return;
                }

                if (response.status === 429) {
                    this.showError('429', 'Too Many Requests', 'You have made too many requests. Please wait a moment and try again.');
                    return;
                }

                if (!response.ok || !data.success) {
                    this.showError('generic', 'Connection Error', data.message || `Server error (${response.status})`);
                    return;
                }

                // Use applicationId (JWT) from backend, fallback to api_key or config values for legacy
                const apiKey = data.applicationId || data.api_key || this.applicationId || this.vonageApiKey;
                await this.initializeVonage(apiKey, data.token, data.session_id);
            } catch (error) {
                console.error('Error joining video room:', error);
                this.showError('generic', 'Network Error', 'Network error. Please check your connection and try again.');
            } finally {
                this.loading = false;
            }
        },
        
        async initializeVonage(apiKey, token, sessionId) {
            try {
                // Check if OpenTok.js SDK is loaded
                if (typeof OT === 'undefined') {
                    throw new Error('OpenTok.js SDK not loaded. Please refresh the page.');
                }
                
                // Initialize OpenTok session
                // OpenTok.js uses OT.initSession() to create a session
                this.session = OT.initSession(apiKey, sessionId);
                
                // Handle session events
                this.session.on('sessionConnected', () => {
                    console.log('OpenTok session connected');
                });
                
                this.session.on('sessionDisconnected', () => {
                    console.log('OpenTok session disconnected');
                    this.state = 'ended';
                });
                
                this.session.on('error', (error) => {
                    console.error('OpenTok session error:', error);
                    if ((error.code === 1004 || error.code === 1006 || error.code === 1008) && this.mode === 'video') {
                        this.refreshVideoToken();
                        return;
                    }
                    this.showError('generic', 'Session Error', 'An error occurred with the consultation session. Please try again.');
                    this.state = 'error';
                });
                
                // Connect to session with token
                this.session.connect(token, (error) => {
                    if (error) {
                        console.error('Error connecting to OpenTok session:', error);
                        if ((error.code === 1004 || error.code === 1006 || error.code === 1008) && this.mode === 'video') {
                            this.refreshVideoToken();
                            return;
                        }
                        this.showError('generic', 'Connection Error', error.message || 'Failed to connect to consultation session.');
                        this.state = 'error';
                        return;
                    }
                    
                    console.log('Connected to OpenTok session');
                    
                    // Render based on mode
                    const container = document.getElementById('vonage-container');
                    if (!container) {
                        console.error('Vonage container not found');
                        return;
                    }
                    
                    if (this.mode === 'video') {
                        this.renderVideo(container, sessionId);
                    } else if (this.mode === 'voice') {
                        this.renderVoice(container, sessionId);
                    } else if (this.mode === 'chat') {
                        this.renderChat(container, sessionId);
                    }
                    
                    this.state = 'connected';
                    this.startStatusPolling();
                });
                
            } catch (error) {
                console.error('Error initializing Vonage:', error);
                this.showError('generic', 'Initialization Error', error.message || 'Failed to initialize consultation. Please try again.');
            }
        },

        async refreshVideoToken() {
            if (this.state !== 'connected' && this.state !== 'loading') {
                return;
            }

            try {
                const response = await fetch(this.videoRefreshUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Failed to refresh token');
                }

                this.cleanupVonage();
                // Use applicationId (JWT) from backend, fallback to vonageApiKey for legacy
                const apiKey = data.applicationId || data.api_key || this.vonageApiKey;
                await this.initializeVonage(apiKey, data.token, data.session_id);
            } catch (e) {
                console.error('Token refresh failed:', e);
                this.showError('generic', 'Session Expired', 'Your session expired. Please re-join the consultation.');
                this.state = 'error';
            }
        },
        
        renderVideo(container, sessionId) {
            // Clear container
            container.innerHTML = '';
            
            // Create main video container with grid layout
            const videoGrid = document.createElement('div');
            videoGrid.className = 'grid grid-cols-1 md:grid-cols-2 gap-4 mb-4';
            
            // Create publisher container (local video)
            const publisherWrapper = document.createElement('div');
            publisherWrapper.className = 'relative bg-gray-900 rounded-lg overflow-hidden';
            const publisherContainer = document.createElement('div');
            publisherContainer.id = 'publisher-container';
            publisherContainer.className = 'w-full h-64';
            publisherWrapper.appendChild(publisherContainer);
            
            // Add local video label
            const localLabel = document.createElement('div');
            localLabel.className = 'absolute top-2 left-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded';
            localLabel.textContent = 'You';
            publisherWrapper.appendChild(localLabel);
            
            // Create subscriber container (remote video)
            const subscriberWrapper = document.createElement('div');
            subscriberWrapper.className = 'relative bg-gray-800 rounded-lg overflow-hidden';
            const subscriberContainer = document.createElement('div');
            subscriberContainer.id = 'subscriber-container';
            subscriberContainer.className = 'w-full h-64';
            subscriberWrapper.appendChild(subscriberContainer);
            
            // Add remote video label
            const remoteLabel = document.createElement('div');
            remoteLabel.id = 'remote-label';
            remoteLabel.className = 'absolute top-2 left-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded';
            remoteLabel.textContent = 'Waiting for participant...';
            subscriberWrapper.appendChild(remoteLabel);
            
            videoGrid.appendChild(publisherWrapper);
            videoGrid.appendChild(subscriberWrapper);
            container.appendChild(videoGrid);
            
            // Create controls bar
            const controlsBar = this.createControlsBar('video');
            container.appendChild(controlsBar);
            
            // Create connection quality indicator
            const qualityIndicator = this.createQualityIndicator();
            container.appendChild(qualityIndicator);
            
            // Publish local video/audio stream
            this.publisher = OT.initPublisher(publisherContainer, {
                videoSource: 'camera',
                audioSource: 'microphone',
                publishAudio: true,
                publishVideo: true,
                width: '100%',
                height: '100%',
                style: {
                    buttonDisplayMode: 'on',
                    nameDisplayMode: 'on'
                }
            }, (error) => {
                if (error) {
                    console.error('Error initializing publisher:', error);
                    this.showError('generic', 'Camera/Microphone Error', 'Failed to access camera or microphone. Please check permissions.');
                    return;
                }
                
                // Publish to session
                this.session.publish(this.publisher, (error) => {
                    if (error) {
                        console.error('Error publishing stream:', error);
                        this.showError('generic', 'Publish Error', 'Failed to publish video stream.');
                    } else {
                        console.log('Publisher stream published');
                        this.publisher = this.publisher; // Store reference
                    }
                });
            });
            
            // Monitor connection quality
            this.monitorConnectionQuality();
            
            // Subscribe to remote streams
            this.session.on('streamCreated', (event) => {
                console.log('Remote stream created:', event.stream);
                const subscriber = this.session.subscribe(event.stream, subscriberContainer, {
                    width: '100%',
                    height: '100%',
                    subscribeToAudio: true,
                    subscribeToVideo: true
                }, (error) => {
                    if (error) {
                        console.error('Error subscribing to stream:', error);
                    } else {
                        console.log('Subscribed to remote stream');
                        this.subscribers.push(subscriber);
                        remoteLabel.textContent = 'Participant';
                        
                        // Update participant list
                        this.updateParticipants();
                    }
                });
            });
            
            // Handle stream destroyed
            this.session.on('streamDestroyed', (event) => {
                console.log('Stream destroyed:', event.stream);
                this.subscribers = this.subscribers.filter(s => s.stream !== event.stream);
                if (this.subscribers.length === 0) {
                    remoteLabel.textContent = 'Waiting for participant...';
                }
                this.updateParticipants();
            });
        },
        
        renderVoice(container, sessionId) {
            // Clear container
            container.innerHTML = '';
            
            // Create audio call UI
            const audioCallUI = document.createElement('div');
            audioCallUI.className = 'text-center py-12';
            
            // Create participant avatars
            const avatarsContainer = document.createElement('div');
            avatarsContainer.className = 'flex justify-center items-center space-x-8 mb-8';
            
            // Local participant avatar
            const localAvatar = document.createElement('div');
            localAvatar.className = 'w-32 h-32 bg-purple-600 rounded-full flex items-center justify-center text-white text-4xl font-bold relative';
            localAvatar.textContent = this.isPatient ? 'P' : 'D';
            const localLabel = document.createElement('div');
            localLabel.className = 'absolute -bottom-6 text-sm text-gray-600 font-medium';
            localLabel.textContent = 'You';
            localAvatar.appendChild(localLabel);
            
            // Remote participant avatar
            const remoteAvatar = document.createElement('div');
            remoteAvatar.id = 'remote-avatar';
            remoteAvatar.className = 'w-32 h-32 bg-gray-400 rounded-full flex items-center justify-center text-white text-4xl font-bold relative';
            remoteAvatar.textContent = '?';
            const remoteLabel = document.createElement('div');
            remoteLabel.id = 'remote-label';
            remoteLabel.className = 'absolute -bottom-6 text-sm text-gray-600 font-medium';
            remoteLabel.textContent = 'Waiting...';
            remoteAvatar.appendChild(remoteLabel);
            
            avatarsContainer.appendChild(localAvatar);
            avatarsContainer.appendChild(remoteAvatar);
            audioCallUI.appendChild(avatarsContainer);
            
            // Call status
            const callStatus = document.createElement('div');
            callStatus.id = 'call-status';
            callStatus.className = 'text-gray-600 mb-6';
            callStatus.textContent = 'Connecting...';
            audioCallUI.appendChild(callStatus);
            
            container.appendChild(audioCallUI);
            
            // Create controls bar
            const controlsBar = this.createControlsBar('voice');
            container.appendChild(controlsBar);
            
            // Create connection quality indicator
            const qualityIndicator = this.createQualityIndicator();
            container.appendChild(qualityIndicator);
            
            // Create audio-only publisher container (hidden, for audio only)
            const publisherContainer = document.createElement('div');
            publisherContainer.id = 'publisher-container';
            publisherContainer.className = 'hidden';
            container.appendChild(publisherContainer);
            
            // Publish audio-only stream
            this.publisher = OT.initPublisher(publisherContainer, {
                videoSource: null, // No video
                audioSource: 'microphone',
                publishAudio: true,
                publishVideo: false, // Audio-only
                width: '100%',
                height: 'auto',
                style: {
                    buttonDisplayMode: 'off',
                    nameDisplayMode: 'off'
                }
            }, (error) => {
                if (error) {
                    console.error('Error initializing audio publisher:', error);
                    this.showError('generic', 'Microphone Error', 'Failed to access microphone. Please check permissions.');
                    return;
                }
                
                // Publish to session
                this.session.publish(this.publisher, (error) => {
                    if (error) {
                        console.error('Error publishing audio stream:', error);
                        this.showError('generic', 'Publish Error', 'Failed to publish audio stream.');
                    } else {
                        console.log('Audio publisher stream published');
                        callStatus.textContent = 'Connected';
                        this.publisher = this.publisher; // Store reference
                    }
                });
            });
            
            // Monitor connection quality
            this.monitorConnectionQuality();
            
            // Subscribe to remote audio streams
            this.session.on('streamCreated', (event) => {
                console.log('Remote audio stream created:', event.stream);
                const subscriberContainer = document.createElement('div');
                subscriberContainer.id = 'subscriber-container';
                subscriberContainer.className = 'hidden';
                container.appendChild(subscriberContainer);
                
                const subscriber = this.session.subscribe(event.stream, subscriberContainer, {
                    width: '100%',
                    height: 'auto',
                    subscribeToAudio: true,
                    subscribeToVideo: false // Audio-only
                }, (error) => {
                    if (error) {
                        console.error('Error subscribing to audio stream:', error);
                    } else {
                        console.log('Subscribed to remote audio stream');
                        this.subscribers.push(subscriber);
                        remoteAvatar.textContent = this.isPatient ? 'D' : 'P';
                        remoteLabel.textContent = this.isPatient ? 'Doctor' : 'Patient';
                        callStatus.textContent = 'In Call';
                        this.updateParticipants();
                    }
                });
            });
            
            // Handle stream destroyed
            this.session.on('streamDestroyed', (event) => {
                console.log('Audio stream destroyed:', event.stream);
                this.subscribers = this.subscribers.filter(s => s.stream !== event.stream);
                if (this.subscribers.length === 0) {
                    remoteAvatar.textContent = '?';
                    remoteLabel.textContent = 'Waiting...';
                    callStatus.textContent = 'Waiting for participant...';
                }
                this.updateParticipants();
            });
        },
        
        async renderChat(container, sessionId) {
            // Clear container
            container.innerHTML = '';
            container.className = 'flex flex-col h-[600px] bg-white rounded-lg border border-gray-200 overflow-hidden';
            
            // Create chat header
            const chatHeader = document.createElement('div');
            chatHeader.className = 'bg-purple-600 text-white px-4 py-3 flex items-center justify-between';
            const headerTitle = document.createElement('h3');
            headerTitle.className = 'font-semibold';
            headerTitle.textContent = 'Consultation Chat';
            const participantCount = document.createElement('span');
            participantCount.id = 'chat-participant-count';
            participantCount.className = 'text-sm bg-purple-700 px-2 py-1 rounded';
            participantCount.textContent = '2 participants';
            chatHeader.appendChild(headerTitle);
            chatHeader.appendChild(participantCount);
            container.appendChild(chatHeader);
            
            // Create messages container
            const messagesContainer = document.createElement('div');
            messagesContainer.id = 'chat-messages';
            messagesContainer.className = 'flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50';
            container.appendChild(messagesContainer);
            
            // Create typing indicator
            const typingIndicator = document.createElement('div');
            typingIndicator.id = 'typing-indicator';
            typingIndicator.className = 'px-4 py-2 text-sm text-gray-500 italic hidden';
            typingIndicator.textContent = 'Someone is typing...';
            container.appendChild(typingIndicator);
            
            // Create input area
            const inputArea = document.createElement('div');
            inputArea.className = 'border-t border-gray-200 p-4 bg-white';
            
            // File input (hidden)
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.id = 'chat-file-input';
            fileInput.className = 'hidden';
            fileInput.accept = 'image/*,.pdf,.doc,.docx';
            fileInput.multiple = false;
            this.fileInput = fileInput;
            inputArea.appendChild(fileInput);
            
            // Input container
            const inputContainer = document.createElement('div');
            inputContainer.className = 'flex items-center space-x-2';
            
            // File attach button
            const attachBtn = document.createElement('button');
            attachBtn.type = 'button';
            attachBtn.className = 'p-2 text-gray-600 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition';
            attachBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>';
            attachBtn.onclick = () => fileInput.click();
            inputContainer.appendChild(attachBtn);
            
            // Message input
            const messageInput = document.createElement('input');
            messageInput.type = 'text';
            messageInput.id = 'chat-message-input';
            messageInput.placeholder = 'Type your message...';
            messageInput.className = 'flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500';
            messageInput.onkeypress = (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendChatMessage();
                }
            };
            messageInput.oninput = () => {
                this.handleTyping();
            };
            inputContainer.appendChild(messageInput);
            
            // Send button
            const sendBtn = document.createElement('button');
            sendBtn.type = 'button';
            sendBtn.className = 'px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition disabled:opacity-50';
            sendBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>';
            sendBtn.onclick = () => this.sendChatMessage();
            inputContainer.appendChild(sendBtn);
            
            inputArea.appendChild(inputContainer);
            container.appendChild(inputArea);
            
            // Initialize Vonage Conversations SDK
            try {
                await this.initializeChatClient(sessionId);
            } catch (error) {
                console.error('Error initializing chat:', error);
                this.showError('generic', 'Chat Error', 'Failed to initialize chat. Please try again.');
            }
        },
        
        async initializeChatClient(conversationId) {
            try {
                // Get token from current session data
                const token = await this.getChatToken();
                if (!token) {
                    throw new Error('Failed to get chat token');
                }
                
                // Initialize Vonage Conversations Client
                // Note: Using the token we already have from the session
                this.conversationClient = new ConversationClient({
                    debug: false
                });
                
                // Join conversation
                this.conversation = await this.conversationClient.joinConversation(conversationId, {
                    token: token,
                    user: {
                        name: this.isPatient ? 'Patient' : 'Doctor'
                    }
                });
                
                // Load previous messages
                await this.loadChatHistory();
                
                // Listen for new messages
                this.conversation.on('text', (event) => {
                    this.handleIncomingMessage(event);
                });
                
                // Listen for typing events
                this.conversation.on('typing:start', (event) => {
                    if (event.from.name !== (this.isPatient ? 'Patient' : 'Doctor')) {
                        this.typingUsers.add(event.from.name);
                        this.updateTypingIndicator();
                    }
                });
                
                this.conversation.on('typing:stop', (event) => {
                    this.typingUsers.delete(event.from.name);
                    this.updateTypingIndicator();
                });
                
                // Listen for member events
                this.conversation.on('member:joined', () => {
                    this.updateParticipantCount();
                });
                
                this.conversation.on('member:left', () => {
                    this.updateParticipantCount();
                });
                
                console.log('Chat client initialized successfully');
            } catch (error) {
                console.error('Error initializing chat client:', error);
                throw error;
            }
        },
        
        async getChatToken() {
            // Token is already available from joinConsultation
            // We'll store it when we get it
            return this.chatToken;
        },
        
        async loadChatHistory() {
            try {
                // Load chat history from backend
                const response = await fetch(`/consultations/${this.consultationId}/chat/messages`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.messages) {
                        data.messages.forEach(msg => {
                            this.addMessageToUI(msg, false);
                        });
                        this.scrollToBottom();
                    }
                }
            } catch (error) {
                console.error('Error loading chat history:', error);
            }
        },
        
        handleIncomingMessage(event) {
            const message = {
                id: event.id,
                message: event.body.text,
                sender_name: event.from.name,
                sender_type: event.from.name.includes('Doctor') ? 'doctor' : 'patient',
                sent_at: new Date(event.timestamp),
                message_type: 'text'
            };
            
            this.addMessageToUI(message, true);
            this.saveMessageToBackend(message);
        },
        
        addMessageToUI(message, isNew) {
            const messagesContainer = document.getElementById('chat-messages');
            if (!messagesContainer) return;
            
            const messageDiv = document.createElement('div');
            const isOwnMessage = (message.sender_type === 'patient' && this.isPatient) || 
                                (message.sender_type === 'doctor' && !this.isPatient);
            
            messageDiv.className = `flex ${isOwnMessage ? 'justify-end' : 'justify-start'}`;
            
            const messageBubble = document.createElement('div');
            messageBubble.className = `max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${
                isOwnMessage 
                    ? 'bg-purple-600 text-white' 
                    : 'bg-white text-gray-800 border border-gray-200'
            }`;
            
            if (!isOwnMessage) {
                const senderName = document.createElement('div');
                senderName.className = 'text-xs font-semibold mb-1 text-gray-600';
                senderName.textContent = message.sender_name;
                messageBubble.appendChild(senderName);
            }
            
            const messageText = document.createElement('div');
            messageText.className = 'text-sm whitespace-pre-wrap';
            messageText.textContent = message.message;
            messageBubble.appendChild(messageText);
            
            const timestamp = document.createElement('div');
            timestamp.className = `text-xs mt-1 ${isOwnMessage ? 'text-purple-100' : 'text-gray-500'}`;
            timestamp.textContent = this.formatTime(message.sent_at);
            messageBubble.appendChild(timestamp);
            
            messageDiv.appendChild(messageBubble);
            messagesContainer.appendChild(messageDiv);
            
            if (isNew) {
                this.scrollToBottom();
            }
            
            this.chatMessages.push(message);
        },
        
        async sendChatMessage() {
            const input = document.getElementById('chat-message-input');
            const message = input.value.trim();
            
            if (!message || !this.conversation) return;
            
            try {
                // Send via Vonage Conversations
                await this.conversation.sendText(message);
                
                // Clear input
                input.value = '';
                
                // Stop typing indicator
                this.stopTyping();
            } catch (error) {
                console.error('Error sending message:', error);
                alert('Failed to send message. Please try again.');
            }
        },
        
        handleTyping() {
            if (!this.isTyping && this.conversation) {
                this.isTyping = true;
                this.conversation.startTyping();
            }
            
            clearTimeout(this.typingTimeout);
            this.typingTimeout = setTimeout(() => {
                this.stopTyping();
            }, 3000);
        },
        
        stopTyping() {
            if (this.isTyping && this.conversation) {
                this.conversation.stopTyping();
                this.isTyping = false;
            }
            clearTimeout(this.typingTimeout);
        },
        
        updateTypingIndicator() {
            const indicator = document.getElementById('typing-indicator');
            if (!indicator) return;
            
            if (this.typingUsers.size > 0) {
                const names = Array.from(this.typingUsers);
                indicator.textContent = names.length === 1 
                    ? `${names[0]} is typing...`
                    : 'Someone is typing...';
                indicator.classList.remove('hidden');
            } else {
                indicator.classList.add('hidden');
            }
        },
        
        updateParticipantCount() {
            const countEl = document.getElementById('chat-participant-count');
            if (countEl && this.conversation) {
                const memberCount = this.conversation.members.length || 2;
                countEl.textContent = `${memberCount} participant${memberCount !== 1 ? 's' : ''}`;
            }
        },
        
        scrollToBottom() {
            const messagesContainer = document.getElementById('chat-messages');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        },
        
        formatTime(date) {
            const d = new Date(date);
            const now = new Date();
            const diff = now - d;
            
            if (diff < 60000) return 'Just now';
            if (diff < 3600000) return `${Math.floor(diff / 60000)}m ago`;
            return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },
        
        async saveMessageToBackend(message) {
            try {
                await fetch(`/consultations/${this.consultationId}/chat/messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(message)
                });
            } catch (error) {
                console.error('Error saving message to backend:', error);
            }
        },
        
        createControlsBar(mode) {
            const controlsBar = document.createElement('div');
            controlsBar.className = 'flex justify-center items-center space-x-4 p-4 bg-gray-100 rounded-lg mt-4';
            
            // Mute/Unmute button
            const muteBtn = document.createElement('button');
            muteBtn.id = 'mute-btn';
            muteBtn.className = 'p-3 rounded-full bg-white hover:bg-gray-200 transition shadow-md';
            muteBtn.innerHTML = '<svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path></svg>';
            muteBtn.onclick = () => this.toggleMute();
            controlsBar.appendChild(muteBtn);
            
            // Video toggle (only for video mode)
            if (mode === 'video') {
                const videoBtn = document.createElement('button');
                videoBtn.id = 'video-btn';
                videoBtn.className = 'p-3 rounded-full bg-white hover:bg-gray-200 transition shadow-md';
                videoBtn.innerHTML = '<svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>';
                videoBtn.onclick = () => this.toggleVideo();
                controlsBar.appendChild(videoBtn);
                
                // Screen share button
                const screenShareBtn = document.createElement('button');
                screenShareBtn.id = 'screen-share-btn';
                screenShareBtn.className = 'p-3 rounded-full bg-white hover:bg-gray-200 transition shadow-md';
                screenShareBtn.innerHTML = '<svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>';
                screenShareBtn.onclick = () => this.toggleScreenShare();
                controlsBar.appendChild(screenShareBtn);
            }
            
            // Recording button (optional)
            const recordBtn = document.createElement('button');
            recordBtn.id = 'record-btn';
            recordBtn.className = 'p-3 rounded-full bg-white hover:bg-gray-200 transition shadow-md';
            recordBtn.innerHTML = '<svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>';
            recordBtn.onclick = () => this.toggleRecording();
            controlsBar.appendChild(recordBtn);
            
            return controlsBar;
        },
        
        createQualityIndicator() {
            const qualityDiv = document.createElement('div');
            qualityDiv.id = 'connection-quality';
            qualityDiv.className = 'flex items-center justify-center space-x-2 mt-2 text-sm';
            
            const qualityIcon = document.createElement('div');
            qualityIcon.id = 'quality-icon';
            qualityIcon.className = 'w-3 h-3 rounded-full bg-green-500';
            
            const qualityText = document.createElement('span');
            qualityText.id = 'quality-text';
            qualityText.className = 'text-gray-600';
            qualityText.textContent = 'Connection: Good';
            
            qualityDiv.appendChild(qualityIcon);
            qualityDiv.appendChild(qualityText);
            
            return qualityDiv;
        },
        
        toggleMute() {
            if (!this.publisher) return;
            
            this.isMuted = !this.isMuted;
            this.publisher.publishAudio(!this.isMuted);
            
            const muteBtn = document.getElementById('mute-btn');
            if (muteBtn) {
                if (this.isMuted) {
                    muteBtn.classList.add('bg-red-500');
                    muteBtn.classList.remove('bg-white');
                    muteBtn.querySelector('svg').classList.add('text-white');
                    muteBtn.querySelector('svg').classList.remove('text-gray-700');
                } else {
                    muteBtn.classList.remove('bg-red-500');
                    muteBtn.classList.add('bg-white');
                    muteBtn.querySelector('svg').classList.remove('text-white');
                    muteBtn.querySelector('svg').classList.add('text-gray-700');
                }
            }
        },
        
        toggleVideo() {
            if (!this.publisher) return;
            
            this.isVideoEnabled = !this.isVideoEnabled;
            this.publisher.publishVideo(this.isVideoEnabled);
            
            const videoBtn = document.getElementById('video-btn');
            if (videoBtn) {
                if (!this.isVideoEnabled) {
                    videoBtn.classList.add('bg-red-500');
                    videoBtn.classList.remove('bg-white');
                    videoBtn.querySelector('svg').classList.add('text-white');
                    videoBtn.querySelector('svg').classList.remove('text-gray-700');
                } else {
                    videoBtn.classList.remove('bg-red-500');
                    videoBtn.classList.add('bg-white');
                    videoBtn.querySelector('svg').classList.remove('text-white');
                    videoBtn.querySelector('svg').classList.add('text-gray-700');
                }
            }
        },
        
        async toggleScreenShare() {
            if (!this.session) return;
            
            try {
                if (!this.isScreenSharing) {
                    // Start screen sharing
                    const screenPublisher = OT.initPublisher('publisher-container', {
                        videoSource: 'screen',
                        publishAudio: false,
                        publishVideo: true,
                        width: '100%',
                        height: '100%'
                    }, (error) => {
                        if (error) {
                            console.error('Error starting screen share:', error);
                            alert('Failed to start screen sharing. Please check permissions.');
                            return;
                        }
                        
                        this.session.publish(screenPublisher, (error) => {
                            if (error) {
                                console.error('Error publishing screen share:', error);
                            } else {
                                this.isScreenSharing = true;
                                this.publisher.publishVideo(false); // Hide camera
                                const btn = document.getElementById('screen-share-btn');
                                if (btn) {
                                    btn.classList.add('bg-green-500');
                                    btn.classList.remove('bg-white');
                                }
                            }
                        });
                    });
                } else {
                    // Stop screen sharing
                    const publishers = this.session.getPublishers();
                    publishers.forEach(pub => {
                        if (pub.stream && pub.stream.videoType === 'screen') {
                            this.session.unpublish(pub);
                        }
                    });
                    this.isScreenSharing = false;
                    this.publisher.publishVideo(true); // Show camera again
                    const btn = document.getElementById('screen-share-btn');
                    if (btn) {
                        btn.classList.remove('bg-green-500');
                        btn.classList.add('bg-white');
                    }
                }
            } catch (error) {
                console.error('Error toggling screen share:', error);
                alert('Screen sharing is not supported in this browser.');
            }
        },
        
        toggleRecording() {
            this.isRecording = !this.isRecording;
            const btn = document.getElementById('record-btn');
            if (btn) {
                if (this.isRecording) {
                    btn.classList.add('bg-red-500', 'animate-pulse');
                    btn.classList.remove('bg-white');
                    btn.querySelector('svg').classList.add('text-white');
                } else {
                    btn.classList.remove('bg-red-500', 'animate-pulse');
                    btn.classList.add('bg-white');
                    btn.querySelector('svg').classList.remove('text-white');
                }
            }
            
            if (this.mode !== 'video') {
                fetch(`/consultations/${this.consultationId}/session/recording`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ recording: this.isRecording })
                }).catch(err => console.error('Error toggling recording:', err));
                return;
            }

            if (this.isRecording) {
                fetch(this.videoRecordingStartUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({})
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data && data.success && data.archive && data.archive.vonage_archive_id) {
                            this.currentArchiveId = data.archive.vonage_archive_id;
                        }
                    })
                    .catch(err => console.error('Error starting recording:', err));
            } else {
                if (!this.currentArchiveId) {
                    return;
                }
                fetch(this.videoRecordingStopUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ archive_id: this.currentArchiveId })
                })
                    .then(() => {
                        this.currentArchiveId = null;
                    })
                    .catch(err => console.error('Error stopping recording:', err));
            }
        },
        
        monitorConnectionQuality() {
            if (!this.session) return;
            
            setInterval(() => {
                if (this.publisher) {
                    const stats = this.publisher.getStats((error, stats) => {
                        if (error || !stats) return;
                        
                        // Analyze connection quality
                        let quality = 'good';
                        if (stats.video && stats.video.packetsLost > 10) {
                            quality = 'poor';
                        } else if (stats.video && stats.video.packetsLost > 5) {
                            quality = 'fair';
                        }
                        
                        this.connectionQuality = quality;
                        this.updateQualityIndicator();
                    });
                }
            }, 5000);
        },
        
        updateQualityIndicator() {
            const icon = document.getElementById('quality-icon');
            const text = document.getElementById('quality-text');
            
            if (!icon || !text) return;
            
            const qualityMap = {
                good: { color: 'bg-green-500', text: 'Connection: Good' },
                fair: { color: 'bg-yellow-500', text: 'Connection: Fair' },
                poor: { color: 'bg-red-500', text: 'Connection: Poor' }
            };
            
            const quality = qualityMap[this.connectionQuality] || qualityMap.good;
            icon.className = `w-3 h-3 rounded-full ${quality.color}`;
            text.textContent = quality.text;
        },
        
        updateParticipants() {
            if (!this.session) return;
            
            const connections = this.session.getConnections();
            this.participants = connections.map(conn => ({
                id: conn.connectionId,
                data: conn.data
            }));
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
                const response = await fetch(this.mode === 'video' ? this.videoEndUrl : this.endSessionUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.cleanupVonage();
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
                    const response = await fetch(this.mode === 'video' ? this.videoStatusUrl : this.statusUrl, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
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
                                window.location.href = this.dashboardUrl;
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
            
            // Stop typing
            this.stopTyping();
            
            // Disconnect chat
            if (this.conversation) {
                try {
                    this.conversation.leave();
                } catch (error) {
                    console.error('Error leaving conversation:', error);
                }
                this.conversation = null;
            }
            
            if (this.conversationClient) {
                this.conversationClient = null;
            }

            const session = this.session;

            if (session && this.publisher) {
                try {
                    session.unpublish(this.publisher);
                } catch (error) {
                    console.error('Error unpublishing:', error);
                }
            }

            if (this.publisher) {
                try {
                    this.publisher.destroy();
                } catch (error) {
                }
                this.publisher = null;
            }

            if (session && Array.isArray(this.subscribers)) {
                this.subscribers.forEach(sub => {
                    try {
                        session.unsubscribe(sub);
                    } catch (error) {
                        console.error('Error unsubscribing:', error);
                    }
                });
            }
            this.subscribers = [];

            if (session) {
                try {
                    session.disconnect();
                } catch (error) {
                    console.error('Error disconnecting session:', error);
                }
            }

            this.session = null;
        },
        
        cleanupVonage() {
            if (this.statusPollInterval) {
                clearInterval(this.statusPollInterval);
                this.statusPollInterval = null;
            }

            try {
                if (this.publisher && this.session) {
                    try {
                        this.session.unpublish(this.publisher);
                    } catch (e) {
                    }

                    try {
                        this.publisher.destroy();
                    } catch (e) {
                    }
                }

                this.publisher = null;

                if (Array.isArray(this.subscribers)) {
                    this.subscribers.forEach((sub) => {
                        try {
                            sub.destroy();
                        } catch (e) {
                        }
                    });
                }
                this.subscribers = [];

                if (this.session) {
                    try {
                        this.session.disconnect();
                    } catch (e) {
                    }
                }
            } catch (e) {
            }

            this.session = null;
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
