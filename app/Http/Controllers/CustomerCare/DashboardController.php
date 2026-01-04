<?php

namespace App\Http\Controllers\CustomerCare;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display customer care dashboard
     */
    public function index()
    {
        $customerCare = Auth::guard('customer_care')->user();
        
        // Get statistics
        $stats = [
            'total_consultations' => Consultation::where('customer_care_id', $customerCare->id)->count(),
            'pending_consultations' => Consultation::where('customer_care_id', $customerCare->id)
                                                  ->where('status', 'pending')->count(),
            'scheduled_consultations' => Consultation::where('customer_care_id', $customerCare->id)
                                                     ->where('status', 'scheduled')->count(),
            'completed_consultations' => Consultation::where('customer_care_id', $customerCare->id)
                                                    ->where('status', 'completed')->count(),
            'cancelled_consultations' => Consultation::where('customer_care_id', $customerCare->id)
                                                    ->where('status', 'cancelled')->count(),
        ];

        // Get recent consultations
        $recentConsultations = Consultation::where('customer_care_id', $customerCare->id)
                                          ->with(['doctor', 'patient'])
                                          ->latest()
                                          ->limit(10)
                                          ->get();

        return view('customer-care.dashboard', compact('stats', 'recentConsultations'));
    }

    /**
     * Display consultations list
     */
    public function consultations(Request $request)
    {
        $customerCare = Auth::guard('customer_care')->user();
        
        $query = Consultation::where('customer_care_id', $customerCare->id)
                            ->with(['doctor', 'patient', 'payment']);

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status != '') {
            $query->where('payment_status', $request->payment_status);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $consultations = $query->latest()->paginate(20);

        return view('customer-care.consultations', compact('consultations'));
    }

    /**
     * Display consultation details
     */
    public function showConsultation($id)
    {
        $customerCare = Auth::guard('customer_care')->user();
        
        $consultation = Consultation::where('customer_care_id', $customerCare->id)
                                   ->with(['doctor', 'patient', 'payment', 'canvasser', 'nurse'])
                                   ->findOrFail($id);

        return view('customer-care.consultation-details', compact('consultation'));
    }
}
