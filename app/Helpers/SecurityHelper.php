<?php

namespace App\Helpers;

class SecurityHelper
{
    /**
     * Sanitize string input - remove dangerous characters
     */
    public static function sanitizeString(?string $value): ?string
    {
        if (!is_string($value)) {
            return $value;
        }
        
        // Remove HTML tags
        $value = strip_tags($value);
        
        // Remove null bytes
        $value = str_replace(chr(0), '', $value);
        
        // Remove invisible characters
        $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);
        
        // Remove extra whitespace
        $value = preg_replace('/\s+/', ' ', $value);
        
        // Trim
        return trim($value);
    }

    /**
     * Sanitize HTML input - allow safe tags only
     */
    public static function sanitizeHtml(?string $value, array $allowedTags = []): ?string
    {
        if (!is_string($value)) {
            return $value;
        }
        
        // Default safe tags
        if (empty($allowedTags)) {
            $allowedTags = '<p><br><strong><em><ul><ol><li><a><h1><h2><h3><h4>';
        } else {
            $allowedTags = '<' . implode('><', $allowedTags) . '>';
        }
        
        // Strip dangerous tags
        $value = strip_tags($value, $allowedTags);
        
        // Remove javascript: and data: protocols
        $value = preg_replace('/javascript:/i', '', $value);
        $value = preg_replace('/data:/i', '', $value);
        
        // Remove event handlers (onclick, onload, etc.)
        $value = preg_replace('/on\w+\s*=/i', '', $value);
        
        return $value;
    }

    /**
     * Sanitize email input
     */
    public static function sanitizeEmail(?string $value): ?string
    {
        if (!is_string($value)) {
            return $value;
        }
        
        return filter_var(trim(strtolower($value)), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize phone input
     */
    public static function sanitizePhone(?string $value): ?string
    {
        if (!is_string($value)) {
            return $value;
        }
        
        // Remove all non-numeric characters except +
        return preg_replace('/[^0-9+]/', '', trim($value));
    }

    /**
     * Sanitize integer input
     */
    public static function sanitizeInteger($value): ?int
    {
        if (is_int($value)) {
            return $value;
        }
        
        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }
        
        return null;
    }

    /**
     * Sanitize float input
     */
    public static function sanitizeFloat($value): ?float
    {
        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }
        
        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }
        
        return null;
    }

    /**
     * Sanitize filename - remove dangerous characters
     */
    public static function sanitizeFilename(?string $filename): ?string
    {
        if (!is_string($filename)) {
            return $filename;
        }
        
        // Remove directory traversal attempts
        $filename = basename($filename);
        
        // Remove special characters except dots, dashes, and underscores
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        
        // Remove multiple dots (potential extension confusion)
        $filename = preg_replace('/\.{2,}/', '.', $filename);
        
        return $filename;
    }

    /**
     * Validate file upload for security
     */
    public static function validateFileUpload(\Illuminate\Http\UploadedFile $file, array $allowedMimes, int $maxSize = 5120): bool
    {
        // Check if file is valid
        if (!$file->isValid()) {
            return false;
        }
        
        // Check file size (in KB)
        if ($file->getSize() > ($maxSize * 1024)) {
            return false;
        }
        
        // Check MIME type
        $mime = $file->getMimeType();
        if (!in_array($mime, $allowedMimes)) {
            return false;
        }
        
        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = [
            'application/pdf' => ['pdf'],
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/gif' => ['gif'],
            'application/msword' => ['doc'],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        ];
        
        if (isset($allowedExtensions[$mime])) {
            if (!in_array($extension, $allowedExtensions[$mime])) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Check for SQL injection patterns
     */
    public static function containsSqlInjection(?string $value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        
        $dangerous_patterns = [
            '/union.*select/i',
            '/select.*from/i',
            '/insert.*into/i',
            '/delete.*from/i',
            '/drop.*table/i',
            '/update.*set/i',
            '/exec.*\(/i',
            '/execute.*\(/i',
            '/script.*>/i',
            '/<script/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
        ];
        
        foreach ($dangerous_patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check for XSS patterns
     */
    public static function containsXss(?string $value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        
        $dangerous_patterns = [
            '/<script/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<img.*onerror/i',
        ];
        
        foreach ($dangerous_patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Log security incident
     */
    public static function logSecurityIncident(string $type, array $data = []): void
    {
        \Log::channel('security')->warning("Security incident: {$type}", array_merge([
            'timestamp' => now()->toDateTimeString(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'user_id' => auth()->id(),
        ], $data));
    }
}

