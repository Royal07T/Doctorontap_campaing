<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $doctor;
    public $campaignDetails;

    /**
     * Create a new message instance.
     */
    public function __construct($doctor, $campaignDetails = [])
    {
        $this->doctor = $doctor;
        $this->campaignDetails = $campaignDetails;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Campaign Starting Soon - DoctorOnTap',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.campaign-notification',
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
