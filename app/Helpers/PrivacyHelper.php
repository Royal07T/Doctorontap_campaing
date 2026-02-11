<?php

namespace App\Helpers;

class PrivacyHelper
{
    /**
     * Mask email address for display
     * Example: john.doe@example.com -> j***@e***.com
     */
    public static function maskEmail(?string $email): string
    {
        if (empty($email)) {
            return 'N/A';
        }

        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '***';
        }

        $username = $parts[0];
        $domain = $parts[1];
        
        // Mask username: show first character, rest as ***
        $maskedUsername = strlen($username) > 1 
            ? substr($username, 0, 1) . str_repeat('*', min(3, strlen($username) - 1))
            : '*';
        
        // Mask domain: show first character, rest as ***
        $domainParts = explode('.', $domain);
        $domainName = $domainParts[0];
        $extension = isset($domainParts[1]) ? '.' . $domainParts[1] : '';
        
        $maskedDomain = strlen($domainName) > 1
            ? substr($domainName, 0, 1) . str_repeat('*', min(2, strlen($domainName) - 1))
            : '*';
        
        return $maskedUsername . '@' . $maskedDomain . $extension;
    }

    /**
     * Mask phone number for display
     * Example: +2347081114942 -> +234***4942
     */
    public static function maskPhone(?string $phone): string
    {
        if (empty($phone)) {
            return 'N/A';
        }

        // Remove all non-digit characters except +
        $cleaned = preg_replace('/[^\d+]/', '', $phone);
        
        // If phone is too short, return masked
        if (strlen($cleaned) < 4) {
            return '***';
        }

        // Show country code (if starts with +) and last 4 digits
        if (strpos($cleaned, '+') === 0) {
            $countryCode = substr($cleaned, 0, 4); // e.g., +234
            $lastDigits = substr($cleaned, -4);
            $middle = str_repeat('*', max(3, strlen($cleaned) - 8));
            return $countryCode . $middle . $lastDigits;
        } else {
            // No country code, show last 4 digits
            $lastDigits = substr($cleaned, -4);
            $middle = str_repeat('*', max(3, strlen($cleaned) - 4));
            return $middle . $lastDigits;
        }
    }

    /**
     * Check if current user is customer care (for masking logic)
     */
    public static function shouldMask(): bool
    {
        return auth()->guard('customer_care')->check();
    }
}

