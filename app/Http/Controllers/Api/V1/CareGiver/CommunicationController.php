<?php

namespace App\Http\Controllers\Api\V1\CareGiver;

use App\Http\Controllers\Controller;
use App\Models\InboundMessage;
use App\Services\VonageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunicationController extends Controller
{
    protected VonageService $vonage;

    public function __construct(VonageService $vonage)
    {
        $this->vonage = $vonage;
    }

    /**
     * Get message threads for the caregiver's patients.
     */
    public function threads(Request $request)
    {
        $caregiver = Auth::user();
        $patientIds = $caregiver->assignedPatients()->pluck('patients.id');

        $threads = InboundMessage::whereIn('patient_id', $patientIds)
            ->select('patient_id')
            ->selectRaw('MAX(created_at) as last_message_at')
            ->selectRaw('COUNT(*) as message_count')
            ->groupBy('patient_id')
            ->with('patient:id,name,phone')
            ->orderByDesc('last_message_at')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $threads,
        ]);
    }

    /**
     * Get messages for a specific patient.
     */
    public function messages(Request $request, $patientId)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $messages = InboundMessage::where('patient_id', $patientId)
            ->when($request->channel, fn($q, $ch) => $q->where('channel', $ch))
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 30));

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    /**
     * Send a message to a patient.
     */
    public function send(Request $request, $patientId)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'channel' => 'required|string|in:sms,whatsapp',
        ]);

        $patient = \App\Models\Patient::findOrFail($patientId);

        if (empty($patient->phone)) {
            return response()->json(['success' => false, 'message' => 'Patient has no phone number'], 422);
        }

        try {
            if ($validated['channel'] === 'whatsapp') {
                $result = $this->vonage->sendWhatsAppMessage($patient->phone, $validated['message']);
            } else {
                $result = $this->vonage->sendSMS($patient->phone, $validated['message']);
            }

            InboundMessage::create([
                'channel' => $validated['channel'],
                'message_type' => 'text',
                'from_number' => config('vonage.from_number', 'system'),
                'to_number' => $patient->phone,
                'message_text' => $validated['message'],
                'status' => 'sent',
                'patient_id' => $patientId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage(),
            ], 500);
        }
    }
}
