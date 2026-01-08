<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MenstrualCycle;
use App\Models\Patient;
use App\Helpers\SmsServiceHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendFertilityNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fertility:notify {--days-before=1 : Number of days before fertile window to send notification}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send fertility window notifications to spouses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysBefore = (int) $this->option('days-before');
        $this->info("Checking for fertility windows starting in {$daysBefore} day(s)...");

        $smsService = SmsServiceHelper::getService();
        $notificationsSent = 0;
        $notificationsFailed = 0;

        // Get all female patients with menstrual cycles that have spouse numbers
        $patients = Patient::where('gender', 'female')
            ->whereHas('menstrualCycles', function ($query) {
                $query->whereNotNull('spouse_number')
                      ->where('spouse_number', '!=', '');
            })
            ->with(['menstrualCycles' => function ($query) {
                $query->whereNotNull('spouse_number')
                      ->where('spouse_number', '!=', '')
                      ->orderBy('start_date', 'desc');
            }])
            ->get();

        foreach ($patients as $patient) {
            $menstrualCycles = $patient->menstrualCycles;
            
            if ($menstrualCycles->isEmpty()) {
                continue;
            }

            // Get the latest cycle
            $latestCycle = $menstrualCycles->first();
            $spouseNumber = $latestCycle->spouse_number;

            if (empty($spouseNumber)) {
                continue;
            }

            // Calculate fertility window based on the latest cycle
            $fertileWindowStart = $this->calculateFertileWindowStart($patient, $menstrualCycles);
            
            if (!$fertileWindowStart) {
                continue;
            }

            // Check if we should send notification today
            $today = Carbon::today();
            $notificationDate = $fertileWindowStart->copy()->subDays($daysBefore);

            if ($today->equalTo($notificationDate)) {
                // Send notification
                $message = $this->buildFertilityMessage($patient, $fertileWindowStart);
                
                try {
                    $result = $smsService->sendSMS($spouseNumber, $message);
                    
                    if ($result['success'] ?? false) {
                        $notificationsSent++;
                        $this->info("✓ Sent fertility notification to {$spouseNumber} for patient {$patient->name}");
                        Log::info('Fertility notification sent', [
                            'patient_id' => $patient->id,
                            'spouse_number' => $spouseNumber,
                            'fertile_window_start' => $fertileWindowStart->format('Y-m-d'),
                        ]);
                    } else {
                        $notificationsFailed++;
                        $this->error("✗ Failed to send notification to {$spouseNumber}: " . ($result['message'] ?? 'Unknown error'));
                        Log::error('Fertility notification failed', [
                            'patient_id' => $patient->id,
                            'spouse_number' => $spouseNumber,
                            'error' => $result['message'] ?? 'Unknown error',
                        ]);
                    }
                } catch (\Exception $e) {
                    $notificationsFailed++;
                    $this->error("✗ Exception sending notification to {$spouseNumber}: " . $e->getMessage());
                    Log::error('Fertility notification exception', [
                        'patient_id' => $patient->id,
                        'spouse_number' => $spouseNumber,
                        'exception' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info("\n=== Summary ===");
        $this->info("Notifications sent: {$notificationsSent}");
        $this->info("Notifications failed: {$notificationsFailed}");
        $this->info("Total patients checked: " . $patients->count());

        return Command::SUCCESS;
    }

    /**
     * Calculate the fertile window start date
     */
    private function calculateFertileWindowStart(Patient $patient, $menstrualCycles): ?Carbon
    {
        if ($menstrualCycles->isEmpty()) {
            return null;
        }

        $latestCycle = $menstrualCycles->first();
        $lastPeriodStart = $latestCycle->start_date;

        // Calculate average cycle length
        $averageCycleLength = $this->calculateAverageCycleLength($menstrualCycles);
        
        if ($averageCycleLength <= 0) {
            $averageCycleLength = 28; // Default cycle length
        }

        // Calculate next period start
        $daysSinceLastPeriod = $lastPeriodStart->diffInDays(Carbon::today());
        
        if ($daysSinceLastPeriod < $averageCycleLength) {
            // Next period hasn't started yet
            $nextPeriodStart = $lastPeriodStart->copy()->addDays($averageCycleLength);
        } else {
            // Calculate how many cycles have passed
            $cyclesSinceLastPeriod = floor($daysSinceLastPeriod / $averageCycleLength);
            $nextPeriodStart = $lastPeriodStart->copy()->addDays($averageCycleLength * ($cyclesSinceLastPeriod + 1));
        }

        // Ovulation typically occurs 14 days before next period
        $nextOvulation = $nextPeriodStart->copy()->subDays(14);
        
        // Fertile window: 5 days before ovulation to 1 day after
        $fertileWindowStart = $nextOvulation->copy()->subDays(5);

        return $fertileWindowStart;
    }

    /**
     * Calculate average cycle length
     */
    private function calculateAverageCycleLength($menstrualCycles): float
    {
        if ($menstrualCycles->count() < 2) {
            return 28; // Default
        }

        $cycleLengths = [];
        $sortedCycles = $menstrualCycles->sortBy('start_date')->values();

        for ($i = 0; $i < $sortedCycles->count() - 1; $i++) {
            $current = $sortedCycles[$i];
            $previous = $sortedCycles[$i + 1];
            
            if ($current->start_date && $previous->start_date) {
                $cycleLengths[] = $current->start_date->diffInDays($previous->start_date);
            }
        }

        if (empty($cycleLengths)) {
            return 28;
        }

        return round(array_sum($cycleLengths) / count($cycleLengths));
    }

    /**
     * Build the fertility notification message
     */
    private function buildFertilityMessage(Patient $patient, Carbon $fertileWindowStart): string
{
    $fertileWindowEnd = $fertileWindowStart->copy()->addDays(6);
    $ovulationDay = $fertileWindowStart->copy()->addDays(5);

    $message  = "Hello! This is a fertility reminder from DoctorOnTap.\n\n";
    $message .= "Your partner’s fertile window is coming up.\n\n";
    $message .= "🗓 Fertile Window:\n";
    $message .= $fertileWindowStart->format('F j, Y') . " – " . $fertileWindowEnd->format('F j, Y') . "\n\n";
    $message .= "⭐ Most Fertile Day (Ovulation):\n";
    $message .= $ovulationDay->format('F j, Y') . "\n\n";
    $message .= "💡 Why this period matters:\n";
    $message .= "• Sperm can live up to 5 days\n";
    $message .= "• The egg is fertile for about 24 hours\n";
    $message .= "• Chances are highest just before and on ovulation day\n\n";
    $message .= "This window offers the best chance for conception.\n\n";
    $message .= "DoctorOnTap — Supporting your family’s journey 💙";

    return $message;
}
}

