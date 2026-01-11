<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class SystemHealthController extends Controller
{
    /**
     * Display system health dashboard
     * 
     * All health checks are performed in real-time on each request.
     * No caching - ensures fresh system status information.
     */
    public function index()
    {
        // Real-time health checks
        $health = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'storage' => $this->checkStorage(),
        ];

        // Real-time metrics from database
        $metrics = [
            'queue_size' => $this->getQueueSize(),
            'failed_jobs_24h' => $this->getFailedJobsCount(),
            'cache_hits' => $this->getCacheStats(),
        ];

        return view('super-admin.system-health.index', compact('health', 'metrics'));
    }

    /**
     * Check database connectivity
     * 
     * Real-time check - attempts to connect to database and measures response time.
     * No caching - fresh check on every request.
     */
    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo(); // Real-time database connection test
            $responseTime = (microtime(true) - $start) * 1000;

            return [
                'status' => 'healthy',
                'response_time_ms' => round($responseTime, 2),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache
     * 
     * Real-time check - attempts to write and read from cache.
     * No caching - fresh check on every request.
     */
    private function checkCache(): array
    {
        try {
            $key = 'health_check_' . time();
            Cache::put($key, 'ok', 1); // Real-time cache write test
            $value = Cache::get($key); // Real-time cache read test
            Cache::forget($key);

            return [
                'status' => $value === 'ok' ? 'healthy' : 'unhealthy',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check queue
     * 
     * Real-time check - queries jobs and failed_jobs tables directly.
     * No caching - fresh data from database on every request.
     */
    private function checkQueue(): array
    {
        try {
            // Real-time database queries
            $queueSize = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subDay())
                ->count();

            $status = 'healthy';
            if ($failedJobs > 10) {
                $status = 'warning';
            }
            if ($queueSize > 1000) {
                $status = 'warning';
            }

            return [
                'status' => $status,
                'queue_size' => $queueSize,
                'failed_jobs_24h' => $failedJobs,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unknown',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check storage
     * 
     * Real-time check - queries filesystem for storage information.
     * No caching - fresh disk space information on every request.
     */
    private function checkStorage(): array
    {
        try {
            // Real-time filesystem queries
            $totalSpace = disk_total_space(storage_path());
            $freeSpace = disk_free_space(storage_path());
            $usedSpace = $totalSpace - $freeSpace;
            $usagePercent = ($usedSpace / $totalSpace) * 100;

            $status = 'healthy';
            if ($usagePercent > 90) {
                $status = 'critical';
            } elseif ($usagePercent > 75) {
                $status = 'warning';
            }

            return [
                'status' => $status,
                'total_gb' => round($totalSpace / 1024 / 1024 / 1024, 2),
                'free_gb' => round($freeSpace / 1024 / 1024 / 1024, 2),
                'used_gb' => round($usedSpace / 1024 / 1024 / 1024, 2),
                'usage_percent' => round($usagePercent, 2),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unknown',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get queue size
     * 
     * Real-time count from jobs table.
     * No caching - fresh count on every request.
     */
    private function getQueueSize(): int
    {
        try {
            return DB::table('jobs')->count(); // Real-time database query
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get failed jobs count (last 24 hours)
     * 
     * Real-time count from failed_jobs table.
     * No caching - fresh count on every request.
     */
    private function getFailedJobsCount(): int
    {
        try {
            return DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subDay())
                ->count(); // Real-time database query
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get cache statistics
     * 
     * Real-time cache statistics from the configured cache driver.
     * For Redis, attempts to get stats. For other drivers, returns basic info.
     */
    private function getCacheStats(): array
    {
        try {
            $cacheDriver = config('cache.default');
            
            if ($cacheDriver === 'redis') {
                try {
                    $redis = Redis::connection();
                    $info = $redis->info('stats');
                    
                    // Redis info returns a string, parse it
                    $stats = [];
                    if (is_string($info)) {
                        $lines = explode("\r\n", $info);
                        foreach ($lines as $line) {
                            if (strpos($line, ':') !== false) {
                                [$key, $value] = explode(':', $line, 2);
                                $stats[trim($key)] = trim($value);
                            }
                        }
                    } elseif (is_array($info)) {
                        $stats = $info;
                    }
                    
                    return [
                        'hits' => (int) ($stats['keyspace_hits'] ?? 0),
                        'misses' => (int) ($stats['keyspace_misses'] ?? 0),
                        'driver' => 'redis',
                    ];
                } catch (\Exception $e) {
                    // Redis connection failed, fall through to default
                }
            }
            
            // For non-Redis drivers or if Redis fails, return basic info
            return [
                'hits' => 0,
                'misses' => 0,
                'driver' => $cacheDriver ?? 'unknown',
            ];
        } catch (\Exception $e) {
            return [
                'hits' => 0,
                'misses' => 0,
                'driver' => 'unknown',
                'error' => $e->getMessage(),
            ];
        }
    }
}
