<?php

namespace App\Services;

use App\Models\Consultation;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Log;

class NotificationTrackingService
{
    /**
     * Log an email notification
     */
    public function logEmail(
        Consultation $consultation,
        string $category,
        string $recipient,
        string $subject,
        ?string $recipientName = null
    ): NotificationLog {
        return NotificationLog::create([
            'consultation_id' => $consultation->id,
            'consultation_reference' => $consultation->reference,
            'type' => 'email',
            'category' => $category,
            'subject' => $subject,
            'recipient' => $recipient,
            'recipient_name' => $recipientName ?? $consultation->first_name,
            'status' => 'pending',
            'provider' => config('mail.default'),
        ]);
    }

    /**
     * Log an SMS notification
     */
    public function logSms(
        Consultation $consultation,
        string $category,
        string $recipient,
        string $message,
        ?string $recipientName = null
    ): NotificationLog {
        return NotificationLog::create([
            'consultation_id' => $consultation->id,
            'consultation_reference' => $consultation->reference,
            'type' => 'sms',
            'category' => $category,
            'message' => $message,
            'recipient' => $recipient,
            'recipient_name' => $recipientName ?? $consultation->first_name,
            'status' => 'pending',
            'provider' => 'termii',
        ]);
    }

    /**
     * Log a WhatsApp notification
     */
    public function logWhatsApp(
        Consultation $consultation,
        string $category,
        string $recipient,
        string $message,
        ?string $recipientName = null
    ): NotificationLog {
        return NotificationLog::create([
            'consultation_id' => $consultation->id,
            'consultation_reference' => $consultation->reference,
            'type' => 'whatsapp',
            'category' => $category,
            'message' => $message,
            'recipient' => $recipient,
            'recipient_name' => $recipientName ?? $consultation->first_name,
            'status' => 'pending',
            'provider' => 'termii',
        ]);
    }

    /**
     * Update notification status after sending
     */
    public function updateSendStatus(
        NotificationLog $log,
        bool $success,
        ?string $providerMessageId = null,
        ?string $providerResponse = null,
        ?string $errorMessage = null
    ): void {
        if ($success) {
            $log->markAsSent($providerMessageId, $providerResponse);
            
            // Update consultation tracking fields based on category
            $this->updateConsultationTracking($log);
            
            Log::info('Notification sent and logged', [
                'notification_id' => $log->id,
                'type' => $log->type,
                'category' => $log->category,
                'consultation_ref' => $log->consultation_reference,
            ]);
        } else {
            $log->markAsFailed($errorMessage);
            
            // Update failure count on consultation
            $consultation = $log->consultation;
            $consultation->increment('notification_failure_count');
            $consultation->update(['last_notification_attempt' => now()]);
            
            Log::error('Notification failed and logged', [
                'notification_id' => $log->id,
                'type' => $log->type,
                'category' => $log->category,
                'consultation_ref' => $log->consultation_reference,
                'error' => $errorMessage,
            ]);
        }
    }

    /**
     * Update consultation tracking fields
     */
    protected function updateConsultationTracking(NotificationLog $log): void
    {
        $consultation = $log->consultation;
        
        if ($log->category === 'treatment_plan' && $log->type === 'email') {
            $consultation->update([
                'treatment_plan_email_sent' => true,
                'treatment_plan_email_sent_at' => now(),
                'treatment_plan_email_status' => 'sent',
                'last_notification_attempt' => now(),
            ]);
        } elseif ($log->category === 'treatment_plan' && $log->type === 'sms') {
            $consultation->update([
                'treatment_plan_sms_sent' => true,
                'treatment_plan_sms_sent_at' => now(),
                'treatment_plan_sms_status' => 'sent',
                'last_notification_attempt' => now(),
            ]);
        }
    }

    /**
     * Get delivery summary for a consultation
     */
    public function getDeliverySummary(Consultation $consultation): array
    {
        $logs = NotificationLog::forConsultation($consultation->id)->get();
        
        $summary = [
            'total' => $logs->count(),
            'delivered' => $logs->where('status', 'delivered')->count(),
            'sent' => $logs->where('status', 'sent')->count(),
            'failed' => $logs->where('status', 'failed')->count(),
            'pending' => $logs->where('status', 'pending')->count(),
            'by_type' => [
                'email' => $logs->where('type', 'email')->count(),
                'sms' => $logs->where('type', 'sms')->count(),
                'whatsapp' => $logs->where('type', 'whatsapp')->count(),
            ],
            'treatment_plan_notifications' => $logs->where('category', 'treatment_plan')->all(),
            'last_successful' => $logs->where('status', 'delivered')->sortByDesc('delivered_at')->first(),
            'recent_failures' => $logs->where('status', 'failed')->sortByDesc('failed_at')->take(5)->values(),
        ];
        
        return $summary;
    }

    /**
     * Get treatment plan delivery status
     */
    public function getTreatmentPlanDeliveryStatus(Consultation $consultation): array
    {
        $logs = NotificationLog::forConsultation($consultation->id)
            ->byCategory('treatment_plan')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $emailLog = $logs->where('type', 'email')->first();
        $smsLog = $logs->where('type', 'sms')->first();
        
        return [
            'email' => [
                'sent' => $consultation->treatment_plan_email_sent,
                'sent_at' => $consultation->treatment_plan_email_sent_at,
                'status' => $consultation->treatment_plan_email_status,
                'log' => $emailLog,
                'delivered' => $emailLog && $emailLog->status === 'delivered',
            ],
            'sms' => [
                'sent' => $consultation->treatment_plan_sms_sent,
                'sent_at' => $consultation->treatment_plan_sms_sent_at,
                'status' => $consultation->treatment_plan_sms_status,
                'log' => $smsLog,
                'delivered' => $smsLog && $smsLog->status === 'delivered',
            ],
            'any_delivered' => ($emailLog && $emailLog->status === 'delivered') || ($smsLog && $smsLog->status === 'delivered'),
            'all_failed' => $logs->isNotEmpty() && $logs->every(fn($log) => $log->status === 'failed'),
            'attempt_count' => $logs->count(),
            'last_attempt' => $consultation->last_notification_attempt,
        ];
    }

    /**
     * Check if patient has received treatment plan
     */
    public function hasReceivedTreatmentPlan(Consultation $consultation): bool
    {
        $status = $this->getTreatmentPlanDeliveryStatus($consultation);
        return $status['any_delivered'];
    }

    /**
     * Get failed notifications that need retry
     */
    public function getFailedNotificationsForRetry(int $maxRetries = 3): \Illuminate\Database\Eloquent\Collection
    {
        return NotificationLog::failed()
            ->where('retry_count', '<', $maxRetries)
            ->whereNull('last_retry_at')
            ->orWhere('last_retry_at', '<', now()->subHour())
            ->orderBy('created_at', 'desc')
            ->get();
    }
}

