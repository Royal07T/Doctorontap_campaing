<?php

namespace App\Http\Controllers\CustomerCare;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Prospect;
use App\Models\Doctor;
use App\Models\Consultation;
use App\Services\ConsultationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BookingController extends Controller
{
    protected $consultationService;

    public function __construct(ConsultationService $consultationService)
    {
        $this->consultationService = $consultationService;
    }

    /**
     * Show booking form
     * Requires: book_consultation_for_user permission
     */
    public function create(Request $request)
    {
        $agent = Auth::guard('customer_care')->user();
        
        // Permission check: Only authenticated Customer Care agents can book consultations
        if (!$agent) {
            abort(403, 'Unauthorized. You must be logged in as Customer Care to book consultations.');
        }
        
        $patientId = $request->get('patient_id');
        $prospectId = $request->get('prospect_id');
        
        $patient = null;
        $prospect = null;
        
        if ($patientId) {
            $patient = Patient::findOrFail($patientId);
        } elseif ($prospectId) {
            $prospect = Prospect::findOrFail($prospectId);
            if ($prospect->status !== 'Converted') {
                return redirect()
                    ->route('customer-care.prospects.show', $prospect)
                    ->with('error', 'Prospect must be converted to patient before booking. Please convert the prospect first.');
            }
        }

        // Get available doctors
        $doctors = Doctor::where('is_approved', true)
            ->where('is_available', true)
            ->orderBy('name')
            ->get();

        return view('customer-care.booking.create', compact('patient', 'prospect', 'doctors'));
    }

    /**
     * Store booking (create consultation)
     * Requires: book_consultation_for_user permission
     */
    public function store(Request $request)
    {
        $agent = Auth::guard('customer_care')->user();
        
        // Permission check: Only authenticated Customer Care agents can book consultations
        if (!$agent) {
            abort(403, 'Unauthorized. You must be logged in as Customer Care to book consultations.');
        }
        
        $validated = $request->validate([
            'patient_id' => 'nullable|exists:patients,id',
            'prospect_id' => 'nullable|exists:prospects,id',
            'service_type' => 'required|in:video,audio,home_visit',
            'doctor_id' => 'required|exists:doctors,id',
            'scheduled_at' => 'required|date|after:now',
            'problem' => 'required|string|min:10|max:1000',
            'severity' => 'required|in:low,moderate,high,urgent',
            'age' => 'nullable|integer|min:1|max:150',
            'gender' => 'nullable|in:Male,Female,Other',
        ]);

        DB::beginTransaction();
        try {
            // Determine patient
            $patient = null;
            if ($validated['patient_id']) {
                $patient = Patient::findOrFail($validated['patient_id']);
            } elseif ($validated['prospect_id']) {
                $prospect = Prospect::findOrFail($validated['prospect_id']);
                if ($prospect->status !== 'Converted') {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', 'Prospect must be converted to patient before booking.');
                }
                // Find patient by mobile number
                $patient = Patient::where('phone', $prospect->mobile_number)->first();
                if (!$patient) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', 'Patient not found. Please convert prospect first.');
                }
            } else {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Please select either a patient or prospect.');
            }

            // Verify doctor availability
            $doctor = Doctor::findOrFail($validated['doctor_id']);
            if (!$doctor->is_available || !$doctor->is_approved) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Selected doctor is not available for booking.');
            }

            // Check time slot availability
            $scheduledAt = Carbon::parse($validated['scheduled_at']);
            $dayOfWeek = strtolower($scheduledAt->format('l'));
            $schedule = $doctor->availability_schedule ?? [];
            $daySchedule = $schedule[$dayOfWeek] ?? null;

            if (!$daySchedule || !($daySchedule['enabled'] ?? false)) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Doctor is not available on the selected day.');
            }

            // Check for existing consultation at this time
            $existingConsultation = Consultation::where('doctor_id', $doctor->id)
                ->where('scheduled_at', $scheduledAt->format('Y-m-d H:i:s'))
                ->whereIn('status', ['pending', 'scheduled'])
                ->first();

            if ($existingConsultation) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'This time slot is already booked. Please choose another time.');
            }

            // Map service type to consultation mode
            $consultationModeMap = [
                'video' => 'video',
                'audio' => 'voice',
                'home_visit' => 'whatsapp', // Home visits use WhatsApp for coordination
            ];
            $consultationMode = $consultationModeMap[$validated['service_type']] ?? 'whatsapp';

            // Create consultation
            $reference = 'CONSULT-' . time() . '-' . Str::random(6);
            
            $consultation = Consultation::create([
                'reference' => $reference,
                'patient_id' => $patient->id,
                'first_name' => $patient->first_name,
                'last_name' => $patient->last_name,
                'email' => $patient->email ?? $patient->user->email ?? '',
                'mobile' => $patient->phone,
                'age' => $validated['age'] ?? $patient->age ?? 0,
                'gender' => $validated['gender'] ?? $patient->gender ?? 'Other',
                'problem' => $validated['problem'],
                'severity' => $validated['severity'],
                'consult_mode' => $consultationMode, // Legacy field
                'consultation_mode' => $consultationMode,
                'doctor_id' => $doctor->id,
                'customer_care_id' => $agent->id,
                'booked_by_customer_service' => true,
                'booked_by_agent_id' => $agent->id,
                'scheduled_at' => $scheduledAt,
                'status' => 'scheduled',
                'payment_status' => 'unpaid',
            ]);

            // Audit log
            Log::info('Consultation booked by customer service', [
                'consultation_id' => $consultation->id,
                'consultation_reference' => $reference,
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'agent_id' => $agent->id,
                'agent_name' => $agent->name,
                'scheduled_at' => $scheduledAt->toDateTimeString(),
                'service_type' => $validated['service_type'],
                'action' => 'consultation_booked_by_cs',
            ]);

            DB::commit();

            // PAYMENT CHECK: If payment is required, initialize payment and redirect
            if ($consultation->requiresPayment() && !$consultation->isPaid()) {
                // Initialize payment
                $paymentController = app(\App\Http\Controllers\PaymentController::class);
                $paymentRequest = new \Illuminate\Http\Request([
                    'amount' => $doctor->effective_consultation_fee ?? 0,
                    'customer_email' => $consultation->email,
                    'customer_name' => $consultation->first_name . ' ' . $consultation->last_name,
                    'customer_phone' => $consultation->mobile,
                    'doctor_id' => $doctor->id,
                    'metadata' => [
                        'consultation_id' => $consultation->id,
                        'consultation_reference' => $reference,
                    ],
                ]);

                $paymentResponse = $paymentController->initialize($paymentRequest);
                $paymentData = json_decode($paymentResponse->getContent(), true);

                if ($paymentData['success'] && isset($paymentData['checkout_url'])) {
                    // Link payment to consultation
                    $payment = \App\Models\Payment::where('reference', $paymentData['reference'])->first();
                    if ($payment) {
                        $consultation->update([
                            'payment_id' => $payment->id,
                            'payment_status' => 'pending',
                        ]);
                    }

                    return redirect()
                        ->route('customer-care.consultations.show', $consultation)
                        ->with('info', 'Consultation booked successfully. Payment link: ' . $paymentData['checkout_url'])
                        ->with('payment_url', $paymentData['checkout_url']);
                }
            }

            return redirect()
                ->route('customer-care.consultations.show', $consultation)
                ->with('success', 'Consultation booked successfully. Reference: ' . $reference);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to book consultation by customer service', [
                'error' => $e->getMessage(),
                'agent_id' => $agent->id,
                'request_data' => $validated,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to book consultation. Please try again.');
        }
    }

    /**
     * Get doctor availability (AJAX)
     */
    public function getDoctorAvailability(Request $request, $doctorId)
    {
        try {
            $doctor = Doctor::where('id', $doctorId)
                ->where('is_approved', true)
                ->where('is_available', true)
                ->firstOrFail();

            $schedule = $doctor->availability_schedule ?? [];
            
            // Get existing consultations
            $existingConsultations = Consultation::where('doctor_id', $doctor->id)
                ->whereIn('status', ['pending', 'scheduled'])
                ->whereNotNull('scheduled_at')
                ->where('scheduled_at', '>=', now())
                ->get()
                ->map(function($consultation) {
                    return [
                        'date' => $consultation->scheduled_at->format('Y-m-d'),
                        'time' => $consultation->scheduled_at->format('H:i'),
                        'datetime' => $consultation->scheduled_at->format('Y-m-d H:i:s'),
                    ];
                });

            return response()->json([
                'success' => true,
                'doctor' => [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                    'specialization' => $doctor->specialization,
                ],
                'availability_schedule' => $schedule,
                'booked_slots' => $existingConsultations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found or unavailable'
            ], 404);
        }
    }
}
