<?php

namespace App\Mail;

use App\Models\CareGiver;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CareGiverAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $careGiver;
    public $password;
    public $adminName;

    /**
     * Create a new message instance.
     */
    public function __construct(CareGiver $careGiver, string $password, string $adminName)
    {
        $this->careGiver = $careGiver;
        $this->password = $password;
        $this->adminName = $adminName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Care Giver Account Has Been Created - DoctorOnTap',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
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
