<?php

if (!function_exists('domain_url')) {
    /**
     * Generate a URL for a specific domain type.
     *
     * @param string $domainType
     * @param string $path
     * @param array $parameters
     * @return string
     */
    function domain_url(string $domainType, string $path = '', array $parameters = []): string
    {
        if (!config('domains.enabled')) {
            return url($path, $parameters);
        }

        $domain = config("domains.domains.{$domainType}");
        
        if (!$domain) {
            return url($path, $parameters);
        }

        $scheme = request()->getScheme();
        $url = "{$scheme}://{$domain}";
        
        if ($path) {
            $url .= '/' . ltrim($path, '/');
        }
        
        if (!empty($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }
        
        return $url;
    }
}

if (!function_exists('admin_url')) {
    /**
     * Generate a URL for the admin domain.
     * 
     * @param string $path
     * @param array $parameters
     * @return string
     */
    function admin_url(string $path = '', array $parameters = []): string
    {
        return domain_url('admin', $path, $parameters);
    }
}

if (!function_exists('patient_url')) {
    /**
     * Generate a URL for the patient domain.
     *
     * @param string $path
     * @param array $parameters
     * @return string
     */
    function patient_url(string $path = '', array $parameters = []): string
    {
        return domain_url('patient', $path, $parameters);
    }
}

if (!function_exists('doctor_url')) {
    /**
     * Generate a URL for the doctor domain.
     * 
     * @param string $path
     * @param array $parameters
     * @return string
     */
    function doctor_url(string $path = '', array $parameters = []): string
    {
        return domain_url('doctor', $path, $parameters);
    }
}

if (!function_exists('current_domain_type')) {
    /**
     * Get the current domain type based on the request host.
     *
     * @return string|null
     */
    function current_domain_type(): ?string
    {
        if (!config('domains.enabled')) {
            return null;
        }

        $currentHost = request()->getHost();
        
        foreach (config('domains.domains') as $type => $domain) {
            if ($currentHost === $domain) {
                return $type;
            }
        }
        
        return null;
    }
}

if (!function_exists('is_domain')) {
    /**
     * Check if the current request is on a specific domain type.
     *
     * @param string $domainType
     * @return bool
     */
    function is_domain(string $domainType): bool
    {
        if (!config('domains.enabled')) {
            return false;
        }

        $expectedDomain = config("domains.domains.{$domainType}");
        $currentHost = request()->getHost();
        
        return $expectedDomain && $currentHost === $expectedDomain;
    }
}

if (!function_exists('email_logo_inline')) {
    /**
     * Get the logo URL for email templates.
     * Returns the full URL to the logo image for use in emails.
     *
     * @return string
     */
    function email_logo_inline(): string
    {
        // Try to get logo from settings or use default
        try {
            $logoPath = \App\Models\Setting::get('email_logo_path', 'img/logo-text.png');
        } catch (\Exception $e) {
            $logoPath = 'img/logo-text.png';
        }
        
        // If logo path doesn't start with http, make it a full URL
        if (!filter_var($logoPath, FILTER_VALIDATE_URL)) {
            return asset($logoPath);
        }
        
        return $logoPath;
    }
}
