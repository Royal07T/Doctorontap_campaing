<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomerCare;
use App\Models\Patient;
use App\Models\CustomerInteraction;
use App\Models\SupportTicket;
use App\Models\Escalation;
use App\Models\InteractionNote;
use App\Models\AdminUser;
use App\Models\Doctor;
use Illuminate\Support\Facades\DB;

class CustomerCareModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing customer care agents
        $agents = CustomerCare::where('is_active', true)->get();
        
        if ($agents->isEmpty()) {
            $this->command->warn('No active customer care agents found. Please create customer care agents first.');
            return;
        }

        // Get existing patients
        $patients = Patient::take(20)->get();
        
        if ($patients->isEmpty()) {
            $this->command->warn('No patients found. Please seed patients first.');
            return;
        }

        // Get admin and doctor for escalations
        $admin = AdminUser::first();
        $doctor = Doctor::first();

        // Create customer interactions
        $this->command->info('Creating customer interactions...');
        $interactions = [];
        for ($i = 0; $i < 30; $i++) {
            $startedAt = now()->subDays(rand(0, 30))->subHours(rand(0, 23));
            $endedAt = $startedAt->copy()->addMinutes(rand(5, 60));
            $duration = $endedAt->diffInSeconds($startedAt);

            $interaction = CustomerInteraction::create([
                'user_id' => $patients->random()->id,
                'agent_id' => $agents->random()->id,
                'channel' => collect(['chat', 'call', 'email'])->random(),
                'summary' => $this->generateSummary(),
                'status' => collect(['active', 'resolved', 'pending'])->random(),
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'duration_seconds' => $duration,
            ]);

            $interactions[] = $interaction;

            // Add notes to some interactions
            if (rand(0, 1)) {
                InteractionNote::create([
                    'customer_interaction_id' => $interaction->id,
                    'created_by' => $interaction->agent_id,
                    'note' => $this->generateNote(),
                    'is_internal' => true,
                ]);
            }
        }

        // Create support tickets
        $this->command->info('Creating support tickets...');
        $tickets = [];
        for ($i = 0; $i < 25; $i++) {
            $ticket = SupportTicket::create([
                'user_id' => $patients->random()->id,
                'agent_id' => $agents->random()->id,
                'category' => collect(['billing', 'appointment', 'technical', 'medical'])->random(),
                'subject' => $this->generateTicketSubject(),
                'description' => $this->generateTicketDescription(),
                'status' => collect(['open', 'pending', 'resolved', 'escalated'])->random(),
                'priority' => collect(['low', 'medium', 'high', 'urgent'])->random(),
                'resolved_at' => collect(['open', 'pending', 'escalated'])->contains(collect(['open', 'pending', 'resolved', 'escalated'])->random()) 
                    ? null 
                    : now()->subDays(rand(0, 10)),
                'resolved_by' => collect(['open', 'pending', 'escalated'])->contains(collect(['open', 'pending', 'resolved', 'escalated'])->random())
                    ? null
                    : $agents->random()->id,
            ]);

            $tickets[] = $ticket;
        }

        // Create escalations
        $this->command->info('Creating escalations...');
        foreach ($tickets as $ticket) {
            if ($ticket->status === 'escalated' && rand(0, 1)) {
                Escalation::create([
                    'support_ticket_id' => $ticket->id,
                    'escalated_by' => $ticket->agent_id,
                    'escalated_to_type' => collect(['admin', 'doctor'])->random(),
                    'escalated_to_id' => collect(['admin', 'doctor'])->random() === 'admin' 
                        ? ($admin ? $admin->id : null)
                        : ($doctor ? $doctor->id : null),
                    'reason' => $this->generateEscalationReason(),
                    'status' => collect(['pending', 'in_progress', 'resolved'])->random(),
                    'outcome' => collect(['pending', 'in_progress'])->contains(collect(['pending', 'in_progress', 'resolved'])->random())
                        ? null
                        : $this->generateOutcome(),
                    'resolved_at' => collect(['pending', 'in_progress'])->contains(collect(['pending', 'in_progress', 'resolved'])->random())
                        ? null
                        : now()->subDays(rand(0, 5)),
                ]);
            }
        }

        // Escalate some interactions
        foreach ($interactions as $interaction) {
            if (rand(0, 4) === 0) { // 20% chance
                Escalation::create([
                    'customer_interaction_id' => $interaction->id,
                    'escalated_by' => $interaction->agent_id,
                    'escalated_to_type' => collect(['admin', 'doctor'])->random(),
                    'escalated_to_id' => collect(['admin', 'doctor'])->random() === 'admin'
                        ? ($admin ? $admin->id : null)
                        : ($doctor ? $doctor->id : null),
                    'reason' => $this->generateEscalationReason(),
                    'status' => collect(['pending', 'in_progress', 'resolved'])->random(),
                    'outcome' => collect(['pending', 'in_progress'])->contains(collect(['pending', 'in_progress', 'resolved'])->random())
                        ? null
                        : $this->generateOutcome(),
                    'resolved_at' => collect(['pending', 'in_progress'])->contains(collect(['pending', 'in_progress', 'resolved'])->random())
                        ? null
                        : now()->subDays(rand(0, 5)),
                ]);
            }
        }

        $this->command->info('Customer Care Module seeded successfully!');
    }

    private function generateSummary(): string
    {
        $summaries = [
            'Customer inquiry about appointment scheduling',
            'Billing question regarding payment method',
            'Technical issue with accessing treatment plan',
            'Medical question about prescription',
            'Complaint about service quality',
            'Request for consultation rescheduling',
            'Question about insurance coverage',
            'Issue with account access',
            'Inquiry about doctor availability',
            'Request for medical records',
        ];
        return collect($summaries)->random();
    }

    private function generateNote(): string
    {
        $notes = [
            'Customer was very cooperative and understanding.',
            'Issue resolved after explaining the process.',
            'Customer requested follow-up call.',
            'Escalated to supervisor for review.',
            'Customer satisfied with resolution.',
            'Pending additional information from customer.',
        ];
        return collect($notes)->random();
    }

    private function generateTicketSubject(): string
    {
        $subjects = [
            'Payment Processing Issue',
            'Appointment Cancellation Request',
            'Cannot Access Treatment Plan',
            'Billing Discrepancy',
            'Technical Support Needed',
            'Medical Question',
            'Account Access Problem',
            'Service Complaint',
        ];
        return collect($subjects)->random();
    }

    private function generateTicketDescription(): string
    {
        $descriptions = [
            'I am unable to process my payment. The system keeps showing an error message.',
            'I need to cancel my upcoming appointment due to unforeseen circumstances.',
            'I cannot access my treatment plan document. Please help.',
            'There is a discrepancy in my billing statement. I was charged twice.',
            'I am experiencing technical difficulties with the website.',
            'I have a medical question about my prescription.',
            'I cannot log into my account. Password reset is not working.',
            'I am not satisfied with the service I received.',
        ];
        return collect($descriptions)->random();
    }

    private function generateEscalationReason(): string
    {
        $reasons = [
            'Complex billing issue requiring administrative approval',
            'Medical question beyond customer care scope',
            'Technical issue requiring developer intervention',
            'Customer complaint requiring management review',
            'Urgent matter requiring immediate attention',
        ];
        return collect($reasons)->random();
    }

    private function generateOutcome(): string
    {
        $outcomes = [
            'Issue resolved after administrative review',
            'Medical question answered by doctor',
            'Technical issue fixed by development team',
            'Customer complaint addressed by management',
            'Escalation resolved with customer satisfaction',
        ];
        return collect($outcomes)->random();
    }
}
