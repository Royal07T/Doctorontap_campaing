<?php

namespace App\Mail;

use App\Models\Consultation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\ThrottlesExceptions;

class TreatmentPlanReadyNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;
    
    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = [60, 180, 300]; // 1 min, 3 min, 5 min
    
    /**
     * The maximum number of seconds the job can run.
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * Create a new message instance.
     */
    public function __construct(public Consultation $consultation)
    {
        // Eager load the doctor relationship to ensure it's available when the job is processed
        $this->consultation->load('doctor');
    }
    
    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [
            new ThrottlesExceptions(5, 10), // Allow 5 exceptions per 10 minutes
        ];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Treatment Plan is Ready - Payment Required - ' . $this->consultation->reference,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.treatment-plan-ready',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
    
    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        \Log::error('TreatmentPlanReadyNotification email failed after all retries', [
            'consultation_id' => $this->consultation->id ?? 'N/A',
            'consultation_reference' => $this->consultation->reference ?? 'N/A',
            'patient_email' => $this->consultation->email ?? 'N/A',
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
