<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Services\ConsultationSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsultationSessionController extends Controller
{
    protected $sessionService;

    public function __construct(ConsultationSessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    /**
     * Get session token for joining consultation
     */
    public function getToken(Request $request, $id)
    {
        $consultation = Consultation::findOrFail($id);
        $user = Auth::user();
        $userType = $user->getMorphClass();

        // Verify consultation is in-app mode
        if (!$consultation->isInAppMode()) {
            return response()->json([
                'success' => false,
                'message' => 'This consultation does not support in-app sessions'
            ], 400);
        }

        // Check authorization
        if ($userType === 'Doctor' && $consultation->doctor_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only assigned doctor can access this session'
            ], 403);
        }

        if ($userType === 'Patient' && $consultation->patient_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only consultation owner can access this session'
            ], 403);
        }

        // Get active session or create new one
        $session = $consultation->activeSession();
        
        if (!$session) {
            $result = $this->sessionService->createSession($consultation);
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to create session'
                ], 503);
            }
            $session = $result['session'];
        }

        // Get token for user
        $tokenResult = $this->sessionService->getToken($session, $userType, $user->id);
        
        if (!$tokenResult['success']) {
            return response()->json([
                'success' => false,
                'message' => $tokenResult['message'] ?? 'Failed to generate token'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $tokenResult['token'],
                'session_id' => $session->session_id,
                'mode' => $consultation->consultation_mode,
            ]
        ]);
    }

    /**
     * Start consultation session
     */
    public function startSession(Request $request, $id)
    {
        $consultation = Consultation::findOrFail($id);
        $user = Auth::user();

        $result = $this->sessionService->startSession($consultation, $user);
        
        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'] ?? 'Session started',
            'data' => $result['data'] ?? null
        ], $result['success'] ? 200 : 400);
    }

    /**
     * End consultation session
     */
    public function endSession(Request $request, $id)
    {
        $consultation = Consultation::findOrFail($id);
        $user = Auth::user();

        $result = $this->sessionService->endSession($consultation, $user);
        
        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'] ?? 'Session ended',
        ], $result['success'] ? 200 : 400);
    }

    /**
     * Get session status
     */
    public function getStatus($id)
    {
        $consultation = Consultation::findOrFail($id);
        $session = $consultation->activeSession();

        return response()->json([
            'success' => true,
            'data' => [
                'session_status' => $session ? $session->status : 'not_started',
                'consultation_status' => $consultation->status,
            ]
        ]);
    }

    /**
     * Toggle recording
     */
    public function toggleRecording(Request $request, $id)
    {
        $consultation = Consultation::findOrFail($id);
        $request->validate([
            'recording' => 'required|boolean'
        ]);

        // Implementation would go here
        return response()->json([
            'success' => true,
            'message' => 'Recording ' . ($request->recording ? 'started' : 'stopped')
        ]);
    }
}

