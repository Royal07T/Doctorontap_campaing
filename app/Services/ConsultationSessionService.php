<?php

namespace App\Services;

use App\Models\Consultation;
use App\Models\ConsultationSession;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * ConsultationSessionService
 * 
 * Orchestrates Vonage session creation and management for in-app consultations.
 * Handles voice, video, and chat modes.
 * 
 * SECURITY:
 * - Tokens are encrypted before storage
 * - Only assigned doctor and owning patient can access sessions
 * - Sessions expire after consultation ends
 */
class ConsultationSessionService
{
    protected $videoService;
    protected $conversationService;
    protected $voiceService;
    protected $acquiredLocks = []; // Store acquired locks for proper release

    public function __construct(
        VonageVideoService $videoService,
        VonageConversationService $conversationService,
        VonageVoiceService $voiceService
    ) {
        $this->videoService = $videoService;
        $this->conversationService = $conversationService;
        $this->voiceService = $voiceService;
    }

    /**
     * Create a new consultation session based on consultation mode
     * 
     * @param Consultation $consultation
     * @return array ['success' => bool, 'session' => ConsultationSession|null, 'error' => string|null]
     */
    public function createSession(Consultation $consultation): array
    {
        // Only create sessions for in-app modes
        if (!$consultation->isInAppMode()) {
            return [
                'success' => false,
                'message' => 'Session creation only supported for voice, video, or chat modes',
                'error' => 'invalid_mode'
            ];
        }

        // CONCURRENCY LOCKING: Prevent race conditions when multiple requests create sessions
        $lockKey = "consultation_session_lock:{$consultation->id}";
        $lockTimeout = 10; // seconds
        
        // Try to acquire lock (using Redis or Cache)
        $lockAcquired = $this->acquireLock($lockKey, $lockTimeout);
        if (!$lockAcquired) {
            Log::warning('Failed to acquire lock for session creation', [
                'consultation_id' => $consultation->id,
                'lock_key' => $lockKey,
                'timestamp' => now()->toIso8601String()
            ]);
            
            // Wait briefly and check for existing session
            usleep(100000); // 100ms
            $existingActiveSession = $consultation->activeSession();
            if ($existingActiveSession) {
                return [
                    'success' => true,
                    'session' => $existingActiveSession,
                    'vonage_session_id' => $existingActiveSession->vonage_session_id,
                    'message' => 'Using existing active session (acquired after lock wait)'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Unable to acquire lock for session creation. Please try again.',
                'error' => 'lock_timeout'
            ];
        }

        try {
            // FIX 4: Single Active Session Guarantee (with lock protection)
            // Check if there's already an active session for this consultation
            DB::beginTransaction();
            
            $existingActiveSession = DB::table('consultation_sessions')
                ->where('consultation_id', $consultation->id)
                ->whereIn('status', ['pending', 'waiting', 'active'])
                ->lockForUpdate() // Database-level lock
                ->first();
            
            if ($existingActiveSession) {
                $session = ConsultationSession::find($existingActiveSession->id);
                
                DB::commit();
                $this->releaseLock($lockKey);
                
                Log::info('Active session already exists, returning existing session', [
                    'event_type' => 'session_creation_attempt',
                    'consultation_id' => $consultation->id,
                    'session_id' => $session->id,
                    'status' => $session->status,
                    'vonage_session_id' => $session->vonage_session_id,
                    'timestamp' => now()->toIso8601String()
                ]);
                
                return [
                    'success' => true,
                    'session' => $session,
                    'vonage_session_id' => $session->vonage_session_id,
                    'message' => 'Using existing active session'
                ];
            }

            // Ensure doctor is assigned
            if (!$consultation->doctor_id) {
                DB::rollBack();
                $this->releaseLock($lockKey);
                return [
                    'success' => false,
                    'message' => 'Doctor must be assigned before creating session',
                    'error' => 'no_doctor'
                ];
            }

            $mode = $consultation->consultation_mode;
            $doctor = $consultation->doctor;
            $patient = $consultation->patient;

            if (!$doctor) {
                DB::rollBack();
                $this->releaseLock($lockKey);
                return [
                    'success' => false,
                    'message' => 'Doctor not found',
                    'error' => 'doctor_not_found'
                ];
            }

            // Create session based on mode
            switch ($mode) {
                case 'video':
                    $result = $this->createVideoSession($consultation, $doctor, $patient);
                    break;
                case 'chat':
                    $result = $this->createChatSession($consultation, $doctor, $patient);
                    break;
                case 'voice':
                    $result = $this->createVoiceSession($consultation, $doctor, $patient);
                    break;
                default:
                    throw new \Exception("Unsupported consultation mode: {$mode}");
            }

            // FIX 5: Graceful Fallback When Vonage is Disabled
            // If Vonage service is disabled, fail gracefully without blocking consultation
            if (!$result['success']) {
                DB::rollBack();
                $this->releaseLock($lockKey);
                
                // Check if error is due to Vonage being disabled
                if (isset($result['error']) && $result['error'] === 'disabled') {
                    Log::warning('Vonage service disabled, consultation can proceed without session', [
                        'event_type' => 'session_creation_graceful_failure',
                        'consultation_id' => $consultation->id,
                        'mode' => $mode,
                        'error' => $result['error'],
                        'message' => $result['message'] ?? 'Vonage service disabled',
                        'timestamp' => now()->toIso8601String()
                    ]);
                    
                    // Return graceful failure - consultation can still proceed
                    return [
                        'success' => false,
                        'message' => 'In-app consultation mode requires Vonage service to be enabled. Please contact support or use WhatsApp consultation mode.',
                        'error' => 'vonage_disabled',
                        'graceful_failure' => true // Flag for controller to handle gracefully
                    ];
                }
                
                // For other errors, return as-is
                return $result;
            }

            // Create ConsultationSession record
            $session = ConsultationSession::create([
                'consultation_id' => $consultation->id,
                'vonage_session_id' => $result['vonage_session_id'],
                'mode' => $mode,
                'status' => 'pending',
                'token_expires_at' => now()->addHours(24), // Tokens expire in 24 hours
            ]);

            // Store encrypted tokens
            if (isset($result['doctor_token'])) {
                $session->setDoctorToken($result['doctor_token']);
            }
            if (isset($result['patient_token'])) {
                $session->setPatientToken($result['patient_token']);
            }
            $session->save();

            // Update consultation session status
            $consultation->update([
                'session_status' => 'scheduled',
            ]);

            DB::commit();

            // Release lock after successful creation
            $this->releaseLock($lockKey);

            // Structured logging for session creation
            Log::info('Consultation session created successfully', [
                'event_type' => 'session_created',
                'consultation_id' => $consultation->id,
                'session_id' => $session->id,
                'mode' => $mode,
                'status' => $session->status,
                'vonage_session_id' => $result['vonage_session_id'],
                'doctor_id' => $doctor->id,
                'patient_id' => $patient?->id,
                'timestamp' => now()->toIso8601String()
            ]);

            return [
                'success' => true,
                'session' => $session,
                'vonage_session_id' => $result['vonage_session_id']
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Release lock on error
            $this->releaseLock($lockKey);
            
            // Structured error logging
            Log::error('Failed to create consultation session', [
                'event_type' => 'session_creation_failed',
                'consultation_id' => $consultation->id,
                'mode' => $mode,
                'doctor_id' => $doctor->id ?? null,
                'patient_id' => $patient?->id,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toIso8601String()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create consultation session',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Acquire distributed lock for session creation
     * Uses Redis if available, falls back to Cache
     */
    /**
     * Acquire a distributed lock using Laravel Cache
     * 
     * Uses Cache::lock() which works with any cache driver (Redis, Memcached, file, etc.)
     * Falls back gracefully if locking is unavailable.
     * 
     * @param string $lockKey
     * @param int $timeoutSeconds
     * @return bool
     */
    protected function acquireLock(string $lockKey, int $timeoutSeconds): bool
    {
        try {
            // Use Laravel's Cache::lock() which is driver-agnostic
            // This works with Redis, Memcached, file cache, or any cache driver
            $lock = Cache::lock($lockKey, $timeoutSeconds);
            
            // Try to acquire the lock (non-blocking)
            $acquired = $lock->get();
            
            if ($acquired !== false) {
                // Store the lock instance for later release
                $this->acquiredLocks[$lockKey] = $lock;
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::warning('Failed to acquire lock', [
                'event_type' => 'lock_acquisition_failed',
                'lock_key' => $lockKey,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'timestamp' => now()->toIso8601String()
            ]);
            // If locking fails, return false
            // The database-level lock (lockForUpdate) will still provide protection
            return false;
        }
    }

    /**
     * Release a distributed lock
     * 
     * @param string $lockKey
     * @return void
     */
    protected function releaseLock(string $lockKey): void
    {
        try {
            // Release the stored lock instance if available
            if (isset($this->acquiredLocks[$lockKey])) {
                $this->acquiredLocks[$lockKey]->release();
                unset($this->acquiredLocks[$lockKey]);
            } else {
                // Fallback: try to get and release the lock
                $lock = Cache::lock($lockKey);
                $lock->release();
            }
        } catch (\Exception $e) {
            Log::warning('Failed to release lock', [
                'event_type' => 'lock_release_failed',
                'lock_key' => $lockKey,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'timestamp' => now()->toIso8601String()
            ]);
            // Non-critical, continue anyway
            // Lock will expire automatically after timeout
            unset($this->acquiredLocks[$lockKey]);
        }
    }

    /**
     * Transition session to a new state with validation
     * 
     * STATE MACHINE: Enforces valid state transitions
     * 
     * @param ConsultationSession $session
     * @param string $newStatus
     * @param array $context Additional context for logging
     * @return bool Success status
     */
    public function transitionToState(ConsultationSession $session, string $newStatus, array $context = []): bool
    {
        $oldStatus = $session->status;
        
        // Validate transition
        if (!$session->canTransitionTo($newStatus)) {
            Log::warning('Invalid state transition attempted', [
                'event_type' => 'state_transition_rejected',
                'session_id' => $session->id,
                'consultation_id' => $session->consultation_id,
                'current_status' => $oldStatus,
                'attempted_status' => $newStatus,
                'valid_next_states' => $session->getValidNextStates(),
                'context' => $context,
                'timestamp' => now()->toIso8601String()
            ]);
            
            return false;
        }

        try {
            DB::beginTransaction();

            // Update session status
            $updateData = ['status' => $newStatus];
            
            // Update timestamps based on state
            if ($newStatus === 'active' && !$session->session_started_at) {
                $updateData['session_started_at'] = now();
            }
            
            if (in_array($newStatus, ['ended', 'failed', 'cancelled']) && !$session->session_ended_at) {
                $updateData['session_ended_at'] = now();
            }
            
            if ($newStatus === 'failed' && isset($context['error_message'])) {
                $updateData['error_message'] = $context['error_message'];
            }

            $session->update($updateData);

            // Update consultation session_status
            $consultation = $session->consultation;
            if ($consultation) {
                $consultationSessionStatus = match($newStatus) {
                    'active' => 'active',
                    'ended' => 'completed',
                    'failed' => 'cancelled',
                    'cancelled' => 'cancelled',
                    default => $consultation->session_status
                };
                
                if ($consultation->session_status !== $consultationSessionStatus) {
                    $consultation->update(['session_status' => $consultationSessionStatus]);
                }
            }

            DB::commit();

            // Structured logging for state transition
            Log::info('Session state transitioned', [
                'event_type' => 'state_transition',
                'session_id' => $session->id,
                'consultation_id' => $session->consultation_id,
                'previous_status' => $oldStatus,
                'new_status' => $newStatus,
                'context' => $context,
                'timestamp' => now()->toIso8601String()
            ]);

            // Log token invalidation for terminal states
            if (in_array($newStatus, ['ended', 'failed', 'cancelled'])) {
                Log::info('Session tokens invalidated due to terminal state', [
                    'event_type' => 'token_invalidation',
                    'session_id' => $session->id,
                    'consultation_id' => $session->consultation_id,
                    'status' => $newStatus,
                    'reason' => $context['reason'] ?? 'state_transition',
                    'timestamp' => now()->toIso8601String()
                ]);
            }

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to transition session state', [
                'event_type' => 'state_transition_failed',
                'session_id' => $session->id,
                'consultation_id' => $session->consultation_id,
                'current_status' => $oldStatus,
                'attempted_status' => $newStatus,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'timestamp' => now()->toIso8601String()
            ]);
            
            return false;
        }
    }

    /**
     * Create a video session
     */
    protected function createVideoSession(Consultation $consultation, Doctor $doctor, ?Patient $patient): array
    {
        // Create Vonage Video session
        $sessionResult = $this->videoService->createSession();
        if (!$sessionResult['success']) {
            return $sessionResult;
        }

        $sessionId = $sessionResult['session_id'];

        // Generate tokens for doctor and patient
        $doctorTokenResult = $this->videoService->generateToken(
            $sessionId,
            'publisher',
            "Dr. {$doctor->name}",
            86400 // 24 hours
        );

        if (!$doctorTokenResult['success']) {
            return $doctorTokenResult;
        }

        $patientName = $patient ? "{$patient->first_name} {$patient->last_name}" : "{$consultation->first_name} {$consultation->last_name}";
        $patientTokenResult = $this->videoService->generateToken(
            $sessionId,
            'publisher',
            $patientName,
            86400
        );

        if (!$patientTokenResult['success']) {
            return $patientTokenResult;
        }

        return [
            'success' => true,
            'vonage_session_id' => $sessionId,
            'doctor_token' => $doctorTokenResult['token'],
            'patient_token' => $patientTokenResult['token'],
        ];
    }

    /**
     * Create a chat session
     */
    protected function createChatSession(Consultation $consultation, Doctor $doctor, ?Patient $patient): array
    {
        $conversationName = "Consultation #{$consultation->reference}";

        // Create Vonage Conversation
        $conversationResult = $this->conversationService->createConversation($conversationName);
        if (!$conversationResult['success']) {
            return $conversationResult;
        }

        $conversationId = $conversationResult['conversation_id'];

        // Generate tokens for doctor and patient
        $doctorUserId = "doctor_{$doctor->id}";
        $doctorTokenResult = $this->conversationService->generateToken(
            $conversationId,
            "Dr. {$doctor->name}",
            $doctorUserId,
            86400
        );

        if (!$doctorTokenResult['success']) {
            return $doctorTokenResult;
        }

        // Add doctor to conversation
        $this->conversationService->addMember($conversationId, $doctorUserId, "Dr. {$doctor->name}");

        $patientUserId = $patient ? "patient_{$patient->id}" : "patient_email_{$consultation->email}";
        $patientName = $patient ? "{$patient->first_name} {$patient->last_name}" : "{$consultation->first_name} {$consultation->last_name}";
        $patientTokenResult = $this->conversationService->generateToken(
            $conversationId,
            $patientName,
            $patientUserId,
            86400
        );

        if (!$patientTokenResult['success']) {
            return $patientTokenResult;
        }

        // Add patient to conversation
        $this->conversationService->addMember($conversationId, $patientUserId, $patientName);

        return [
            'success' => true,
            'vonage_session_id' => $conversationId,
            'doctor_token' => $doctorTokenResult['token'],
            'patient_token' => $patientTokenResult['token'],
        ];
    }

    /**
     * Create a voice session
     * 
     * IMPORTANT: Voice consultations use Vonage Video API in audio-only mode,
     * NOT Vonage Voice API (telephony). This is documented in config/consultation.php
     * 
     * This ensures:
     * - Consistent WebRTC-based communication
     * - No phone number requirements
     * - Lower latency than PSTN
     * - Better quality for in-app consultations
     */
    protected function createVoiceSession(Consultation $consultation, Doctor $doctor, ?Patient $patient): array
    {
        // Voice sessions can use Video API with audio-only mode
        // Or use Voice API for PSTN calls
        // For in-app voice, we'll use Video API with audio-only
        
        $sessionResult = $this->videoService->createSession();
        if (!$sessionResult['success']) {
            return $sessionResult;
        }

        $sessionId = $sessionResult['session_id'];

        // Generate tokens for doctor and patient (audio-only)
        $doctorTokenResult = $this->videoService->generateToken(
            $sessionId,
            'publisher',
            "Dr. {$doctor->name}",
            86400
        );

        if (!$doctorTokenResult['success']) {
            return $doctorTokenResult;
        }

        $patientName = $patient ? "{$patient->first_name} {$patient->last_name}" : "{$consultation->first_name} {$consultation->last_name}";
        $patientTokenResult = $this->videoService->generateToken(
            $sessionId,
            'publisher',
            $patientName,
            86400
        );

        if (!$patientTokenResult['success']) {
            return $patientTokenResult;
        }

        return [
            'success' => true,
            'vonage_session_id' => $sessionId,
            'doctor_token' => $doctorTokenResult['token'],
            'patient_token' => $patientTokenResult['token'],
        ];
    }

    /**
     * Get session tokens for a user
     * SECURITY: Only returns tokens if user is authorized
     * TOKEN INVALIDATION: Returns null if session is in terminal state
     * 
     * @param ConsultationSession $session
     * @param string $userType 'doctor' or 'patient'
     * @param int|null $userId The user ID to verify authorization
     * @return array ['success' => bool, 'token' => string|null, 'error' => string|null]
     */
    public function getSessionToken(ConsultationSession $session, string $userType, ?int $userId = null): array
    {
        // Verify authorization
        $consultation = $session->consultation;
        
        // Structured logging for token request
        Log::info('Session token requested', [
            'event_type' => 'token_request',
            'session_id' => $session->id,
            'consultation_id' => $session->consultation_id,
            'user_type' => $userType,
            'user_id' => $userId,
            'session_status' => $session->status,
            'timestamp' => now()->toIso8601String()
        ]);
        
        if ($userType === 'doctor') {
            if ($consultation->doctor_id !== $userId) {
                Log::warning('Unauthorized token request - doctor', [
                    'event_type' => 'token_request_unauthorized',
                    'session_id' => $session->id,
                    'consultation_id' => $session->consultation_id,
                    'user_type' => $userType,
                    'user_id' => $userId,
                    'assigned_doctor_id' => $consultation->doctor_id,
                    'timestamp' => now()->toIso8601String()
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Unauthorized: Only assigned doctor can access this session',
                    'error' => 'unauthorized'
                ];
            }
            $token = $session->getDoctorToken();
        } elseif ($userType === 'patient') {
            // Verify patient ownership
            $isOwner = false;
            if ($consultation->patient_id && $consultation->patient_id === $userId) {
                $isOwner = true;
            } elseif ($userId === null && auth()->guard('patient')->check()) {
                $patient = auth()->guard('patient')->user();
                $isOwner = $consultation->patient_id === $patient->id || 
                          $consultation->email === $patient->email;
            }
            
            if (!$isOwner) {
                Log::warning('Unauthorized token request - patient', [
                    'event_type' => 'token_request_unauthorized',
                    'session_id' => $session->id,
                    'consultation_id' => $session->consultation_id,
                    'user_type' => $userType,
                    'user_id' => $userId,
                    'consultation_patient_id' => $consultation->patient_id,
                    'consultation_email' => $consultation->email,
                    'timestamp' => now()->toIso8601String()
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Unauthorized: Only consultation owner can access this session',
                    'error' => 'unauthorized'
                ];
            }
            $token = $session->getPatientToken();
        } else {
            return [
                'success' => false,
                'message' => 'Invalid user type',
                'error' => 'invalid_user_type'
            ];
        }

        if (!$token) {
            // Token is null - could be due to terminal state or expiration
            // Logging is already done in getDoctorToken/getPatientToken
            return [
                'success' => false,
                'message' => 'Token not available. Session may have ended or tokens expired.',
                'error' => 'token_not_available'
            ];
        }

        // Check if tokens are expired (additional check)
        if ($session->areTokensExpired()) {
            Log::warning('Token request denied - tokens expired', [
                'event_type' => 'token_request_expired',
                'session_id' => $session->id,
                'consultation_id' => $session->consultation_id,
                'user_type' => $userType,
                'user_id' => $userId,
                'expires_at' => $session->token_expires_at?->toIso8601String(),
                'timestamp' => now()->toIso8601String()
            ]);
            
            return [
                'success' => false,
                'message' => 'Session tokens have expired',
                'error' => 'tokens_expired'
            ];
        }

        // Structured logging for successful token retrieval
        Log::info('Session token retrieved successfully', [
            'event_type' => 'token_retrieved',
            'session_id' => $session->id,
            'consultation_id' => $session->consultation_id,
            'user_type' => $userType,
            'user_id' => $userId,
            'session_status' => $session->status,
            'mode' => $session->mode,
            'timestamp' => now()->toIso8601String()
        ]);

        return [
            'success' => true,
            'token' => $token,
            'session_id' => $session->vonage_session_id,
            'mode' => $session->mode,
        ];
    }

    /**
     * Start a consultation session
     * Uses state machine for safe transitions
     */
    public function startSession(ConsultationSession $session): bool
    {
        return $this->transitionToState($session, 'active', [
            'reason' => 'manual_start',
            'triggered_by' => 'user_action'
        ]);
    }

    /**
     * End a consultation session
     * Uses state machine for safe transitions
     * TOKEN INVALIDATION: Tokens are automatically invalidated
     */
    public function endSession(ConsultationSession $session): bool
    {
        return $this->transitionToState($session, 'ended', [
            'reason' => 'manual_end',
            'triggered_by' => 'user_action'
        ]);
    }
}

