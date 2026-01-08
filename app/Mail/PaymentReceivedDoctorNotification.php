<?php

namespace App\Mail;

use App\Models\Consultation;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceivedDoctorNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $consultation;
    public $payment;

    /**
     * Create a new message instance.
     */
    public function __construct(Consultation $consultation, Payment $payment)
    {
        $this->consultation = $consultation;
        $this->payment = $payment;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Received - Consultation ' . $this->consultation->reference,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-received-doctor',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
