<?php

namespace App\Mail;

use App\Models\CustomerCare;
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
class CustomerCareAccountCreated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $customerCare;
    public $password;
    public $adminName;
    public $templateContent;
    public $templateSubject;

    /**
     * Create a new message instance.
     */
    public function __construct(CustomerCare $customerCare, string $password, string $adminName)
    {
        $this->customerCare = $customerCare;
        $this->password = $password;
        $this->adminName = $adminName;
        
        // Prepare template data
        $templateData = [
            'name' => $customerCare->name ?? '',
            'email' => $customerCare->email ?? '',
            'password' => $password,
            'admin_name' => $adminName,
            'login_link' => route('customer-care.login'),
        ];

        // Try to get template from CommunicationTemplate system
        $rendered = EmailTemplateService::render('CustomerCareAccountCreated', $templateData);
        
        if ($rendered) {
            $this->templateContent = $rendered['content'];
            $this->templateSubject = $rendered['subject'];
        } else {
            // Fallback to default view if template not found
            $this->templateContent = null;
            $this->templateSubject = 'Your Customer Care Account Has Been Created - DoctorOnTap';
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
            view: 'emails.customer-care-account-created',
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
