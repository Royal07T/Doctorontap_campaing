<?php

namespace App\Notifications;

use App\Services\TermiiService;
use App\Services\VonageService;
use Illuminate\Support\Facades\Log;

class ConsultationSmsNotification
{
    protected $smsService;

    /**
     * Constructor with dependency injection
     * Automatically selects the configured SMS provider
     */
    public function __construct()
    {
        $provider = config('services.sms_provider', 'termii');
        
        if ($provider === 'vonage') {
            $this->smsService = app(VonageService::class);
        } else {
            $this->smsService = app(TermiiService::class);
        }
    }

    /**
     * Send consultation confirmation SMS to patient
     *
     * @param array $data Consultation data
     * @return array Result of SMS sending
     */
    public function sendConsultationConfirmation(array $data): array
    {
        $phone = $data['mobile'] ?? null;
        $reference = $data['consultation_reference'] ?? 'N/A';
        $patientName = $data['first_name'] ?? 'Patient';

        if (empty($phone)) {
            return $this->logAndReturnError(
                'Cannot send SMS: No phone number provided',
                ['consultation_reference' => $reference],
                'No phone number provided'
            );
        }

        $message = $this->getConsultationConfirmationTemplate($patientName, $reference);
        
        return $this->sendSmsWithLogging(
            $phone,
            $message,
            'consultation_confirmation',
            ['consultation_reference' => $reference, 'phone' => $phone]
        );
    }

    /**
     * Send payment request SMS to patient
     *
     * @param \App\Models\Consultation $consultation
     * @return array Result of SMS sending
     */
    public function sendPaymentRequest($consultation): array
    {
        $phone = $consultation->mobile;
        $reference = $consultation->reference;
        $patientName = $consultation->first_name;
        $fee = $consultation->doctor ? $consultation->doctor->effective_consultation_fee : 0;

        if (empty($phone)) {
            return $this->logAndReturnError(
                'Cannot send payment SMS: No phone number',
                ['consultation_id' => $consultation->id],
                'No phone number provided'
            );
        }

        $message = $this->getPaymentRequestTemplate($patientName, $fee, $reference);
        
        return $this->sendSmsWithLogging(
            $phone,
            $message,
            'payment_request',
            [
                'consultation_id' => $consultation->id,
                'reference' => $reference,
                'phone' => $phone
            ]
        );
    }

    /**
     * Send treatment plan ready SMS to patient
     *
     * @param \App\Models\Consultation $consultation
     * @return array Result of SMS sending
     */
    public function sendTreatmentPlanReady($consultation): array
    {
        $phone = $consultation->mobile;
        $reference = $consultation->reference;
        $patientName = $consultation->first_name;

        if (empty($phone)) {
            return $this->logAndReturnError(
                'Cannot send treatment plan SMS: No phone number',
                ['consultation_id' => $consultation->id],
                'No phone number provided'
            );
        }

        $accessUrl = route('consultation.view-treatment-plan', ['reference' => $reference]);
        $message = $this->getTreatmentPlanReadyTemplate($patientName, $accessUrl);
        
        return $this->sendSmsWithLogging(
            $phone,
            $message,
            'treatment_plan_ready',
            [
                'consultation_id' => $consultation->id,
                'reference' => $reference,
                'phone' => $phone
            ]
        );
    }

    /**
     * Send consultation status change SMS to patient
     *
     * @param \App\Models\Consultation $consultation
     * @param string $status New status
     * @return array Result of SMS sending
     */
    public function sendStatusChange($consultation, string $status): array
    {
        $phone = $consultation->mobile;
        $reference = $consultation->reference;
        $patientName = $consultation->first_name;

        if (empty($phone)) {
            return $this->logAndReturnError(
                'Cannot send status change SMS: No phone number',
                ['consultation_id' => $consultation->id],
                'No phone number provided',
                'warning'
            );
        }

        $message = $this->getStatusChangeTemplate($patientName, $reference, $status);
        
        return $this->sendSmsWithLogging(
            $phone,
            $message,
            'status_change',
            [
                'consultation_id' => $consultation->id,
                'status' => $status
            ]
        );
    }

    /**
     * Send new consultation notification SMS to doctor
     *
     * @param \App\Models\Doctor $doctor
     * @param array $consultationData Consultation information
     * @return array Result of SMS sending
     */
    public function sendDoctorNewConsultation($doctor, array $consultationData): array
    {
        if (!$doctor || empty($doctor->phone)) {
            return $this->logAndReturnError(
                'Cannot send doctor SMS: No phone number',
                [
                    'doctor_id' => $doctor->id ?? null,
                    'consultation_reference' => $consultationData['consultation_reference'] ?? 'N/A'
                ],
                'No doctor phone number provided'
            );
        }

        $doctorName = $doctor->first_name ?? $doctor->name ?? 'Doctor';
        $patientName = $consultationData['first_name'] ?? 'Patient';
        $reference = $consultationData['consultation_reference'] ?? 'N/A';
        $severity = ucfirst($consultationData['severity'] ?? 'N/A');
        $mode = ucfirst($consultationData['consult_mode'] ?? 'N/A');

        $message = $this->getDoctorNewConsultationTemplate($doctorName, $patientName, $severity, $mode, $reference);
        
        return $this->sendSmsWithLogging(
            $doctor->phone,
            $message,
            'doctor_consultation',
            [
                'doctor_id' => $doctor->id,
                'doctor_phone' => $doctor->phone,
                'consultation_reference' => $reference
            ]
        );
    }

    // ========================================
    // SMS TEMPLATES
    // ========================================

    /**
     * Get consultation confirmation SMS template
     *
     * @param string $patientName
     * @param string $reference
     * @return string
     */
    protected function getConsultationConfirmationTemplate(string $patientName, string $reference): string
    {
        return "Dear {$patientName}, your consultation has been booked successfully! Reference: {$reference}. We'll contact you shortly via WhatsApp. You only pay AFTER consultation. - DoctorOnTap";
    }

    /**
     * Get payment request SMS template
     *
     * @param string $patientName
     * @param float $fee
     * @param string $reference
     * @return string
     */
    protected function getPaymentRequestTemplate(string $patientName, float $fee, string $reference): string
    {
        return "Dear {$patientName}, your consultation is complete. Please pay NGN{$fee} to access your treatment plan. Ref: {$reference}. Visit your payment link in the email sent. - DoctorOnTap";
    }

    /**
     * Get treatment plan ready SMS template
     *
     * @param string $patientName
     * @param string $accessUrl
     * @return string
     */
    protected function getTreatmentPlanReadyTemplate(string $patientName, string $accessUrl): string
    {
        return "Dear {$patientName}, your treatment plan is ready! Access it here: {$accessUrl} - DoctorOnTap";
    }

    /**
     * Get status change SMS template
     *
     * @param string $patientName
     * @param string $reference
     * @param string $status
     * @return string
     */
    protected function getStatusChangeTemplate(string $patientName, string $reference, string $status): string
    {
        $templates = [
            'assigned' => "Dear {$patientName}, a doctor has been assigned to your consultation. Ref: {$reference}. We'll contact you soon. - DoctorOnTap",
            'in_progress' => "Dear {$patientName}, your consultation is now in progress. Ref: {$reference}. - DoctorOnTap",
            'completed' => "Dear {$patientName}, your consultation is complete. You'll receive payment details shortly. Ref: {$reference}. - DoctorOnTap",
            'cancelled' => "Dear {$patientName}, your consultation has been cancelled. Ref: {$reference}. Contact us for details. - DoctorOnTap",
        ];

        return $templates[$status] ?? "Your consultation status has been updated. Ref: {$reference}. - DoctorOnTap";
    }

    /**
     * Send delay query SMS to doctor (Admin-initiated)
     *
     * @param \App\Models\Doctor $doctor
     * @param array $consultationData Consultation information
     * @return array Result of SMS sending
     */
    public function sendDelayQuerySms($doctor, array $consultationData): array
    {
        if (!$doctor || empty($doctor->phone)) {
            return $this->logAndReturnError(
                'Cannot send delay query SMS: No phone number',
                [
                    'doctor_id' => $doctor->id ?? null,
                    'consultation_reference' => $consultationData['consultation_reference'] ?? 'N/A'
                ],
                'No doctor phone number provided'
            );
        }

        $doctorName = $doctor->first_name ?? $doctor->name ?? 'Doctor';
        $patientName = ($consultationData['first_name'] ?? 'Patient') . ' ' . ($consultationData['last_name'] ?? '');
        $reference = $consultationData['consultation_reference'] ?? 'N/A';

        $message = $this->getDelayQueryTemplate($doctorName, $patientName, $reference);
        
        return $this->sendSmsWithLogging(
            $doctor->phone,
            $message,
            'delay_query',
            [
                'doctor_id' => $doctor->id,
                'doctor_phone' => $doctor->phone,
                'consultation_reference' => $reference
            ]
        );
    }

    /**
     * Get doctor new consultation SMS template
     *
     * @param string $doctorName
     * @param string $patientName
     * @param string $severity
     * @param string $mode
     * @param string $reference
     * @return string
     */
    protected function getDoctorNewConsultationTemplate(
        string $doctorName,
        string $patientName,
        string $severity,
        string $mode,
        string $reference
    ): string {
        return "Dr. {$doctorName}, new consultation assigned! Patient: {$patientName}, Severity: {$severity}, Mode: {$mode}, Ref: {$reference}. Check your dashboard. - DoctorOnTap";
    }

    /**
     * Get delay query SMS template
     *
     * @param string $doctorName
     * @param string $patientName
     * @param string $reference
     * @return string
     */
    protected function getDelayQueryTemplate(string $doctorName, string $patientName, string $reference): string
    {
        return "URGENT: Dr. {$doctorName}, you are late for appointment. Patient: {$patientName}, Ref: {$reference}. Please initiate consultation immediately or update status. Check your dashboard now! - DoctorOnTap";
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Send SMS with comprehensive logging
     *
     * @param string $phone
     * @param string $message
     * @param string $type
     * @param array $context
     * @return array
     */
    private function sendSmsWithLogging(string $phone, string $message, string $type, array $context = []): array
    {
        $normalizedPhone = $this->normalizePhoneNumber($phone);

        try {
            $result = $this->smsService->sendSMS($normalizedPhone, $message);

            if ($result['success']) {
                $this->logSuccess($type, $context, $result);
            } else {
                $this->logFailure($type, $context, $result);
            }

            return $result;
        } catch (\Exception $e) {
            return $this->logAndReturnException($type, $context, $e);
        }
    }

    /**
     * Normalize phone number to Nigerian format
     *
     * @param string $phone
     * @return string
     */
    protected function normalizePhoneNumber(string $phone): string
    {
        // Remove all spaces, hyphens, and parentheses
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

        // Remove any non-digit characters except +
        $phone = preg_replace('/[^\d+]/', '', $phone);

        // If starts with 0, replace with 234
        if (substr($phone, 0, 1) === '0') {
            $phone = '234' . substr($phone, 1);
        }

        // If starts with 234 but no +, add it
        if (substr($phone, 0, 3) === '234' && substr($phone, 0, 1) !== '+') {
            $phone = '+' . $phone;
        }

        // If doesn't start with + or 234, assume it needs +234 prefix
        if (substr($phone, 0, 1) !== '+' && substr($phone, 0, 3) !== '234') {
            $phone = '+234' . $phone;
        }

        return $phone;
    }

    /**
     * Log successful SMS sending
     *
     * @param string $type
     * @param array $context
     * @param array $result
     * @return void
     */
    private function logSuccess(string $type, array $context, array $result): void
    {
        Log::info(ucfirst(str_replace('_', ' ', $type)) . ' SMS sent', array_merge($context, [
            'message_id' => $result['data']['message_id'] ?? null,
            'balance' => $result['data']['balance'] ?? null
        ]));
    }

    /**
     * Log failed SMS sending
     *
     * @param string $type
     * @param array $context
     * @param array $result
     * @return void
     */
    private function logFailure(string $type, array $context, array $result): void
    {
        Log::warning(ucfirst(str_replace('_', ' ', $type)) . ' SMS failed', array_merge($context, [
            'error' => $result['error'] ?? 'Unknown error',
            'status_code' => $result['status_code'] ?? null
        ]));
    }

    /**
     * Log exception and return error response
     *
     * @param string $type
     * @param array $context
     * @param \Exception $e
     * @return array
     */
    private function logAndReturnException(string $type, array $context, \Exception $e): array
    {
        Log::error('Exception sending ' . str_replace('_', ' ', $type) . ' SMS', array_merge($context, [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]));

        return [
            'success' => false,
            'message' => 'Exception occurred',
            'error' => $e->getMessage()
        ];
    }

    /**
     * Log error/warning and return error response
     *
     * @param string $message
     * @param array $context
     * @param string $returnMessage
     * @param string $level
     * @return array
     */
    /**
     * Send referral notification via SMS
     *
     * @param \App\Models\Consultation $originalConsultation
     * @param \App\Models\Consultation $newConsultation
     * @param \App\Models\Doctor $referringDoctor
     * @param \App\Models\Doctor $referredToDoctor
     * @param string $recipientType 'patient' or 'doctor'
     * @return array Result of SMS sending
     */
    public function sendReferralNotification($originalConsultation, $newConsultation, $referringDoctor, $referredToDoctor, string $recipientType): array
    {
        if ($recipientType === 'patient') {
            $phone = $originalConsultation->mobile;
            $patientName = $originalConsultation->first_name ?? 'Patient';
            $message = $this->getReferralTemplate($patientName, $originalConsultation->reference, $newConsultation->reference, $referringDoctor->name ?? $referringDoctor->full_name, $referredToDoctor->name ?? $referredToDoctor->full_name, 'patient');
        } else {
            $phone = $referredToDoctor->phone;
            $doctorName = $referredToDoctor->first_name ?? $referredToDoctor->name ?? 'Doctor';
            $patientName = $originalConsultation->first_name ?? 'Patient';
            $message = $this->getReferralTemplate($doctorName, $originalConsultation->reference, $newConsultation->reference, $referringDoctor->name ?? $referringDoctor->full_name, $referredToDoctor->name ?? $referredToDoctor->full_name, 'doctor', $patientName);
        }

        if (empty($phone)) {
            return $this->logAndReturnError(
                "Cannot send referral SMS to {$recipientType}: No phone number",
                [
                    'consultation_id' => $newConsultation->id,
                    'recipient_type' => $recipientType
                ],
                'No phone number provided'
            );
        }

        return $this->sendSmsWithLogging(
            $phone,
            $message,
            'referral_notification',
            [
                'consultation_id' => $newConsultation->id,
                'original_consultation_id' => $originalConsultation->id,
                'recipient_type' => $recipientType,
                'phone' => $phone
            ]
        );
    }

    /**
     * Get referral SMS template
     *
     * @param string $recipientName
     * @param string $originalReference
     * @param string $newReference
     * @param string $referringDoctorName
     * @param string $referredToDoctorName
     * @param string $recipientType
     * @param string|null $patientName
     * @return string
     */
    protected function getReferralTemplate(string $recipientName, string $originalReference, string $newReference, string $referringDoctorName, string $referredToDoctorName, string $recipientType, ?string $patientName = null): string
    {
        if ($recipientType === 'patient') {
            return "Dear {$recipientName}, Dr. {$referringDoctorName} has referred you to Dr. {$referredToDoctorName}. New consultation Ref: {$newReference}. You'll be notified of next steps. - DoctorOnTap";
        } else {
            $patientText = $patientName ? " for patient {$patientName}" : '';
            return "Dear Dr. {$recipientName}, you have received a new patient referral{$patientText} from Dr. {$referringDoctorName}. New consultation Ref: {$newReference}. Please review and respond. - DoctorOnTap";
        }
    }

    private function logAndReturnError(
        string $message,
        array $context,
        string $returnMessage,
        string $level = 'warning'
    ): array {
        $level === 'error' ? Log::error($message, $context) : Log::warning($message, $context);

        return [
            'success' => false,
            'message' => $returnMessage
        ];
    }
}
