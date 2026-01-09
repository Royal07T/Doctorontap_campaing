<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\SecurityAlert;
use App\Models\Setting;
use Symfony\Component\HttpFoundation\Response;

class SecurityMonitoring
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        // Wrap all monitoring in try-catch to prevent middleware from breaking app
        try {
            // Monitor suspicious patterns
            $this->monitorSuspiciousActivity($request);
            
            // Monitor file access patterns
            $this->monitorFileAccess($request);
            
            // Monitor SQL injection attempts
            $this->monitorSQLInjection($request);
            
            // Monitor XSS attempts
            $this->monitorXSSAttempts($request);
        } catch (\Exception $e) {
            // Log the error but don't break the request
            Log::error('Security monitoring failed: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        $response = $next($request);
        
        // Log response details
        try {
            $this->logResponseDetails($request, $response, $startTime);
        } catch (\Exception $e) {
            // Silently fail - response logging is non-critical
            Log::warning('Failed to log response details: ' . $e->getMessage());
        }
        
        return $response;
    }
    
    /**
     * Monitor suspicious activity patterns
     */
    protected function monitorSuspiciousActivity(Request $request): void
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $url = $request->fullUrl();
        $path = $request->path();
        
        // Environment-based configuration
        $isDevelopment = app()->environment('local', 'testing');
        $localIps = ['127.0.0.1', '::1', 'localhost'];
        $isLocalhost = in_array($ip, $localIps);
        
        // Legitimate polling endpoints that should be excluded from rapid request detection
        $excludedPaths = [
            'notifications/unread-count',
            'service-worker.js',
            'sw.js',
            'api/notifications',
            'health',
            'up',
        ];
        
        $isExcludedPath = false;
        foreach ($excludedPaths as $excludedPath) {
            if (str_contains($path, $excludedPath)) {
                $isExcludedPath = true;
                break;
            }
        }
        
        // In development: Skip monitoring for localhost completely
        if ($isDevelopment && $isLocalhost) {
            return;
        }
        
        // Check for suspicious user agents (always check, even for excluded paths)
        $suspiciousUserAgents = [
            'sqlmap', 'nikto', 'nmap', 'masscan', 'zap', 'burp',
            'wget', 'curl', 'python-requests', 'bot', 'crawler',
            'scanner', 'exploit', 'hack', 'attack'
        ];
        
        foreach ($suspiciousUserAgents as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                $this->logSecurityEvent('suspicious_user_agent', [
                    'ip' => $ip,
                    'user_agent' => $userAgent,
                    'url' => $url,
                    'pattern' => $pattern,
                    'severity' => 'medium'
                ]);
            }
        }
        
        // Skip rapid request detection for excluded paths (legitimate polling)
        if ($isExcludedPath) {
            return;
        }
        
        // Smart rapid request detection
        // Different thresholds for different scenarios
        try {
            $key = "rapid_requests:{$ip}";
            $alertKey = "rapid_requests_alerted:{$ip}";
            $requests = Cache::get($key, 0);
            $alreadyAlerted = Cache::get($alertKey, false);
            
            // Context-aware thresholds
            // Production: Higher threshold (legitimate users with multiple tabs)
            // Development: Even higher or disabled for localhost
            if ($isDevelopment && $isLocalhost) {
                $threshold = 1000; // Very high for localhost in dev
            } elseif ($isDevelopment) {
                $threshold = 500; // Higher for dev environment
            } else {
                $threshold = 300; // Production: 300 requests/minute per IP
            }
            
            // Check if this looks like an attack pattern
            // Attacks typically hit many different endpoints rapidly
            $endpointKey = "rapid_requests_endpoints:{$ip}";
            $endpoints = Cache::get($endpointKey, []);
            $endpoints[] = $path;
            
            // Keep only unique endpoints from last minute
            $endpoints = array_unique(array_slice($endpoints, -50));
            Cache::put($endpointKey, $endpoints, 60);
            
            $uniqueEndpoints = count($endpoints);
            
            // More suspicious if hitting many different endpoints rapidly
            // Legitimate polling hits same endpoint repeatedly
            $isSuspiciousPattern = $uniqueEndpoints > 20 && $requests > ($threshold * 0.7);
            
            if ($requests > $threshold && !$alreadyAlerted) {
                // Only alert if it's a suspicious pattern OR exceeds threshold significantly
                if ($isSuspiciousPattern || $requests > ($threshold * 1.5)) {
                    $this->logSecurityEvent('rapid_requests', [
                        'ip' => $ip,
                        'user_agent' => $userAgent,
                        'url' => $url,
                        'request_count' => $requests,
                        'unique_endpoints' => $uniqueEndpoints,
                        'threshold' => $threshold,
                        'is_suspicious_pattern' => $isSuspiciousPattern,
                        'severity' => $isSuspiciousPattern ? 'high' : 'medium'
                    ]);
                    
                    // Mark as alerted to prevent spam
                    Cache::put($alertKey, true, 60); // 1 minute
                }
            }
            
            Cache::put($key, $requests + 1, 60); // 1 minute cache
        } catch (\Exception $e) {
            // Cache might not be available - continue without rate tracking
        }
    }
    
    /**
     * Monitor file access patterns
     */
    protected function monitorFileAccess(Request $request): void
    {
        $url = $request->fullUrl();
        $path = $request->path();
        
        // Check for attempts to access sensitive files
        $sensitivePatterns = [
            '/\.env/',
            '/\.git/',
            '/\.htaccess/',
            '/\.htpasswd/',
            '/config/',
            '/database/',
            '/storage\/logs/',
            '/vendor/',
            '/node_modules/',
            '/\.php$/',
            '/\.sql$/',
            '/\.log$/',
            '/backup/',
            '/adminer/',
            '/phpmyadmin/',
            '/wp-admin/',
            '/wp-login/'
        ];
        
        foreach ($sensitivePatterns as $pattern) {
            if (preg_match($pattern, $url)) {
                $this->logSecurityEvent('sensitive_file_access', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $url,
                    'path' => $path,
                    'pattern' => $pattern,
                    'severity' => 'high'
                ]);
            }
        }
    }
    
    /**
     * Monitor SQL injection attempts
     */
    protected function monitorSQLInjection(Request $request): void
    {
        $allInput = array_merge(
            $request->all(),
            $request->query->all(),
            $request->request->all()
        );
        
        $sqlPatterns = [
            '/(\bunion\b.*\bselect\b)/i',
            '/(\bselect\b.*\bfrom\b)/i',
            '/(\binsert\b.*\binto\b)/i',
            '/(\bupdate\b.*\bset\b)/i',
            '/(\bdelete\b.*\bfrom\b)/i',
            '/(\bdrop\b.*\btable\b)/i',
            '/(\balter\b.*\btable\b)/i',
            '/(\bexec\b|\bexecute\b)/i',
            '/(\bscript\b.*\b>)/i',
            '/(\bwaitfor\b.*\bdelay\b)/i',
            '/(\bxp_cmdshell\b)/i',
            '/(\bsp_executesql\b)/i',
            '/(\bchar\b.*\b\()/i',
            '/(\bconcat\b.*\b\()/i',
            '/(\bgroup_concat\b)/i',
            '/(\binformation_schema\b)/i',
            '/(\bpg_sleep\b)/i',
            '/(\bsleep\b.*\b\()/i',
            '/(\bbenchmark\b.*\b\()/i'
        ];
        
        foreach ($allInput as $key => $value) {
            if (is_string($value)) {
                foreach ($sqlPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $this->logSecurityEvent('sql_injection_attempt', [
                            'ip' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'url' => $request->fullUrl(),
                            'input_key' => $key,
                            'input_value' => substr($value, 0, 200), // Limit length
                            'pattern' => $pattern,
                            'severity' => 'critical'
                        ]);
                    }
                }
            }
        }
    }
    
    /**
     * Monitor XSS attempts
     */
    protected function monitorXSSAttempts(Request $request): void
    {
        $allInput = array_merge(
            $request->all(),
            $request->query->all(),
            $request->request->all()
        );
        
        $xssPatterns = [
            '/(<script[^>]*>.*?<\/script>)/i',
            '/(<iframe[^>]*>.*?<\/iframe>)/i',
            '/(<object[^>]*>.*?<\/object>)/i',
            '/(<embed[^>]*>.*?<\/embed>)/i',
            '/(<applet[^>]*>.*?<\/applet>)/i',
            '/(<form[^>]*>.*?<\/form>)/i',
            '/(<input[^>]*>)/i',
            '/(<textarea[^>]*>.*?<\/textarea>)/i',
            '/(<select[^>]*>.*?<\/select>)/i',
            '/(<link[^>]*>)/i',
            '/(<meta[^>]*>)/i',
            '/(<style[^>]*>.*?<\/style>)/i',
            '/(<link[^>]*>)/i',
            '/(javascript:)/i',
            '/(vbscript:)/i',
            '/(onload\s*=)/i',
            '/(onerror\s*=)/i',
            '/(onclick\s*=)/i',
            '/(onmouseover\s*=)/i',
            '/(onfocus\s*=)/i',
            '/(onblur\s*=)/i',
            '/(onchange\s*=)/i',
            '/(onsubmit\s*=)/i',
            '/(onreset\s*=)/i',
            '/(onselect\s*=)/i',
            '/(onkeydown\s*=)/i',
            '/(onkeyup\s*=)/i',
            '/(onkeypress\s*=)/i',
            '/(onmousedown\s*=)/i',
            '/(onmouseup\s*=)/i',
            '/(onmousemove\s*=)/i',
            '/(onmouseout\s*=)/i',
            '/(onmouseenter\s*=)/i',
            '/(onmouseleave\s*=)/i',
            '/(oncontextmenu\s*=)/i',
            '/(ondblclick\s*=)/i',
            '/(onabort\s*=)/i',
            '/(oncanplay\s*=)/i',
            '/(oncanplaythrough\s*=)/i',
            '/(ondurationchange\s*=)/i',
            '/(onemptied\s*=)/i',
            '/(onended\s*=)/i',
            '/(onerror\s*=)/i',
            '/(onloadeddata\s*=)/i',
            '/(onloadedmetadata\s*=)/i',
            '/(onloadstart\s*=)/i',
            '/(onpause\s*=)/i',
            '/(onplay\s*=)/i',
            '/(onplaying\s*=)/i',
            '/(onprogress\s*=)/i',
            '/(onratechange\s*=)/i',
            '/(onseeked\s*=)/i',
            '/(onseeking\s*=)/i',
            '/(onstalled\s*=)/i',
            '/(onsuspend\s*=)/i',
            '/(ontimeupdate\s*=)/i',
            '/(onvolumechange\s*=)/i',
            '/(onwaiting\s*=)/i',
            '/(onbeforeunload\s*=)/i',
            '/(onhashchange\s*=)/i',
            '/(onmessage\s*=)/i',
            '/(onoffline\s*=)/i',
            '/(ononline\s*=)/i',
            '/(onpagehide\s*=)/i',
            '/(onpageshow\s*=)/i',
            '/(onpopstate\s*=)/i',
            '/(onresize\s*=)/i',
            '/(onstorage\s*=)/i',
            '/(onunload\s*=)/i'
        ];
        
        foreach ($allInput as $key => $value) {
            if (is_string($value)) {
                foreach ($xssPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $this->logSecurityEvent('xss_attempt', [
                            'ip' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'url' => $request->fullUrl(),
                            'input_key' => $key,
                            'input_value' => substr($value, 0, 200), // Limit length
                            'pattern' => $pattern,
                            'severity' => 'high'
                        ]);
                    }
                }
            }
        }
    }
    
    /**
     * Log response details
     */
    protected function logResponseDetails(Request $request, Response $response, float $startTime): void
    {
        $executionTime = microtime(true) - $startTime;
        
        // Log slow requests
        if ($executionTime > 5.0) { // More than 5 seconds
            Log::warning('Slow request detected', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time' => $executionTime,
                'status_code' => $response->getStatusCode(),
                'user_agent' => $request->userAgent()
            ]);
        }
        
        // Log error responses
        if ($response->getStatusCode() >= 400) {
            Log::warning('Error response', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'status_code' => $response->getStatusCode(),
                'user_agent' => $request->userAgent(),
                'execution_time' => $executionTime
            ]);
        }
    }
    
    /**
     * Log security events
     */
    protected function logSecurityEvent(string $eventType, array $data): void
    {
        $severity = $data['severity'] ?? 'medium';
        
        $logData = array_merge($data, [
            'event_type' => $eventType,
            'timestamp' => now(),
            'severity' => $severity
        ]);
        
        // Log based on severity
        switch ($severity) {
            case 'critical':
                Log::critical("Security Event: {$eventType}", $logData);
                $this->sendSecurityAlert($eventType, $logData);
                break;
            case 'high':
                Log::error("Security Event: {$eventType}", $logData);
                $this->sendSecurityAlert($eventType, $logData);
                break;
            case 'medium':
                Log::warning("Security Event: {$eventType}", $logData);
                break;
            default:
                Log::info("Security Event: {$eventType}", $logData);
                break;
        }
        
        // Store in cache for monitoring dashboard
        $this->storeSecurityEvent($eventType, $logData);
    }
    
    /**
     * Send security alert
     */
    protected function sendSecurityAlert(string $eventType, array $data): void
    {
        // Always log the alert
        Log::alert("SECURITY ALERT: {$eventType}", $data);
        
        // Check if email alerts are enabled
        $alertsEnabled = Setting::get('security_alerts_enabled', false);
        
        if (!$alertsEnabled) {
            return;
        }
        
        // Get alert email recipients
        $alertEmails = Setting::get('security_alert_emails', []);
        
        if (empty($alertEmails) || !is_array($alertEmails)) {
            return;
        }
        
        // Get configured severities that should trigger emails
        $alertSeverities = Setting::get('security_alert_severities', ['critical', 'high']);
        
        if (!is_array($alertSeverities)) {
            $alertSeverities = ['critical', 'high'];
        }
        
        $severity = $data['severity'] ?? 'medium';
        
        // Only send email if severity is in the configured list
        if (!in_array($severity, $alertSeverities)) {
            return;
        }
        
        // Check thresholds to avoid email spam
        if (!$this->shouldSendAlert($severity, $eventType)) {
            return;
        }
        
        // Send email to all configured recipients
        $sentCount = 0;
        $failedEmails = [];
        
        try {
            foreach ($alertEmails as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    try {
                        Log::info('Sending security alert email', [
                            'recipient' => $email,
                            'event_type' => $eventType,
                            'severity' => $severity,
                            'ip' => $data['ip'] ?? 'N/A'
                        ]);
                        
                        Mail::to($email)->send(new SecurityAlert($eventType, $data, $severity));
                        
                        $sentCount++;
                        
                        Log::info('Security alert email sent successfully', [
                            'recipient' => $email,
                            'event_type' => $eventType,
                            'severity' => $severity
                        ]);
                    } catch (\Exception $e) {
                        $failedEmails[] = $email;
                        Log::error('Failed to send security alert email to recipient', [
                            'recipient' => $email,
                            'event_type' => $eventType,
                            'severity' => $severity,
                            'error' => $e->getMessage()
                        ]);
                    }
                } else {
                    Log::warning('Invalid email address in security alert recipients', [
                        'email' => $email,
                        'event_type' => $eventType
                    ]);
                }
            }
            
            Log::info('Security alert email sending completed', [
                'event_type' => $eventType,
                'severity' => $severity,
                'total_recipients' => count($alertEmails),
                'sent_count' => $sentCount,
                'failed_count' => count($failedEmails),
                'sent_to' => array_diff($alertEmails, $failedEmails),
                'failed_to' => $failedEmails
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send security alert emails', [
                'event_type' => $eventType,
                'severity' => $severity,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Check if alert should be sent based on thresholds
     */
    protected function shouldSendAlert(string $severity, string $eventType): bool
    {
        // Get threshold settings
        $criticalThreshold = Setting::get('security_alert_threshold_critical', 1);
        $highThreshold = Setting::get('security_alert_threshold_high', 5);
        
        // For critical and high severity, check hourly thresholds
        if ($severity === 'critical' || $severity === 'high') {
            $hour = now()->format('Y-m-d-H');
            $key = "security_alert_sent:{$severity}:{$hour}";
            $sentCount = Cache::get($key, 0);
            
            $threshold = $severity === 'critical' ? $criticalThreshold : $highThreshold;
            
            if ($sentCount >= $threshold) {
                return false; // Already sent threshold number of alerts this hour
            }
            
            // Increment counter
            Cache::put($key, $sentCount + 1, 3600); // 1 hour cache
        }
        
        return true;
    }
    
    /**
     * Store security event for monitoring
     */
    protected function storeSecurityEvent(string $eventType, array $data): void
    {
        try {
            $key = "security_events:" . now()->format('Y-m-d-H');
            $events = Cache::get($key, []);
            
            $events[] = $data;
            
            // Keep only last 100 events per hour
            if (count($events) > 100) {
                $events = array_slice($events, -100);
            }
            
            Cache::put($key, $events, 3600); // 1 hour
        } catch (\Exception $e) {
            // Cache might not be available - event is already logged
        }
    }
}
