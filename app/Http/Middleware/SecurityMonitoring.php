<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
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
        
        // Check for suspicious user agents
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
        
        // Check for rapid requests from same IP
        try {
            $key = "rapid_requests:{$ip}";
            $requests = Cache::get($key, 0);
            
            if ($requests > 100) { // More than 100 requests in 1 minute
                $this->logSecurityEvent('rapid_requests', [
                    'ip' => $ip,
                    'user_agent' => $userAgent,
                    'url' => $url,
                    'request_count' => $requests,
                    'severity' => 'high'
                ]);
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
        // In a real implementation, you would send emails, SMS, or webhooks
        // For now, we'll just log it
        Log::alert("SECURITY ALERT: {$eventType}", $data);
        
        // You could implement:
        // - Email notifications to security team
        // - SMS alerts for critical events
        // - Webhook notifications to security monitoring systems
        // - Slack/Discord notifications
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
