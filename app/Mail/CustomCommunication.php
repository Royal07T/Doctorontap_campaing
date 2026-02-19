<?php

namespace App\Mail;

use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomCommunication extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $content;
    public $customSubject;
    public $templateContent;
    public $templateSubject;
    public $recipientData;

    /**
     * Create a new message instance.
     */
    public function __construct($content, $customSubject = 'Message from DoctorOnTap Support', $recipientData = [])
    {
        $this->content = $content;
        $this->customSubject = $customSubject;
        $this->recipientData = $recipientData;
        
        // Prepare template data with comprehensive recipient information
        $templateData = array_merge([
            'message' => $content,
            'subject' => $customSubject,
        ], $recipientData);

        // Try to get template from CommunicationTemplate system
        $rendered = EmailTemplateService::render('CustomCommunication', $templateData);
        
        if ($rendered) {
            $this->templateContent = $rendered['content'];
            $this->templateSubject = $rendered['subject'];
        } else {
            // Fallback to default view if template not found
            $this->templateContent = null;
            $this->templateSubject = $customSubject;
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
            markdown: 'emails.custom-communication',
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
