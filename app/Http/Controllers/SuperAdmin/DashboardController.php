<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Payment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\AdminUser;
use App\Models\Canvasser;
use App\Models\Nurse;
use App\Models\CustomerCare;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display super admin dashboard
     */
    public function index()
    {
        // Cache statistics for 5 minutes
        $stats = Cache::remember('super_admin_dashboard_stats', 300, function () {
            return [
                // User statistics
                'total_admins' => AdminUser::count(),
                'total_doctors' => Doctor::count(),
                'total_patients' => Patient::count(),
                'total_canvassers' => Canvasser::count(),
                'total_nurses' => Nurse::count(),
                'total_customer_care' => CustomerCare::count(),
                
                // Consultation statistics
                'total_consultations' => Consultation::count(),
                'pending_consultations' => Consultation::where('status', 'pending')->count(),
                'completed_consultations' => Consultation::where('status', 'completed')->count(),
                'cancelled_consultations' => Consultation::where('status', 'cancelled')->count(),
                
                // Payment statistics
                'total_revenue' => Payment::where('status', 'success')->sum('amount'),
                'pending_payments' => Payment::where('status', 'pending')->count(),
                'failed_payments' => Payment::where('status', 'failed')->count(),
                
                // Activity statistics
                'activity_logs_today' => ActivityLog::whereDate('created_at', today())->count(),
                'activity_logs_this_week' => ActivityLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            ];
        });

        // Recent activity logs (last 20)
        $recentActivities = ActivityLog::latest()
            ->limit(20)
            ->get();

        // System health indicators
        $systemHealth = $this->getSystemHealth();

        // Recent critical events (last 24 hours)
        $criticalEvents = ActivityLog::where('action', 'like', '%critical%')
            ->orWhere('action', 'like', '%error%')
            ->where('created_at', '>=', now()->subDay())
            ->latest()
            ->limit(10)
            ->get();

        return view('super-admin.dashboard', compact(
            'stats',
            'recentActivities',
            'systemHealth',
            'criticalEvents'
        ));
    }

    /**
     * Get system health indicators
     */
    private function getSystemHealth(): array
    {
        // Database connectivity
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $responseTime = (microtime(true) - $start) * 1000;
            $database = [
                'status' => 'healthy',
                'response_time_ms' => round($responseTime, 2),
            ];
        } catch (\Exception $e) {
            $database = [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }

        // Cache status
        try {
            $key = 'health_check_' . time();
            Cache::put($key, 'ok', 1);
            $value = Cache::get($key);
            Cache::forget($key);
            $cache = [
                'status' => $value === 'ok' ? 'healthy' : 'unhealthy',
            ];
        } catch (\Exception $e) {
            $cache = [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }

        // Queue status
        try {
            $queueSize = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->where('failed_at', '>=', now()->subDay())->count();
            
            $status = 'healthy';
            if ($failedJobs > 10) {
                $status = 'warning';
            }
            if ($queueSize > 1000) {
                $status = 'warning';
            }
            
            $queue = [
                'status' => $status,
                'queue_size' => $queueSize,
                'failed_jobs_24h' => $failedJobs,
            ];
        } catch (\Exception $e) {
            $queue = [
                'status' => 'unknown',
                'error' => $e->getMessage(),
            ];
        }

        // Storage status
        try {
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

            $storage = [
                'status' => $status,
                'total_gb' => round($totalSpace / 1024 / 1024 / 1024, 2),
                'free_gb' => round($freeSpace / 1024 / 1024 / 1024, 2),
                'used_gb' => round($usedSpace / 1024 / 1024 / 1024, 2),
                'usage_percent' => round($usagePercent, 2),
            ];
        } catch (\Exception $e) {
            $storage = [
                'status' => 'unknown',
                'error' => $e->getMessage(),
            ];
        }

        return [
            'database' => $database,
            'cache' => $cache,
            'queue' => $queue,
            'storage' => $storage,
        ];
    }
}
