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
        
        // Force HTTPS in production or when using HTTPS (e.g., ngrok)
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
        
        // Force HTTPS if APP_URL uses https or if request is coming through HTTPS proxy
        if (str_starts_with(config('app.url'), 'https://') || 
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Graceful check for Vonage Video credentials
        if (config('services.vonage.video_enabled')) {
            $apiKey = config('services.vonage.api_key');
            $apiSecret = config('services.vonage.api_secret');
            $appId = config('services.vonage.application_id');
            $privateKeyPath = config('services.vonage.private_key_path');

            if (empty($apiKey) || empty($apiSecret)) {
                \Log::warning('VONAGE_VIDEO_ENABLED is true but VONAGE_API_KEY/VONAGE_API_SECRET are missing. Video features will be disabled.');
                config(['services.vonage.video_enabled' => false]);
            } elseif (!empty($appId) && !empty($privateKeyPath) && !file_exists(base_path($privateKeyPath))) {
                \Log::warning("VONAGE_VIDEO_ENABLED is true and Application ID is set, but private key file at {$privateKeyPath} is missing. Video features will be disabled until the key is provided.");
                config(['services.vonage.video_enabled' => false]);
            }
        }
    }
}
