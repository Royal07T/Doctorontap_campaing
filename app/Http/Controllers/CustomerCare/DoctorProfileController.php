<?php

namespace App\Http\Controllers\CustomerCare;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorProfileController extends Controller
{
    /**
     * Display doctors directory
     */
    public function index(Request $request)
    {
        $query = Doctor::query();

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('specialization', 'like', "%{$search}%");
            });
        }

        if ($request->has('specialization') && $request->specialization !== '') {
            $query->where('specialization', $request->specialization);
        }

        $doctors = $query->withCount(['consultations'])
            ->latest()
            ->paginate(20);

        $specializations = Doctor::whereNotNull('specialization')
            ->distinct()
            ->pluck('specialization');

        return view('customer-care.doctors.index', compact('doctors', 'specializations'));
    }

    /**
     * Display doctor dossier
     */
    public function show(Doctor $doctor)
    {
        $doctor->load([
            'consultations.patient',
            'reviews.patientReviewer',
            'reviews.doctorReviewer'
        ]);
        
        // Fetch communications for this doctor
        $communications = \DB::table('patient_communications')
            ->where('doctor_id', $doctor->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('customer-care.doctors.show', compact('doctor', 'communications'));
    }
}
