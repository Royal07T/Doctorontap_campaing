<?php

namespace App\Http\Controllers\Canvasser;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display canvasser dashboard
     */
    public function index()
    {
        $canvasser = Auth::guard('canvasser')->user();
        
        // Get statistics
        $stats = [
            'total_patients' => Patient::where('canvasser_id', $canvasser->id)->count(),
            'consulted_patients' => Patient::where('canvasser_id', $canvasser->id)
                                           ->where('has_consulted', true)->count(),
            'total_amount' => Patient::where('canvasser_id', $canvasser->id)
                                     ->sum('total_amount_paid'),
            'total_consultations' => Consultation::where('canvasser_id', $canvasser->id)->count(),
        ];

        // Get recent patients
        $recentPatients = Patient::where('canvasser_id', $canvasser->id)
                                 ->latest()
                                 ->limit(10)
                                 ->get();

        return view('canvasser.dashboard', compact('stats', 'recentPatients'));
    }

    /**
     * Store a new patient
     */
    public function storePatient(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:patients,email',
                'phone' => 'required|string|max:20',
                'gender' => 'required|in:male,female',
            ]);

            $canvasser = Auth::guard('canvasser')->user();

            $patient = Patient::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'gender' => $validated['gender'],
                'canvasser_id' => $canvasser->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Patient registered successfully!',
                'patient' => $patient
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register patient: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View all patients registered by this canvasser
     */
    public function patients(Request $request)
    {
        $canvasser = Auth::guard('canvasser')->user();
        
        $query = Patient::where('canvasser_id', $canvasser->id);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Filter by consultation status
        if ($request->filled('status')) {
            if ($request->status === 'consulted') {
                $query->where('has_consulted', true);
            } elseif ($request->status === 'not_consulted') {
                $query->where('has_consulted', false);
            }
        }
        
        $patients = $query->latest()->paginate(15);
        
        return view('canvasser.patients', compact('patients'));
    }
}

