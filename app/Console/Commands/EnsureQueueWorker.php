<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class EnsureQueueWorker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:ensure-worker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure the queue worker is running. Starts it if not running.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if queue worker is already running
        $result = Process::run('pgrep -f "queue:work database"');
        
        if ($result->successful() && trim($result->output()) !== '') {
            $this->info('Queue worker is already running.');
            return Command::SUCCESS;
        }

        // Queue worker is not running, start it in the background using nohup
        $this->info('Starting queue worker...');
        
        $artisanPath = base_path('artisan');
        $logPath = storage_path('logs/worker.log');
        
        // Remove --max-time for Supervisor/systemd managed workers
        // For cron-based setups, use a longer max-time (8 hours) to reduce restarts
        // Supervisor/systemd will handle automatic restarts
        $maxTime = env('QUEUE_WORKER_MAX_TIME', 28800); // 8 hours default
        
        $command = sprintf(
            'nohup %s %s queue:work database --sleep=3 --tries=3 --timeout=90 --max-jobs=1000 --max-time=%d >> %s 2>&1 &',
            PHP_BINARY,
            escapeshellarg($artisanPath),
            $maxTime,
            escapeshellarg($logPath)
        );

        // Execute the command in background
        exec($command);

        // Wait a moment and verify it started
        sleep(2);
        $check = Process::run('pgrep -f "queue:work database"');
        
        if ($check->successful() && trim($check->output()) !== '') {
            $this->info('Queue worker started successfully.');
            return Command::SUCCESS;
        }

        $this->error('Failed to start queue worker. Please check logs at: ' . $logPath);
        return Command::FAILURE;
    }
}

