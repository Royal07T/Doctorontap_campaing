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
                        
                        <!-- SDK Loading Indicator (for video/voice modes) -->
                        <div x-show="(mode === 'video' || mode === 'voice') && !sdkLoaded" class="mb-4 text-sm text-yellow-600 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <div class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Loading video call SDK...</span>
                            </div>
                        </div>
                        
                        <button 
                            @click="(mode === 'video' || mode === 'voice') ? runPreCallTest() : joinConsultation()"
                            :disabled="loading || ((mode === 'video' || mode === 'voice') && !sdkLoaded)"
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
                        <p class="text-gray-700 font-medium" x-text="mode === 'video' && isPatient && videoJoinRetries > 0 ? 'Waiting for doctor to start the video room...' : 'Connecting to consultation...'"></p>
                        <p class="text-sm text-gray-500 mt-2" x-text="mode === 'video' && isPatient && videoJoinRetries > 0 ? `Attempt ${videoJoinRetries}/${maxVideoJoinRetries} - Please wait...` : 'Please wait while we establish the connection.'"></p>
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
                    <div id="vonage-container" class="bg-gray-100 rounded-lg p-8 min-h-[400px] relative">
                        <!-- Vonage SDK will render here -->
                        
                        <!-- Live Captions Overlay -->
                        <div 
                            x-show="showCaptions && captions.length > 0"
                            class="absolute bottom-4 left-4 right-4 bg-black bg-opacity-75 text-white p-4 rounded-lg max-h-32 overflow-y-auto"
                            style="display: none;"
                        >
                            <div class="text-sm font-medium mb-2">Live Captions</div>
                            <div class="text-sm space-y-1">
                                <template x-for="(caption, index) in captions.slice(-3)" :key="index">
                                    <div x-text="caption.text" class="opacity-90"></div>
                                </template>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Captions Toggle Button -->
                    <div x-show="state === 'connected' && (mode === 'video' || mode === 'voice')" class="mt-4 flex justify-center">
                        <button 
                            @click="toggleCaptions()"
                            :class="showCaptions ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-600 hover:bg-gray-700'"
                            class="px-4 py-2 text-white rounded-lg transition-colors text-sm"
                        >
                            <span x-show="!showCaptions">Enable Captions</span>
                            <span x-show="showCaptions">Disable Captions</span>
                        </button>
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
                
                <!-- Pre-Call Test Modal -->
                <div 
                    x-show="showPreCallTest"
                    @click.away="showPreCallTest = false"
                    class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center"
                    style="display: none;"
                >
                    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4 p-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Pre-Call Test</h3>
                        <p class="text-gray-600 mb-6">Checking your device and connection before joining...</p>
                        
                        <!-- Test Results -->
                        <div class="space-y-4 mb-6">
                            <!-- Browser Support -->
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <span class="font-medium text-gray-700">Browser Support</span>
                                </div>
                                <div class="flex items-center">
                                    <template x-if="preCallTestResults.browser === null">
                                        <div class="flex items-center text-gray-500">
                                            <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Checking...
                                        </div>
                                    </template>
                                    <template x-if="preCallTestResults.browser === 'passed'">
                                        <div class="flex items-center text-green-600">
                                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Supported
                                        </div>
                                    </template>
                                    <template x-if="preCallTestResults.browser === 'failed'">
                                        <div class="flex items-center text-red-600">
                                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                            Not Supported
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Camera Test -->
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <span class="font-medium text-gray-700">Camera</span>
                                </div>
                                <div class="flex items-center">
                                    <template x-if="preCallTestResults.camera === null">
                                        <div class="flex items-center text-gray-500">
                                            <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Checking...
                                        </div>
                                    </template>
                                    <template x-if="preCallTestResults.camera === 'checking'">
                                        <div class="flex items-center text-blue-600">
                                            <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Testing...
                                        </div>
                                    </template>
                                    <template x-if="preCallTestResults.camera === 'passed'">
                                        <div class="flex items-center text-green-600">
                                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Working
                                        </div>
                                    </template>
                                    <template x-if="preCallTestResults.camera === 'failed'">
                                        <div class="flex items-center text-red-600">
                                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                            Not Available
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Microphone Test -->
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <span class="font-medium text-gray-700">Microphone</span>
                                </div>
                                <div class="flex items-center">
                                    <template x-if="preCallTestResults.microphone === null">
                                        <div class="flex items-center text-gray-500">
                                            <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Checking...
                                        </div>
                                    </template>
                                    <template x-if="preCallTestResults.microphone === 'checking'">
                                        <div class="flex items-center text-blue-600">
                                            <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Testing...
                                        </div>
                                    </template>
                                    <template x-if="preCallTestResults.microphone === 'passed'">
                                        <div class="flex items-center text-green-600">
                                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Working
                                        </div>
                                    </template>
                                    <template x-if="preCallTestResults.microphone === 'failed'">
                                        <div class="flex items-center text-red-600">
                                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                            Not Available
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Bandwidth Test -->
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <span class="font-medium text-gray-700">Connection</span>
                                </div>
                                <div class="flex items-center">
                                    <template x-if="preCallTestResults.bandwidth === null">
                                        <div class="flex items-center text-gray-500">
                                            <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Checking...
                                        </div>
                                    </template>
                                    <template x-if="preCallTestResults.bandwidth === 'checking'">
                                        <div class="flex items-center text-blue-600">
                                            <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Testing...
                                        </div>
                                    </template>
                                    <template x-if="preCallTestResults.bandwidth === 'passed'">
                                        <div class="flex items-center text-green-600">
                                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Good
                                        </div>
                                    </template>
                                    <template x-if="preCallTestResults.bandwidth === 'failed'">
                                        <div class="flex items-center text-yellow-600">
                                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            Limited
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex justify-end space-x-3">
                            <button 
                                @click="showPreCallTest = false; preCallTestInProgress = false"
                                :disabled="preCallTestInProgress"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition disabled:opacity-50"
                            >
                                Cancel
                            </button>
                            <button 
                                @click="joinConsultation()"
                                :disabled="preCallTestInProgress || preCallTestResults.browser === 'failed'"
                                class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition disabled:opacity-50"
                            >
                                Join Anyway
                            </button>
                        </div>
                    </div>
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
// Prevent infinite reload loops
(function() {
    const reloadKey = 'consultation_session_reload_prevent';
    const maxReloads = 3;
    const reloadWindow = 30000; // 30 seconds
    
    // Check if we've reloaded too many times
    const reloadCount = parseInt(sessionStorage.getItem(reloadKey) || '0');
    const lastReloadTime = parseInt(sessionStorage.getItem(reloadKey + '_time') || '0');
    const now = Date.now();
    
    if (reloadCount >= maxReloads && (now - lastReloadTime) < reloadWindow) {
        console.error('Preventing infinite reload loop detected');
        sessionStorage.setItem(reloadKey, '0');
        sessionStorage.setItem(reloadKey + '_time', '0');
        alert('Page reload loop detected. Please refresh manually if needed.');
        return;
    }
    
    // Track reloads
    if (performance.navigation.type === 1) { // Reload
        sessionStorage.setItem(reloadKey, (reloadCount + 1).toString());
        sessionStorage.setItem(reloadKey + '_time', now.toString());
    } else {
        // Reset on fresh load
        sessionStorage.setItem(reloadKey, '0');
        sessionStorage.setItem(reloadKey + '_time', '0');
    }
})();

// Global error handler to prevent reload loops
window.addEventListener('error', function(event) {
    console.error('Global error caught:', event.error, event.filename, event.lineno);
    // Prevent default error handling that might cause reload
    event.preventDefault();
    return false;
});

// Prevent unhandled promise rejections from causing issues
window.addEventListener('unhandledrejection', function(event) {
    console.error('Unhandled promise rejection:', event.reason);
    // Prevent default handling
    event.preventDefault();
});

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
        state: 'idle', // idle, loading, connected, error, ended, precall-test
        loading: false,
        ending: false,
        errorType: null,
        errorTitle: '',
        errorMessage: '',
        showReviewModal: false,
        sdkLoaded: false, // Track if OpenTok SDK is loaded
        initialized: false, // Track if component has been initialized
        videoJoinRetries: 0, // Track retry attempts for video join
        maxVideoJoinRetries: 20, // Maximum retries (20 * 3 seconds = 60 seconds)
        beforeUnloadListenerAdded: false, // Track if beforeunload listener was added
        
        // Pre-Call Test
        showPreCallTest: false,
        preCallTestResults: {
            camera: null, // null, 'checking', 'passed', 'failed'
            microphone: null,
            bandwidth: null,
            browser: null
        },
        preCallTestInProgress: false,
        
        // Live Captions
        captionsEnabled: false,
        captions: [], // Array of caption objects {text, timestamp}
        showCaptions: false,
        
        // OpenTok SDK
        session: null,
        publisher: null,
        subscribers: [], // Array to track all subscribers
        statusPollInterval: null,
        connectionQualityInterval: null,
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
            try {
                // Prevent multiple initializations
                if (this.initialized) {
                    console.warn('Component already initialized, skipping...');
                    return;
                }
                this.initialized = true;
                
                // Ensure state is initialized
                this.state = this.state || 'idle';
                this.loading = false;
                
                // Check if OpenTok SDK is already loaded
                this.checkSDKStatus();
                
                // Wait for OpenTok.js SDK to load (for video/voice modes)
                // Don't await this - let it run in background to prevent blocking
                if (this.mode === 'video' || this.mode === 'voice') {
                    this.waitForOpenTokSDK().catch(err => {
                        console.error('Error waiting for OpenTok SDK:', err);
                        // Don't show error immediately, let user manually join
                    });
                }

                // Only add beforeunload listener once
                if (!this.beforeUnloadListenerAdded) {
                    window.addEventListener('beforeunload', () => {
                        this.cleanupVonage();
                    });
                    this.beforeUnloadListenerAdded = true;
                }
            } catch (error) {
                console.error('Error in init():', error);
                this.showError('generic', 'Initialization Error', 
                    'Failed to initialize consultation session. Please refresh the page.');
                // Don't reload - just show error
            }
        },
        
        checkSDKStatus() {
            // Check if SDK is loaded and update reactive property
            this.sdkLoaded = typeof OT !== 'undefined';
            
            // If not loaded yet, check periodically
            if (!this.sdkLoaded && (this.mode === 'video' || this.mode === 'voice')) {
                const checkInterval = setInterval(() => {
                    this.sdkLoaded = typeof OT !== 'undefined';
                    if (this.sdkLoaded) {
                        clearInterval(checkInterval);
                        console.log('OpenTok.js SDK loaded successfully');
                    }
                }, 500);
                
                // Stop checking after 10 seconds
                setTimeout(() => {
                    clearInterval(checkInterval);
                    if (!this.sdkLoaded) {
                        console.error('OpenTok.js SDK failed to load after 10 seconds');
                    }
                }, 10000);
            }
        },
        
        async waitForOpenTokSDK() {
            // Wait up to 10 seconds for OpenTok SDK to load
            let attempts = 0;
            const maxAttempts = 20; // 20 attempts * 500ms = 10 seconds
            
            while (typeof OT === 'undefined' && attempts < maxAttempts) {
                await new Promise(resolve => setTimeout(resolve, 500));
                attempts++;
            }
            
            this.sdkLoaded = typeof OT !== 'undefined';
            
            if (!this.sdkLoaded) {
                console.error('OpenTok.js SDK failed to load after 10 seconds');
                this.showError('generic', 'SDK Loading Error', 'The video call SDK failed to load. Please refresh the page and check your internet connection.');
                return false;
            }
            
            console.log('OpenTok.js SDK loaded successfully');
            return true;
        },
        
        async runPreCallTest() {
            if (this.mode !== 'video' && this.mode !== 'voice') {
                return this.joinConsultation();
            }
            
            this.showPreCallTest = true;
            this.preCallTestInProgress = true;
            
            // Reset results
            this.preCallTestResults = {
                camera: null,
                microphone: null,
                bandwidth: null,
                browser: null
            };
            
            try {
                // Check browser support first
                if (typeof OT === 'undefined') {
                    await this.waitForOpenTokSDK();
                }
                
                if (typeof OT === 'undefined') {
                    this.preCallTestResults.browser = 'failed';
                    this.preCallTestInProgress = false;
                    return;
                }
                
                const systemRequirements = OT.checkSystemRequirements();
                this.preCallTestResults.browser = systemRequirements === 1 ? 'passed' : 'failed';
                
                if (systemRequirements !== 1) {
                    this.preCallTestInProgress = false;
                    return;
                }
                
                // Test camera
                this.preCallTestResults.camera = 'checking';
                try {
                    const devices = await OT.getDevices();
                    const videoDevices = devices.filter(device => device.kind === 'videoInput');
                    this.preCallTestResults.camera = videoDevices.length > 0 ? 'passed' : 'failed';
                } catch (error) {
                    console.error('Camera test error:', error);
                    this.preCallTestResults.camera = 'failed';
                }
                
                // Test microphone
                this.preCallTestResults.microphone = 'checking';
                try {
                    const devices = await OT.getDevices();
                    const audioDevices = devices.filter(device => device.kind === 'audioInput');
                    this.preCallTestResults.microphone = audioDevices.length > 0 ? 'passed' : 'failed';
                } catch (error) {
                    console.error('Microphone test error:', error);
                    this.preCallTestResults.microphone = 'failed';
                }
                
                // Test bandwidth (simplified - check connection quality)
                this.preCallTestResults.bandwidth = 'checking';
                try {
                    // Create a temporary test publisher to check bandwidth
                    const testContainer = document.createElement('div');
                    testContainer.style.position = 'absolute';
                    testContainer.style.left = '-9999px';
                    document.body.appendChild(testContainer);
                    
                    const testPublisher = OT.initPublisher(testContainer, {
                        videoSource: null, // Audio only for bandwidth test
                        publishAudio: true,
                        publishVideo: false,
                        width: 0,
                        height: 0
                    }, (error) => {
                        if (error) {
                            this.preCallTestResults.bandwidth = 'failed';
                        } else {
                            // Check stats after a short delay
                            setTimeout(() => {
                                testPublisher.getStats((err, stats) => {
                                    if (err || !stats) {
                                        this.preCallTestResults.bandwidth = 'failed';
                                    } else {
                                        // Simple check - if we can get stats, connection is good
                                        this.preCallTestResults.bandwidth = 'passed';
                                    }
                                    testPublisher.destroy();
                                    document.body.removeChild(testContainer);
                                });
                            }, 1000);
                        }
                    });
                } catch (error) {
                    console.error('Bandwidth test error:', error);
                    this.preCallTestResults.bandwidth = 'failed';
                }
                
            } catch (error) {
                console.error('Pre-call test error:', error);
            } finally {
                this.preCallTestInProgress = false;
            }
        },
        
        async joinConsultation() {
            // Close pre-call test if open
            this.showPreCallTest = false;
            
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
            // Check if OpenTok SDK is loaded before proceeding
            if (typeof OT === 'undefined') {
                console.warn('OpenTok.js SDK not loaded, waiting for it to load...');
                const sdkLoaded = await this.waitForOpenTokSDK();
                if (!sdkLoaded) {
                    this.loading = false;
                    return;
                }
            }
            
            this.state = 'loading';
            this.loading = true;
            this.errorType = null;

            try {
                if (!this.isPatient) {
                    const createResponse = await fetch(this.videoCreateUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({})
                    });
                    
                    if (!createResponse.ok) {
                        const createData = await createResponse.json();
                        console.error('Failed to create video room:', createData);
                        this.showError('generic', 'Room Creation Failed', createData.message || 'Failed to create video room. Please try again.');
                        return;
                    }
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
                    // Room doesn't exist yet - retry after a delay
                    this.videoJoinRetries++;
                    
                    // Use message from backend if available
                    const errorMessage = data.message || 'The video room has not been created yet. Please wait for the doctor to start the session.';
                    
                    if (this.videoJoinRetries >= this.maxVideoJoinRetries) {
                        this.showError('generic', 'Room Not Available', errorMessage + ' Please ask the doctor to start the session or try again later.');
                        this.loading = false;
                        return;
                    }
                    
                    // Increase delay with each retry to avoid rate limiting (3s, 5s, 7s, etc.)
                    const retryDelay = Math.min(3000 + (this.videoJoinRetries * 2000), 10000);
                    console.log(`Video room not found, retrying in ${retryDelay/1000} seconds... (attempt ${this.videoJoinRetries}/${this.maxVideoJoinRetries})`);
                    // Keep loading state and show waiting message
                    this.state = 'loading';
                    setTimeout(() => {
                        this.joinVideoRoom();
                    }, retryDelay);
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
                    // Rate limited - wait longer before retrying
                    this.videoJoinRetries++;
                    const retryAfter = data.retry_after || 10; // Use server's retry_after or default to 10 seconds
                    
                    if (this.videoJoinRetries >= this.maxVideoJoinRetries) {
                        this.showError('429', 'Too Many Requests', 'You have made too many requests. Please wait a moment and try again.');
                        this.loading = false;
                        return;
                    }
                    
                    console.log(`Rate limited, retrying after ${retryAfter} seconds... (attempt ${this.videoJoinRetries}/${this.maxVideoJoinRetries})`);
                    this.state = 'loading';
                    setTimeout(() => {
                        this.joinVideoRoom();
                    }, retryAfter * 1000);
                    return;
                }

                if (!response.ok || !data.success) {
                    this.showError('generic', 'Connection Error', data.message || `Server error (${response.status})`);
                    return;
                }

                // Reset retry counter on successful response
                this.videoJoinRetries = 0;
                
                // Validate required data
                if (!data.token || !data.session_id) {
                    console.error('Missing required data from server:', data);
                    this.showError('generic', 'Invalid Response', 'The server returned incomplete data. Please try again.');
                    return;
                }
                
                // Use applicationId (JWT) from backend, fallback to api_key or config values for legacy
                const apiKey = data.applicationId || data.api_key || this.applicationId || this.vonageApiKey;
                
                if (!apiKey) {
                    console.error('No API key or Application ID available');
                    this.showError('generic', 'Configuration Error', 'Video call configuration is missing. Please contact support.');
                    return;
                }
                
                await this.initializeVonage(apiKey, data.token, data.session_id);
            } catch (error) {
                console.error('Error joining video room:', error);
                this.showError('generic', 'Network Error', error.message || 'Network error. Please check your connection and try again.');
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
                
                // Check system requirements before initializing (per Vonage documentation)
                // https://developer.vonage.com/en/video/guides/join-session
                const systemRequirements = OT.checkSystemRequirements();
                if (systemRequirements !== 1) {
                    const errorMsg = 'Your browser does not support WebRTC. Please use a modern browser like Chrome, Firefox, or Safari.';
                    this.showError('generic', 'Browser Not Supported', errorMsg);
                    this.state = 'error';
                    return;
                }
                
                // Initialize OpenTok session with performance optimizations
                // According to Vonage documentation: https://developer.vonage.com/en/video/guides/create-session
                // singlePeerConnection: All subscriber streams delivered with a single connection to Media Router
                // Benefits: Reduced client resource consumption, improved rate control, support for larger sessions
                const sessionOptions = {
                    singlePeerConnection: true // Available in OpenTok.js 2.28.0+
                };
                this.session = OT.initSession(apiKey, sessionId, sessionOptions);
                
                // Track connection count for monitoring
                let connectionCount = 0;
                
                // Handle session events using object syntax (per Vonage documentation)
                // https://developer.vonage.com/en/video/guides/join-session
                this.session.on({
                    // Session connection
                    sessionConnected: () => {
                        console.log('OpenTok session connected');
                        connectionCount = 1; // Include own connection
                        
                        // Enable captions if available
                        if (this.captionsEnabled && this.session.caption) {
                            this.session.caption.start((error) => {
                                if (error) {
                                    console.error('Failed to start captions:', error);
                                } else {
                                    console.log('Captions started');
                                }
                            });
                        }
                    },
                    
                    // Live Captions events
                    captionReceived: (event) => {
                        if (this.showCaptions) {
                            this.captions.push({
                                text: event.text,
                                timestamp: Date.now()
                            });
                            // Keep only last 10 captions
                            if (this.captions.length > 10) {
                                this.captions.shift();
                            }
                        }
                    },
                    
                    // Automatic reconnection (per Vonage documentation)
                    sessionReconnecting: () => {
                        console.log('Session reconnecting...');
                        this.showNotification('Reconnecting to session...', 'info');
                    },
                    
                    sessionReconnected: () => {
                        console.log('Session reconnected successfully');
                        this.showNotification('Reconnected to session', 'success');
                    },
                    
                    // Session disconnection with reason checking (per Vonage documentation)
                    sessionDisconnected: (event) => {
                        console.log('OpenTok session disconnected', event);
                        
                        // Check disconnection reason (per Vonage documentation)
                        if (event.reason === 'networkDisconnected') {
                            this.showError('generic', 'Connection Lost', 
                                'You lost your internet connection. Please check your connection and try connecting again.');
                        } else if (event.reason === 'clientDisconnected') {
                            console.log('Client disconnected normally');
                        } else {
                            console.log('Session disconnected:', event.reason || 'Unknown reason');
                        }
                        
                        this.state = 'ended';
                    },
                    
                    // Track when other clients connect/disconnect (per Vonage documentation)
                    connectionCreated: (event) => {
                        connectionCount++;
                        if (event.connection.connectionId !== this.session.connection.connectionId) {
                            console.log(`Another client connected. ${connectionCount} total connections.`);
                            this.showNotification('Participant joined the session', 'info');
                        }
                    },
                    
                    connectionDestroyed: (event) => {
                        connectionCount--;
                        console.log(`A client disconnected. ${connectionCount} total connections.`);
                        if (connectionCount <= 1) {
                            this.showNotification('Participant left the session', 'warning');
                        }
                    },
                    
                    // Handle session errors
                    error: (error) => {
                        console.error('OpenTok session error:', error);
                        
                        // Handle connection limit exceeded (per Vonage documentation)
                        // https://developer.vonage.com/en/video/guides/broadcast/interactive#connection-and-stream-limit-errors-in-the-opentok-js-web
                        if (error.name === 'OT_CONNECTION_LIMIT_EXCEEDED') {
                            this.showError('connection_limit', 'Session Full', 
                                'The consultation session has reached the maximum number of participants (15,000). Please try again later or contact support.');
                            this.state = 'error';
                            return;
                        }
                        
                        // Handle specific error codes (per Vonage documentation)
                        if (error.code === 1004 || error.code === 1006 || error.code === 1008) {
                            // Token expired or network issues
                            if (this.mode === 'video') {
                                this.refreshVideoToken();
                                return;
                            }
                        }
                        
                        this.showError('generic', 'Session Error', 
                            'An error occurred with the consultation session. Please try again.');
                        this.state = 'error';
                    }
                });
                
                // Connect to session with token (per Vonage documentation)
                // https://developer.vonage.com/en/video/guides/join-session#troubleshooting-session-connection-issues-javascript
                this.session.connect(token, (error) => {
                    if (error) {
                        console.error('Error connecting to OpenTok session:', error);
                        
                        // Handle connection limit exceeded (per Vonage documentation)
                        // https://developer.vonage.com/en/video/guides/broadcast/interactive#connection-and-stream-limit-errors-in-the-opentok-js-web
                        if (error.name === 'OT_CONNECTION_LIMIT_EXCEEDED') {
                            this.showError('connection_limit', 'Session Full', 
                                'The consultation session has reached the maximum number of participants (15,000). Please try again later or contact support.');
                            this.state = 'error';
                            return;
                        }
                        
                        // Handle specific connection errors (per Vonage documentation)
                        if (error.name === 'OT_NOT_CONNECTED' || error.code === 1006) {
                            this.showError('generic', 'Connection Failed', 
                                'Failed to connect. Please check your connection and try connecting again.');
                        } else if (error.code === 1004) {
                            // Invalid or expired token
                            if (this.mode === 'video') {
                                this.refreshVideoToken();
                                return;
                            } else {
                                this.showError('generic', 'Token Error', 
                                    'Your session token is invalid or expired. Please refresh and try again.');
                            }
                        } else {
                            // Unknown error
                            this.showError('generic', 'Connection Error', 
                                error.message || 'An unknown error occurred connecting. Please try again later.');
                        }
                        
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
                this.showError('generic', 'Initialization Error', 
                    error.message || 'Failed to initialize consultation. Please try again.');
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
            // Per Vonage documentation: https://developer.vonage.com/en/use-cases/one-on-one-video-with-vonage-video-api
            this.publisher = OT.initPublisher(publisherContainer, {
                insertMode: 'append', // Ensures video is appended to container
                resolution: '1280x720', // HD quality (720p)
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
            // Per Vonage documentation: https://developer.vonage.com/en/use-cases/one-on-one-video-with-vonage-video-api
            this.session.on('streamCreated', (event) => {
                console.log('Remote stream created:', event.stream);
                const subscriberOptions = {
                    insertMode: 'append', // Ensures subscriber video is appended to container
                    width: '100%',
                    height: '100%',
                    subscribeToAudio: true,
                    subscribeToVideo: true
                };
                const subscriber = this.session.subscribe(event.stream, subscriberContainer, subscriberOptions, (error) => {
                    if (error) {
                        console.error('Error subscribing to stream:', error);
                        
                        // Handle stream limit exceeded (per Vonage documentation)
                        // https://developer.vonage.com/en/video/guides/broadcast/interactive#connection-and-stream-limit-errors-in-the-opentok-js-web
                        if (error.name === 'OT_STREAM_LIMIT_EXCEEDED') {
                            this.showError('stream_limit', 'Stream Limit Reached', 
                                'The consultation session has reached the maximum number of streams (15,000). Please try again later or contact support.');
                            return;
                        }
                        
                        this.showNotification('Failed to subscribe to participant stream', 'error');
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
            // Per Vonage documentation: https://developer.vonage.com/en/use-cases/one-on-one-video-with-vonage-video-api
            this.publisher = OT.initPublisher(publisherContainer, {
                insertMode: 'append', // Ensures audio publisher is appended to container
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
            // Per Vonage documentation: https://developer.vonage.com/en/use-cases/one-on-one-video-with-vonage-video-api
            this.session.on('streamCreated', (event) => {
                console.log('Remote audio stream created:', event.stream);
                const subscriberContainer = document.createElement('div');
                subscriberContainer.id = 'subscriber-container';
                subscriberContainer.className = 'hidden';
                container.appendChild(subscriberContainer);
                
                const subscriberOptions = {
                    insertMode: 'append', // Ensures subscriber audio is appended to container
                    width: '100%',
                    height: 'auto',
                    subscribeToAudio: true,
                    subscribeToVideo: false // Audio-only
                };
                const subscriber = this.session.subscribe(event.stream, subscriberContainer, subscriberOptions, (error) => {
                    if (error) {
                        console.error('Error subscribing to audio stream:', error);
                        
                        // Handle stream limit exceeded (per Vonage documentation)
                        // https://developer.vonage.com/en/video/guides/broadcast/interactive#connection-and-stream-limit-errors-in-the-opentok-js-web
                        if (error.name === 'OT_STREAM_LIMIT_EXCEEDED') {
                            this.showError('stream_limit', 'Stream Limit Reached', 
                                'The consultation session has reached the maximum number of streams (15,000). Please try again later or contact support.');
                            return;
                        }
                        
                        this.showNotification('Failed to subscribe to participant audio stream', 'error');
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
        
        toggleCaptions() {
            if (!this.session) return;
            
            this.showCaptions = !this.showCaptions;
            this.captionsEnabled = this.showCaptions;
            
            if (this.showCaptions) {
                // Start captions if session is connected
                if (this.session.connection && this.session.caption) {
                    this.session.caption.start((error) => {
                        if (error) {
                            console.error('Failed to start captions:', error);
                            this.showNotification('Failed to enable captions. They may not be available for this session.', 'warning');
                            this.showCaptions = false;
                            this.captionsEnabled = false;
                        } else {
                            console.log('Captions enabled');
                            this.showNotification('Live captions enabled', 'info');
                        }
                    });
                } else {
                    this.showNotification('Captions are not available for this session', 'warning');
                    this.showCaptions = false;
                    this.captionsEnabled = false;
                }
            } else {
                // Stop captions
                if (this.session.caption) {
                    this.session.caption.stop((error) => {
                        if (error) {
                            console.error('Failed to stop captions:', error);
                        } else {
                            console.log('Captions disabled');
                        }
                    });
                }
                this.captions = [];
            }
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
                    // Per Vonage documentation: https://developer.vonage.com/en/video/guides/publish-stream
                    const screenPublisher = OT.initPublisher('publisher-container', {
                        insertMode: 'append', // Ensures screen share is appended to container
                        resolution: '1280x720', // HD quality (720p) for screen sharing
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
            if (!this.session || this.connectionQualityInterval) return;
            
            this.connectionQualityInterval = setInterval(() => {
                if (this.publisher && this.state === 'connected') {
                    this.publisher.getStats((error, stats) => {
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
        
        showNotification(message, type = 'info') {
            // Display a temporary notification without changing error state
            // Types: 'info', 'success', 'warning', 'error'
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg max-w-sm ${
                type === 'success' ? 'bg-green-500 text-white' :
                type === 'warning' ? 'bg-yellow-500 text-white' :
                type === 'error' ? 'bg-red-500 text-white' :
                'bg-blue-500 text-white'
            }`;
            notification.textContent = message;
            notification.style.transition = 'opacity 0.3s ease-in-out';
            
            document.body.appendChild(notification);
            
            // Auto-remove after 3 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
            
            console.log(`[${type.toUpperCase()}] ${message}`);
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
            
            if (this.connectionQualityInterval) {
                clearInterval(this.connectionQualityInterval);
                this.connectionQualityInterval = null;
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
