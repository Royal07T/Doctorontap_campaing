<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Models\Consultation;
use App\Observers\ConsultationObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Override Laravel's default BroadcastController with our custom one
        // that supports multiple guards (admin, doctor, patient, nurse, canvasser)
        $this->app->bind(
            \Illuminate\Broadcasting\BroadcastController::class,
            \App\Http\Controllers\BroadcastController::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use custom Tailwind pagination view
        Paginator::defaultView('vendor.pagination.tailwind');
        
        // Register Consultation observer
        Consultation::observe(ConsultationObserver::class);
        
        // HIPAA Compliance: Force HTTPS in production
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        if (config('services.vonage.video_enabled')) {
            $apiKey = config('services.vonage.api_key');
            $apiSecret = config('services.vonage.api_secret');

            if (empty($apiKey) || empty($apiSecret)) {
                throw new \RuntimeException('VONAGE_VIDEO_ENABLED is true but VONAGE_API_KEY/VONAGE_API_SECRET are missing.');
            }
        }
    }
}
