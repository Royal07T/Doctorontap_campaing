<?php

namespace App\Services;

use App\Models\Consultation;
use App\Models\ConsultationSession;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

        // Ensure doctor is assigned
        if (!$consultation->doctor_id) {
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
            return [
                'success' => false,
                'message' => 'Doctor not found',
                'error' => 'doctor_not_found'
            ];
        }

        try {
            DB::beginTransaction();

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

            if (!$result['success']) {
                DB::rollBack();
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

            Log::info('Consultation session created', [
                'consultation_id' => $consultation->id,
                'session_id' => $session->id,
                'mode' => $mode,
                'vonage_session_id' => $result['vonage_session_id']
            ]);

            return [
                'success' => true,
                'session' => $session,
                'vonage_session_id' => $result['vonage_session_id']
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create consultation session', [
                'consultation_id' => $consultation->id,
                'mode' => $mode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create consultation session',
                'error' => $e->getMessage()
            ];
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
     * Note: Voice uses Vonage Voice API, which may require different handling
     * For now, we'll use Video API with audio-only mode
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
        
        if ($userType === 'doctor') {
            if ($consultation->doctor_id !== $userId) {
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
            return [
                'success' => false,
                'message' => 'Token not found or expired',
                'error' => 'token_not_found'
            ];
        }

        // Check if tokens are expired
        if ($session->areTokensExpired()) {
            return [
                'success' => false,
                'message' => 'Session tokens have expired',
                'error' => 'tokens_expired'
            ];
        }

        return [
            'success' => true,
            'token' => $token,
            'session_id' => $session->vonage_session_id,
            'mode' => $session->mode,
        ];
    }

    /**
     * Start a consultation session
     */
    public function startSession(ConsultationSession $session): bool
    {
        try {
            $session->markAsActive();
            $session->consultation->update([
                'session_status' => 'active',
                'started_at' => now(),
            ]);

            Log::info('Consultation session started', [
                'session_id' => $session->id,
                'consultation_id' => $session->consultation_id
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to start consultation session', [
                'session_id' => $session->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * End a consultation session
     */
    public function endSession(ConsultationSession $session): bool
    {
        try {
            $session->markAsEnded();
            $session->consultation->update([
                'session_status' => 'completed',
                'ended_at' => now(),
            ]);

            Log::info('Consultation session ended', [
                'session_id' => $session->id,
                'consultation_id' => $session->consultation_id
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to end consultation session', [
                'session_id' => $session->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}

