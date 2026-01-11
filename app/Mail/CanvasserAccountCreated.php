<?php

namespace App\Mail;

use App\Models\Canvasser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * OPTIMIZATION: Implements ShouldQueue to prevent blocking HTTP requests
 */
class CanvasserAccountCreated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $canvasser;
    public $password;
    public $adminName;

    /**
     * Create a new message instance.
     */
    public function __construct(Canvasser $canvasser, string $password, string $adminName)
    {
        $this->canvasser = $canvasser;
        $this->password = $password;
        $this->adminName = $adminName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Canvasser Account Has Been Created - DoctorOnTap',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.canvasser-account-created',
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
