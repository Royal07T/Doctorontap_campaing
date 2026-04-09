<?php

namespace App\Jobs;

use App\Services\LeadFollowUpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LeadFollowUpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $backoff = 60;

    public function handle(LeadFollowUpService $service): void
    {
        Log::info('LeadFollowUpJob started');

        $results = $service->processAll();

        Log::info('LeadFollowUpJob completed', $results);
    }
}
