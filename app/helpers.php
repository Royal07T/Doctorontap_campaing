<?php

if (!function_exists('format_whatsapp_phone')) {
    /**
     * Format phone number for WhatsApp links
     * Converts Nigerian local format (080XXXXX) to international format (23480XXXXX)
     * 
     * @param string $phone The phone number to format
     * @return string Formatted phone number with country code
     */
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

if (!function_exists('app_url')) {
    /**
     * Generate application URL that always reads from env (not cached config)
     * 
     * @param string $path Optional path to append
     * @return string Full application URL
     */
    function app_url($path = '') {
        $baseUrl = env('APP_URL', config('app.url', 'http://localhost'));
        $baseUrl = rtrim($baseUrl, '/');
        $path = ltrim($path, '/');
        return $path ? $baseUrl . '/' . $path : $baseUrl;
    }
}

if (!function_exists('getSymptomIcon')) {
    /**
     * Get the appropriate SVG icon for a symptom
     */
    function getSymptomIcon($iconType) {
        $icons = [
            'menstruation' => '<svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.69C12 2.69 6 10 6 14c0 3.31 2.69 6 6 6s6-2.69 6-6c0-4-6-11.31-6-11.31z"/></svg>',
            'cough' => '<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8h-1.5M21 12h-1.5M21 16h-1.5M23 6c-1.5 0-1.5 1.5-1.5 1.5s0-1.5-1.5-1.5M23 10c-1.5 0-1.5 1.5-1.5 1.5s0-1.5-1.5-1.5M23 14c-1.5 0-1.5 1.5-1.5 1.5s0-1.5-1.5-1.5M23 18c-1.5 0-1.5 1.5-1.5 1.5s0-1.5-1.5-1.5" /></svg>',
            'headache' => '<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 8h8M8 12h8M8 16h4" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l2 2M3 10l2-2M21 6l-2 2M21 10l-2-2" /></svg>',
            'fever' => '<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v2m0 16v2" /></svg>',
            'stomach' => '<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h8M8 14h8" /><circle cx="12" cy="12" r="1.5" fill="currentColor"/></svg>',
            'back' => '<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v20M8 6l4-4 4 4M8 18l4 4 4-4" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 8h12M6 12h12M6 16h12" /><circle cx="12" cy="8" r="1" fill="currentColor"/><circle cx="12" cy="12" r="1" fill="currentColor"/><circle cx="12" cy="16" r="1" fill="currentColor"/></svg>',
            'eye' => '<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>',
            'ear' => '<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" /><circle cx="12" cy="12" r="1.5" fill="currentColor"/></svg>',
            'joint' => '<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8M8 12h8M8 17h8" /><circle cx="6" cy="7" r="1.5" fill="currentColor"/><circle cx="6" cy="12" r="1.5" fill="currentColor"/><circle cx="6" cy="17" r="1.5" fill="currentColor"/><circle cx="18" cy="7" r="1.5" fill="currentColor"/><circle cx="18" cy="12" r="1.5" fill="currentColor"/><circle cx="18" cy="17" r="1.5" fill="currentColor"/><circle cx="12" cy="12" r="2" fill="currentColor"/></svg>',
            'skin' => '<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>',
            'chest' => '<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>',
            'rash' => '<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z" /><circle cx="8" cy="8" r="1.5" fill="currentColor"/><circle cx="16" cy="8" r="1.5" fill="currentColor"/><circle cx="8" cy="12" r="1.5" fill="currentColor"/><circle cx="16" cy="12" r="1.5" fill="currentColor"/><circle cx="8" cy="16" r="1.5" fill="currentColor"/><circle cx="16" cy="16" r="1.5" fill="currentColor"/><circle cx="12" cy="10" r="1.5" fill="currentColor"/><circle cx="12" cy="14" r="1.5" fill="currentColor"/></svg>',
        ];
        
        return $icons[$iconType] ?? '<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>';
    }
}
