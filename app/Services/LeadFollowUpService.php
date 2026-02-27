<?php

namespace App\Services;

use App\Models\Lead;
use App\Notifications\LeadFollowUp;
use App\Services\VonageService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class LeadFollowUpService
{
    protected VonageService $vonage;

    public function __construct(VonageService $vonage)
    {
        $this->vonage = $vonage;
    }

    /**
     * Process all leads that are due for follow-up.
     */
    public function processAll(): array
    {
        $results = ['processed' => 0, 'errors' => 0, 'skipped' => 0];

        Lead::dueForFollowUp()->chunk(50, function ($leads) use (&$results) {
            foreach ($leads as $lead) {
                try {
                    $this->processLead($lead);
                    $results['processed']++;
                } catch (\Exception $e) {
                    Log::error('LeadFollowUp failed', [
                        'lead_id' => $lead->id,
                        'error'   => $e->getMessage(),
                    ]);
                    $results['errors']++;
                }
            }
        });

        return $results;
    }

    /**
     * Process a single lead follow-up.
     */
    public function processLead(Lead $lead): void
    {
        if (!$lead->isDueForFollowUp()) {
            return;
        }

        $channel = $lead->getFollowUpChannel();
        $stage   = $lead->followup_stage;

        switch ($channel) {
            case 'whatsapp':
                $this->sendWhatsApp($lead, $stage);
                break;

            case 'email':
                $this->sendEmail($lead, $stage);
                break;

            case 'sms':
                $this->sendSms($lead, $stage);
                break;
        }

        // Advance the lead to the next stage
        $lead->advanceStage();
    }

    /**
     * Send WhatsApp follow-up (Day 1).
     */
    protected function sendWhatsApp(Lead $lead, string $stage): void
    {
        if (!$lead->phone) {
            Log::warning('Lead has no phone for WhatsApp', ['lead_id' => $lead->id]);
            return;
        }

        $message = $this->buildMessage($lead, $stage);

        try {
            $this->vonage->sendWhatsAppMessage($lead->phone, $message);

            Log::info('WhatsApp follow-up sent', [
                'lead_id' => $lead->id,
                'stage'   => $stage,
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp follow-up failed', [
                'lead_id' => $lead->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send Email follow-up (Day 3).
     */
    protected function sendEmail(Lead $lead, string $stage): void
    {
        if (!$lead->email) {
            Log::warning('Lead has no email', ['lead_id' => $lead->id]);
            return;
        }

        Notification::route('mail', $lead->email)
            ->notify(new LeadFollowUp($lead, $stage));

        Log::info('Email follow-up sent', [
            'lead_id' => $lead->id,
            'stage'   => $stage,
        ]);
    }

    /**
     * Send SMS follow-up (Day 7).
     */
    protected function sendSms(Lead $lead, string $stage): void
    {
        if (!$lead->phone) {
            Log::warning('Lead has no phone for SMS', ['lead_id' => $lead->id]);
            return;
        }

        $message = $this->buildMessage($lead, $stage);

        try {
            $this->vonage->sendSMS($lead->phone, $message);

            Log::info('SMS follow-up sent', [
                'lead_id' => $lead->id,
                'stage'   => $stage,
            ]);
        } catch (\Exception $e) {
            Log::error('SMS follow-up failed', [
                'lead_id' => $lead->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Build a channel-appropriate message based on stage.
     */
    protected function buildMessage(Lead $lead, string $stage): string
    {
        $name = $lead->name ?? 'there';

        return match ($stage) {
            Lead::STAGE_NEW => "Hi {$name}! 👋 Thank you for your interest in DoctorOnTap's home healthcare services. Our caregivers provide compassionate, professional care right at your doorstep. Would you like to learn more? Reply YES to get started.",

            Lead::STAGE_DAY1 => "Hello {$name}, just following up on your interest in DoctorOnTap. We offer Executive, Sovereign, and Standard care plans tailored to your needs. Ready to schedule a consultation? Call us or reply to this message.",

            Lead::STAGE_DAY3 => "Hi {$name}, this is DoctorOnTap. We noticed you haven't signed up yet. For a limited time, get 10% off your first month. Don't miss out on quality home healthcare! Visit doctorontap.com or call us today.",

            default => "Hello {$name}, DoctorOnTap is here to help with your home healthcare needs. Contact us anytime!",
        };
    }
}
