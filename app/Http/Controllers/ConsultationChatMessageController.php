<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\ConsultationChatMessage;
use App\Models\ConsultationSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ConsultationChatMessageController extends Controller
{
    /**
     * Get chat messages for a consultation session
     */
    public function index(Request $request, $consultationId)
    {
        $consultation = Consultation::findOrFail($consultationId);
        
        // Verify authorization
        $user = Auth::user();
        $isAuthorized = false;
        
        if (Auth::guard('doctor')->check()) {
            $isAuthorized = $consultation->doctor_id === $user->id;
        } elseif (Auth::guard('patient')->check()) {
            $isAuthorized = $consultation->patient_id === $user->id || 
                          $consultation->email === $user->email;
        }
        
        if (!$isAuthorized) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        // Get active session
        $session = $consultation->activeSession();
        if (!$session || $session->mode !== 'chat') {
            return response()->json([
                'success' => false,
                'message' => 'No active chat session found'
            ], 404);
        }
        
        // Get messages
        $messages = ConsultationChatMessage::where('consultation_session_id', $session->id)
            ->orderBy('sent_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'sender_name' => $message->sender_name,
                    'sender_type' => $message->sender_type,
                    'message_type' => $message->message_type,
                    'sent_at' => $message->sent_at->toIso8601String(),
                    'file_url' => $message->file_url,
                    'file_name' => $message->file_name,
                ];
            });
        
        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }
    
    /**
     * Store a new chat message
     */
    public function store(Request $request, $consultationId)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
            'message_type' => 'nullable|in:text,image,file',
            'vonage_message_id' => 'nullable|string',
            'file_url' => 'nullable|url',
            'file_name' => 'nullable|string|max:255',
            'file_type' => 'nullable|string|max:100',
            'file_size' => 'nullable|integer',
        ]);
        
        $consultation = Consultation::findOrFail($consultationId);
        
        // Verify authorization
        $user = Auth::user();
        $senderType = null;
        $senderId = null;
        $senderName = null;
        
        if (Auth::guard('doctor')->check()) {
            if ($consultation->doctor_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            $senderType = 'doctor';
            $senderId = $user->id;
            $senderName = "Dr. {$user->name}";
        } elseif (Auth::guard('patient')->check()) {
            if ($consultation->patient_id !== $user->id && $consultation->email !== $user->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            $senderType = 'patient';
            $senderId = $user->id;
            $senderName = "{$user->first_name} {$user->last_name}";
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        // Get active session
        $session = $consultation->activeSession();
        if (!$session || $session->mode !== 'chat') {
            return response()->json([
                'success' => false,
                'message' => 'No active chat session found'
            ], 404);
        }
        
        // Create message
        $message = ConsultationChatMessage::create([
            'consultation_session_id' => $session->id,
            'message' => $request->message,
            'message_type' => $request->message_type ?? 'text',
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'sender_name' => $senderName,
            'vonage_message_id' => $request->vonage_message_id,
            'file_url' => $request->file_url,
            'file_name' => $request->file_name,
            'file_type' => $request->file_type,
            'file_size' => $request->file_size,
            'sent_at' => now(),
        ]);
        
        Log::info('Chat message saved', [
            'consultation_id' => $consultationId,
            'session_id' => $session->id,
            'message_id' => $message->id,
            'sender_type' => $senderType
        ]);
        
        return response()->json([
            'success' => true,
            'message' => $message->load('consultationSession')
        ]);
    }
}
