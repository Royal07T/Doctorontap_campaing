<?php

namespace App\Mail;

use App\Models\Consultation;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentFailedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $consultation;
    public $payment;
    public $failureReason;

    /**
     * Create a new message instance.
     */
    public function __construct(Consultation $consultation, Payment $payment, string $failureReason = '')
    {
        $this->consultation = $consultation;
        $this->payment = $payment;
        $this->failureReason = $failureReason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Unsuccessful - DoctorOnTap',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-failed',
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

