<?php

namespace App\Mail;

use App\Models\Consultation;
use App\Models\NotificationLog;
use App\Services\NotificationTrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\SentMessage;

class ReviewRequest extends Mailable
{
    use Queueable, SerializesModels;
    
    /**
     * The notification log for tracking
     *
     * @var NotificationLog
     */
    protected $notificationLog;

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
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'How was your consultation with Dr. ' . $this->consultation->doctor->name . '?',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
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
