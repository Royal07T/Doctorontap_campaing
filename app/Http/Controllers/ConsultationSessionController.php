<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\ConsultationSession;
use App\Services\ConsultationSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * ConsultationSessionController
 * 
 * Handles consultation session management for in-app consultations (voice, video, chat).
 * 
 * SECURITY:
 * - Only assigned doctor can access doctor endpoints
 * - Only consultation owner (patient) can access patient endpoints
 * - All tokens are encrypted at rest
 */
class ConsultationSessionController extends Controller
{
    protected $sessionService;

    public function __construct(ConsultationSessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function waitingRoom(Consultation $consultation)
    {
        if (!$consultation->isInAppMode()) {
            abort(404);
        }

        // PAYMENT CHECK: Verify payment before allowing access to waiting room (for patients only)
        if (auth()->guard('patient')->check()) {
            $patient = auth()->guard('patient')->user();
            if ($consultation->patient_id === $patient->id || $consultation->email === $patient->email) {
                if ($consultation->requiresPaymentBeforeStart()) {
                    return redirect()
                        ->route('payment.request', ['reference' => $consultation->reference])
                        ->with('error', 'Payment is required before this consultation can proceed.');
                }
            }
        }

        if (auth()->guard('doctor')->check()) {
            if ($consultation->doctor_id !== auth()->guard('doctor')->id()) {
                abort(403);
            }
        } elseif (auth()->guard('patient')->check()) {
            $patient = auth()->guard('patient')->user();
            if ($consultation->patient_id !== $patient->id && $consultation->email !== $patient->email) {
                abort(403);
            }
        } else {
            abort(401);
        }

        return view('consultation.session.waiting-room', compact('consultation'));
    }

    public function active(Consultation $consultation)
    {
        if (!$consultation->isInAppMode()) {
            abort(404);
        }

        if (auth()->guard('doctor')->check()) {
            if ($consultation->doctor_id !== auth()->guard('doctor')->id()) {
                abort(403);
            }
        } elseif (auth()->guard('patient')->check()) {
            $patient = auth()->guard('patient')->user();
            if ($consultation->patient_id !== $patient->id && $consultation->email !== $patient->email) {
                abort(403);
            }
        } else {
            abort(401);
        }

        return view('consultation.session.active', compact('consultation'));
    }

    /**
     * Get session token for joining consultation
     * 
     * SECURITY:
     * - Uses POST method to prevent token exposure in logs, browser history, or URL parameters
     * - Verifies user authorization before returning token
     * - Rate limited to prevent abuse (10 requests per minute)
     * - Tokens are encrypted at rest and only decrypted for authorized users
     * 
     * NOTE: This endpoint was changed from GET to POST for security reasons.
     * Tokens should never be exposed in URLs or server logs.
     */
    public function getToken(Request $request, Consultation $consultation)
    {
        // Verify consultation is in-app mode
        if (!$consultation->isInAppMode()) {
            return response()->json([
                'success' => false,
                'message' => 'This consultation does not support in-app sessions'
            ], 400);
        }

        // PAYMENT CHECK: Verify payment before allowing token generation (for patients only)
        if (auth()->guard('patient')->check()) {
            $patient = auth()->guard('patient')->user();
            if (($consultation->patient_id === $patient->id || $consultation->email === $patient->email) 
                && $consultation->requiresPaymentBeforeStart()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment is required before this consultation can proceed. Please complete payment first.',
                    'payment_required' => true,
                    'payment_url' => route('payment.request', ['reference' => $consultation->reference]),
                ], 400);
            }
        }

        // Determine user type and ID
        $userType = null;
        $userId = null;

        if (auth()->guard('doctor')->check()) {
            $userType = 'doctor';
            $userId = auth()->guard('doctor')->id();
            
            // Verify doctor is assigned to this consultation
            if ($consultation->doctor_id !== $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Only assigned doctor can access this session'
                ], 403);
            }
        } elseif (auth()->guard('patient')->check()) {
            $userType = 'patient';
            $userId = auth()->guard('patient')->id();
            
            // Verify patient owns this consultation
            if ($consultation->patient_id !== $userId && $consultation->email !== auth()->guard('patient')->user()->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Only consultation owner can access this session'
                ], 403);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        // Get active session or create new one
        $session = $consultation->activeSession();
        
        if (!$session) {
            // Create new session
            $result = $this->sessionService->createSession($consultation);
            if (!$result['success']) {
                // FIX 5: Graceful fallback when Vonage is disabled
                // Don't block user if Vonage service is disabled - return helpful message
                if (isset($result['graceful_failure']) && $result['graceful_failure'] === true) {
                    return response()->json([
                        'success' => false,
                        'message' => $result['message'] ?? 'Vonage service is currently unavailable. Please contact support or use WhatsApp consultation mode.',
                        'error' => 'vonage_disabled',
                        'graceful_failure' => true
                    ], 503); // 503 Service Unavailable - indicates temporary unavailability
                }
                
                // For other errors, return 500
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create session: ' . ($result['message'] ?? 'Unknown error')
                ], 500);
            }
            $session = $result['session'];
        }

        // Get token for user
        $tokenResult = $this->sessionService->getSessionToken($session, $userType, $userId);
        
        if (!$tokenResult['success']) {
            return response()->json([
                'success' => false,
                'message' => $tokenResult['message'] ?? 'Failed to get session token'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'token' => $tokenResult['token'],
            'session_id' => $tokenResult['session_id'],
            'mode' => $tokenResult['mode'],
            'consultation_id' => $consultation->id,
        ]);
    }

    /**
     * Start a consultation session
     */
    public function startSession(Request $request, Consultation $consultation)
    {
        // Verify authorization (same as getToken)
        if (auth()->guard('doctor')->check()) {
            if ($consultation->doctor_id !== auth()->guard('doctor')->id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        } elseif (auth()->guard('patient')->check()) {
            $patient = auth()->guard('patient')->user();
            if ($consultation->patient_id !== $patient->id && $consultation->email !== $patient->email) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $session = $consultation->activeSession();
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No active session found'
            ], 404);
        }

        $this->sessionService->startSession($session);

        return response()->json([
            'success' => true,
            'message' => 'Session started'
        ]);
    }

    /**
     * End a consultation session
     */
    public function endSession(Request $request, Consultation $consultation)
    {
        // Verify authorization (same as getToken)
        if (auth()->guard('doctor')->check()) {
            if ($consultation->doctor_id !== auth()->guard('doctor')->id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        } elseif (auth()->guard('patient')->check()) {
            $patient = auth()->guard('patient')->user();
            if ($consultation->patient_id !== $patient->id && $consultation->email !== $patient->email) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $session = $consultation->activeSession();
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No active session found'
            ], 404);
        }

        $this->sessionService->endSession($session);

        return response()->json([
            'success' => true,
            'message' => 'Session ended'
        ]);
    }

    /**
     * Get session status
     */
    public function getStatus(Consultation $consultation)
    {
        // Verify authorization
        if (auth()->guard('doctor')->check()) {
            if ($consultation->doctor_id !== auth()->guard('doctor')->id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        } elseif (auth()->guard('patient')->check()) {
            $patient = auth()->guard('patient')->user();
            if ($consultation->patient_id !== $patient->id && $consultation->email !== $patient->email) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $session = $consultation->activeSession();
        
        // Get session status, checking if scheduled time has passed
        $sessionStatus = $consultation->session_status;
        
        // If scheduled time has passed and status is still "scheduled", change to "waiting"
        if ($consultation->scheduled_at && $consultation->scheduled_at->isPast()) {
            if ($sessionStatus === 'scheduled') {
                $sessionStatus = 'waiting';
                // Update the consultation status in database
                $consultation->update(['session_status' => 'waiting']);
            }
        } elseif ($consultation->scheduled_at && $consultation->scheduled_at->isFuture()) {
            // If scheduled time hasn't arrived yet, ensure status is "scheduled"
            if (!$sessionStatus || $sessionStatus === 'waiting') {
                $sessionStatus = 'scheduled';
                $consultation->update(['session_status' => 'scheduled']);
            }
        }

        return response()->json([
            'success' => true,
            'has_session' => $session !== null,
            'session_status' => $session ? $session->status : $sessionStatus,
            'consultation_status' => $sessionStatus,
            'mode' => $consultation->consultation_mode,
        ]);
    }
    
    /**
     * Toggle session recording
     */
    public function toggleRecording(Request $request, Consultation $consultation)
    {
        $request->validate([
            'recording' => 'required|boolean'
        ]);
        
        // Verify authorization
        if (auth()->guard('doctor')->check()) {
            if ($consultation->doctor_id !== auth()->guard('doctor')->id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        } elseif (auth()->guard('patient')->check()) {
            $patient = auth()->guard('patient')->user();
            if ($consultation->patient_id !== $patient->id && $consultation->email !== $patient->email) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }
        
        $session = $consultation->activeSession();
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'No active session found'], 404);
        }
        
        // Log recording toggle
        Log::info('Session recording toggled', [
            'consultation_id' => $consultation->id,
            'session_id' => $session->id,
            'recording' => $request->recording,
            'user_type' => auth()->guard('doctor')->check() ? 'doctor' : 'patient'
        ]);
        
        // TODO: Implement actual recording start/stop via Vonage API
        // For now, just acknowledge the request
        
        return response()->json([
            'success' => true,
            'recording' => $request->recording,
            'message' => $request->recording ? 'Recording started' : 'Recording stopped'
        ]);
    }
}
