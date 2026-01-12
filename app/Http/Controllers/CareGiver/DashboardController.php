<?php

namespace App\Http\Controllers\CareGiver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ActivityLog;

class DashboardController extends Controller
{
    /**
     * Display the care giver dashboard
     */
    public function index()
    {
        $careGiver = Auth::guard('care_giver')->user();
        
        // Get assigned patients count (only active assignments)
        // Using direct query to avoid relationship caching issues
        $assignedPatientsCount = DB::table('caregiver_patient_assignments')
            ->where('caregiver_id', $careGiver->id)
            ->where('status', 'active')
            ->count();
        
        // Get active care plans count (via assignments with care_plan_id)
        // Using direct query to avoid relationship caching issues
        $activeCarePlansCount = DB::table('caregiver_patient_assignments')
            ->where('caregiver_id', $careGiver->id)
            ->where('status', 'active')
            ->whereNotNull('care_plan_id')
            ->distinct('care_plan_id')
            ->count('care_plan_id');
        
        // Count pending daily logs (vital signs from last 24 hours that need attention)
        // For now, we'll count vital signs recorded in last 24 hours
        // Using direct query to avoid relationship caching issues
        $pendingDailyLogs = DB::table('vital_signs')
            ->where('caregiver_id', $careGiver->id)
            ->where('created_at', '>=', now()->subDay())
            ->count();
        
        // Get recent caregiver activity (last 5 actions from ActivityLog)
        $recentActivity = ActivityLog::where('user_type', 'caregiver')
            ->where('user_id', $careGiver->id)
            ->latest()
            ->limit(5)
            ->get();
        
        // Get statistics
        $stats = [
            'assigned_patients' => $assignedPatientsCount,
            'active_care_plans' => $activeCarePlansCount,
            'pending_daily_logs' => $pendingDailyLogs,
            'total_vital_signs' => DB::table('vital_signs')
                ->where('caregiver_id', $careGiver->id)
                ->count(),
        ];
        
        return view('care-giver.dashboard', compact('careGiver', 'stats', 'recentActivity'));
    }
}

