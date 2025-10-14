<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display doctor dashboard
     */
    public function index()
    {
        $doctor = Auth::guard('doctor')->user();
        
        $stats = [
            'total_consultations' => Consultation::where('doctor_id', $doctor->id)->count(),
            'pending_consultations' => Consultation::where('doctor_id', $doctor->id)
                                                    ->where('status', 'pending')->count(),
            'scheduled_consultations' => Consultation::where('doctor_id', $doctor->id)
                                                     ->where('status', 'scheduled')->count(),
            'completed_consultations' => Consultation::where('doctor_id', $doctor->id)
                                                     ->where('status', 'completed')->count(),
        ];

        // Get recent consultations
        $recentConsultations = Consultation::where('doctor_id', $doctor->id)
                                           ->latest()
                                           ->limit(10)
                                           ->get();

        return view('doctor.dashboard', compact('stats', 'recentConsultations'));
    }
}

