<?php

namespace App\Mail;

use App\Models\Consultation;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * OPTIMIZATION: Implements ShouldQueue to prevent blocking HTTP requests
 * Email will be sent asynchronously via queue, improving response times
 */
class ConsultationReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $consultation;
    public $templateContent;
    public $templateSubject;

    /**
     * Create a new message instance.
     */
    public function __construct(Consultation $consultation)
    {
        $this->consultation = $consultation;
        
        // Prepare template data with comprehensive recipient information
        $templateData = [
            // Recipient Information
            'first_name' => $consultation->first_name ?? '',
            'last_name' => $consultation->last_name ?? '',
            'full_name' => ($consultation->first_name ?? '') . ' ' . ($consultation->last_name ?? ''),
            'email' => $consultation->email ?? '',
            'phone' => $consultation->mobile ?? '',
            'mobile' => $consultation->mobile ?? '',
            'age' => isset($consultation->age) ? (string)$consultation->age : '',
            'gender' => $consultation->gender ?? '',
            
            // Consultation Information
            'reference' => $consultation->reference ?? '',
            'scheduled_date' => $consultation->scheduled_at ? $consultation->scheduled_at->format('Y-m-d') : '',
            'scheduled_time' => $consultation->scheduled_at ? $consultation->scheduled_at->format('H:i') : '',
            'scheduled_datetime' => $consultation->scheduled_at ? $consultation->scheduled_at->format('F d, Y h:i A') : '',
            'doctor_name' => $consultation->doctor->name ?? '',
            'doctor_specialization' => $consultation->doctor->specialization ?? '',
        ];

        // Try to get template from CommunicationTemplate system
        $rendered = EmailTemplateService::render('ConsultationReminder', $templateData);
        
        if ($rendered) {
            $this->templateContent = $rendered['content'];
            $this->templateSubject = $rendered['subject'];
        } else {
            // Fallback to default view if template not found
            $this->templateContent = null;
            $this->templateSubject = 'Reminder: Your Consultation with DoctorOnTap - ' . $this->consultation->reference;
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
            tags: ['consultation', 'reminder'],
            metadata: [
                'consultation_reference' => $this->consultation->reference,
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
            view: 'emails.consultation-reminder',
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

