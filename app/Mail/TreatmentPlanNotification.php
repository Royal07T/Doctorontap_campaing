<?php

namespace App\Mail;

use App\Models\Consultation;
use App\Models\NotificationLog;
use App\Services\NotificationTrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\SentMessage;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * OPTIMIZATION: Implements ShouldQueue to prevent blocking HTTP requests
 * Email will be sent asynchronously via queue, improving response times
 */
class TreatmentPlanNotification extends Mailable implements ShouldQueue
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
        // Eager load the doctor relationship to ensure it's available when the job is processed
        $this->consultation->load('doctor');
        
        // Create notification log for tracking
        $trackingService = app(NotificationTrackingService::class);
        $this->notificationLog = $trackingService->logEmail(
            $this->consultation,
            'treatment_plan',
            $this->consultation->email,
            'Your Treatment Plan is Ready - ' . $this->consultation->reference,
            $this->consultation->first_name . ' ' . $this->consultation->last_name
        );
    }
    

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Treatment Plan is Ready - ' . $this->consultation->reference,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.treatment-plan-notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        
        try {
            // Generate patient-friendly PDF (without clinical documentation)
            // Only includes: Treatment Plan, Medications, Follow-up, Lifestyle, Appointments
            $pdf = Pdf::loadView('pdfs.treatment-plan-patient', [
                'consultation' => $this->consultation
            ]);
            
            $attachments[] = Attachment::fromData(fn () => $pdf->output(), 'treatment-plan-' . $this->consultation->reference . '.pdf')
                ->withMime('application/pdf');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to generate treatment plan PDF', [
                'consultation_id' => $this->consultation->id,
                'reference' => $this->consultation->reference,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw - continue with file attachments even if PDF fails
        }
        
        // Add treatment plan file attachments
        if ($this->consultation->treatment_plan_attachments && count($this->consultation->treatment_plan_attachments) > 0) {
            foreach ($this->consultation->treatment_plan_attachments as $attachment) {
                try {
                    $filePath = storage_path('app/' . $attachment['path']);
                    if (file_exists($filePath)) {
                        $attachments[] = Attachment::fromPath($filePath)
                            ->as($attachment['original_name'] ?? basename($attachment['path']))
                            ->withMime($attachment['mime_type'] ?? 'application/octet-stream');
                    } else {
                        \Illuminate\Support\Facades\Log::warning('Treatment plan attachment file not found', [
                            'consultation_id' => $this->consultation->id,
                            'path' => $attachment['path'] ?? 'N/A',
                        ]);
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to attach treatment plan file', [
                        'consultation_id' => $this->consultation->id,
                        'attachment' => $attachment,
                        'error' => $e->getMessage(),
                    ]);
                    // Continue with other attachments
                }
            }
        }
        
        return $attachments;
    }
    
    /**
     * Handle the message being sent.
     *
     * @param  \Illuminate\Mail\SentMessage  $sent
     * @return void
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
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        \Log::error('TreatmentPlanNotification email failed after all retries', [
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