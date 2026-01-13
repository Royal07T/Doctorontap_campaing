<?php

namespace App\Http\Controllers;

use App\Models\ConsultationSession;
use App\Services\ConsultationSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * VonageSessionWebhookController
 * 
 * Handles webhook events from Vonage Video API and Conversations API
 * for in-app consultation sessions.
 * 
 * SECURITY:
 * - Validates webhook signatures to ensure authenticity
 * - Updates session state based on real-time events
 * - Reconciles session status with Vonage events
 */
class VonageSessionWebhookController extends Controller
{
    protected $sessionService;

    public function __construct(ConsultationSessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    /**
     * Handle Vonage session webhook events
     * 
     * Supported events:
     * - participant.joined
     * - participant.left
     * - session.ended
     * - session.failed
     * 
     * POST /vonage/webhook/session
     */
    public function handleSessionEvent(Request $request)
    {
        // Validate webhook signature
        $signatureValid = $this->validateWebhookSignature($request);
        if (!$signatureValid) {
            Log::warning('SECURITY ALERT: Invalid Vonage session webhook signature', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toDateTimeString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid signature'
            ], 401);
        }

        $eventType = $request->input('event');
        $payload = $request->all();

        // Structured logging for webhook event
        Log::info('Vonage session webhook event received', [
            'event_type' => $eventType,
            'session_id' => $payload['session_id'] ?? null,
            'consultation_id' => null, // Will be resolved from session
            'ip' => $request->ip(),
            'timestamp' => now()->toIso8601String()
        ]);

        try {
            switch ($eventType) {
                case 'participant.joined':
                    return $this->handleParticipantJoined($payload);
                
                case 'participant.left':
                    return $this->handleParticipantLeft($payload);
                
                case 'session.ended':
                    return $this->handleSessionEnded($payload);
                
                case 'session.failed':
                    return $this->handleSessionFailed($payload);
                
                default:
                    Log::warning('Unknown Vonage session webhook event type', [
                        'event_type' => $eventType,
                        'payload' => $payload
                    ]);
                    
                    return response()->json([
                        'status' => 'ignored',
                        'message' => 'Unknown event type'
                    ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Error processing Vonage session webhook', [
                'event_type' => $eventType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $payload
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Handle participant.joined event
     */
    protected function handleParticipantJoined(array $payload): \Illuminate\Http\JsonResponse
    {
        $sessionId = $payload['session_id'] ?? null;
        if (!$sessionId) {
            return response()->json(['status' => 'error', 'message' => 'Missing session_id'], 400);
        }

        $session = ConsultationSession::where('vonage_session_id', $sessionId)->first();
        if (!$session) {
            Log::warning('Participant joined event for unknown session', [
                'vonage_session_id' => $sessionId,
                'payload' => $payload
            ]);
            return response()->json(['status' => 'ignored', 'message' => 'Session not found'], 200);
        }

        // Update session to active if it's pending
        if ($session->status === 'pending') {
            $this->sessionService->transitionToState($session, 'active', [
                'reason' => 'participant_joined',
                'event_data' => $payload
            ]);
        }

        Log::info('Participant joined session', [
            'event_type' => 'participant.joined',
            'session_id' => $session->id,
            'consultation_id' => $session->consultation_id,
            'vonage_session_id' => $sessionId,
            'participant_id' => $payload['participant_id'] ?? null,
            'timestamp' => now()->toIso8601String()
        ]);

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Handle participant.left event
     */
    protected function handleParticipantLeft(array $payload): \Illuminate\Http\JsonResponse
    {
        $sessionId = $payload['session_id'] ?? null;
        if (!$sessionId) {
            return response()->json(['status' => 'error', 'message' => 'Missing session_id'], 400);
        }

        $session = ConsultationSession::where('vonage_session_id', $sessionId)->first();
        if (!$session) {
            Log::warning('Participant left event for unknown session', [
                'vonage_session_id' => $sessionId,
                'payload' => $payload
            ]);
            return response()->json(['status' => 'ignored', 'message' => 'Session not found'], 200);
        }

        // Check if session should end (both participants left)
        // Note: This is a simplified check - Vonage may send multiple events
        // In production, you might want to track participant count
        $remainingParticipants = $payload['remaining_participants'] ?? null;
        
        if ($remainingParticipants !== null && $remainingParticipants === 0) {
            // All participants left, end the session
            $this->sessionService->transitionToState($session, 'ended', [
                'reason' => 'all_participants_left',
                'event_data' => $payload
            ]);
        }

        Log::info('Participant left session', [
            'event_type' => 'participant.left',
            'session_id' => $session->id,
            'consultation_id' => $session->consultation_id,
            'vonage_session_id' => $sessionId,
            'participant_id' => $payload['participant_id'] ?? null,
            'remaining_participants' => $remainingParticipants,
            'timestamp' => now()->toIso8601String()
        ]);

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Handle session.ended event
     */
    protected function handleSessionEnded(array $payload): \Illuminate\Http\JsonResponse
    {
        $sessionId = $payload['session_id'] ?? null;
        if (!$sessionId) {
            return response()->json(['status' => 'error', 'message' => 'Missing session_id'], 400);
        }

        $session = ConsultationSession::where('vonage_session_id', $sessionId)->first();
        if (!$session) {
            Log::warning('Session ended event for unknown session', [
                'vonage_session_id' => $sessionId,
                'payload' => $payload
            ]);
            return response()->json(['status' => 'ignored', 'message' => 'Session not found'], 200);
        }

        $this->sessionService->transitionToState($session, 'ended', [
            'reason' => 'session_ended_event',
            'event_data' => $payload
        ]);

        Log::info('Session ended via webhook', [
            'event_type' => 'session.ended',
            'session_id' => $session->id,
            'consultation_id' => $session->consultation_id,
            'vonage_session_id' => $sessionId,
            'timestamp' => now()->toIso8601String()
        ]);

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Handle session.failed event
     */
    protected function handleSessionFailed(array $payload): \Illuminate\Http\JsonResponse
    {
        $sessionId = $payload['session_id'] ?? null;
        if (!$sessionId) {
            return response()->json(['status' => 'error', 'message' => 'Missing session_id'], 400);
        }

        $session = ConsultationSession::where('vonage_session_id', $sessionId)->first();
        if (!$session) {
            Log::warning('Session failed event for unknown session', [
                'vonage_session_id' => $sessionId,
                'payload' => $payload
            ]);
            return response()->json(['status' => 'ignored', 'message' => 'Session not found'], 200);
        }

        $errorMessage = $payload['error'] ?? $payload['error_message'] ?? 'Session failed';
        
        $this->sessionService->transitionToState($session, 'failed', [
            'reason' => 'session_failed_event',
            'error_message' => $errorMessage,
            'event_data' => $payload
        ]);

        Log::error('Session failed via webhook', [
            'event_type' => 'session.failed',
            'session_id' => $session->id,
            'consultation_id' => $session->consultation_id,
            'vonage_session_id' => $sessionId,
            'error_message' => $errorMessage,
            'timestamp' => now()->toIso8601String()
        ]);

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Validate Vonage webhook signature
     * 
     * Vonage uses HMAC SHA256 signature in the X-Vonage-Signature header
     */
    protected function validateWebhookSignature(Request $request): bool
    {
        // Allow local testing without signature
        if (app()->environment('local') && !config('services.vonage.enforce_webhook_signature', true)) {
            return true;
        }

        $signature = $request->header('X-Vonage-Signature');
        $secretKey = config('services.vonage.webhook_secret') ?? config('services.vonage.api_secret');

        if (!$signature || !$secretKey) {
            return false;
        }

        // Vonage signs the raw request body
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secretKey);

        return hash_equals($expectedSignature, $signature);
    }
}
