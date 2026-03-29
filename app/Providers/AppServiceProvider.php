<?php

namespace App\Providers;

use App\Models\Consultation;
use App\Observers\ConsultationObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Override Laravel's default BroadcastController with our custom one
        // that supports multiple guards
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

        // HTTPS URLs for assets and route() when APP_URL is https (e.g. ngrok) or in production.
        $appUrl = (string) config('app.url', '');
        if ($this->app->environment('production') || str_starts_with($appUrl, 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
