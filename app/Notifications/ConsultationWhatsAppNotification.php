<?php

namespace App\Notifications;

use App\Services\TermiiService;
use App\Services\VonageService;
use Illuminate\Support\Facades\Log;

class ConsultationWhatsAppNotification
{
    protected $whatsappService;
    protected string $provider;

    /**
     * Constructor with dependency injection
     * Automatically selects the configured WhatsApp provider
     */
    public function __construct()
    {
        $this->provider = config('services.whatsapp_provider', 'termii');
        
        if ($this->provider === 'vonage') {
            $this->whatsappService = app(VonageService::class);
        } else {
            $this->whatsappService = app(TermiiService::class);
        }
    }

    // ============================================
    // TEMPLATE-BASED NOTIFICATIONS
    // Use these to INITIATE conversations
    // ============================================

    /**
     * Send consultation confirmation via WhatsApp (Template)
     *
     * @param array $data Consultation data
     * @param string $templateIdOrName Template ID (Termii) or Template Name (Vonage)
     * @return array Result of WhatsApp sending
     */
    public function sendConsultationConfirmationTemplate(array $data, string $templateIdOrName): array
    {
        $phone = $data['mobile'] ?? null;
        $reference = $data['consultation_reference'] ?? 'N/A';
        $patientName = $data['first_name'] ?? 'Patient';

        if (empty($phone)) {
            return $this->logAndReturnError(
                'Cannot send WhatsApp: No phone number provided',
                ['consultation_reference' => $reference],
                'No phone number provided'
            );
        }

        // Template variables
        $templateData = [
            'patient_name' => $patientName,
            'reference' => $reference,
            'date' => now()->format('M d, Y'),
            'time' => now()->format('g:i A'),
        ];
        
        return $this->sendWhatsAppTemplateWithLogging(
            $phone,
            $templateIdOrName,
            $templateData,
            'consultation_confirmation_whatsapp',
            ['consultation_reference' => $reference, 'phone' => $phone]
        );
    }

    /**
     * Send doctor notification via WhatsApp (Template)
     *
     * @param \App\Models\Doctor $doctor
     * @param array $consultationData
     * @param string $templateIdOrName Template ID (Termii) or Template Name (Vonage)
     * @return array Result of WhatsApp sending
     */
    public function sendDoctorNewConsultationTemplate($doctor, array $consultationData, string $templateIdOrName): array
    {
        $phone = $doctor->phone;
        $reference = $consultationData['consultation_reference'] ?? 'N/A';

        if (empty($phone)) {
            return $this->logAndReturnError(
                'Cannot send WhatsApp to doctor: No phone number',
                ['doctor_id' => $doctor->id],
                'No phone number provided'
            );
        }

        // Template variables
        $templateData = [
            'doctor_name' => $doctor->first_name ?? 'Doctor',
            'patient_name' => $consultationData['first_name'] ?? 'Patient',
            'reference' => $reference,
            'complaint' => $consultationData['chief_complaint'] ?? 'Not specified',
        ];
        
        return $this->sendWhatsAppTemplateWithLogging(
            $phone,
            $templateIdOrName,
            $templateData,
            'doctor_notification_whatsapp',
            [
                'doctor_id' => $doctor->id,
                'consultation_reference' => $reference,
                'phone' => $phone
            ]
        );
    }

    // ============================================
    // CONVERSATIONAL MESSAGES (Within 24hr window)
    // Use these to REPLY after patient messages
    // ============================================

    /**
     * Send treatment plan ready notification via WhatsApp
     *
     * @param \App\Models\Consultation $consultation
     * @param string|null $pdfUrl Optional: URL to treatment plan PDF
     * @return array Result of WhatsApp sending
     */
    public function sendTreatmentPlanReady($consultation, ?string $pdfUrl = null): array
    {
        $phone = $consultation->mobile;
        $reference = $consultation->reference;
        $patientName = $consultation->first_name;

        if (empty($phone)) {
            return $this->logAndReturnError(
                'Cannot send treatment plan WhatsApp: No phone number',
                ['consultation_id' => $consultation->id],
                'No phone number provided'
            );
        }

        $accessUrl = route('consultation.view-treatment-plan', ['reference' => $reference]);
        
        $message = "🎉 *Great News, {$patientName}!*\n\n";
        $message .= "Your treatment plan is ready!\n\n";
        $message .= "📋 *Reference:* {$reference}\n";
        $message .= "👨‍⚕️ *Next Steps:*\n";
        $message .= "1. Review your treatment plan\n";
        $message .= "2. Complete payment to unlock full access\n";
        $message .= "3. Start your treatment journey\n\n";
        $message .= "🔗 *View Plan:* {$accessUrl}\n\n";
        $message .= "💳 *Payment:* NGN 5,000\n\n";
        $message .= "Questions? Reply to this message!\n\n";
        $message .= "— *DoctorOnTap Healthcare* 🏥";
        
        return $this->sendWhatsAppMessageWithLogging(
            $phone,
            $message,
            $pdfUrl, // Optional PDF attachment
            "Treatment Plan for {$patientName} ({$reference})",
            'treatment_plan_ready_whatsapp',
            [
                'consultation_id' => $consultation->id,
                'reference' => $reference,
                'phone' => $phone,
                'has_pdf' => !empty($pdfUrl)
            ]
        );
    }

    /**
     * Send payment confirmation via WhatsApp
     *
     * @param \App\Models\Consultation $consultation
     * @return array Result of WhatsApp sending
     */
    public function sendPaymentConfirmed($consultation): array
    {
        $phone = $consultation->mobile;
        $reference = $consultation->reference;
        $patientName = $consultation->first_name;

        if (empty($phone)) {
            return $this->logAndReturnError(
                'Cannot send payment confirmation WhatsApp: No phone number',
                ['consultation_id' => $consultation->id],
                'No phone number provided'
            );
        }

        $message = "✅ *Payment Confirmed!*\n\n";
        $message .= "Hi {$patientName},\n\n";
        $message .= "Thank you! We've received your payment.\n\n";
        $message .= "📋 *Reference:* {$reference}\n";
        $message .= "💳 *Amount:* NGN 5,000\n\n";
        $message .= "🎉 *What's Next:*\n";
        $message .= "• Full treatment plan unlocked\n";
        $message .= "• Doctor will contact you for follow-up\n";
        $message .= "• Keep this reference for records\n\n";
        $message .= "📱 Reply if you have questions!\n\n";
        $message .= "— *DoctorOnTap Healthcare* 🏥";
        
        return $this->sendWhatsAppMessageWithLogging(
            $phone,
            $message,
            null,
            null,
            'payment_confirmed_whatsapp',
            [
                'consultation_id' => $consultation->id,
                'reference' => $reference,
                'phone' => $phone
            ]
        );
    }

    /**
     * Send vital signs report via WhatsApp
     *
     * @param \App\Models\VitalSign $vitalSign
     * @param string|null $pdfUrl Optional: URL to PDF report
     * @return array Result of WhatsApp sending
     */
    public function sendVitalSignsReport($vitalSign, ?string $pdfUrl = null): array
    {
        $phone = $vitalSign->patient->phone ?? null;
        $patientName = $vitalSign->patient->full_name ?? 'Patient';

        if (empty($phone)) {
            return $this->logAndReturnError(
                'Cannot send vital signs WhatsApp: No phone number',
                ['vital_sign_id' => $vitalSign->id],
                'No phone number provided'
            );
        }

        $message = "📊 *Your Health Report is Ready!*\n\n";
        $message .= "Hi {$patientName},\n\n";
        $message .= "Your vital signs have been recorded:\n\n";
        $message .= "🩺 *Vital Signs:*\n";
        $message .= "• Blood Pressure: {$vitalSign->blood_pressure} mmHg\n";
        $message .= "• Heart Rate: {$vitalSign->pulse_rate} bpm\n";
        $message .= "• Temperature: {$vitalSign->temperature}°C\n";
        $message .= "• Weight: {$vitalSign->weight} kg\n";
        $message .= "• Height: {$vitalSign->height} cm\n\n";
        
        // Add health interpretation
        $message .= "💡 *Interpretation:* See attached PDF for detailed analysis.\n\n";
        $message .= "Questions? Reply to chat with a healthcare professional!\n\n";
        $message .= "— *DoctorOnTap Healthcare* 🏥";
        
        return $this->sendWhatsAppMessageWithLogging(
            $phone,
            $message,
            $pdfUrl,
            "Vital Signs Report for {$patientName}",
            'vital_signs_report_whatsapp',
            [
                'vital_sign_id' => $vitalSign->id,
                'patient_id' => $vitalSign->patient_id,
                'phone' => $phone,
                'has_pdf' => !empty($pdfUrl)
            ]
        );
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Send WhatsApp template with logging
     *
     * @param string $phone
     * @param string $templateIdOrName Template ID (Termii) or Template Name (Vonage)
     * @param array $templateData
     * @param string $type
     * @param array $context
     * @return array
     */
    private function sendWhatsAppTemplateWithLogging(
        string $phone,
        string $templateIdOrName,
        array $templateData,
        string $type,
        array $context = []
    ): array {
        try {
            if ($this->provider === 'vonage') {
                // Vonage uses template names and requires Messages API
                // Format: template name, language, and parameters
                $result = $this->whatsappService->sendWhatsAppTemplate(
                    $phone,
                    $templateIdOrName, // Template name
                    'en', // Language code (can be made configurable)
                    $this->convertTemplateDataForVonage($templateData)
                );
            } else {
                // Termii uses template IDs
                $normalizedPhone = $this->normalizePhoneNumber($phone);
                $result = $this->whatsappService->sendWhatsAppTemplate($normalizedPhone, $templateIdOrName, $templateData);
            }

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
     * Send WhatsApp message with logging
     *
     * @param string $phone
     * @param string $message
     * @param string|null $mediaUrl
     * @param string|null $caption
     * @param string $type
     * @param array $context
     * @return array
     */
    private function sendWhatsAppMessageWithLogging(
        string $phone,
        string $message,
        ?string $mediaUrl,
        ?string $caption,
        string $type,
        array $context = []
    ): array {
        try {
            if ($this->provider === 'vonage') {
                // Vonage WhatsApp text messages (within 24-hour window)
                // Note: Media support can be added later if needed
                $result = $this->whatsappService->sendWhatsAppMessage($phone, $message);
            } else {
                // Termii WhatsApp messages
                $normalizedPhone = $this->normalizePhoneNumber($phone);
                $result = $this->whatsappService->sendWhatsAppMessage($normalizedPhone, $message, $mediaUrl, $caption);
            }

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
     * Normalize phone number to format for WhatsApp (234XXXXXXXXXX)
     *
     * @param string $phone
     * @return string
     */
    protected function normalizePhoneNumber(string $phone): string
    {
        // Remove all spaces, hyphens, parentheses, and +
        $phone = preg_replace('/[\s\-\(\)+]/', '', $phone);

        // If starts with 0, replace with 234
        if (substr($phone, 0, 1) === '0') {
            $phone = '234' . substr($phone, 1);
        }

        // If doesn't start with 234, add it
        if (substr($phone, 0, 3) !== '234') {
            $phone = '234' . $phone;
        }

        return $phone;
    }

    /**
     * Log successful WhatsApp sending
     *
     * @param string $type
     * @param array $context
     * @param array $result
     * @return void
     */
    private function logSuccess(string $type, array $context, array $result): void
    {
        Log::info(ucfirst(str_replace('_', ' ', $type)) . ' sent', array_merge($context, [
            'message_id' => $result['data']['message_id'] ?? null,
            'channel' => 'whatsapp'
        ]));
    }

    /**
     * Log failed WhatsApp sending
     *
     * @param string $type
     * @param array $context
     * @param array $result
     * @return void
     */
    private function logFailure(string $type, array $context, array $result): void
    {
        Log::warning(ucfirst(str_replace('_', ' ', $type)) . ' failed', array_merge($context, [
            'error' => $result['error'] ?? 'Unknown error',
            'channel' => 'whatsapp'
        ]));
    }

    /**
     * Log and return error
     *
     * @param string $message
     * @param array $context
     * @param string $returnMessage
     * @param string $level
     * @return array
     */
    private function logAndReturnError(
        string $message,
        array $context,
        string $returnMessage,
        string $level = 'error'
    ): array {
        Log::$level($message, array_merge($context, ['channel' => 'whatsapp']));

        return [
            'success' => false,
            'message' => $returnMessage,
            'error' => 'validation_error'
        ];
    }

    /**
     * Log exception and return error
     *
     * @param string $type
     * @param array $context
     * @param \Exception $e
     * @return array
     */
    private function logAndReturnException(string $type, array $context, \Exception $e): array
    {
        Log::error(ucfirst(str_replace('_', ' ', $type)) . ' exception', array_merge($context, [
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'channel' => 'whatsapp'
        ]));

        return [
            'success' => false,
            'message' => 'Exception occurred while sending WhatsApp',
            'error' => $e->getMessage()
        ];
    }

    /**
     * Send referral notification via WhatsApp
     *
     * @param \App\Models\Consultation $originalConsultation
     * @param \App\Models\Consultation $newConsultation
     * @param \App\Models\Doctor $referringDoctor
     * @param \App\Models\Doctor $referredToDoctor
     * @param string $recipientType 'patient' or 'doctor'
     * @return array Result of WhatsApp sending
     */
    public function sendReferralNotification($originalConsultation, $newConsultation, $referringDoctor, $referredToDoctor, string $recipientType): array
    {
        if ($recipientType === 'patient') {
            $phone = $originalConsultation->mobile;
            $patientName = $originalConsultation->first_name ?? 'Patient';
            $message = $this->getReferralWhatsAppTemplate($patientName, $originalConsultation->reference, $newConsultation->reference, $referringDoctor->name ?? $referringDoctor->full_name, $referredToDoctor->name ?? $referredToDoctor->full_name, 'patient');
        } else {
            $phone = $referredToDoctor->phone;
            $doctorName = $referredToDoctor->first_name ?? $referredToDoctor->name ?? 'Doctor';
            $patientName = $originalConsultation->first_name ?? 'Patient';
            $message = $this->getReferralWhatsAppTemplate($doctorName, $originalConsultation->reference, $newConsultation->reference, $referringDoctor->name ?? $referringDoctor->full_name, $referredToDoctor->name ?? $referredToDoctor->full_name, 'doctor', $patientName);
        }

        if (empty($phone)) {
            return $this->logAndReturnError(
                "Cannot send referral WhatsApp to {$recipientType}: No phone number",
                [
                    'consultation_id' => $newConsultation->id,
                    'recipient_type' => $recipientType
                ],
                'No phone number provided'
            );
        }

        return $this->sendWhatsAppMessageWithLogging(
            $phone,
            $message,
            null,
            "Referral Notification - {$newConsultation->reference}",
            'referral_notification_whatsapp',
            [
                'consultation_id' => $newConsultation->id,
                'original_consultation_id' => $originalConsultation->id,
                'recipient_type' => $recipientType,
                'phone' => $phone
            ]
        );
    }

    /**
     * Get referral WhatsApp template
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
    protected function getReferralWhatsAppTemplate(string $recipientName, string $originalReference, string $newReference, string $referringDoctorName, string $referredToDoctorName, string $recipientType, ?string $patientName = null): string
    {
        if ($recipientType === 'patient') {
            $message = "🔄 *Consultation Referral*\n\n";
            $message .= "Hi {$recipientName},\n\n";
            $message .= "Dr. {$referringDoctorName} has referred you to Dr. {$referredToDoctorName} for specialized care.\n\n";
            $message .= "📋 *Details:*\n";
            $message .= "• Original Ref: {$originalReference}\n";
            $message .= "• New Ref: {$newReference}\n\n";
            $message .= "A new consultation has been created. You'll receive further updates.\n\n";
            $message .= "Questions? Reply to this message!\n\n";
            $message .= "— *DoctorOnTap Healthcare* 🏥";
        } else {
            $message = "🔄 *New Patient Referral*\n\n";
            $message .= "Hi Dr. {$recipientName},\n\n";
            $message .= "You have received a new patient referral from Dr. {$referringDoctorName}";
            if ($patientName) {
                $message .= " for patient {$patientName}";
            }
            $message .= ".\n\n";
            $message .= "📋 *Details:*\n";
            $message .= "• Original Ref: {$originalReference}\n";
            $message .= "• New Ref: {$newReference}\n\n";
            $message .= "Please review the consultation and proceed with appropriate care.\n\n";
            $message .= "— *DoctorOnTap Healthcare* 🏥";
        }

        return $message;
    }

    /**
     * Convert template data format for Vonage WhatsApp
     * Vonage uses a different parameter structure than Termii
     *
     * @param array $templateData
     * @return array
     */
    protected function convertTemplateDataForVonage(array $templateData): array
    {
        // Vonage expects parameters in a specific format
        // This is a basic conversion - you may need to adjust based on your template structure
        $parameters = [];
        
        foreach ($templateData as $key => $value) {
            $parameters[] = [
                'type' => 'text',
                'text' => (string) $value
            ];
        }
        
        return $parameters;
    }
}

