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
            // When domains are not enabled, prepend the route prefix
            $prefix = $domainType === 'admin' ? 'admin' : 
                     ($domainType === 'patient' ? 'patient' : 
                     ($domainType === 'doctor' ? 'doctor' : 
                     ($domainType === 'nurse' ? 'nurse' : 
                     ($domainType === 'canvasser' ? 'canvasser' : 
                     ($domainType === 'caregiver' ? 'care-giver' : 
                     ($domainType === 'customer_care' ? 'customer-care' : ''))))));
            
            if ($prefix && $path) {
                $path = $prefix . '/' . ltrim($path, '/');
            }
            
            return url($path, $parameters);
        }

        $domain = config("domains.domains.{$domainType}");
        
        if (!$domain) {
            // Fallback: prepend route prefix when domain config is missing
            $prefix = $domainType === 'admin' ? 'admin' : 
                     ($domainType === 'patient' ? 'patient' : 
                     ($domainType === 'doctor' ? 'doctor' : 
                     ($domainType === 'nurse' ? 'nurse' : 
                     ($domainType === 'canvasser' ? 'canvasser' : 
                     ($domainType === 'caregiver' ? 'care-giver' : 
                     ($domainType === 'customer_care' ? 'customer-care' : ''))))));
            
            if ($prefix && $path) {
                $path = $prefix . '/' . ltrim($path, '/');
            }
            
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

if (!function_exists('app_url')) {
    /**
     * Get the application URL.
     * Returns the base URL of the application.
     *
     * @param string $path Optional path to append
     * @return string
     */
    function app_url(string $path = ''): string
    {
        $baseUrl = config('app.url');
        
        if ($path) {
            return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
        }
        
        return $baseUrl;
    }
}

if (!function_exists('format_whatsapp_phone')) {
    /**
     * Format a phone number for WhatsApp URL (wa.me links).
     * Removes all non-numeric characters and ensures international format.
     * 
     * For Nigerian numbers:
     * - "+2348012345678" → "2348012345678"
     * - "08012345678" → "2348012345678"
     * - "2348012345678" → "2348012345678"
     *
     * @param string|null $phone
     * @return string
     */
    function format_whatsapp_phone(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Handle empty result
        if (empty($cleaned)) {
            return '';
        }

        // Handle Nigerian phone numbers
        // If starts with 0, replace with 234 (Nigeria country code)
        if (strlen($cleaned) === 11 && $cleaned[0] === '0') {
            $cleaned = '234' . substr($cleaned, 1);
        }
        // If starts with 234, keep as is
        elseif (strlen($cleaned) === 13 && substr($cleaned, 0, 3) === '234') {
            // Already in correct format
        }
        // If it's 10 digits and doesn't start with 0, assume it's missing country code
        elseif (strlen($cleaned) === 10) {
            $cleaned = '234' . $cleaned;
        }

        return $cleaned;
    }
}

if (!function_exists('medical_icon')) {
    /**
     * Get a medical icon SVG by category and key.
     * 
     * @param string $category The icon category ('specializations' or 'symptoms')
     * @param string $key The icon key/name (e.g., 'stethoscope', 'heart', 'cough')
     * @param array $attributes Optional attributes to add to the SVG (e.g., ['class' => 'w-6 h-6', 'fill' => 'currentColor'])
     * @return string The SVG content as a string
     */
    function medical_icon(string $category, string $key, array $attributes = []): string
    {
        // Sanitize inputs to prevent directory traversal
        $category = preg_replace('/[^a-z0-9_-]/', '', strtolower($category));
        $key = preg_replace('/[^a-z0-9_-]/', '', strtolower($key));
        
        // Only allow specific categories
        if (!in_array($category, ['specializations', 'symptoms'])) {
            return '';
        }
        
        $iconPath = resource_path("icons/{$category}/{$key}.svg");
        
        // Check if file exists
        if (!file_exists($iconPath)) {
            return '';
        }
        
        // Read SVG content
        $svgContent = file_get_contents($iconPath);
        
        // If attributes are provided, inject them into the SVG
        if (!empty($attributes)) {
            // Extract the opening <svg> tag
            $svgTagPattern = '/<svg\s+([^>]*)>/';
            
            if (preg_match($svgTagPattern, $svgContent, $matches)) {
                $existingAttributes = $matches[1];
                
                // Parse existing attributes
                $attrArray = [];
                // Match attributes with or without quotes (including hyphenated attributes like stroke-width)
                preg_match_all('/([\w-]+)(?:=["\']([^"\']*)["\'])?/', $existingAttributes, $attrMatches, PREG_SET_ORDER);
                foreach ($attrMatches as $match) {
                    $name = $match[1];
                    $value = isset($match[2]) && $match[2] !== '' ? $match[2] : '';
                    $attrArray[$name] = $value;
                }
                
                // Merge with new attributes (new attributes override existing ones)
                $attrArray = array_merge($attrArray, $attributes);
                
                // Build new attributes string
                $newAttributes = '';
                foreach ($attrArray as $name => $value) {
                    if ($value !== '') {
                        $newAttributes .= sprintf(' %s="%s"', $name, htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
                    } else {
                        $newAttributes .= sprintf(' %s', $name);
                    }
                }
                
                // Replace the SVG tag
                $svgContent = preg_replace($svgTagPattern, "<svg{$newAttributes}>", $svgContent);
            }
        }
        
        return $svgContent;
    }
}
