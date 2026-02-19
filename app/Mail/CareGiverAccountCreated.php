<?php

namespace App\Mail;

use App\Models\CareGiver;
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
class CareGiverAccountCreated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $careGiver;
    public $password;
    public $adminName;
    public $templateContent;
    public $templateSubject;

    /**
     * Create a new message instance.
     */
    public function __construct(CareGiver $careGiver, string $password, string $adminName)
    {
        $this->careGiver = $careGiver;
        $this->password = $password;
        $this->adminName = $adminName;
        
        // Prepare template data
        $templateData = [
            'name' => $careGiver->name ?? '',
            'email' => $careGiver->email ?? '',
            'password' => $password,
            'admin_name' => $adminName,
            'login_link' => route('care-giver.login'),
        ];

        // Try to get template from CommunicationTemplate system
        $rendered = EmailTemplateService::render('CareGiverAccountCreated', $templateData);
        
        if ($rendered) {
            $this->templateContent = $rendered['content'];
            $this->templateSubject = $rendered['subject'];
        } else {
            // Fallback to default view if template not found
            $this->templateContent = null;
            $this->templateSubject = 'Your Care Giver Account Has Been Created - DoctorOnTap';
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
            view: 'emails.care-giver-account-created',
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
