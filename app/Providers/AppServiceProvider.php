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
        //
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
    }
}
