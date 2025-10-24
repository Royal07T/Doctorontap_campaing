<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SecurityController extends Controller
{
    /**
     * Display security monitoring dashboard
     */
    public function index()
    {
        $securityStats = $this->getSecurityStats();
        $recentEvents = $this->getRecentSecurityEvents();
        $threatLevel = $this->calculateThreatLevel();
        
        return view('admin.security', compact('securityStats', 'recentEvents', 'threatLevel'));
    }
    
    /**
     * Get security statistics
     */
    protected function getSecurityStats()
    {
        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');
        
        // Get events from cache (last 24 hours)
        $events = [];
        for ($i = 0; $i < 24; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $hourEvents = Cache::get("security_events:{$hour}", []);
            $events = array_merge($events, $hourEvents);
        }
        
        $stats = [
            'total_events' => count($events),
            'critical_events' => count(array_filter($events, fn($e) => $e['severity'] === 'critical')),
            'high_events' => count(array_filter($events, fn($e) => $e['severity'] === 'high')),
            'medium_events' => count(array_filter($events, fn($e) => $e['severity'] === 'medium')),
            'low_events' => count(array_filter($events, fn($e) => $e['severity'] === 'low')),
            'unique_ips' => count(array_unique(array_column($events, 'ip'))),
            'sql_injection_attempts' => count(array_filter($events, fn($e) => $e['event_type'] === 'sql_injection_attempt')),
            'xss_attempts' => count(array_filter($events, fn($e) => $e['event_type'] === 'xss_attempt')),
            'sensitive_file_access' => count(array_filter($events, fn($e) => $e['event_type'] === 'sensitive_file_access')),
            'rapid_requests' => count(array_filter($events, fn($e) => $e['event_type'] === 'rapid_requests')),
            'suspicious_user_agent' => count(array_filter($events, fn($e) => $e['event_type'] === 'suspicious_user_agent')),
        ];
        
        return $stats;
    }
    
    /**
     * Get recent security events
     */
    protected function getRecentSecurityEvents($limit = 50)
    {
        $events = [];
        
        // Get events from last 6 hours
        for ($i = 0; $i < 6; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $hourEvents = Cache::get("security_events:{$hour}", []);
            $events = array_merge($events, $hourEvents);
        }
        
        // Sort by timestamp (newest first)
        usort($events, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        return array_slice($events, 0, $limit);
    }
    
    /**
     * Calculate current threat level
     */
    protected function calculateThreatLevel()
    {
        $events = [];
        
        // Get events from last hour
        for ($i = 0; $i < 1; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $hourEvents = Cache::get("security_events:{$hour}", []);
            $events = array_merge($events, $hourEvents);
        }
        
        $criticalCount = count(array_filter($events, fn($e) => $e['severity'] === 'critical'));
        $highCount = count(array_filter($events, fn($e) => $e['severity'] === 'high'));
        $mediumCount = count(array_filter($events, fn($e) => $e['severity'] === 'medium'));
        
        if ($criticalCount > 0) {
            return ['level' => 'critical', 'color' => 'red', 'message' => 'Critical security threats detected'];
        } elseif ($highCount > 5) {
            return ['level' => 'high', 'color' => 'orange', 'message' => 'High security activity detected'];
        } elseif ($mediumCount > 10) {
            return ['level' => 'medium', 'color' => 'yellow', 'message' => 'Moderate security activity'];
        } else {
            return ['level' => 'low', 'color' => 'green', 'message' => 'Normal security status'];
        }
    }
    
    /**
     * Get security events by type
     */
    public function eventsByType(Request $request)
    {
        $eventType = $request->get('type', 'all');
        $severity = $request->get('severity', 'all');
        $hours = $request->get('hours', 24);
        
        $events = [];
        
        for ($i = 0; $i < $hours; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $hourEvents = Cache::get("security_events:{$hour}", []);
            $events = array_merge($events, $hourEvents);
        }
        
        // Filter by type
        if ($eventType !== 'all') {
            $events = array_filter($events, fn($e) => $e['event_type'] === $eventType);
        }
        
        // Filter by severity
        if ($severity !== 'all') {
            $events = array_filter($events, fn($e) => $e['severity'] === $severity);
        }
        
        // Sort by timestamp (newest first)
        usort($events, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        return response()->json([
            'events' => array_values($events),
            'total' => count($events),
            'filters' => [
                'type' => $eventType,
                'severity' => $severity,
                'hours' => $hours
            ]
        ]);
    }
    
    /**
     * Get IP threat analysis
     */
    public function ipAnalysis(Request $request)
    {
        $ip = $request->get('ip');
        $hours = $request->get('hours', 24);
        
        $events = [];
        
        for ($i = 0; $i < $hours; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $hourEvents = Cache::get("security_events:{$hour}", []);
            $events = array_merge($events, $hourEvents);
        }
        
        // Filter by IP
        if ($ip) {
            $events = array_filter($events, fn($e) => $e['ip'] === $ip);
        }
        
        // Group by IP
        $ipStats = [];
        foreach ($events as $event) {
            $eventIp = $event['ip'];
            if (!isset($ipStats[$eventIp])) {
                $ipStats[$eventIp] = [
                    'ip' => $eventIp,
                    'total_events' => 0,
                    'critical_events' => 0,
                    'high_events' => 0,
                    'medium_events' => 0,
                    'low_events' => 0,
                    'event_types' => [],
                    'last_seen' => $event['timestamp'],
                    'user_agents' => []
                ];
            }
            
            $ipStats[$eventIp]['total_events']++;
            $ipStats[$eventIp][$event['severity'] . '_events']++;
            
            if (!in_array($event['event_type'], $ipStats[$eventIp]['event_types'])) {
                $ipStats[$eventIp]['event_types'][] = $event['event_type'];
            }
            
            if (!in_array($event['user_agent'], $ipStats[$eventIp]['user_agents'])) {
                $ipStats[$eventIp]['user_agents'][] = $event['user_agent'];
            }
            
            if (strtotime($event['timestamp']) > strtotime($ipStats[$eventIp]['last_seen'])) {
                $ipStats[$eventIp]['last_seen'] = $event['timestamp'];
            }
        }
        
        // Sort by total events (most active first)
        uasort($ipStats, function($a, $b) {
            return $b['total_events'] - $a['total_events'];
        });
        
        return response()->json([
            'ip_stats' => array_values($ipStats),
            'total_ips' => count($ipStats),
            'filters' => [
                'ip' => $ip,
                'hours' => $hours
            ]
        ]);
    }
    
    /**
     * Block IP address
     */
    public function blockIp(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'reason' => 'required|string|max:255',
            'duration' => 'required|integer|min:1|max:8760' // Max 1 year
        ]);
        
        $ip = $request->ip;
        $reason = $request->reason;
        $duration = $request->duration; // in hours
        
        // Store blocked IP in cache
        $blockedIps = Cache::get('blocked_ips', []);
        $blockedIps[$ip] = [
            'ip' => $ip,
            'reason' => $reason,
            'blocked_at' => now(),
            'blocked_until' => now()->addHours($duration),
            'blocked_by' => auth()->id()
        ];
        
        Cache::put('blocked_ips', $blockedIps, 24 * 365); // Store for 1 year
        
        // Log the blocking action
        Log::warning('IP address blocked', [
            'ip' => $ip,
            'reason' => $reason,
            'duration_hours' => $duration,
            'blocked_by' => auth()->id(),
            'timestamp' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => "IP address {$ip} has been blocked for {$duration} hours"
        ]);
    }
    
    /**
     * Get blocked IPs
     */
    public function blockedIps()
    {
        $blockedIps = Cache::get('blocked_ips', []);
        
        // Filter out expired blocks
        $activeBlocks = array_filter($blockedIps, function($block) {
            return now()->lt($block['blocked_until']);
        });
        
        return response()->json([
            'blocked_ips' => array_values($activeBlocks),
            'total' => count($activeBlocks)
        ]);
    }
    
    /**
     * Unblock IP address
     */
    public function unblockIp(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip'
        ]);
        
        $ip = $request->ip;
        $blockedIps = Cache::get('blocked_ips', []);
        
        if (isset($blockedIps[$ip])) {
            unset($blockedIps[$ip]);
            Cache::put('blocked_ips', $blockedIps, 24 * 365);
            
            Log::info('IP address unblocked', [
                'ip' => $ip,
                'unblocked_by' => auth()->id(),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "IP address {$ip} has been unblocked"
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => "IP address {$ip} is not currently blocked"
        ], 404);
    }
}
