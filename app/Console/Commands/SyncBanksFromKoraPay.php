<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bank;
use App\Services\KoraPayPayoutService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SyncBanksFromKoraPay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'banks:sync-korapay {--country=NG : Country code (NG, KE, ZA)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync banks from KoraPay API to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $countryCode = $this->option('country');
        
        $this->info("🔄 Syncing banks from KoraPay API for country: {$countryCode}...");

        $payoutService = app(KoraPayPayoutService::class);
        $result = $payoutService->fetchBanks($countryCode);

        if (!$result['success']) {
            $this->error('❌ Failed to fetch banks from KoraPay: ' . $result['message']);
            return Command::FAILURE;
        }

        $banks = $result['data'] ?? [];
        
        if (empty($banks)) {
            $this->warn('⚠️  No banks returned from KoraPay API.');
            return Command::FAILURE;
        }

        $this->info("📦 Processing " . count($banks) . " banks from KoraPay...");

        $sortOrder = 1;
        $successCount = 0;
        $updatedCount = 0;
        $createdCount = 0;

        $progressBar = $this->output->createProgressBar(count($banks));
        $progressBar->start();

        foreach ($banks as $bankData) {
            if (empty($bankData['code']) || empty($bankData['name'])) {
                $progressBar->advance();
                continue;
            }

            // Generate unique slug - append code if slug already exists
            $baseSlug = $bankData['slug'] ?? Str::slug($bankData['name']);
            $slug = $baseSlug;
            $slugCounter = 1;
            
            // Check if slug exists for a different bank code
            while (Bank::where('slug', $slug)->where('code', '!=', $bankData['code'])->exists()) {
                $slug = $baseSlug . '-' . $bankData['code'];
                break; // Use code as suffix to ensure uniqueness
            }

            $bank = Bank::updateOrCreate(
                ['code' => $bankData['code']],
                [
                    'name' => $bankData['name'],
                    'slug' => $slug,
                    'is_active' => true,
                    'sort_order' => $sortOrder++,
                ]
            );

            if ($bank->wasRecentlyCreated) {
                $createdCount++;
            } else {
                $updatedCount++;
            }
            $successCount++;
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✅ Successfully synced banks from KoraPay!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Processed', $successCount],
                ['Created', $createdCount],
                ['Updated', $updatedCount],
            ]
        );

        Log::info('Banks synced from KoraPay', [
            'country_code' => $countryCode,
            'total' => $successCount,
            'created' => $createdCount,
            'updated' => $updatedCount,
        ]);

        return Command::SUCCESS;
    }
}
