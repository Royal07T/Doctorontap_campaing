<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\VonageService;

class TestVonageBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vonage:test-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Vonage account balance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $vonage = new VonageService();

        $this->info('🚀 Vonage Balance Check');
        $this->newLine();

        // Check if API credentials are configured
        if (empty(config('services.vonage.api_key')) || empty(config('services.vonage.api_secret'))) {
            $this->error('❌ Vonage API credentials are not configured!');
            $this->info('Add VONAGE_API_KEY and VONAGE_API_SECRET to your .env file.');
            return Command::FAILURE;
        }

        $this->info('⏳ Checking balance...');
        $result = $vonage->checkBalance();

        $this->newLine();

        if ($result['success']) {
            $this->info('✅ Balance retrieved successfully!');
            $this->newLine();
            
            if (isset($result['data'])) {
                $this->info('📊 Account Details:');
                
                $currency = $result['data']['currency'] ?? 'EUR';
                $balance = $result['data']['balance'] ?? '0.00';
                
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['Balance', "{$currency} {$balance}"],
                        ['Auto Reload', isset($result['data']['auto_reload']) ? ($result['data']['auto_reload'] ? '✅ On' : '❌ Off') : 'N/A'],
                    ]
                );
            }
            
            return Command::SUCCESS;
        } else {
            $this->error('❌ Failed to retrieve balance!');
            $this->newLine();
            
            if (isset($result['error'])) {
                $this->error('Error Details:');
                $this->line("  {$result['error']}");
            }
            
            return Command::FAILURE;
        }
    }
}
