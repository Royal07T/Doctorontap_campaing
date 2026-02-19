<?php

namespace App\Mail;

use App\Models\Consultation;
use App\Models\NotificationLog;
use App\Services\EmailTemplateService;
use App\Services\NotificationTrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\SentMessage;

/** OPTIMIZATION: Implements ShouldQueue to prevent blocking HTTP requests */
class ReviewRequest extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    
    /**
     * The notification log for tracking
     *
     * @var NotificationLog
     */
    protected $notificationLog;
    public $templateContent;
    public $templateSubject;

    /**
     * Create a new message instance.
     */
    public function __construct(public Consultation $consultation)
    {
        // Eager load the doctor relationship
        $this->consultation->load('doctor');
        
        // Create notification log for tracking
        $trackingService = app(NotificationTrackingService::class);
        $this->notificationLog = $trackingService->logEmail(
            $this->consultation,
            'review_request',
            $this->consultation->email,
            'How was your consultation with Dr. ' . $this->consultation->doctor->name . '?',
            $this->consultation->first_name . ' ' . $this->consultation->last_name
        );
        
        // Prepare template data with comprehensive recipient information
        $templateData = [
            // Recipient Information
            'first_name' => $this->consultation->first_name ?? '',
            'last_name' => $this->consultation->last_name ?? '',
            'full_name' => ($this->consultation->first_name ?? '') . ' ' . ($this->consultation->last_name ?? ''),
            'email' => $this->consultation->email ?? '',
            'phone' => $this->consultation->mobile ?? '',
            'mobile' => $this->consultation->mobile ?? '',
            'age' => isset($this->consultation->age) ? (string)$this->consultation->age : '',
            'gender' => $this->consultation->gender ?? '',
            
            // Consultation Information
            'reference' => $this->consultation->reference ?? '',
            'review_link' => route('consultation.review', $this->consultation->id),
            
            // Doctor Information
            'doctor_name' => $this->consultation->doctor->name ?? '',
            'doctor_specialization' => $this->consultation->doctor->specialization ?? '',
        ];

        // Try to get template from CommunicationTemplate system
        $rendered = EmailTemplateService::render('ReviewRequest', $templateData);
        
        if ($rendered) {
            $this->templateContent = $rendered['content'];
            $this->templateSubject = $rendered['subject'];
        } else {
            // Fallback to default view if template not found
            $this->templateContent = null;
            $this->templateSubject = 'How was your consultation with Dr. ' . $this->consultation->doctor->name . '?';
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->templateSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // If template content is available, use it; otherwise fallback to view
        if ($this->templateContent) {
            return new Content(
                htmlString: $this->templateContent,
            );
        }

        return new Content(
            view: 'emails.review-request',
        );
    }

    /**
     * Handle the message being sent.
     */
    public function sent(SentMessage $sent)
    {
        // Update notification log as sent
        if ($this->notificationLog) {
            $trackingService = app(NotificationTrackingService::class);
            $trackingService->updateSendStatus(
                $this->notificationLog,
                true,
                $sent->getMessageId(),
                'Email sent successfully via ' . config('mail.default')
            );
        }
    }
    
    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        \Log::error('ReviewRequest email failed after all retries', [
            'consultation_id' => $this->consultation->id ?? 'N/A',
            'consultation_reference' => $this->consultation->reference ?? 'N/A',
            'patient_email' => $this->consultation->email ?? 'N/A',
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
        
        // Update notification log as failed
        if ($this->notificationLog) {
            $trackingService = app(NotificationTrackingService::class);
            $trackingService->updateSendStatus(
                $this->notificationLog,
                false,
                null,
                null,
                $exception->getMessage()
            );
        }
    }
}
