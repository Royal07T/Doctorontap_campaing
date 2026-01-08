<?php

namespace App\Services;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Notification;
use App\Mail\ConsultationConfirmation;
use App\Mail\ConsultationAdminAlert;
use App\Mail\ConsultationDoctorNotification;
use App\Notifications\ConsultationSmsNotification;
use App\Notifications\ConsultationWhatsAppNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ConsultationService
{
    /**
     * Create a new consultation from validated data
     *
     * @param array $validated Validated request data
     * @param array $uploadedDocuments Uploaded medical documents
     * @return array Returns consultation data and reference
     * @throws \Exception
     */
    public function createConsultation(array $validated, array $uploadedDocuments = []): array
    {
        // Generate unique consultation reference
        $reference = 'CONSULT-' . time() . '-' . Str::random(6);

        // Get doctor details from ID if a doctor was selected
        $doctorEmail = null;
        $doctorId = null;
        $doctorName = null;
        $doctorFee = null;
        
        if (!empty($validated['doctor'])) {
            $doctor = Doctor::find($validated['doctor']);
            if ($doctor) {
                $doctorName = $doctor->name;
                $doctorId = $doctor->id;
                $doctorEmail = $doctor->email;
                $doctorFee = $doctor->effective_consultation_fee;
                
                $validated['doctor_name'] = $doctorName;
                $validated['doctor_id'] = $doctorId;
                $validated['doctor'] = $doctorName; // Replace ID with name for emails
                $validated['doctor_fee'] = $doctorFee;
            }
        }

        // Create or update patient record
        $patient = $this->findOrCreatePatient($validated);

        // Create consultation record
        $consultation = Consultation::create([
            'reference' => $reference,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'age' => $validated['age'],
            'gender' => $validated['gender'],
            'problem' => $validated['problem'],
            'medical_documents' => !empty($uploadedDocuments) ? $uploadedDocuments : null,
            'severity' => $validated['severity'],
            'emergency_symptoms' => $validated['emergency_symptoms'] ?? null,
            'consult_mode' => $validated['consult_mode'],
            'doctor_id' => $doctorId,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        // Create notifications for patient and doctor
        $this->createNotifications($consultation, $patient, $validated, $reference, $doctorId, $doctorName);

        // Update patient aggregates
        $this->updatePatientAggregates($patient);

        // Prepare data for notifications
        $validated['consultation_reference'] = $reference;
        $validated['has_documents'] = !empty($uploadedDocuments);
        $validated['documents_count'] = count($uploadedDocuments);

        // Send notifications (emails, SMS, WhatsApp)
        $this->sendNotifications($validated, $reference, $doctorId, $doctorEmail, $doctorName);

        return [
            'consultation' => $consultation,
            'reference' => $reference,
            'patient' => $patient,
        ];
    }

    /**
     * Find or create patient record
     *
     * @param array $validated
     * @return Patient
     * @throws \Exception
     */
    private function findOrCreatePatient(array $validated): Patient
    {
        // First, check if a soft-deleted patient exists with this email
        $patient = Patient::withTrashed()->where('email', $validated['email'])->first();
        
        if ($patient) {
            // Patient exists (soft-deleted or not)
            if ($patient->trashed()) {
                // Restore the soft-deleted patient
                $patient->restore();
                Log::info('Restored soft-deleted patient', [
                    'patient_id' => $patient->id,
                    'email' => $validated['email']
                ]);
            }
            
            // Update the patient record
            $patient->update([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'phone' => $validated['mobile'],
                'gender' => $validated['gender'],
                'age' => $validated['age'],
            ]);
        } else {
            // Create new patient
            $patient = Patient::create([
                'email' => $validated['email'],
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'phone' => $validated['mobile'],
                'gender' => $validated['gender'],
                'age' => $validated['age'],
            ]);
            
            // Send email verification notification for new patients
            try {
                $patient->sendEmailVerificationNotification();
                Log::info('Verification email sent to new patient', [
                    'patient_id' => $patient->id,
                    'email' => $patient->email
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send verification email: ' . $e->getMessage(), [
                    'patient_id' => $patient->id,
                    'email' => $patient->email
                ]);
            }
        }

        return $patient;
    }

    /**
     * Create notifications for patient and doctor
     *
     * @param Consultation $consultation
     * @param Patient $patient
     * @param array $validated
     * @param string $reference
     * @param int|null $doctorId
     * @param string|null $doctorName
     * @return void
     */
    private function createNotifications(
        Consultation $consultation,
        Patient $patient,
        array $validated,
        string $reference,
        ?int $doctorId,
        ?string $doctorName
    ): void {
        try {
            // Notification for patient (if authenticated)
            if ($patient && $patient->id) {
                Notification::create([
                    'user_type' => 'patient',
                    'user_id' => $patient->id,
                    'title' => 'Consultation Created',
                    'message' => "Your consultation request (Ref: {$reference}) has been submitted successfully. " . 
                        ($doctorId ? "You have been assigned to Dr. {$doctorName}." : "A doctor will be assigned shortly."),
                    'type' => 'success',
                    'action_url' => patient_url('consultations/' . $consultation->id),
                    'data' => [
                        'consultation_id' => $consultation->id,
                        'consultation_reference' => $reference,
                        'doctor_id' => $doctorId,
                        'doctor_name' => $doctorName,
                        'type' => 'consultation_created'
                    ]
                ]);
            }

            // Notification for doctor (if assigned)
            if ($doctorId) {
                $assignedDoctor = Doctor::find($doctorId);
                if ($assignedDoctor) {
                    Notification::create([
                        'user_type' => 'doctor',
                        'user_id' => $doctorId,
                        'title' => 'New Consultation Assigned',
                        'message' => "A new consultation (Ref: {$reference}) has been assigned to you. Patient: {$validated['first_name']} {$validated['last_name']}",
                        'type' => 'info',
                        'action_url' => doctor_url('consultations/' . $consultation->id),
                        'data' => [
                            'consultation_id' => $consultation->id,
                            'consultation_reference' => $reference,
                            'patient_name' => $validated['first_name'] . ' ' . $validated['last_name'],
                            'type' => 'new_consultation'
                        ]
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to create consultation notifications', [
                'consultation_id' => $consultation->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update patient aggregates
     *
     * @param Patient $patient
     * @return void
     */
    private function updatePatientAggregates(Patient $patient): void
    {
        try {
            $patient->increment('consultations_count');
            $patient->last_consultation_at = now();
            $patient->save();
        } catch (\Exception $e) {
            Log::warning('Failed to update patient aggregates: ' . $e->getMessage());
            // Non-critical, continue anyway
        }
    }

    /**
     * Send all notifications (emails, SMS, WhatsApp)
     *
     * @param array $validated
     * @param string $reference
     * @param int|null $doctorId
     * @param string|null $doctorEmail
     * @param string|null $doctorName
     * @return void
     */
    private function sendNotifications(
        array $validated,
        string $reference,
        ?int $doctorId,
        ?string $doctorEmail,
        ?string $doctorName
    ): void {
        $emailsSent = 0;
        $adminEmail = config('mail.admin_email');

        // Send confirmation email to the patient
        try {
            Mail::to($validated['email'])->send(new ConsultationConfirmation($validated));
            $emailsSent++;
            Log::info('Patient confirmation email sent successfully', [
                'consultation_reference' => $reference,
                'patient_email' => $validated['email']
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to send patient confirmation email: ' . $e->getMessage(), [
                'consultation_reference' => $reference,
                'patient_email' => $validated['email']
            ]);
        }

        // Send SMS confirmation to the patient
        try {
            $smsNotification = new ConsultationSmsNotification();
            $smsResult = $smsNotification->sendConsultationConfirmation($validated);
            
            if ($smsResult['success']) {
                Log::info('Patient confirmation SMS sent successfully', [
                    'consultation_reference' => $reference,
                    'patient_mobile' => $validated['mobile']
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send patient confirmation SMS: ' . $e->getMessage(), [
                'consultation_reference' => $reference,
                'patient_mobile' => $validated['mobile'] ?? 'N/A'
            ]);
        }

        // Send alert email to admin
        try {
            Mail::to($adminEmail)->send(new ConsultationAdminAlert($validated));
            $emailsSent++;
            Log::info('Admin alert email sent successfully', [
                'consultation_reference' => $reference,
                'admin_email' => $adminEmail
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to send admin alert email: ' . $e->getMessage(), [
                'consultation_reference' => $reference,
                'admin_email' => $adminEmail
            ]);
        }

        // Send notification email to the assigned doctor
        if ($doctorEmail) {
            try {
                Mail::to($doctorEmail)->send(new ConsultationDoctorNotification($validated));
                $emailsSent++;
                Log::info('Doctor notification email sent successfully', [
                    'consultation_reference' => $reference,
                    'doctor_email' => $doctorEmail
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to send doctor notification email: ' . $e->getMessage(), [
                    'consultation_reference' => $reference,
                    'doctor_email' => $doctorEmail ?? 'N/A'
                ]);
            }
        }

        // Send SMS notification to the assigned doctor
        if ($doctorId) {
            try {
                $smsNotification = new ConsultationSmsNotification();
                $assignedDoctor = Doctor::find($doctorId);
                
                if ($assignedDoctor) {
                    $smsResult = $smsNotification->sendDoctorNewConsultation($assignedDoctor, $validated);
                    
                    if ($smsResult['success']) {
                        Log::info('Doctor notification SMS sent successfully', [
                            'consultation_reference' => $reference,
                            'doctor_id' => $doctorId,
                            'doctor_phone' => $assignedDoctor->phone
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to send doctor notification SMS: ' . $e->getMessage(), [
                    'consultation_reference' => $reference,
                    'doctor_id' => $doctorId
                ]);
            }
        }

        // Send WhatsApp notifications
        $this->sendWhatsAppNotifications($validated, $reference, $doctorId);

        Log::info('Consultation booking completed - emails sent', [
            'consultation_reference' => $reference,
            'total_emails_sent' => $emailsSent,
            'patient_email' => $validated['email'],
            'admin_email' => $adminEmail,
            'doctor_email' => $doctorEmail ?? 'N/A'
        ]);
    }

    /**
     * Send WhatsApp notifications
     *
     * @param array $validated
     * @param string $reference
     * @param int|null $doctorId
     * @return void
     */
    private function sendWhatsAppNotifications(array $validated, string $reference, ?int $doctorId): void
    {
        // Send WhatsApp notification to PATIENT (if enabled)
        if (config('services.termii.whatsapp_enabled')) {
            try {
                $whatsapp = new ConsultationWhatsAppNotification();
                
                $patientResult = $whatsapp->sendConsultationConfirmationTemplate(
                    $validated,
                    'patient_booking_confirmation'
                );
                
                if ($patientResult['success']) {
                    Log::info('Patient WhatsApp notification sent successfully', [
                        'consultation_reference' => $reference,
                        'patient_phone' => $validated['mobile']
                    ]);
                } else {
                    Log::warning('Patient WhatsApp notification failed', [
                        'consultation_reference' => $reference,
                        'error' => $patientResult['message'] ?? 'Unknown error'
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Patient WhatsApp notification error: ' . $e->getMessage(), [
                    'consultation_reference' => $reference,
                    'phone' => $validated['mobile'] ?? 'N/A'
                ]);
            }
        }
        
        // Send WhatsApp notification to DOCTOR (if doctor assigned and WhatsApp enabled)
        if (config('services.termii.whatsapp_enabled') && $doctorId) {
            try {
                $assignedDoctor = Doctor::find($doctorId);
                
                if ($assignedDoctor && $assignedDoctor->phone) {
                    $whatsapp = new ConsultationWhatsAppNotification();
                    
                    $doctorResult = $whatsapp->sendDoctorNewConsultationTemplate(
                        $assignedDoctor,
                        $validated,
                        'doctor_new_consultation'
                    );
                    
                    if ($doctorResult['success']) {
                        Log::info('Doctor WhatsApp notification sent successfully', [
                            'consultation_reference' => $reference,
                            'doctor_id' => $doctorId,
                            'doctor_phone' => $assignedDoctor->phone
                        ]);
                    } else {
                        Log::warning('Doctor WhatsApp notification failed', [
                            'consultation_reference' => $reference,
                            'doctor_id' => $doctorId,
                            'error' => $doctorResult['message'] ?? 'Unknown error'
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Doctor WhatsApp notification error: ' . $e->getMessage(), [
                    'consultation_reference' => $reference,
                    'doctor_id' => $doctorId
                ]);
            }
        }
    }

    /**
     * Handle medical document uploads
     *
     * @param \Illuminate\Http\UploadedFile[] $files
     * @return array
     */
    public function handleDocumentUploads(array $files): array
    {
        $uploadedDocuments = [];
        
        foreach ($files as $file) {
            try {
                $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                // Store in private storage (storage/app/private/medical_documents)
                $filePath = $file->storeAs('medical_documents', $fileName);
                
                $uploadedDocuments[] = [
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name' => $fileName,
                    'path' => $filePath,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            } catch (\Exception $e) {
                Log::error('Failed to upload medical document: ' . $e->getMessage(), [
                    'file_name' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Continue with other files rather than failing completely
            }
        }
        
        return $uploadedDocuments;
    }
}

