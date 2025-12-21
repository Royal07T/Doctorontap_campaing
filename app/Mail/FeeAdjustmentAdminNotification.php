<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FeeAdjustmentAdminNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $patient;
    public $oldFee;
    public $newFee;
    public $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, Patient $patient, $oldFee, $newFee, $reason)
    {
        $this->booking = $booking;
        $this->patient = $patient;
        $this->oldFee = $oldFee;
        $this->newFee = $newFee;
        $this->reason = $reason;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $difference = $this->newFee - $this->oldFee;

        // Load doctor relationship if it exists
        $doctor = $this->booking->doctor;

        return $this->subject('[AUDIT] Fee Adjustment - ' . $this->booking->reference)
                    ->view('emails.fee-adjustment-admin-notification')
                    ->with([
                        'booking' => $this->booking,
                        'patient' => $this->patient,
                        'oldFee' => number_format($this->oldFee, 2),
                        'newFee' => number_format($this->newFee, 2),
                        'difference' => number_format($difference, 2),
                        'reason' => $this->reason,
                        'doctor' => $doctor, // Can be null if no doctor assigned
                    ]);
    }
}

