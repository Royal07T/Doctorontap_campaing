<?php

namespace App\Http\Controllers\Canvasser;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display canvasser dashboard
     */
    public function index()
    {
        $stats = [
            'total_consultations' => Consultation::count(),
            'pending_consultations' => Consultation::where('status', 'pending')->count(),
            'completed_consultations' => Consultation::where('status', 'completed')->count(),
        ];

        return view('canvasser.dashboard', compact('stats'));
    }
}

