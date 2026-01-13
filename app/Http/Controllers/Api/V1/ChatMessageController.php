<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\ConsultationChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatMessageController extends Controller
{
    /**
     * Get chat messages for a consultation
     */
    public function index(Request $request, $id)
    {
        $consultation = Consultation::findOrFail($id);
        $user = Auth::user();
        $userType = $user->getMorphClass();

        // Check authorization
        if ($userType === 'Doctor' && $consultation->doctor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($userType === 'Patient' && $consultation->patient_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = ConsultationChatMessage::where('consultation_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * Store a new chat message
     */
    public function store(Request $request, $id)
    {
        $consultation = Consultation::findOrFail($id);
        $user = Auth::user();
        $userType = $user->getMorphClass();

        // Check authorization
        if ($userType === 'Doctor' && $consultation->doctor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($userType === 'Patient' && $consultation->patient_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:1000'],
        ]);

        // SECURITY: Sanitize message to prevent XSS
        $sanitizedMessage = strip_tags(trim($request->message));
        
        if (empty($sanitizedMessage)) {
            return response()->json([
                'success' => false,
                'message' => 'Message cannot be empty'
            ], 422);
        }

        $message = ConsultationChatMessage::create([
            'consultation_id' => $id,
            'sender_type' => $userType === 'Doctor' ? 'doctor' : 'patient',
            'sender_id' => $user->id,
            'message' => $sanitizedMessage,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $message
        ], 201);
    }
}

