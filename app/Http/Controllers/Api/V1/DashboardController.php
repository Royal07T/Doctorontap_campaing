<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Get dashboard data based on user type
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userType = $user->getMorphClass();

        $data = [];

        switch ($userType) {
            case 'Patient':
                $data = $this->getPatientDashboard($user);
                break;
            case 'Doctor':
                $data = $this->getDoctorDashboard($user);
                break;
            case 'AdminUser':
                $data = $this->getAdminDashboard($user);
                break;
            case 'Nurse':
                $data = $this->getNurseDashboard($user);
                break;
            default:
                $data = ['message' => 'Dashboard data not available for this user type'];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    protected function getPatientDashboard($patient)
    {
        return [
            'patient' => $patient,
            'stats' => [
                'total_consultations' => Consultation::where('patient_id', $patient->id)->count(),
                'pending_consultations' => Consultation::where('patient_id', $patient->id)->where('status', 'pending')->count(),
                'completed_consultations' => Consultation::where('patient_id', $patient->id)->where('status', 'completed')->count(),
            ],
            'recent_consultations' => Consultation::where('patient_id', $patient->id)
                ->with(['doctor'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];
    }

    protected function getDoctorDashboard($doctor)
    {
        return [
            'doctor' => $doctor,
            'stats' => [
                'total_consultations' => Consultation::where('doctor_id', $doctor->id)->count(),
                'pending_consultations' => Consultation::where('doctor_id', $doctor->id)->where('status', 'pending')->count(),
                'in_progress_consultations' => Consultation::where('doctor_id', $doctor->id)->where('status', 'in_progress')->count(),
                'completed_consultations' => Consultation::where('doctor_id', $doctor->id)->where('status', 'completed')->count(),
            ],
            'recent_consultations' => Consultation::where('doctor_id', $doctor->id)
                ->with(['patient'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];
    }

    protected function getAdminDashboard($admin)
    {
        return [
            'admin' => $admin,
            'stats' => [
                'total_consultations' => Consultation::count(),
                'pending_consultations' => Consultation::where('status', 'pending')->count(),
                'total_patients' => Patient::count(),
                'total_doctors' => Doctor::where('is_approved', true)->count(),
            ],
            'recent_consultations' => Consultation::with(['doctor', 'patient'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];
    }

    protected function getNurseDashboard($nurse)
    {
        return [
            'nurse' => $nurse,
            'stats' => [
                'assigned_consultations' => Consultation::where('nurse_id', $nurse->id)->count(),
                'pending_consultations' => Consultation::where('nurse_id', $nurse->id)->where('status', 'pending')->count(),
            ],
            'recent_consultations' => Consultation::where('nurse_id', $nurse->id)
                ->with(['doctor', 'patient'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];
    }
}

