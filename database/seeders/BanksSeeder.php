<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bank;
use App\Services\KoraPayPayoutService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class BanksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Fetches banks from KoraPay API instead of hardcoding
     * Source: https://developers.korapay.com/docs/payout-via-api
     */
    public function run(): void
    {
        $this->command->info('🔄 Fetching banks from KoraPay API...');

        $payoutService = app(KoraPayPayoutService::class);
        $result = $payoutService->fetchBanks('NG'); // Nigeria

        if (!$result['success']) {
            $this->command->error('❌ Failed to fetch banks from KoraPay: ' . $result['message']);
            $this->command->warn('💡 Falling back to hardcoded banks list...');
            $this->seedFallbackBanks();
            return;
        }

        $banks = $result['data'] ?? [];
        
        if (empty($banks)) {
            $this->command->warn('⚠️  No banks returned from KoraPay API. Using fallback list...');
            $this->seedFallbackBanks();
            return;
        }

        $this->command->info('📦 Processing ' . count($banks) . ' banks from KoraPay...');

        $sortOrder = 1;
        $successCount = 0;
        $updatedCount = 0;
        $createdCount = 0;

        foreach ($banks as $bankData) {
            if (empty($bankData['code']) || empty($bankData['name'])) {
                continue;
            }

            // Generate unique slug - append code if slug already exists
            $baseSlug = $bankData['slug'] ?? Str::slug($bankData['name']);
            $slug = $baseSlug;
            
            // Check if slug exists for a different bank code
            if (Bank::where('slug', $slug)->where('code', '!=', $bankData['code'])->exists()) {
                $slug = $baseSlug . '-' . $bankData['code']; // Use code as suffix to ensure uniqueness
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
        }

        $this->command->info("✅ Successfully synced banks from KoraPay!");
        $this->command->info("   📊 Total: {$successCount} | Created: {$createdCount} | Updated: {$updatedCount}");
    }

    /**
     * Fallback method to seed banks if KoraPay API fails
     * This ensures the system still works even if API is unavailable
     */
    private function seedFallbackBanks(): void
    {
        $banks = [
            ['name' => 'Access Bank', 'code' => '044', 'sort_order' => 1],
            ['name' => 'Citibank Nigeria', 'code' => '023', 'sort_order' => 2],
            ['name' => 'Ecobank Nigeria', 'code' => '050', 'sort_order' => 3],
            ['name' => 'Fidelity Bank', 'code' => '070', 'sort_order' => 4],
            ['name' => 'First Bank of Nigeria', 'code' => '011', 'sort_order' => 5],
            ['name' => 'First City Monument Bank', 'code' => '214', 'sort_order' => 6],
            ['name' => 'Guaranty Trust Bank', 'code' => '058', 'sort_order' => 7],
            ['name' => 'Heritage Bank', 'code' => '030', 'sort_order' => 8],
            ['name' => 'Jaiz Bank', 'code' => '301', 'sort_order' => 9],
            ['name' => 'Keystone Bank', 'code' => '082', 'sort_order' => 10],
            ['name' => 'Providus Bank', 'code' => '101', 'sort_order' => 11],
            ['name' => 'Polaris Bank', 'code' => '076', 'sort_order' => 12],
            ['name' => 'Stanbic IBTC Bank', 'code' => '221', 'sort_order' => 13],
            ['name' => 'Standard Chartered Bank', 'code' => '068', 'sort_order' => 14],
            ['name' => 'Sterling Bank', 'code' => '232', 'sort_order' => 15],
            ['name' => 'Suntrust Bank', 'code' => '100', 'sort_order' => 16],
            ['name' => 'Union Bank of Nigeria', 'code' => '032', 'sort_order' => 17],
            ['name' => 'United Bank for Africa', 'code' => '033', 'sort_order' => 18],
            ['name' => 'Unity Bank', 'code' => '215', 'sort_order' => 19],
            ['name' => 'Wema Bank', 'code' => '035', 'sort_order' => 20],
            ['name' => 'Zenith Bank', 'code' => '057', 'sort_order' => 21],
        ];

        foreach ($banks as $bank) {
            Bank::updateOrCreate(
                ['code' => $bank['code']],
                [
                    'name' => $bank['name'],
                    'slug' => Str::slug($bank['name']),
                    'is_active' => true,
                    'sort_order' => $bank['sort_order'],
                ]
            );
        }

        $this->command->info('✅ Successfully seeded ' . count($banks) . ' banks from fallback list!');
    }
}
