<?php

namespace App\Notifications;

use App\Models\Patient;
use App\Models\VitalSign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent when a caregiver records a vital sign that exceeds
 * critical thresholds.  Dispatched in real-time by VitalsEntry.
 *
 * Channels: mail + database (SMS is handled by EscalationAlertJob).
 */
class CriticalVitalAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected VitalSign $vitalSign,
        protected Patient   $patient,
        protected array     $alerts = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $alertList = implode("\n- ", $this->alerts);

        return (new MailMessage)
            ->subject("⚠ Critical Vital Alert – {$this->patient->name}")
            ->greeting('Critical Alert')
            ->line("A vital-sign reading for **{$this->patient->name}** has exceeded safe thresholds:")
            ->line("- {$alertList}")
            ->line('Recorded at: ' . $this->vitalSign->created_at->format('M d, Y h:i A'))
            ->action('View Patient', url("/care-giver/patients/{$this->patient->id}"))
            ->salutation('— DoctorOnTap Alerts');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'        => 'critical_vital_alert',
            'patient_id'  => $this->patient->id,
            'vital_id'    => $this->vitalSign->id,
            'flag_status' => $this->vitalSign->flag_status,
            'alerts'      => $this->alerts,
        ];
    }
}
