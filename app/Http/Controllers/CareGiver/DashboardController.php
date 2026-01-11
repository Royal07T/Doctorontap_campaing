<?php

namespace App\Http\Controllers\CareGiver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Consultation;

class DashboardController extends Controller
{
    /**
     * Display the care giver dashboard
     */
    public function index()
    {
        $careGiver = Auth::guard('care_giver')->user();
        
        // Get statistics
        $stats = [
            'total_consultations' => $careGiver->consultations()->count(),
            'pending_consultations' => $careGiver->consultations()->where('status', 'pending')->count(),
            'completed_consultations' => $careGiver->consultations()->where('status', 'completed')->count(),
            'today_consultations' => $careGiver->consultations()->whereDate('created_at', today())->count(),
        ];
        
        // Get recent consultations
        $recentConsultations = $careGiver->consultations()
            ->with(['patient', 'doctor'])
            ->latest()
            ->limit(10)
            ->get();
        
        return view('care-giver.dashboard', compact('careGiver', 'stats', 'recentConsultations'));
    }
}

