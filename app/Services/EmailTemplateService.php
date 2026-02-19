<?php

namespace App\Services;

use App\Models\CommunicationTemplate;
use Illuminate\Support\Facades\Log;

/**
 * Service to handle email template mapping and rendering
 * Maps Mail classes to CommunicationTemplate entries
 */
class EmailTemplateService
{
    /**
     * Template name mappings
     * Maps Mail class names to CommunicationTemplate names
     */
    protected static $templateMappings = [
        'ConsultationConfirmation' => 'consultation_confirmation',
        'ConsultationAdminAlert' => 'consultation_admin_alert',
        'ConsultationDoctorNotification' => 'consultation_doctor_notification',
        'ConsultationReminder' => 'consultation_reminder',
        'ConsultationStatusChange' => 'consultation_status_change',
        'PaymentRequest' => 'payment_request',
        'PaymentReceivedAdminNotification' => 'payment_received_admin',
        'PaymentReceivedDoctorNotification' => 'payment_received_doctor',
        'PaymentFailedNotification' => 'payment_failed',
        'FeeAdjustmentNotification' => 'fee_adjustment',
        'FeeAdjustmentAdminNotification' => 'fee_adjustment_admin',
        'TreatmentPlanNotification' => 'treatment_plan_notification',
        'TreatmentPlanReadyNotification' => 'treatment_plan_ready',
        'ReviewRequest' => 'review_request',
        'CanvasserAccountCreated' => 'canvasser_account_created',
        'NurseAccountCreated' => 'nurse_account_created',
        'CustomerCareAccountCreated' => 'customer_care_account_created',
        'CareGiverAccountCreated' => 'care_giver_account_created',
        'DoctorReassignmentNotification' => 'doctor_reassignment',
        'ReferralNotification' => 'referral_notification',
        'DocumentsForwardedToDoctor' => 'documents_forwarded_to_doctor',
        'SecurityAlert' => 'security_alert',
        'DelayQueryNotification' => 'delay_query_notification',
        'CampaignNotification' => 'campaign_notification',
        'CanvasserConsultationConfirmation' => 'canvasser_consultation_confirmation',
        'VitalSignsReport' => 'vital_signs_report',
        'CustomCommunication' => 'custom_communication',
    ];

    /**
     * Get template for a Mail class
     * 
     * @param string $mailClassName The Mail class name (e.g., 'ConsultationConfirmation')
     * @return CommunicationTemplate|null
     */
    public static function getTemplate(string $mailClassName): ?CommunicationTemplate
    {
        $templateName = self::$templateMappings[$mailClassName] ?? null;
        
        if (!$templateName) {
            Log::warning("No template mapping found for Mail class: {$mailClassName}");
            return null;
        }

        $template = CommunicationTemplate::active()
            ->byChannel('email')
            ->where('name', $templateName)
            ->first();

        if (!$template) {
            Log::warning("Template not found or inactive: {$templateName} for Mail class: {$mailClassName}");
        }

        return $template;
    }

    /**
     * Enrich template data with recipient information
     * Automatically adds name, age, phone, email, gender, etc. if available
     * 
     * @param array $data Original template data
     * @return array Enriched template data
     */
    public static function enrichWithRecipientInfo(array $data): array
    {
        // Extract recipient information from various sources
        $enriched = $data;
        
        // Full name (combine first_name and last_name if not already set)
        if (!isset($enriched['full_name']) || empty($enriched['full_name'])) {
            $firstName = $enriched['first_name'] ?? '';
            $lastName = $enriched['last_name'] ?? '';
            if ($firstName || $lastName) {
                $enriched['full_name'] = trim($firstName . ' ' . $lastName);
            }
        }
        
        // Phone number (check multiple possible fields)
        if (!isset($enriched['phone']) || empty($enriched['phone'])) {
            $enriched['phone'] = $enriched['mobile'] ?? $enriched['phone_number'] ?? $enriched['contact_number'] ?? '';
        }
        
        // Mobile number (alias for phone)
        if (!isset($enriched['mobile']) || empty($enriched['mobile'])) {
            $enriched['mobile'] = $enriched['phone'] ?? '';
        }
        
        // Email
        if (!isset($enriched['email']) || empty($enriched['email'])) {
            $enriched['email'] = $enriched['email_address'] ?? '';
        }
        
        // Age (ensure it's a string for template)
        if (isset($enriched['age'])) {
            $enriched['age'] = (string)$enriched['age'];
        }
        
        // Gender
        if (!isset($enriched['gender']) || empty($enriched['gender'])) {
            $enriched['gender'] = $enriched['sex'] ?? '';
        }
        
        // Format phone number for display (if available)
        if (!empty($enriched['phone'])) {
            $enriched['phone_formatted'] = self::formatPhoneNumber($enriched['phone']);
        }
        
        // Add company information
        $enriched['company_name'] = \App\Models\Setting::get('company_name', 'DoctorOnTap');
        $enriched['company_email'] = \App\Models\Setting::get('company_email', config('mail.from.address'));
        $enriched['company_phone'] = \App\Models\Setting::get('company_phone', '');
        $enriched['company_website'] = \App\Models\Setting::get('company_website', '');
        
        // Add current date/time
        $enriched['current_date'] = now()->format('F d, Y');
        $enriched['current_time'] = now()->format('h:i A');
        $enriched['current_datetime'] = now()->format('F d, Y h:i A');
        
        return $enriched;
    }
    
    /**
     * Format phone number for display
     * 
     * @param string $phone
     * @return string
     */
    private static function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        // Format Nigerian numbers (if starts with 0 or 234)
        if (strlen($cleaned) >= 10) {
            if (strpos($cleaned, '234') === 0 && strlen($cleaned) === 13) {
                // Format: +234 800 000 0000
                return '+' . substr($cleaned, 0, 3) . ' ' . substr($cleaned, 3, 3) . ' ' . substr($cleaned, 6, 3) . ' ' . substr($cleaned, 9);
            } elseif (strpos($cleaned, '0') === 0 && strlen($cleaned) === 11) {
                // Format: 0800 000 0000
                return substr($cleaned, 0, 4) . ' ' . substr($cleaned, 4, 3) . ' ' . substr($cleaned, 7);
            }
        }
        
        return $phone; // Return original if can't format
    }

    /**
     * Render email template with data
     * Automatically enriches data with recipient information
     * 
     * @param string $mailClassName The Mail class name
     * @param array $data Template variables
     * @return array|null ['subject' => string, 'content' => string, 'plain_text' => string] or null if template not found
     */
    public static function render(string $mailClassName, array $data = []): ?array
    {
        $template = self::getTemplate($mailClassName);
        
        if (!$template) {
            return null;
        }

        try {
            // Enrich data with recipient information
            $enrichedData = self::enrichWithRecipientInfo($data);
            
            return $template->render($enrichedData);
        } catch (\Exception $e) {
            Log::error("Failed to render email template: {$template->name}", [
                'error' => $e->getMessage(),
                'mail_class' => $mailClassName,
            ]);
            return null;
        }
    }

    /**
     * Get subject for email
     * 
     * @param string $mailClassName The Mail class name
     * @param array $data Template variables
     * @param string $defaultSubject Fallback subject if template not found
     * @return string
     */
    public static function getSubject(string $mailClassName, array $data = [], string $defaultSubject = ''): string
    {
        $rendered = self::render($mailClassName, $data);
        
        if ($rendered && !empty($rendered['subject'])) {
            return $rendered['subject'];
        }

        return $defaultSubject;
    }

    /**
     * Get content for email
     * 
     * @param string $mailClassName The Mail class name
     * @param array $data Template variables
     * @return string|null HTML content or null if template not found
     */
    public static function getContent(string $mailClassName, array $data = []): ?string
    {
        $rendered = self::render($mailClassName, $data);
        
        return $rendered['content'] ?? null;
    }

    /**
     * Check if template exists for Mail class
     * 
     * @param string $mailClassName The Mail class name
     * @return bool
     */
    public static function hasTemplate(string $mailClassName): bool
    {
        return self::getTemplate($mailClassName) !== null;
    }

    /**
     * Get all template mappings
     * 
     * @return array
     */
    public static function getMappings(): array
    {
        return self::$templateMappings;
    }
}

