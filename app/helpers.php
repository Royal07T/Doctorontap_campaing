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

if (!function_exists('create_notification')) {
    /**
     * Create a notification for a user
     * 
     * @param string $userType The type of user (patient, doctor, admin, nurse, canvasser)
     * @param int $userId The ID of the user
     * @param string $title The notification title
     * @param string $message The notification message
     * @param string $type The notification type (info, success, warning, error)
     * @param string|null $actionUrl Optional URL to navigate to when clicked
     * @param array|null $data Optional additional data
     * @return \App\Models\Notification
     */
    function create_notification($userType, $userId, $title, $message, $type = 'info', $actionUrl = null, $data = null) {
        $notification = \App\Models\Notification::create([
            'user_type' => $userType,
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'action_url' => $actionUrl,
            'data' => $data,
        ]);
        
        // Clear cache for unread count when new notification is created
        $cacheKey = "notifications.unread_count.{$userType}.{$userId}";
        \Illuminate\Support\Facades\Cache::forget($cacheKey);
        
        return $notification;
    }
}

