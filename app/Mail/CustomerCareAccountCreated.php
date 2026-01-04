<?php

namespace App\Mail;

use App\Models\CustomerCare;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerCareAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $customerCare;
    public $password;
    public $adminName;

    /**
     * Create a new message instance.
     */
    public function __construct(CustomerCare $customerCare, string $password, string $adminName)
    {
        $this->customerCare = $customerCare;
        $this->password = $password;
        $this->adminName = $adminName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Customer Care Account Has Been Created - DoctorOnTap',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
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
