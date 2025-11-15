<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TermiiService;

class CheckTermiiSmsStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'termii:check-sms {message_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the delivery status of a Termii SMS message';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $messageId = $this->argument('message_id');
        $termii = new TermiiService();

        $this->info('🔍 Checking SMS Delivery Status...');
        $this->newLine();

        $result = $termii->getMessageStatus($messageId);

        if ($result['success']) {
            $this->info('✅ Message Status Retrieved');
            $this->newLine();
            
            $this->table(
                ['Field', 'Value'],
                collect($result['data'] ?? [])->map(function ($value, $key) {
                    return [$key, is_array($value) ? json_encode($value) : $value];
                })->toArray()
            );
        } else {
            $this->error('❌ Failed to check message status');
            $this->error('Error: ' . ($result['message'] ?? 'Unknown error'));
            
            if (isset($result['error'])) {
                $this->newLine();
                $this->warn('Details:');
                $this->line(json_encode($result['error'], JSON_PRETTY_PRINT));
            }
        }

        return $result['success'] ? Command::SUCCESS : Command::FAILURE;
    }
}

