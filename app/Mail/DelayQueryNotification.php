<?php

namespace App\Mail;

use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * OPTIMIZATION: Implements ShouldQueue to prevent blocking HTTP requests
 */
class DelayQueryNotification extends Mailable implements ShouldQueue
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
            // Doctor Information
            'doctor_name' => $data['doctor_name'] ?? '',
            
            // Consultation Information
            'reference' => $data['consultation_reference'] ?? $data['reference'] ?? '',
            'query_message' => $data['query_message'] ?? $data['message'] ?? 'Please respond regarding the delay',
            'response_link' => $data['response_link'] ?? route('doctor.consultations'),
            
            // Patient Information
            'patient_name' => ($data['patient_name'] ?? '') ?: (($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')),
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'full_name' => ($data['patient_name'] ?? '') ?: (($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')),
            'email' => $data['email'] ?? '',
            'phone' => $data['mobile'] ?? $data['phone'] ?? '',
            'mobile' => $data['mobile'] ?? $data['phone'] ?? '',
            'age' => isset($data['age']) ? (string)$data['age'] : '',
            'gender' => $data['gender'] ?? '',
        ];

        // Try to get template from CommunicationTemplate system
        $rendered = EmailTemplateService::render('DelayQueryNotification', $templateData);
        
        if ($rendered) {
            $this->templateContent = $rendered['content'];
            $this->templateSubject = $rendered['subject'];
        } else {
            // Fallback to default view if template not found
            $this->templateContent = null;
            $this->templateSubject = 'URGENT: You are late for Appointment - DoctorOnTap';
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
            tags: ['urgent', 'delay-query', 'doctor-notification'],
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
            view: 'emails.delay-query-notification',
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
}

