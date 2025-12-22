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

if (!function_exists('email_logo_inline')) {
    /**
     * Get inline base64-encoded logo for email embedding
     * This allows the logo to display in emails without requiring external URL access
     * 
     * @return string Data URI with base64-encoded PNG logo
     */
    function email_logo_inline() {
        $logoPath = public_path('img/whitelogo.png');
        
        if (!file_exists($logoPath)) {
            return '';
        }
        
        $imageData = base64_encode(file_get_contents($logoPath));
        return 'data:image/png;base64,' . $imageData;
    }
}

