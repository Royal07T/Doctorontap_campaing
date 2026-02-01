<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Consultation;
use App\Services\VonageService;
use App\Services\VonageVideoService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CustomerCareController extends Controller
{
    protected $vonageService;
    protected $videoService;

    public function __construct(VonageService $vonageService = null, VonageVideoService $videoService = null)
    {
        $this->vonageService = $vonageService;
        $this->videoService = $videoService;
    }

    /**
     * Display the customer care dashboard
     */
    public function dashboard()
    {
        // Get recent patients for quick access
        $recentPatients = Patient::orderBy('created_at', 'desc')
            ->take(10)
            ->get(['id', 'name', 'phone', 'email']);

        // Get today's consultations
        $recentConsultations = Consultation::with(['patient', 'doctor'])
            ->whereDate('scheduled_at', today())
            ->orderBy('scheduled_at')
            ->get();

        // Get communication statistics
        $stats = [
            'total_patients' => Patient::count(),
            'total_consultations' => Consultation::count(),
            'today_consultations' => $recentConsultations->count(),
            'pending_consultations' => Consultation::where('status', 'pending')->count(),
            'scheduled_consultations' => Consultation::where('status', 'scheduled')->count(),
            'completed_consultations' => Consultation::where('status', 'completed')->count(),
            'active_patients' => Patient::count(), // All patients are considered active
        ];

        // Get customer care specific statistics
        $customerCareStats = [
            'active_interactions' => 0, // TODO: Implement when communication tracking is ready
            'pending_tickets' => 0, // TODO: Implement when ticket system is ready
            'resolved_tickets_today' => 0, // TODO: Implement when ticket system is ready
            'escalated_cases' => 0, // TODO: Implement when escalation system is ready
            'avg_response_time' => 0, // TODO: Implement when response tracking is ready
        ];

        return view('customer-care.dashboard', compact('recentPatients', 'recentConsultations', 'stats', 'customerCareStats'));
    }

    /**
     * Search for patients
     */
    public function searchPatients(Request $request)
    {
        $query = $request->get('q');
        
        $patients = Patient::where(function($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('email', 'LIKE', "%{$query}%")
              ->orWhere('phone', 'LIKE', "%{$query}%");
        })
        ->limit(20)
        ->get(['id', 'name', 'phone', 'email', 'date_of_birth']);

        return response()->json([
            'patients' => $patients
        ]);
    }

    /**
     * Get patient details for communication
     */
    public function getPatientDetails($id)
    {
        $patient = Patient::with(['consultations' => function($query) {
            $query->orderBy('created_at', 'desc')->take(5);
        }])->findOrFail($id);

        return response()->json([
            'patient' => $patient,
            'age' => $patient->date_of_birth ? $patient->date_of_birth->age : $patient->age,
            'recent_consultations' => $patient->consultations
        ]);
    }

    /**
     * Send SMS to patient
     */
    public function sendSms(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'message' => 'required|string|max:1600',
            'template' => 'nullable|string'
        ]);

        try {
            $patient = Patient::findOrFail($request->patient_id);
            
            // Log the communication
            $communicationId = $this->logCommunication([
                'patient_id' => $patient->id,
                'type' => 'sms',
                'direction' => 'outbound',
                'content' => $request->message,
                'template' => $request->template,
                'status' => 'pending'
            ]);

            // Send SMS
            if ($this->vonageService) {
                $result = $this->vonageService->sendSMS($patient->phone, $request->message);
            } else {
                // Fallback if Vonage service is not available
                $result = [
                    'success' => false,
                    'message' => 'SMS service is not available'
                ];
            }

            if ($result['success']) {
                // Update communication log
                $this->updateCommunicationLog($communicationId, [
                    'status' => 'sent',
                    'message_id' => $result['data']['message_id'] ?? null,
                    'sent_at' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'message_id' => $result['data']['message_id'] ?? null
                ]);
            } else {
                // Update communication log with error
                $this->updateCommunicationLog($communicationId, [
                    'status' => 'failed',
                    'error' => $result['error'] ?? 'Unknown error',
                    'failed_at' => now()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send SMS: ' . ($result['error'] ?? 'Unknown error')
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'patient_id' => $request->patient_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'System error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send WhatsApp message
     */
    public function sendWhatsApp(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'message' => 'required|string|max:1600',
            'template' => 'nullable|string'
        ]);

        try {
            $patient = Patient::findOrFail($request->patient_id);
            
            // Log the communication
            $communicationId = $this->logCommunication([
                'patient_id' => $patient->id,
                'type' => 'whatsapp',
                'direction' => 'outbound',
                'content' => $request->message,
                'template' => $request->template,
                'status' => 'pending'
            ]);

            // Send WhatsApp message (using Vonage WhatsApp API)
            if ($this->vonageService) {
                $result = $this->vonageService->sendWhatsApp($patient->phone, $request->message);
            } else {
                // Fallback if Vonage service is not available
                $result = [
                    'success' => false,
                    'message' => 'WhatsApp service is not available'
                ];
            }

            if ($result['success']) {
                // Update communication log
                $this->updateCommunicationLog($communicationId, [
                    'status' => 'sent',
                    'message_id' => $result['data']['message_id'] ?? null,
                    'sent_at' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'WhatsApp message sent successfully',
                    'message_id' => $result['data']['message_id'] ?? null
                ]);
            } else {
                // Update communication log with error
                $this->updateCommunicationLog($communicationId, [
                    'status' => 'failed',
                    'error' => $result['error'] ?? 'Unknown error',
                    'failed_at' => now()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send WhatsApp message: ' . ($result['error'] ?? 'Unknown error')
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp sending failed', [
                'patient_id' => $request->patient_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'System error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initiate voice call
     */
    public function initiateCall(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'call_type' => 'required|in:voice,video'
        ]);

        try {
            $patient = Patient::findOrFail($request->patient_id);
            
            // Log the communication
            $communicationId = $this->logCommunication([
                'patient_id' => $patient->id,
                'type' => $request->call_type,
                'direction' => 'outbound',
                'content' => 'Call initiated',
                'status' => 'initiated'
            ]);

            if ($request->call_type === 'video') {
                // Create video session
                if ($this->videoService) {
                    $sessionResult = $this->videoService->createSession([
                        'mediaMode' => \OpenTok\MediaMode::ROUTED,
                        'archiveMode' => \OpenTok\ArchiveMode::MANUAL
                    ]);

                    if ($sessionResult['success']) {
                        // Generate tokens for both parties
                        $patientToken = $this->videoService->generateToken(
                            $sessionResult['session_id'], 
                            \OpenTok\Role::PUBLISHER
                        );
                        
                        $agentToken = $this->videoService->generateToken(
                            $sessionResult['session_id'], 
                            \OpenTok\Role::MODERATOR
                        );

                        // Update communication log
                        $this->updateCommunicationLog($communicationId, [
                            'status' => 'active',
                            'session_id' => $sessionResult['session_id'],
                            'started_at' => now()
                        ]);

                        return response()->json([
                            'success' => true,
                            'type' => 'video',
                            'session_id' => $sessionResult['session_id'],
                            'patient_token' => $patientToken,
                            'agent_token' => $agentToken,
                            'message' => 'Video call session created'
                        ]);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Video service is not available'
                    ], 500);
                }
            } else {
                // Initiate voice call using Vonage Voice API
                if ($this->vonageService) {
                    $callResult = $this->vonageService->initiateCall($patient->phone, [
                        'answer_url' => route('customer-care.call-webhook'),
                        'machine_detection' => 'continue'
                    ]);
                } else {
                    $callResult = [
                        'success' => false,
                        'message' => 'Voice service is not available'
                    ];
                }

                if ($callResult['success']) {
                    // Update communication log
                    $this->updateCommunicationLog($communicationId, [
                        'status' => 'ringing',
                        'call_uuid' => $callResult['data']['uuid'] ?? null,
                        'started_at' => now()
                    ]);

                    return response()->json([
                        'success' => true,
                        'type' => 'voice',
                        'call_uuid' => $callResult['data']['uuid'] ?? null,
                        'message' => 'Voice call initiated'
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate call'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Call initiation failed', [
                'patient_id' => $request->patient_id,
                'call_type' => $request->call_type,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'System error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get communication history
     */
    public function getCommunicationHistory($patientId)
    {
        $communications = DB::table('patient_communications')
            ->where('patient_id', $patientId)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'communications' => $communications
        ]);
    }

    /**
     * Get marketing campaigns
     */
    public function getCampaigns()
    {
        $campaigns = DB::table('marketing_campaigns')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'campaigns' => $campaigns
        ]);
    }

    /**
     * Create marketing campaign
     */
    public function createCampaign(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:sms,whatsapp,email',
            'message' => 'required|string',
            'target_audience' => 'required|array',
            'scheduled_at' => 'nullable|date'
        ]);

        try {
            $campaignId = DB::table('marketing_campaigns')->insertGetId([
                'name' => $request->name,
                'type' => $request->type,
                'message' => $request->message,
                'target_audience' => json_encode($request->target_audience),
                'scheduled_at' => $request->scheduled_at,
                'status' => 'draft',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'campaign_id' => $campaignId,
                'message' => 'Campaign created successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Campaign creation failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create campaign: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Log communication
     */
    private function logCommunication($data)
    {
        return DB::table('patient_communications')->insertGetId([
            'patient_id' => $data['patient_id'],
            'type' => $data['type'],
            'direction' => $data['direction'],
            'content' => $data['content'],
            'template' => $data['template'] ?? null,
            'status' => $data['status'],
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Update communication log
     */
    private function updateCommunicationLog($id, $data)
    {
        DB::table('patient_communications')
            ->where('id', $id)
            ->update(array_merge($data, ['updated_at' => now()]));
    }
}
