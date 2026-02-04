<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\PatientMedicalHistory;
use App\Models\Specialty;
use App\Models\Doctor;
use App\Models\Setting;
use App\Models\MenstrualCycle;
use App\Models\MenstrualDailyLog;
use App\Models\SexualHealthRecord;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Mail\ConsultationConfirmation;
use App\Mail\ConsultationAdminAlert;
use App\Mail\ConsultationDoctorNotification;
use App\Notifications\ConsultationSmsNotification;

class DashboardController extends Controller
{
    /**
     * Display patient dashboard
     */
    public function index()
    {
        $patient = Auth::guard('patient')->user();
        
        // Get latest vitals for health snapshot
        $latestVitals = $patient->latestVitalSigns;
        
        // Statistics
        $stats = [
            'total_consultations' => $patient->consultations()->count(),
            'completed_consultations' => $patient->consultations()->where('status', 'completed')->count(),
            'pending_consultations' => $patient->consultations()->where('status', 'pending')->count(),
            'total_paid' => $patient->consultations()
                ->where('payment_status', 'paid')
                ->with('payment')
                ->get()
                ->sum(function($consultation) {
                    return $consultation->payment ? $consultation->payment->amount : 0;
                }),
            'unpaid_consultations' => $patient->consultations()
                ->where('status', 'completed')
                ->where('payment_status', '!=', 'paid')
                ->count(),
        ];

        // Recent consultations
        $recentConsultations = $patient->consultations()
            ->with(['doctor', 'payment'])
            ->latest()
            ->limit(5)
            ->get();

        // Dependents (if patient is a guardian)
        $dependents = $patient->dependents()->get();

        // Upcoming/pending consultations
        $upcomingConsultations = $patient->consultations()
            ->whereIn('status', ['pending', 'scheduled'])
            ->latest()
            ->limit(3)
            ->get();

        // Get all active specialties from database for carousel
        $specializations = Specialty::active()
            ->orderBy('name')
            ->pluck('name');
        
        // If no specialties found in database, fallback to doctor specializations
        if ($specializations->isEmpty()) {
            $specializations = \App\Models\Doctor::whereNotNull('specialization')
                ->where('specialization', '!=', '')
                ->where('is_approved', true)
                ->distinct()
                ->orderBy('specialization')
                ->pluck('specialization');
        }

        // Symptoms with their related specializations and icons
        $symptoms = [
            ['name' => 'Period Doubts or Pregnancy', 'specialization' => 'Obstetrician & Gynecologist (OB-GYN)', 'icon' => 'menstruation-pregnancy', 'color' => '#FF6B9D'],
            ['name' => 'Acne, Pimple or Skin Issues', 'specialization' => 'Dermatologist', 'icon' => 'acne-skin', 'color' => '#FF9F66'],
            ['name' => 'Performance Issues in Bed', 'specialization' => 'Urologist', 'icon' => 'performance', 'color' => '#9333EA'],
            ['name' => 'Cold, Cough or Fever', 'specialization' => 'General Practitioner (GP)', 'icon' => 'cold-cough', 'color' => '#3B82F6'],
            ['name' => 'Child Not Feeling Well', 'specialization' => 'Pediatrician', 'icon' => 'child-sick', 'color' => '#F59E0B'],
            ['name' => 'Depression or Anxiety', 'specialization' => 'Psychiatrist', 'icon' => 'depression-anxiety', 'color' => '#EF4444'],
            ['name' => 'Headache', 'specialization' => 'Neurologist', 'icon' => 'headache', 'color' => '#6D597A'],
            ['name' => 'Stomach Pain', 'specialization' => 'Gastroenterologist', 'icon' => 'stomach-pain', 'color' => '#2A9D8F'],
            ['name' => 'Back Pain', 'specialization' => 'Orthopedic Specialist', 'icon' => 'back-pain', 'color' => '#264653'],
            ['name' => 'Eye Problems', 'specialization' => 'Ophthalmologist', 'icon' => 'eye-problems', 'color' => '#1D3557'],
            ['name' => 'Ear Pain', 'specialization' => 'ENT Specialist (Otolaryngologist)', 'icon' => 'ear-pain', 'color' => '#8D99AE'],
            ['name' => 'Joint Pain', 'specialization' => 'Rheumatologist', 'icon' => 'joint-pain', 'color' => '#588157'],
            ['name' => 'Chest Pain', 'specialization' => 'Cardiologist', 'icon' => 'chest-pain', 'color' => '#C1121F'],
        ];

        // Get menstrual cycle data for female patients
        $menstrualCycles = collect([]);
        $currentCycle = null;
        $nextPeriodPrediction = null;
        $nextOvulationPrediction = null;
        $fertileWindowStart = null;
        $fertileWindowEnd = null;
        $averageCycleLength = null;
        $averagePeriodLength = null;
        $latestSpouseNumber = null;
        
        if (strtolower($patient->gender) === 'female') {
            $menstrualCycles = \App\Models\MenstrualCycle::where('patient_id', $patient->id)
                ->orderBy('start_date', 'desc')
                ->limit(12)
                ->get();
            
            // Get current/active cycle (period that hasn't ended or ended within last 7 days)
            $currentCycle = \App\Models\MenstrualCycle::where('patient_id', $patient->id)
                ->where(function($query) {
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', now()->subDays(7));
                })
                ->orderBy('start_date', 'desc')
                ->first();
            
            // Get latest spouse number from any cycle for pre-filling the form
            $latestCycleWithSpouse = \App\Models\MenstrualCycle::where('patient_id', $patient->id)
                ->whereNotNull('spouse_number')
                ->where('spouse_number', '!=', '')
                ->orderBy('start_date', 'desc')
                ->first();
            
            if ($latestCycleWithSpouse) {
                $latestSpouseNumber = $latestCycleWithSpouse->spouse_number;
            }
            
            // Calculate average cycle length (from start of one period to start of next)
            if ($menstrualCycles->count() >= 2) {
                $cycleLengths = [];
                $periodLengths = [];
                
                for ($i = 0; $i < $menstrualCycles->count() - 1; $i++) {
                    $current = $menstrualCycles[$i];
                    $previous = $menstrualCycles[$i + 1];
                    
                    if ($current->start_date && $previous->start_date) {
                        $cycleLengths[] = $current->start_date->diffInDays($previous->start_date);
                    }
                    
                    if ($current->period_length) {
                        $periodLengths[] = $current->period_length;
                    } elseif ($current->start_date && $current->end_date) {
                        $periodLengths[] = $current->start_date->diffInDays($current->end_date) + 1;
                    }
                }
                
                if (!empty($cycleLengths)) {
                    $averageCycleLength = round(array_sum($cycleLengths) / count($cycleLengths));
                }
                
                if (!empty($periodLengths)) {
                    $averagePeriodLength = round(array_sum($periodLengths) / count($periodLengths));
                }
            }
            
            // Default values if no history
            $averageCycleLength = $averageCycleLength ?? 28; // Average cycle is 28 days
            $averagePeriodLength = $averagePeriodLength ?? 5; // Average period is 5 days
            
            // Ensure averageCycleLength is never zero to prevent division by zero
            if ($averageCycleLength <= 0) {
                $averageCycleLength = 28;
            }
            
            // Predict next period (based on last period START date + average cycle length)
            // Cycle length is from start of one period to start of next period
            $lastPeriodStart = null;
            
            if ($menstrualCycles->isNotEmpty()) {
                // Use the most recent period's start date
                $lastPeriodStart = $menstrualCycles->first()->start_date;
            }
            
            if ($lastPeriodStart && $averageCycleLength > 0) {
                // Next period starts after average cycle length from last period start
                $nextPeriodPrediction = $lastPeriodStart->copy()->addDays($averageCycleLength);
                
                // Only show prediction if it's in the future
                if ($nextPeriodPrediction->isPast()) {
                    // If prediction is in the past, calculate from today or next cycle
                    $daysSinceLastPeriod = now()->diffInDays($lastPeriodStart);
                    if ($averageCycleLength > 0) {
                        $cyclesSinceLastPeriod = floor($daysSinceLastPeriod / $averageCycleLength);
                        $nextPeriodPrediction = $lastPeriodStart->copy()->addDays($averageCycleLength * ($cyclesSinceLastPeriod + 1));
                    } else {
                        // Fallback: just add one cycle length
                        $nextPeriodPrediction = $lastPeriodStart->copy()->addDays(28);
                    }
                }
                
                // Calculate ovulation (typically 14 days before next period)
                // Ovulation occurs approximately 14 days before the next period starts
                $nextOvulationPrediction = $nextPeriodPrediction->copy()->subDays(14);
                
                // Fertile window: 5 days before ovulation to 1 day after (sperm can live up to 5 days)
                $fertileWindowStart = $nextOvulationPrediction->copy()->subDays(5);
                $fertileWindowEnd = $nextOvulationPrediction->copy()->addDay();
            }
        }

        // Get sexual health data for male patients
        $sexualHealthRecords = collect([]);
        $latestSexualHealthRecord = null;
        $stiTestDue = false;
        $nextStiTestDate = null;
        $daysUntilStiTest = null;
        
        if (strtolower($patient->gender) === 'male') {
            $sexualHealthRecords = SexualHealthRecord::where('patient_id', $patient->id)
                ->orderBy('record_date', 'desc')
                ->limit(6)
                ->get();
            
            $latestSexualHealthRecord = SexualHealthRecord::where('patient_id', $patient->id)
                ->orderBy('record_date', 'desc')
                ->first();
            
            // Check if STI test is due (recommended every 6 months)
            if ($latestSexualHealthRecord && $latestSexualHealthRecord->last_sti_test_date) {
                $nextStiTestDate = $latestSexualHealthRecord->last_sti_test_date->copy()->addMonths(6);
                $stiTestDue = $nextStiTestDate->isPast();
                // Calculate days as integer (rounded)
                $daysUntilStiTest = (int) round(now()->diffInDays($nextStiTestDate, false));
            } elseif (!$latestSexualHealthRecord || !$latestSexualHealthRecord->last_sti_test_date) {
                // Never tested or no test date recorded
                $stiTestDue = true;
            }
        }

        // Quick Contacts - Most recently consulted doctors or most consulted doctors for new patients
        $quickContacts = collect();
        
        // Get doctors from recent consultations (only completed ones with doctors)
        $recentConsultationDoctors = $patient->consultations()
            ->whereNotNull('doctor_id')
            ->where('status', 'completed')
            ->with('doctor')
            ->orderBy('created_at', 'desc')
            ->get()
            ->pluck('doctor')
            ->filter(function($doctor) {
                return $doctor && $doctor->is_approved && $doctor->is_available;
            })
            ->unique('id')
            ->take(2);
        
        if ($recentConsultationDoctors->isNotEmpty()) {
            $quickContacts = $recentConsultationDoctors;
        } else {
            // For new patients, get most consulted doctors overall
            $quickContacts = Doctor::where('is_approved', true)
                ->where('is_available', true)
                ->withCount(['consultations as consultations_count' => function($query) {
                    $query->where('status', 'completed');
                }])
                ->orderBy('consultations_count', 'desc')
                ->orderBy('name')
                ->limit(2)
                ->get();
        }

        // Daily Health Tip (can be made dynamic later)
        $dailyHealthTip = "Stress management positively impacts sexual wellness. Try spending 10 minutes today on focused breathing exercises.";

        return view('patient.dashboard', compact('patient', 'stats', 'recentConsultations', 'dependents', 'upcomingConsultations', 'specializations', 'symptoms', 'menstrualCycles', 'currentCycle', 'nextPeriodPrediction', 'nextOvulationPrediction', 'fertileWindowStart', 'fertileWindowEnd', 'averageCycleLength', 'averagePeriodLength', 'sexualHealthRecords', 'latestSexualHealthRecord', 'stiTestDue', 'nextStiTestDate', 'daysUntilStiTest', 'latestSpouseNumber', 'latestVitals', 'quickContacts', 'dailyHealthTip'));
    }

    /**
     * Export patient history
     */
    public function exportHistory()
    {
        $patient = Auth::guard('patient')->user();
        
        // Get all consultations
        $consultations = $patient->consultations()
            ->with(['doctor', 'payment'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get medical records
        $medicalHistories = $patient->medicalHistories()
            ->orderBy('consultation_date', 'desc')
            ->get();
        
        // Get vital signs
        $vitalSigns = $patient->vitalSigns()
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Generate CSV content
        $filename = 'patient_history_' . $patient->id . '_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($patient, $consultations, $medicalHistories, $vitalSigns) {
            $file = fopen('php://output', 'w');
            
            // Patient Information
            fputcsv($file, ['PATIENT HEALTH HISTORY EXPORT']);
            fputcsv($file, ['Generated on: ' . now()->format('F d, Y h:i A')]);
            fputcsv($file, []);
            fputcsv($file, ['PATIENT INFORMATION']);
            fputcsv($file, ['Name', $patient->name]);
            fputcsv($file, ['Email', $patient->email]);
            fputcsv($file, ['Phone', $patient->mobile ?? 'N/A']);
            fputcsv($file, ['Date of Birth', $patient->date_of_birth ? $patient->date_of_birth->format('Y-m-d') : 'N/A']);
            fputcsv($file, ['Gender', $patient->gender ?? 'N/A']);
            fputcsv($file, []);
            
            // Consultations
            fputcsv($file, ['CONSULTATIONS']);
            fputcsv($file, ['Date', 'Doctor', 'Specialization', 'Status', 'Payment Status', 'Reference', 'Problem']);
            foreach ($consultations as $consultation) {
                fputcsv($file, [
                    $consultation->created_at->format('Y-m-d H:i'),
                    $consultation->doctor ? $consultation->doctor->name : 'N/A',
                    $consultation->doctor ? $consultation->doctor->specialization : 'N/A',
                    $consultation->status,
                    $consultation->payment_status,
                    $consultation->reference,
                    $consultation->problem ?? 'N/A'
                ]);
            }
            fputcsv($file, []);
            
            // Medical Records
            fputcsv($file, ['MEDICAL RECORDS']);
            fputcsv($file, ['Date', 'Diagnosis', 'Treatment Plan']);
            foreach ($medicalHistories as $history) {
                fputcsv($file, [
                    $history->consultation_date->format('Y-m-d'),
                    $history->diagnosis ?? 'N/A',
                    $history->treatment_plan ?? 'N/A'
                ]);
            }
            fputcsv($file, []);
            
            // Vital Signs
            fputcsv($file, ['VITAL SIGNS']);
            fputcsv($file, ['Date', 'Blood Pressure', 'Temperature', 'Heart Rate', 'Weight', 'Height']);
            foreach ($vitalSigns as $vital) {
                fputcsv($file, [
                    $vital->created_at->format('Y-m-d H:i'),
                    $vital->blood_pressure ?? 'N/A',
                    $vital->temperature ?? 'N/A',
                    $vital->heart_rate ?? 'N/A',
                    $vital->weight ?? 'N/A',
                    $vital->height ?? 'N/A'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Search functionality for patient portal
     */
    public function search(Request $request)
    {
        $patient = Auth::guard('patient')->user();
        $query = $request->get('q', '');
        $results = [
            'consultations' => collect(),
            'doctors' => collect(),
            'medical_records' => collect(),
        ];

        if ($query) {
            // Search consultations
            $results['consultations'] = $patient->consultations()
                ->where(function($q) use ($query) {
                    $q->where('reference', 'like', "%{$query}%")
                      ->orWhere('problem', 'like', "%{$query}%")
                      ->orWhere('diagnosis', 'like', "%{$query}%");
                })
                ->orWhereHas('doctor', function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('specialization', 'like', "%{$query}%");
                })
                ->with('doctor')
                ->latest()
                ->limit(10)
                ->get();

            // Search doctors
            $results['doctors'] = \App\Models\Doctor::where('is_approved', true)
                ->where('is_available', true)
                ->where(function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('specialization', 'like', "%{$query}%");
                })
                ->limit(10)
                ->get();

            // Search medical records
            $results['medical_records'] = $patient->medicalHistories()
                ->where(function($q) use ($query) {
                    $q->where('diagnosis', 'like', "%{$query}%")
                      ->orWhere('treatment_plan', 'like', "%{$query}%")
                      ->orWhere('chief_complaint', 'like', "%{$query}%");
                })
                ->latest('consultation_date')
                ->limit(10)
                ->get();
        }

        return view('patient.search', compact('query', 'results'));
    }

    /**
     * Display all available doctors
     */
    public function doctors(Request $request)
    {
        $query = \App\Models\Doctor::where('is_approved', true)
            ->where('is_available', true);

        // Filter by specialization if provided
        if ($request->filled('specialization')) {
            $specialization = urldecode($request->specialization);
            $specializationMap = [
                'General Practice (Family Medicine)' => ['General Practice', 'General Practitioner', 'General Practitional'],
                'General Practitioner' => ['General Practitioner', 'General Practice', 'General Practitional'],
                'General Practice' => ['General Practice', 'General Practitioner', 'General Practitional'],
            ];
            
            $searchTerms = $specializationMap[$specialization] ?? [$specialization];
            
            $query->where(function($q) use ($specialization, $searchTerms) {
                $q->where('specialization', $specialization)
                  ->orWhereRaw('LOWER(TRIM(specialization)) = ?', [strtolower(trim($specialization))]);
                
                if (count($searchTerms) > 1 || $searchTerms[0] !== $specialization) {
                    foreach ($searchTerms as $term) {
                        $q->orWhere('specialization', $term)
                          ->orWhereRaw('LOWER(TRIM(specialization)) = ?', [strtolower(trim($term))]);
                    }
                }
            });
        }

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('specialization', 'like', "%{$search}%");
            });
        }

        // Order by consultation count (most consulted first), then by average rating, then by name
        $doctors = $query->withCount(['consultations as consultations_count'])
            ->withCount(['reviews as published_reviews_count' => function($q) {
                $q->where('is_published', true);
            }])
            ->orderBy('consultations_count', 'desc')
            ->orderBy('name')
            ->paginate(12);
        $specializations = \App\Models\Doctor::where('is_approved', true)
            ->where('is_available', true)
            ->whereNotNull('specialization')
            ->distinct()
            ->orderBy('specialization')
            ->pluck('specialization');

        return view('patient.doctors', compact('doctors', 'specializations'));
    }

    /**
     * Display doctors by specialization
     */
    public function doctorsBySpecialization($specialization)
    {
        // Decode URL-encoded specialization (e.g., "General+Practice+%28Family+Medicine%29" -> "General Practice (Family Medicine)")
        $specialization = urldecode($specialization);
        
        // Map common variations to database values
        $specializationMap = [
            'General Practice (Family Medicine)' => ['General Practice', 'General Practitioner', 'General Practitional'],
            'General Practitioner' => ['General Practitioner', 'General Practice', 'General Practitional'],
            'General Practice' => ['General Practice', 'General Practitioner', 'General Practitional'],
        ];
        
        // Check if we have a mapping for this specialization
        $searchTerms = $specializationMap[$specialization] ?? [$specialization];
        
        // Query doctors with this specialization (case-insensitive, trimmed)
        // Try exact match first, then case-insensitive match, then mapped variations
        $doctors = \App\Models\Doctor::where(function($query) use ($specialization, $searchTerms) {
                // Exact match
                $query->where('specialization', $specialization)
                      // Case-insensitive match
                      ->orWhereRaw('LOWER(TRIM(specialization)) = ?', [strtolower(trim($specialization))]);
                
                // If we have mapped terms, also search for those
                if (count($searchTerms) > 1 || $searchTerms[0] !== $specialization) {
                    foreach ($searchTerms as $term) {
                        $query->orWhere('specialization', $term)
                              ->orWhereRaw('LOWER(TRIM(specialization)) = ?', [strtolower(trim($term))]);
                    }
                }
            })
            ->where('is_approved', true)
            ->where('is_available', true)
            ->orderBy('name')
            ->get();

        return view('patient.doctors-by-specialization', compact('doctors', 'specialization'));
    }

    /**
     * Display doctors by symptom
     */
    public function doctorsBySymptom($symptom)
    {
        // Map symptoms to specializations (using database specialty names)
        $symptomMap = [
            'period-doubts-or-pregnancy' => ['Obstetrics & Gynecology (OB/GYN)', 'Obstetrician & Gynecologist (OB-GYN)'],
            'acne-pimple-or-skin-issues' => ['Dermatologist', 'Dermatology'],
            'performance-issues-in-bed' => ['Urologist', 'Urology'],
            'cold-cough-or-fever' => ['General Practitioner (GP)', 'General Practice (Family Medicine)', 'General Practice', 'General Practitioner', 'Internal Medicine'],
            'child-not-feeling-well' => ['Pediatrics', 'Pediatrician'],
            'depression-or-anxiety' => ['Psychiatrist', 'Psychiatry', 'Therapist'],
            'headache' => ['Neurologist', 'Neurology'],
            'stomach-pain' => ['Gastroenterologist', 'Gastroenterology'],
            'back-pain' => ['Orthopedic Specialist', 'Orthopaedics'],
            'eye-problems' => ['Ophthalmologist', 'Ophthalmology'],
            'ear-pain' => ['ENT Specialist (Otolaryngologist)', 'ENT (Otolaryngology)'],
            'joint-pain' => ['Orthopedic Specialist', 'Orthopaedics', 'Rheumatologist'],
            'chest-pain' => ['Cardiologist', 'Cardiology'],
            // Legacy mappings for backward compatibility
            'menstruation-flow' => ['Obstetrics & Gynecology (OB/GYN)', 'Obstetrician & Gynecologist (OB-GYN)'],
            'rashes' => ['Dermatologist', 'Dermatology'],
            'cough' => ['General Practitioner (GP)', 'General Practice (Family Medicine)', 'Internal Medicine'],
            'fever' => ['General Practitioner (GP)', 'General Practice (Family Medicine)', 'Internal Medicine'],
            'skin-issues' => ['Dermatologist', 'Dermatology'],
        ];
        
        // Normalize the symptom slug: handle commas, spaces, and other special characters
        $symptom = strtolower(preg_replace('/[^a-z0-9]+/', '-', str_replace([' ', ','], '-', $symptom)));
        $symptom = trim($symptom, '-'); // Remove leading/trailing hyphens

        $specializations = $symptomMap[$symptom] ?? null;
        
        if (!$specializations) {
            abort(404, 'Symptom not found');
        }

        // Query doctors with any of the mapped specializations (case-insensitive, trimmed)
        $doctors = \App\Models\Doctor::where(function($query) use ($specializations) {
                foreach ($specializations as $index => $specialization) {
                    if ($index === 0) {
                        // Exact match
                        $query->where('specialization', $specialization)
                              // Case-insensitive match
                              ->orWhereRaw('LOWER(TRIM(specialization)) = ?', [strtolower(trim($specialization))]);
                    } else {
                        // Exact match
                        $query->orWhere('specialization', $specialization)
                              // Case-insensitive match
                              ->orWhereRaw('LOWER(TRIM(specialization)) = ?', [strtolower(trim($specialization))]);
                    }
                }
            })
            ->where('is_approved', true)
            ->where('is_available', true)
            ->orderBy('name')
            ->get();
            
        // Use the first specialization for display
        $specialization = $specializations[0];

        // Format symptom name: replace hyphens with spaces, handle "or" properly
        $symptomName = ucwords(str_replace('-', ' ', $symptom));
        // Fix common patterns
        $symptomName = str_replace(' Or ', ' or ', $symptomName);

        return view('patient.doctors-by-specialization', compact('doctors', 'specialization', 'symptomName'));
    }

    /**
     * Display all consultations
     */
    public function consultations(Request $request)
    {
        $patient = Auth::guard('patient')->user();
        
        $query = $patient->consultations()->with(['doctor', 'payment', 'reviews']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Search by reference or doctor name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('doctor', function($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $consultations = $query->latest()->paginate(15);

        // Statistics
        $stats = [
            'total' => $patient->consultations()->count(),
            'completed' => $patient->consultations()->where('status', 'completed')->count(),
            'pending' => $patient->consultations()->where('status', 'pending')->count(),
            'paid' => $patient->consultations()->where('payment_status', 'paid')->count(),
            'unpaid' => $patient->consultations()
                ->where('status', 'completed')
                ->where('payment_status', '!=', 'paid')
                ->count(),
            'total_paid' => $patient->consultations()
                ->where('payment_status', 'paid')
                ->with('payment')
                ->get()
                ->sum(function($consultation) {
                    return $consultation->payment ? $consultation->payment->amount : 0;
                }),
        ];

        // Favorite/Distinct Doctors Count
        $favoriteDoctorsCount = $patient->consultations()
            ->whereNotNull('doctor_id')
            ->distinct('doctor_id')
            ->count('doctor_id');

        // Next Appointment
        $nextAppointment = $patient->consultations()
            ->where('status', 'scheduled')
            ->where(function($q) {
                $q->where('scheduled_at', '>', now())
                  ->orWhereNull('scheduled_at'); // Include pending scheduling if needed, but primarily future scheduled
            })
            ->whereNotNull('scheduled_at') // Strict check for next appointment time
            ->orderBy('scheduled_at', 'asc')
            ->first();

        return view('patient.consultations', compact('consultations', 'stats', 'favoriteDoctorsCount', 'nextAppointment', 'patient'));
    }

    /**
     * View single consultation
     */
    public function viewConsultation($id)
    {
        $patient = Auth::guard('patient')->user();
        
        $consultation = $patient->consultations()
            ->with(['doctor', 'payment'])
            ->findOrFail($id);

        // Mark treatment plan as accessed if it's accessible
        if ($consultation->isTreatmentPlanAccessible()) {
            $consultation->markTreatmentPlanAccessed();
        }

        return view('patient.consultation-details', compact('consultation'));
    }

    /**
     * Display medical records
     */
    public function medicalRecords()
    {
        $patient = Auth::guard('patient')->user();
        
        // Backfill medical history for existing consultations that haven't been synced yet
        // This ensures all treatment plans are reflected in medical records
        $historyService = app(\App\Services\PatientMedicalHistoryService::class);
        
        // Backfill by patient_id
        $syncedByPatientId = $historyService->backfillPatientMedicalHistory($patient);
        
        // Also backfill by email in case some consultations don't have patient_id set
        $syncedByEmail = $historyService->backfillMedicalHistoryByEmail($patient->email);
        
        if ($syncedByPatientId > 0 || $syncedByEmail > 0) {
            \Illuminate\Support\Facades\Log::info('Backfilled medical history for patient', [
                'patient_id' => $patient->id,
                'synced_by_patient_id' => $syncedByPatientId,
                'synced_by_email' => $syncedByEmail
            ]);
        }
        
        $medicalHistories = $patient->medicalHistories()
            ->with('consultation.doctor')
            ->latest('consultation_date')
            ->paginate(10);

        $latestVitals = $patient->latestVitalSigns;

        $stats = [
            'total_records' => $patient->medicalHistories()->count(),
            'total_vital_signs' => $patient->vitalSigns()->count(),
            'last_consultation' => $patient->last_consultation_at,
        ];

        return view('patient.medical-records', compact('medicalHistories', 'latestVitals', 'stats'));
    }

    /**
     * Display profile/settings
     */
    public function profile()
    {
        $patient = Auth::guard('patient')->user();
        
        return view('patient.profile', compact('patient'));
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $patient = Auth::guard('patient')->user();

        // Check if this is a medical information update (no name/phone in request)
        // Since the patient is authenticated, we already have their name, email, and phone
        $isMedicalUpdate = !$request->has('name') && !$request->has('phone');
        
        if ($isMedicalUpdate) {
            // Only validate medical information fields
            // Patient name, email, and phone are already in database from authentication
            $validated = $request->validate([
                'blood_group' => 'nullable|string|max:10|in:A+,A-,B+,B-,AB+,AB-,O+,O-,Unknown',
                'genotype' => 'nullable|string|max:10|in:AA,AS,AC,SS,SC,CC,Unknown',
                'allergies' => 'nullable|string|max:2000',
                'chronic_conditions' => 'nullable|string|max:2000',
                'current_medications' => 'nullable|string|max:2000',
                'surgical_history' => 'nullable|string|max:2000',
                'family_medical_history' => 'nullable|string|max:2000',
                'emergency_contact_name' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|string|max:20',
                'emergency_contact_relationship' => 'nullable|string|max:100',
                'height' => 'nullable|string|max:10',
                'weight' => 'nullable|string|max:10',
                'medical_notes' => 'nullable|string|max:5000',
            ]);
        } else {
            // Validate personal information (and optionally medical info if present)
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'gender' => 'nullable|in:male,female',
                'date_of_birth' => 'nullable|date|before:today',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                // Medical Information (optional when updating personal info)
                'blood_group' => 'nullable|string|max:10|in:A+,A-,B+,B-,AB+,AB-,O+,O-,Unknown',
                'genotype' => 'nullable|string|max:10|in:AA,AS,AC,SS,SC,CC,Unknown',
                'allergies' => 'nullable|string|max:2000',
                'chronic_conditions' => 'nullable|string|max:2000',
                'current_medications' => 'nullable|string|max:2000',
                'surgical_history' => 'nullable|string|max:2000',
                'family_medical_history' => 'nullable|string|max:2000',
                'emergency_contact_name' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|string|max:20',
                'emergency_contact_relationship' => 'nullable|string|max:100',
                'height' => 'nullable|string|max:10',
                'weight' => 'nullable|string|max:10',
                'medical_notes' => 'nullable|string|max:5000',
            ]);
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            try {
                // Delete old photo if exists
                if ($patient->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($patient->photo)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($patient->photo);
                }

                // Store new photo
                $photo = $request->file('photo');
                $fileName = \Illuminate\Support\Str::slug($patient->name) . '-' . time() . '.' . $photo->getClientOriginalExtension();
                
                // Use putFileAs which properly handles the file stream
                // This stores the file at storage/app/public/patients/filename.jpg
                // and returns the path 'patients/filename.jpg'
                $path = \Illuminate\Support\Facades\Storage::disk('public')->putFileAs('patients', $photo, $fileName);
                
                // Verify the file was stored
                if ($path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                    $validated['photo'] = $path;
                    
                    \Log::info('Patient photo uploaded successfully', [
                        'patient_id' => $patient->id,
                        'photo_path' => $path,
                        'url' => \Illuminate\Support\Facades\Storage::url($path)
                    ]);
                } else {
                    \Log::error('Patient photo upload failed - file not found after storage', [
                        'patient_id' => $patient->id,
                        'photo_name' => $fileName,
                        'path' => $path
                    ]);
                    
                    return redirect()->back()->with('error', 'Failed to upload photo. Please try again.');
                }
            } catch (\Exception $e) {
                \Log::error('Patient photo upload exception', [
                    'patient_id' => $patient->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return redirect()->back()->with('error', 'Failed to upload photo: ' . $e->getMessage());
            }
        }

        // Update only the fields that are present in the validated data
        // Since patient is authenticated, name, email, and phone already exist in database
        // We only need to update the medical information fields that were submitted
        $updateData = [];
        foreach ($validated as $key => $value) {
            // For text fields (allergies, chronic_conditions, etc.), allow empty strings
            // User might want to clear them
            $textFields = ['allergies', 'chronic_conditions', 'current_medications', 'surgical_history', 
                          'family_medical_history', 'emergency_contact_name', 'emergency_contact_phone', 
                          'emergency_contact_relationship', 'height', 'weight', 'medical_notes'];
            
            if (in_array($key, $textFields)) {
                // Allow empty strings for text fields (user might want to clear them)
                $updateData[$key] = $value ?? null;
            } elseif ($value !== null && $value !== '') {
                // For other fields (blood_group, genotype, etc.), only include non-null and non-empty values
                $updateData[$key] = $value;
            }
        }
        
        if (!empty($updateData)) {
            try {
                $patient->update($updateData);
                
                \Log::info('Patient profile updated', [
                    'patient_id' => $patient->id,
                    'patient_name' => $patient->name,
                    'patient_email' => $patient->email,
                    'patient_phone' => $patient->phone,
                    'updated_fields' => array_keys($updateData),
                    'is_medical_update' => $isMedicalUpdate,
                    'update_data' => $updateData
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to update patient profile', [
                    'patient_id' => $patient->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return redirect()->back()->with('error', 'Failed to update profile. Please try again.');
            }
        } else {
            \Log::warning('No data to update for patient profile', [
                'patient_id' => $patient->id,
                'validated_data' => $validated
            ]);
        }

        $message = $isMedicalUpdate 
            ? 'Medical information updated successfully!' 
            : 'Profile updated successfully!';
            
        return redirect()->back()->with('success', $message);
    }

    /**
     * Display dependents (children/family members)
     */
    public function dependents()
    {
        $patient = Auth::guard('patient')->user();
        
        $dependents = $patient->dependents()->with('consultations')->get();

        return view('patient.dependents', compact('dependents'));
    }

    /**
     * Display payments history
     */
    public function payments()
    {
        $patient = Auth::guard('patient')->user();
        
        // Get all consultations (both paid and unpaid)
        $consultations = $patient->consultations()
            ->with(['doctor', 'payment'])
            ->latest()
            ->paginate(15);

        // Calculate total paid from actual payments in database
        // Query payments table directly by joining with consultations for accurate amount
        $totalPaid = DB::table('payments')
            ->join('consultations', 'payments.id', '=', 'consultations.payment_id')
            ->where('consultations.patient_id', $patient->id)
            ->where('consultations.payment_status', 'paid')
            ->sum('payments.amount');

        // Get pending payments (unpaid consultations)
        $pendingConsultations = $patient->consultations()
            ->with('doctor')
            ->where(function($query) {
                $query->where('payment_status', '!=', 'paid')
                      ->orWhereNull('payment_status');
            })
            ->where(function($query) {
                $query->where('status', 'completed')
                      ->orWhere('status', 'pending_payment');
            })
            ->latest()
            ->get();

        $stats = [
            'total_paid' => $totalPaid,
            'paid_consultations' => $patient->consultations()->where('payment_status', 'paid')->count(),
            'pending_payments' => $pendingConsultations->count(),
        ];

        return view('patient.payments', compact('consultations', 'pendingConsultations', 'stats'));
    }

    /**
     * Initiate payment for a consultation
     */
    public function initiatePayment($id)
    {
        $patient = Auth::guard('patient')->user();
        
        $consultation = $patient->consultations()
            ->with('doctor')
            ->findOrFail($id);

        // Check if already paid
        if ($consultation->isPaid()) {
            return redirect()->route('patient.payments')
                ->with('error', 'This consultation has already been paid for.');
        }

        // Check if doctor is assigned
        if (!$consultation->doctor) {
            return redirect()->route('patient.payments')
                ->with('error', 'No doctor assigned to this consultation yet.');
        }

        // Determine fee based on consultation type
        $fee = 0;
        if ($consultation->consultation_type === 'pay_now') {
            $fee = \App\Models\Setting::get('consultation_fee_pay_now', \App\Models\Setting::get('pay_now_consultation_fee', 4500));
        } elseif ($consultation->consultation_type === 'pay_later') {
            $fee = \App\Models\Setting::get('consultation_fee_pay_later', \App\Models\Setting::get('pay_later_consultation_fee', 5000));
        } else {
            // Fallback to doctor's effective fee
            $fee = $consultation->doctor->effective_consultation_fee ?? 0;
        }

        if ($fee <= 0) {
            return redirect()->route('patient.payments')
                ->with('error', 'No payment is required for this consultation.');
        }

        // Redirect to payment request page with source parameter to track where payment was initiated
        return redirect()->route('payment.request', [
            'reference' => $consultation->reference,
            'source' => 'dashboard'
        ]);
    }

    /**
     * View receipt for a paid consultation
     */
    public function viewReceipt($id)
    {
        $patient = Auth::guard('patient')->user();
        
        $consultation = $patient->consultations()
            ->with(['doctor', 'payment'])
            ->findOrFail($id);

        // Check if consultation has been paid
        if (!$consultation->payment || $consultation->payment_status !== 'paid') {
            return redirect()->route('patient.payments')
                ->with('error', 'Receipt is only available for paid consultations.');
        }

        return view('patient.receipt', compact('consultation'));
    }

    /**
     * Sanitize all input data
     */
    private function sanitizeInputs(array $inputs): array
    {
        $sanitized = [];
        
        foreach ($inputs as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = $this->sanitizeText($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitize text input - removes HTML tags, XSS attempts, and normalizes whitespace
     */
    private function sanitizeText(?string $text): ?string
    {
        if (empty($text)) {
            return $text;
        }
        
        // Remove HTML tags and PHP tags
        $text = strip_tags($text);
        
        // Remove null bytes and other control characters (except newlines and tabs)
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        
        // Normalize whitespace (multiple spaces to single space)
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Trim whitespace
        $text = trim($text);
        
        // Escape special characters for database storage
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8', false);
        
        return $text;
    }

    /**
     * Sanitize array input
     */
    private function sanitizeArray(array $array): array
    {
        $sanitized = [];
        
        foreach ($array as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = $this->sanitizeText($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitize file name to prevent directory traversal and other attacks
     */
    private function sanitizeFileName(string $fileName): string
    {
        // Remove path components
        $fileName = basename($fileName);
        
        // Remove any remaining directory separators
        $fileName = str_replace(['/', '\\', '..'], '', $fileName);
        
        // Remove special characters except alphanumeric, dots, hyphens, and underscores
        $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
        
        // Limit length
        $fileName = substr($fileName, 0, 255);
        
        return $fileName;
    }

    /**
     * Show menstrual cycle
     */
    public function showMenstrualCycle($id)
    {
        $patient = Auth::guard('patient')->user();
        
        $cycle = MenstrualCycle::where('patient_id', $patient->id)
            ->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'cycle' => $cycle,
        ]);
    }

    /**
     * Store or update menstrual cycle
     */
    public function storeMenstrualCycle(Request $request)
    {
        $patient = Auth::guard('patient')->user();
        
        // Only allow for female patients
        if (strtolower($patient->gender) !== 'female') {
            return response()->json(['error' => 'This feature is only available for female patients.'], 403);
        }
        
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'period_length' => 'nullable|integer|min:1|max:10',
            'flow_intensity' => 'nullable|in:light,moderate,heavy',
            'symptoms' => 'nullable|array',
            'notes' => 'nullable|string|max:500',
            'spouse_number' => 'nullable|string|max:20',
        ]);
        
        // Sanitize inputs
        $validated['notes'] = isset($validated['notes']) ? $this->sanitizeText($validated['notes']) : null;
        
        // Calculate period length if not provided
        if (!isset($validated['period_length']) && isset($validated['start_date']) && isset($validated['end_date'])) {
            $start = \Carbon\Carbon::parse($validated['start_date']);
            $end = \Carbon\Carbon::parse($validated['end_date']);
            $validated['period_length'] = $start->diffInDays($end) + 1;
        }
        
        // Check if there's an active cycle that should be ended
        $activeCycle = MenstrualCycle::where('patient_id', $patient->id)
            ->whereNull('end_date')
            ->where('start_date', '<', $validated['start_date'])
            ->first();
        
        if ($activeCycle) {
            // End the previous cycle
            $activeCycle->update([
                'end_date' => \Carbon\Carbon::parse($validated['start_date'])->subDay(),
                'period_length' => $activeCycle->calculatePeriodLength(),
                'cycle_length' => $activeCycle->calculateCycleLength(),
            ]);
        }
        
        // Normalize spouse number if provided
        $spouseNumber = null;
        if (!empty($validated['spouse_number'])) {
            $spouseNumber = preg_replace('/[^0-9+]/', '', $validated['spouse_number']);
            // Ensure it starts with + if it's an international number, or add country code if needed
            if (!empty($spouseNumber) && !str_starts_with($spouseNumber, '+')) {
                // If it starts with 0, replace with country code (assuming Nigeria +234)
                if (str_starts_with($spouseNumber, '0')) {
                    $spouseNumber = '+234' . substr($spouseNumber, 1);
                } elseif (str_starts_with($spouseNumber, '234')) {
                    $spouseNumber = '+' . $spouseNumber;
                } else {
                    // Assume local number, add country code
                    $spouseNumber = '+234' . $spouseNumber;
                }
            }
        }
        
        // Create new cycle
        $cycle = MenstrualCycle::create([
            'patient_id' => $patient->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'period_length' => $validated['period_length'] ?? null,
            'flow_intensity' => $validated['flow_intensity'] ?? null,
            'symptoms' => $validated['symptoms'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'spouse_number' => $spouseNumber,
        ]);
        
        // Calculate cycle length
        $cycle->cycle_length = $cycle->calculateCycleLength();
        $cycle->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Menstrual cycle recorded successfully.',
            'cycle' => $cycle->load('patient'),
        ]);
    }

    /**
     * Update menstrual cycle
     */
    public function updateMenstrualCycle(Request $request, $id)
    {
        $patient = Auth::guard('patient')->user();
        
        $cycle = MenstrualCycle::where('patient_id', $patient->id)
            ->findOrFail($id);
        
        $validated = $request->validate([
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'period_length' => 'nullable|integer|min:1|max:10',
            'flow_intensity' => 'nullable|in:light,moderate,heavy',
            'symptoms' => 'nullable|array',
            'notes' => 'nullable|string|max:500',
            'spouse_number' => 'nullable|string|max:20',
        ]);
        
        // Sanitize inputs
        if (isset($validated['notes'])) {
            $validated['notes'] = $this->sanitizeText($validated['notes']);
        }
        
        // Normalize spouse number if provided
        if (isset($validated['spouse_number'])) {
            if (!empty($validated['spouse_number'])) {
                $spouseNumber = preg_replace('/[^0-9+]/', '', $validated['spouse_number']);
                // Ensure it starts with + if it's an international number, or add country code if needed
                if (!empty($spouseNumber) && !str_starts_with($spouseNumber, '+')) {
                    // If it starts with 0, replace with country code (assuming Nigeria +234)
                    if (str_starts_with($spouseNumber, '0')) {
                        $spouseNumber = '+234' . substr($spouseNumber, 1);
                    } elseif (str_starts_with($spouseNumber, '234')) {
                        $spouseNumber = '+' . $spouseNumber;
                    } else {
                        // Assume local number, add country code
                        $spouseNumber = '+234' . $spouseNumber;
                    }
                }
                $validated['spouse_number'] = $spouseNumber;
            } else {
                $validated['spouse_number'] = null;
            }
        }
        
        // Calculate period length if not provided
        if (!isset($validated['period_length']) && isset($validated['start_date']) && isset($validated['end_date'])) {
            $start = \Carbon\Carbon::parse($validated['start_date']);
            $end = \Carbon\Carbon::parse($validated['end_date']);
            $validated['period_length'] = $start->diffInDays($end) + 1;
        }
        
        $cycle->update($validated);
        
        // Recalculate cycle length
        $cycle->cycle_length = $cycle->calculateCycleLength();
        $cycle->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Menstrual cycle updated successfully.',
            'cycle' => $cycle->load('patient'),
        ]);
    }

    /**
     * Delete menstrual cycle
     */
    public function deleteMenstrualCycle($id)
    {
        $patient = Auth::guard('patient')->user();
        
        // Only allow for female patients
        if (strtolower($patient->gender) !== 'female') {
            return response()->json(['error' => 'This feature is only available for female patients.'], 403);
        }
        
        $cycle = MenstrualCycle::where('patient_id', $patient->id)
            ->findOrFail($id);
        
        $cycle->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Menstrual cycle deleted successfully.',
        ]);
    }

    /**
     * Store sexual health record
     */
    public function storeSexualHealthRecord(Request $request)
    {
        $patient = Auth::guard('patient')->user();
        
        // Only allow for male patients
        if (strtolower($patient->gender) !== 'male') {
            return response()->json(['error' => 'This feature is only available for male patients.'], 403);
        }
        
        $validated = $request->validate([
            'record_date' => 'required|date',
            'libido_level' => 'nullable|in:low,normal,high',
            'erectile_health_score' => 'nullable|integer|min:1|max:10',
            'ejaculation_issues' => 'nullable|boolean',
            'ejaculation_notes' => 'nullable|string|max:500',
            'last_sti_test_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        // Sanitize inputs
        if (isset($validated['ejaculation_notes'])) {
            $validated['ejaculation_notes'] = $this->sanitizeText($validated['ejaculation_notes']);
        }
        if (isset($validated['notes'])) {
            $validated['notes'] = $this->sanitizeText($validated['notes']);
        }
        
        // Calculate next STI test reminder (6 months from last test)
        if (isset($validated['last_sti_test_date'])) {
            $validated['next_sti_test_reminder'] = \Carbon\Carbon::parse($validated['last_sti_test_date'])->addMonths(6);
            $validated['sti_test_due'] = $validated['next_sti_test_reminder']->isPast();
        }
        
        $record = SexualHealthRecord::create([
            'patient_id' => $patient->id,
            'record_date' => $validated['record_date'],
            'libido_level' => $validated['libido_level'] ?? null,
            'erectile_health_score' => $validated['erectile_health_score'] ?? null,
            'ejaculation_issues' => $validated['ejaculation_issues'] ?? false,
            'ejaculation_notes' => $validated['ejaculation_notes'] ?? null,
            'last_sti_test_date' => $validated['last_sti_test_date'] ?? null,
            'next_sti_test_reminder' => $validated['next_sti_test_reminder'] ?? null,
            'sti_test_due' => $validated['sti_test_due'] ?? false,
            'notes' => $validated['notes'] ?? null,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Sexual health record saved successfully.',
            'record' => $record->load('patient'),
        ]);
    }

    /**
     * Update sexual health record
     */
    public function updateSexualHealthRecord(Request $request, $id)
    {
        $patient = Auth::guard('patient')->user();
        
        $record = SexualHealthRecord::where('patient_id', $patient->id)
            ->findOrFail($id);
        
        $validated = $request->validate([
            'record_date' => 'sometimes|required|date',
            'libido_level' => 'nullable|in:low,normal,high',
            'erectile_health_score' => 'nullable|integer|min:1|max:10',
            'ejaculation_issues' => 'nullable|boolean',
            'ejaculation_notes' => 'nullable|string|max:500',
            'last_sti_test_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        // Sanitize inputs
        if (isset($validated['ejaculation_notes'])) {
            $validated['ejaculation_notes'] = $this->sanitizeText($validated['ejaculation_notes']);
        }
        if (isset($validated['notes'])) {
            $validated['notes'] = $this->sanitizeText($validated['notes']);
        }
        
        // Recalculate STI test reminder if date changed
        if (isset($validated['last_sti_test_date'])) {
            $validated['next_sti_test_reminder'] = \Carbon\Carbon::parse($validated['last_sti_test_date'])->addMonths(6);
            $validated['sti_test_due'] = $validated['next_sti_test_reminder']->isPast();
        }
        
        $record->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Sexual health record updated successfully.',
            'record' => $record->load('patient'),
        ]);
    }

    /**
     * Get doctor availability for booking
     */
    public function getDoctorAvailability($doctorId)
    {
        try {
            $doctor = Doctor::where('id', $doctorId)
                ->where('is_approved', true)
                ->where('is_available', true)
                ->firstOrFail();

            $schedule = $doctor->availability_schedule ?? [];
            
            // Get existing consultations for this doctor to check conflicts
            $existingConsultations = Consultation::where('doctor_id', $doctor->id)
                ->whereIn('status', ['pending', 'scheduled'])
                ->whereNotNull('scheduled_at')
                ->where('scheduled_at', '>=', now())
                ->get()
                ->map(function($consultation) {
                    return [
                        'date' => $consultation->scheduled_at->format('Y-m-d'),
                        'time' => $consultation->scheduled_at->format('H:i'),
                        'datetime' => $consultation->scheduled_at->format('Y-m-d H:i:s'),
                    ];
                });

            return response()->json([
                'success' => true,
                'doctor' => [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                    'specialization' => $doctor->specialization,
                ],
                'availability_schedule' => $schedule,
                'booked_slots' => $existingConsultations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found or unavailable'
            ], 404);
        }
    }

    /**
     * Check if a time slot is available
     */
    public function checkTimeSlotAvailability(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'scheduled_at' => 'required|date|after:now',
        ]);

        $doctor = Doctor::findOrFail($validated['doctor_id']);
        $scheduledAt = \Carbon\Carbon::parse($validated['scheduled_at']);
        $dayOfWeek = strtolower($scheduledAt->format('l')); // monday, tuesday, etc.
        $time = $scheduledAt->format('H:i');

        // Check if doctor is available on this day
        $schedule = $doctor->availability_schedule ?? [];
        $daySchedule = $schedule[$dayOfWeek] ?? null;

        if (!$daySchedule || !($daySchedule['enabled'] ?? false)) {
            return response()->json([
                'success' => false,
                'available' => false,
                'message' => 'Doctor is not available on this day'
            ]);
        }

        // Check if time is within doctor's availability window
        $startTime = \Carbon\Carbon::parse($daySchedule['start']);
        $endTime = \Carbon\Carbon::parse($daySchedule['end']);
        $requestTime = \Carbon\Carbon::parse($time);

        if ($requestTime->lt($startTime) || $requestTime->gte($endTime)) {
            return response()->json([
                'success' => false,
                'available' => false,
                'message' => 'Time slot is outside doctor\'s availability hours'
            ]);
        }

        // Check for conflicts with existing consultations
        // Use lockForUpdate to prevent race conditions when checking availability
        $conflict = DB::transaction(function() use ($doctor, $scheduledAt) {
            return Consultation::where('doctor_id', $doctor->id)
                ->whereIn('status', ['pending', 'scheduled'])
                ->whereNotNull('scheduled_at')
                ->where('scheduled_at', '>=', now())
                ->whereBetween('scheduled_at', [
                    $scheduledAt->copy()->subMinutes(29), // 30-minute buffer
                    $scheduledAt->copy()->addMinutes(29)
                ])
                ->lockForUpdate() // Prevent concurrent bookings
                ->exists();
        });

        if ($conflict) {
            Log::info('Time slot conflict detected during availability check', [
                'event_type' => 'time_slot_conflict_check',
                'doctor_id' => $doctor->id,
                'scheduled_at' => $scheduledAt->toIso8601String(),
                'timestamp' => now()->toIso8601String()
            ]);
            
            return response()->json([
                'success' => false,
                'available' => false,
                'message' => 'This time slot is already booked. Please choose another time.'
            ]);
        }

        return response()->json([
            'success' => true,
            'available' => true,
            'message' => 'Time slot is available'
        ]);
    }

    /**
     * Create consultation with scheduled time
     */
    public function createScheduledConsultation(Request $request)
    {
        $patient = Auth::guard('patient')->user();

        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'scheduled_at' => 'required|date|after:now',
            'consult_mode' => 'required|in:voice,video,chat',
            'problem' => 'required|string|min:10|max:1000',
            'severity' => 'required|in:mild,moderate,severe',
            'emergency_symptoms' => 'nullable|array',
            'emergency_symptoms.*' => 'nullable|string',
            'medical_documents' => 'nullable|array',
            'medical_documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        $doctor = Doctor::findOrFail($validated['doctor_id']);

        // Verify doctor is available
        if (!$doctor->is_available || !$doctor->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor is not available for booking'
            ], 400);
        }

        // Check time slot availability
        $scheduledAt = \Carbon\Carbon::parse($validated['scheduled_at']);
        $dayOfWeek = strtolower($scheduledAt->format('l'));
        $schedule = $doctor->availability_schedule ?? [];
        $daySchedule = $schedule[$dayOfWeek] ?? null;

        if (!$daySchedule || !($daySchedule['enabled'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor is not available on this day'
            ], 400);
        }

        // CONCURRENCY PROTECTION: Use database-level locking to prevent time conflicts
        // This ensures no two patients can book the same time slot simultaneously
        try {
            DB::beginTransaction();
            
            // Lock consultations for this doctor in the time window to prevent race conditions
            $conflict = Consultation::where('doctor_id', $doctor->id)
                ->whereIn('status', ['pending', 'scheduled'])
                ->whereNotNull('scheduled_at')
                ->where('scheduled_at', '>=', now())
                ->whereBetween('scheduled_at', [
                    $scheduledAt->copy()->subMinutes(29),
                    $scheduledAt->copy()->addMinutes(29)
                ])
                ->lockForUpdate() // Database-level lock to prevent concurrent bookings
                ->exists();

            if ($conflict) {
                DB::rollBack();
                
                Log::warning('Booking conflict detected', [
                    'event_type' => 'booking_conflict',
                    'doctor_id' => $doctor->id,
                    'patient_id' => $patient->id,
                    'scheduled_at' => $scheduledAt->toIso8601String(),
                    'timestamp' => now()->toIso8601String()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'This time slot was just booked by another patient. Please choose another time.',
                    'error' => 'time_slot_conflict'
                ], 409); // 409 Conflict
            }

            // Handle medical document uploads - HIPAA: Store in private storage
            $uploadedDocuments = [];
            if ($request->hasFile('medical_documents')) {
                try {
                    foreach ($request->file('medical_documents') as $file) {
                        $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                        // Store in private storage (storage/app/private/medical_documents)
                        $filePath = $file->storeAs('medical_documents', $fileName);
                        
                        $uploadedDocuments[] = [
                            'original_name' => $file->getClientOriginalName(),
                            'stored_name' => $fileName,
                            'path' => $filePath,
                            'size' => $file->getSize(),
                            'mime_type' => $file->getMimeType(),
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to upload medical documents', [
                        'event_type' => 'medical_document_upload_failed',
                        'patient_id' => $patient->id,
                        'doctor_id' => $doctor->id,
                        'error' => $e->getMessage(),
                        'timestamp' => now()->toIso8601String()
                    ]);
                    // Continue without documents rather than failing completely
                }
            }

            // Map consult_mode to consultation_mode enum
            $consultMode = $validated['consult_mode'];
            $consultationMode = in_array($consultMode, ['voice', 'video', 'chat']) ? $consultMode : 'whatsapp';
            
            // Create consultation with proper consultation_mode
            $consultation = Consultation::create([
                'reference' => 'CONS-' . strtoupper(Str::random(8)),
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'first_name' => $patient->first_name,
                'last_name' => $patient->last_name,
                'email' => $patient->email,
                'mobile' => $patient->phone,
                'age' => $patient->age,
                'gender' => $patient->gender,
                'problem' => $validated['problem'],
                'medical_documents' => !empty($uploadedDocuments) ? $uploadedDocuments : null,
                'severity' => $validated['severity'],
                'emergency_symptoms' => $validated['emergency_symptoms'] ?? null,
                'consult_mode' => $consultMode, // Legacy field
                'consultation_mode' => $consultationMode, // New enum field
                'status' => 'scheduled',
                'session_status' => 'scheduled', // For in-app consultations
                'payment_status' => 'unpaid',
                'scheduled_at' => $scheduledAt,
                'consultation_type' => 'pay_later', // Scheduled appointments use pay_later by default
            ]);
            
            // Create Vonage session if consultation is in-app mode
            if ($consultation->isInAppMode()) {
                try {
                    $sessionService = app(\App\Services\ConsultationSessionService::class);
                    $sessionResult = $sessionService->createSession($consultation);
                    
                    if (!$sessionResult['success']) {
                        Log::warning('Failed to create consultation session during booking', [
                            'event_type' => 'session_creation_failed_booking',
                            'consultation_id' => $consultation->id,
                            'mode' => $consultationMode,
                            'error' => $sessionResult['error'] ?? 'unknown',
                            'timestamp' => now()->toIso8601String()
                        ]);
                        // Don't fail booking if session creation fails
                    }
                } catch (\Exception $e) {
                    Log::error('Exception creating session during booking', [
                        'event_type' => 'session_creation_exception_booking',
                        'consultation_id' => $consultation->id,
                        'error' => $e->getMessage(),
                        'timestamp' => now()->toIso8601String()
                    ]);
                    // Don't fail booking if session creation fails
                }
            }
            
            DB::commit();
            
            // Create notifications for both patient and doctor
            try {
                // Notification for patient - personalized message
                $patientNotification = \App\Models\Notification::create([
                    'user_type' => 'patient',
                    'user_id' => $patient->id,
                    'title' => '✅ Appointment Booked Successfully',
                    'message' => "Your appointment with Dr. {$doctor->name} has been scheduled for " . $scheduledAt->format('M d, Y h:i A') . ". Consultation Reference: {$consultation->reference}. Please arrive on time.",
                    'type' => 'success',
                    'action_url' => route('patient.consultation.view', ['id' => $consultation->id]),
                    'data' => [
                        'consultation_id' => $consultation->id,
                        'consultation_reference' => $consultation->reference,
                        'doctor_name' => $doctor->name,
                        'doctor_specialization' => $doctor->specialization,
                        'scheduled_at' => $scheduledAt->toDateTimeString(),
                        'consultation_mode' => $consultationMode,
                        'type' => 'appointment_booked',
                        'notification_for' => 'patient'
                    ]
                ]);

                Log::info('Patient notification created for booking', [
                    'event_type' => 'patient_notification_created',
                    'notification_id' => $patientNotification->id,
                    'user_type' => 'patient',
                    'user_id' => $patient->id,
                    'consultation_id' => $consultation->id,
                    'message_preview' => substr($patientNotification->message, 0, 50),
                    'timestamp' => now()->toIso8601String()
                ]);

                // Notification for doctor - personalized message
                $patientFullName = trim($patient->first_name . ' ' . $patient->last_name) ?: $patient->name;
                $doctorNotification = \App\Models\Notification::create([
                    'user_type' => 'doctor',
                    'user_id' => $doctor->id,
                    'title' => '📅 New Patient Appointment',
                    'message' => "Patient {$patientFullName} has booked a consultation with you scheduled for " . $scheduledAt->format('M d, Y h:i A') . ". Consultation Reference: {$consultation->reference}. Please review the consultation details.",
                    'type' => 'info',
                    'action_url' => route('doctor.consultations.view', ['id' => $consultation->id]),
                    'data' => [
                        'consultation_id' => $consultation->id,
                        'consultation_reference' => $consultation->reference,
                        'patient_name' => $patientFullName,
                        'patient_id' => $patient->id,
                        'scheduled_at' => $scheduledAt->toDateTimeString(),
                        'consultation_mode' => $consultationMode,
                        'type' => 'new_appointment',
                        'notification_for' => 'doctor'
                    ]
                ]);

                Log::info('Doctor notification created for booking', [
                    'event_type' => 'doctor_notification_created',
                    'notification_id' => $doctorNotification->id,
                    'user_type' => 'doctor',
                    'user_id' => $doctor->id,
                    'consultation_id' => $consultation->id,
                    'message_preview' => substr($doctorNotification->message, 0, 50),
                    'timestamp' => now()->toIso8601String()
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create booking notifications', [
                    'event_type' => 'notification_creation_failed',
                    'consultation_id' => $consultation->id,
                    'error' => $e->getMessage(),
                    'timestamp' => now()->toIso8601String()
                ]);
            }

            // Send confirmation emails to both patient and doctor
            try {
                // Prepare patient email data
                $patientEmailData = [
                    'consultation_reference' => $consultation->reference,
                    'first_name' => $patient->first_name,
                    'last_name' => $patient->last_name,
                    'email' => $patient->email,
                    'mobile' => $patient->phone,
                    'age' => $patient->age,
                    'gender' => $patient->gender,
                    'problem' => $validated['problem'],
                    'severity' => $validated['severity'],
                    'emergency_symptoms' => $validated['emergency_symptoms'] ?? null,
                    'consult_mode' => $consultMode,
                    'consultation_mode' => $consultationMode,
                    'has_documents' => !empty($uploadedDocuments),
                    'documents_count' => count($uploadedDocuments),
                    'doctor_name' => $doctor->name,
                    'doctor_specialization' => $doctor->specialization,
                    'scheduled_at' => $scheduledAt->format('M d, Y h:i A'),
                    'scheduled_at_datetime' => $scheduledAt->toDateTimeString(),
                    'is_scheduled' => true,
                ];

                // Send confirmation email to patient
                Mail::to($patient->email)->send(new ConsultationConfirmation($patientEmailData));
                
                Log::info('Patient confirmation email sent successfully', [
                    'event_type' => 'patient_confirmation_email_sent',
                    'consultation_id' => $consultation->id,
                    'consultation_reference' => $consultation->reference,
                    'patient_email' => $patient->email,
                    'timestamp' => now()->toIso8601String()
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to send patient confirmation email', [
                    'event_type' => 'patient_email_send_failed',
                    'consultation_id' => $consultation->id,
                    'patient_email' => $patient->email ?? 'N/A',
                    'error' => $e->getMessage(),
                    'timestamp' => now()->toIso8601String()
                ]);
                // Don't fail booking if email fails
            }

            // Send notification email to doctor
            try {
                if ($doctor->email) {
                    $doctorEmailData = [
                        'consultation_reference' => $consultation->reference,
                        'patient_name' => $patient->name,
                        'patient_first_name' => $patient->first_name,
                        'patient_last_name' => $patient->last_name,
                        'patient_email' => $patient->email,
                        'patient_mobile' => $patient->phone,
                        'patient_age' => $patient->age,
                        'patient_gender' => $patient->gender,
                        'problem' => $validated['problem'],
                        'severity' => $validated['severity'],
                        'emergency_symptoms' => $validated['emergency_symptoms'] ?? null,
                        'consult_mode' => $consultMode,
                        'consultation_mode' => $consultationMode,
                        'has_documents' => !empty($uploadedDocuments),
                        'documents_count' => count($uploadedDocuments),
                        'doctor_name' => $doctor->name,
                        'scheduled_at' => $scheduledAt->format('M d, Y h:i A'),
                        'scheduled_at_datetime' => $scheduledAt->toDateTimeString(),
                        'is_scheduled' => true,
                    ];

                    Mail::to($doctor->email)->send(new ConsultationDoctorNotification($doctorEmailData));
                    
                    Log::info('Doctor notification email sent successfully', [
                        'event_type' => 'doctor_notification_email_sent',
                        'consultation_id' => $consultation->id,
                        'consultation_reference' => $consultation->reference,
                        'doctor_email' => $doctor->email,
                        'timestamp' => now()->toIso8601String()
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to send doctor notification email', [
                    'event_type' => 'doctor_email_send_failed',
                    'consultation_id' => $consultation->id,
                    'doctor_email' => $doctor->email ?? 'N/A',
                    'error' => $e->getMessage(),
                    'timestamp' => now()->toIso8601String()
                ]);
                // Don't fail booking if email fails
            }

            Log::info('Scheduled consultation created successfully', [
                'event_type' => 'consultation_scheduled',
                'consultation_id' => $consultation->id,
                'consultation_reference' => $consultation->reference,
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'scheduled_at' => $scheduledAt->toIso8601String(),
                'consultation_mode' => $consultationMode,
                'timestamp' => now()->toIso8601String()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment booked successfully! Reference: ' . $consultation->reference,
                'consultation' => [
                    'id' => $consultation->id,
                    'reference' => $consultation->reference,
                    'scheduled_at' => $consultation->scheduled_at->format('Y-m-d H:i:s'),
                    'consultation_mode' => $consultationMode,
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create scheduled consultation', [
                'event_type' => 'consultation_booking_failed',
                'doctor_id' => $doctor->id ?? null,
                'patient_id' => $patient->id ?? null,
                'scheduled_at' => $scheduledAt->toIso8601String() ?? null,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toIso8601String()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to book appointment. Please try again or contact support.',
                'error' => 'booking_failed'
            ], 500);
        }
    }

    /**
     * Delete sexual health record
     */
    public function deleteSexualHealthRecord($id)
    {
        $patient = Auth::guard('patient')->user();
        
        // Only allow for male patients
        if (strtolower($patient->gender) !== 'male') {
            return response()->json(['error' => 'This feature is only available for male patients.'], 403);
        }
        
        $record = SexualHealthRecord::where('patient_id', $patient->id)
            ->findOrFail($id);
        
        $record->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Sexual health record deleted successfully.',
        ]);
    }

    /**
     * Show Menstrual Cycle Tracker Page
     */
    public function cycleTracker()
    {
        $patient = Auth::guard('patient')->user();
        
        if (strtolower($patient->gender) !== 'female') {
            return redirect()->route('patient.dashboard')->with('error', 'This feature is only available for female patients.');
        }

        $cycles = MenstrualCycle::where('patient_id', $patient->id)
            ->orderBy('start_date', 'desc')
            ->get();

        $dailyLogs = MenstrualDailyLog::where('patient_id', $patient->id)
            ->orderBy('date', 'desc')
            ->get();

        $currentCycle = MenstrualCycle::where('patient_id', $patient->id)
            ->where(function($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now()->subDays(7));
            })
            ->orderBy('start_date', 'desc')
            ->first();

        // Calculate predictions (same as in index method)
        $nextPeriodPrediction = null;
        $averageCycleLength = 28; // Default
        
        if ($cycles->count() >= 2) {
            $cycleLengths = [];
            for ($i = 0; $i < $cycles->count() - 1; $i++) {
                $current = $cycles[$i];
                $previous = $cycles[$i + 1];
                $cycleLengths[] = \Carbon\Carbon::parse($previous->start_date)->diffInDays(\Carbon\Carbon::parse($current->start_date));
            }
            $averageCycleLength = !empty($cycleLengths) ? round(array_sum($cycleLengths) / count($cycleLengths)) : 28;
        }

        if ($cycles->count() > 0) {
            $latestCycle = $cycles->first();
            $nextPeriodPrediction = \Carbon\Carbon::parse($latestCycle->start_date)->addDays($averageCycleLength);
        }

        return view('patient.cycle-tracker', compact('cycles', 'dailyLogs', 'currentCycle', 'nextPeriodPrediction', 'averageCycleLength'));
    }

    /**
     * Store Menstrual Daily Log
     */
    public function storeDailyLog(Request $request)
    {
        $patient = Auth::guard('patient')->user();
        
        $validated = $request->validate([
            'date' => 'required|date',
            'mood' => 'nullable|string',
            'flow' => 'nullable|string',
            'sleep' => 'nullable|integer',
            'water' => 'nullable|integer',
            'urination' => 'nullable|integer',
            'eating_habits' => 'nullable|integer',
            'symptoms' => 'nullable|array',
            'notes' => 'nullable|string|max:500',
        ]);

        $log = MenstrualDailyLog::updateOrCreate(
            ['patient_id' => $patient->id, 'date' => $validated['date']],
            array_merge($validated, ['patient_id' => $patient->id])
        );

        return response()->json([
            'success' => true,
            'message' => 'Daily log saved successfully!',
            'log' => $log
        ]);
    }

    /**
     * Display all available caregivers
     */
    public function caregivers(Request $request)
    {
        $query = \App\Models\CareGiver::where('is_active', true);

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $caregivers = $query->paginate(12);

        return view('patient.caregivers', compact('caregivers'));
    }
}
