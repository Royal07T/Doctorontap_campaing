<?php

namespace App\Helpers;

use App\Services\TermiiService;
use App\Services\VonageService;

class SmsServiceHelper
{
    /**
     * Get the configured SMS service instance
     *
     * @return TermiiService|VonageService
     */
    public static function getService()
    {
        $provider = config('services.sms_provider', 'termii');
        
        if ($provider === 'vonage') {
            return app(VonageService::class);
        }
        
        return app(TermiiService::class);
    }
}








