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
        
        // Add global helper function for WhatsApp phone formatting
        if (!function_exists('format_whatsapp_phone')) {
            function format_whatsapp_phone($phone) {
                // Remove all non-numeric characters
                $phone = preg_replace('/[^0-9]/', '', $phone);
                
                // Convert Nigerian local format to international format
                // If starts with 0, replace with 234
                // If doesn't start with 234, add 234
                if (strlen($phone) > 0 && $phone[0] === '0') {
                    $phone = '234' . substr($phone, 1);
                } elseif (!str_starts_with($phone, '234')) {
                    // If it doesn't start with 234 and is a valid length, add 234
                    if (strlen($phone) >= 10) {
                        $phone = '234' . $phone;
                    }
                }
                
                return $phone;
            }
        }
    }
}
