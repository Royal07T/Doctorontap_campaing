<?php

namespace App\Mail;

use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** OPTIMIZATION: Implements ShouldQueue to prevent blocking HTTP requests */
class ConsultationConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $data;
    public $templateContent;
    public $templateSubject;
    

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
        
        // Prepare template data with comprehensive recipient information
        $templateData = [
            // Recipient Information
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'full_name' => ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''),
            'email' => $data['email'] ?? '',
            'phone' => $data['mobile'] ?? $data['phone'] ?? '',
            'mobile' => $data['mobile'] ?? $data['phone'] ?? '',
            'age' => isset($data['age']) ? (string)$data['age'] : '',
            'gender' => $data['gender'] ?? '',
            
            // Consultation Information
            'reference' => $data['consultation_reference'] ?? $data['reference'] ?? '',
            'consultation_type' => $data['consultation_type'] ?? 'Consultation',
            'scheduled_date' => $data['scheduled_date'] ?? $data['scheduled_at'] ?? '',
            'scheduled_time' => $data['scheduled_time'] ?? '',
            'scheduled_datetime' => $data['scheduled_at'] ?? '',
            'problem' => $data['problem'] ?? '',
            'severity' => $data['severity'] ?? '',
            
            // Doctor Information
            'doctor_name' => $data['doctor_name'] ?? '',
            'doctor_specialization' => $data['doctor_specialization'] ?? '',
        ];

        // Try to get template from CommunicationTemplate system
        $rendered = EmailTemplateService::render('ConsultationConfirmation', $templateData);
        
        if ($rendered) {
            $this->templateContent = $rendered['content'];
            $this->templateSubject = $rendered['subject'];
        } else {
            // Fallback to default view if template not found
            $this->templateContent = null;
            $this->templateSubject = 'Consultation Request Confirmation - DoctorOnTap';
        }
    }
    

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->templateSubject,
            replyTo: config('mail.admin_email'),
            from: config('mail.from.address'),
            tags: ['consultation', 'confirmation'],
            metadata: [
                'consultation_reference' => $this->data['consultation_reference'] ?? '',
            ],
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
            view: 'emails.consultation-confirmation',
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
        \Log::error('ConsultationConfirmation email failed after all retries', [
            'consultation_reference' => $this->data['consultation_reference'] ?? 'N/A',
            'patient_email' => $this->data['email'] ?? 'N/A',
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
