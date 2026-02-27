<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Automated follow-up notification sent to leads based on
 * their nurture stage (Day 1 / Day 3 / Day 7).
 */
class LeadFollowUp extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Lead   $lead,
        protected string $stage,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return match ($this->stage) {
            'day1' => $this->day1Mail(),
            'day3' => $this->day3Mail(),
            'day7' => $this->day7Mail(),
            default => $this->day1Mail(),
        };
    }

    protected function day1Mail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to DoctorOnTap Care Services')
            ->greeting("Hello {$this->lead->name},")
            ->line('Thank you for your interest in DoctorOnTap\'s home care services.')
            ->line('We offer three plan tiers — Meridian, Executive, and Sovereign — each designed to provide the right level of care for your loved one.')
            ->action('Learn More', url('/'))
            ->salutation('— DoctorOnTap Team');
    }

    protected function day3Mail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Still Interested? Let\'s Talk Care Plans')
            ->greeting("Hi {$this->lead->name},")
            ->line('We noticed you were exploring our care services a few days ago.')
            ->line('Our care coordinators are available to discuss a plan that fits your budget and needs.')
            ->action('Schedule a Call', url('/'))
            ->salutation('— DoctorOnTap Team');
    }

    protected function day7Mail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Special Offer on Care Plans')
            ->greeting("Hi {$this->lead->name},")
            ->line('We wanted to follow up one more time. We\'re currently offering a complimentary initial assessment for new families.')
            ->line('Don\'t miss out — reach out today to get started.')
            ->action('Get Started', url('/'))
            ->salutation('— DoctorOnTap Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'lead_follow_up',
            'lead_id' => $this->lead->id,
            'stage'   => $this->stage,
        ];
    }
}
