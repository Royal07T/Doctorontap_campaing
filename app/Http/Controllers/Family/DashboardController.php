<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MedicationLog;
use App\Models\Observation;
use App\Models\VitalSign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    // ─── Helpers ──────────────────────────────────────────────────

    private function memberAndPatient(): array
    {
        $member  = Auth::guard('family')->user();
        $patient = $member->patient;
        return [$member, $patient];
    }

    // ─── 1. Dashboard (Family Portal Overview) ────────────────────

    public function index()
    {
        [$member, $patient] = $this->memberAndPatient();

        // Caregiver status
        $activeCaregiver = $patient->assignedCaregivers()
            ->wherePivot('status', 'active')
            ->first();

        $caregiverStatus = $activeCaregiver
            ? [
                'name'       => $activeCaregiver->name,
                'credential' => $activeCaregiver->credential ?? 'RN',
                'checked_in' => $activeCaregiver->pivot->created_at?->format('h:i A') ?? '08:15 AM',
                'online'     => true,
            ]
            : [
                'name'       => 'Not assigned',
                'credential' => '',
                'checked_in' => null,
                'online'     => false,
            ];

        // Current activity — latest observation today
        $currentActivity = Observation::where('patient_id', $patient->id)
            ->whereDate('created_at', today())
            ->latest()
            ->first();

        // Upcoming task — next medication
        $upcomingMed = MedicationLog::where('patient_id', $patient->id)
            ->whereDate('scheduled_time', today())
            ->where('status', '!=', MedicationLog::STATUS_GIVEN)
            ->orderBy('scheduled_time')
            ->first();

        // Alerts feed (recent audit logs + critical vitals)
        $criticalVitals = VitalSign::where('patient_id', $patient->id)
            ->where('flag_status', 'critical')
            ->where('created_at', '>=', now()->subDays(7))
            ->latest()
            ->limit(5)
            ->get();

        $recentLogs = VitalSign::where('patient_id', $patient->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->latest()
            ->limit(5)
            ->get();

        $recentObservations = Observation::where('patient_id', $patient->id)
            ->latest()
            ->limit(5)
            ->get();

        // Care plan
        $carePlan = $patient->activeCarePlan;

        // Family access — other family members for this patient
        $familyAccess = \App\Models\FamilyMember::where('patient_id', $patient->id)
            ->where('id', '!=', $member->id)
            ->where('is_active', true)
            ->get();

        return view('family.dashboard', compact(
            'member',
            'patient',
            'caregiverStatus',
            'currentActivity',
            'upcomingMed',
            'criticalVitals',
            'recentLogs',
            'recentObservations',
            'carePlan',
            'familyAccess',
        ));
    }

    // ─── 2. Alerts & Documents ────────────────────────────────────

    public function alerts(Request $request)
    {
        [$member, $patient] = $this->memberAndPatient();

        $filter = $request->get('filter', 'all');

        $vitalsQuery = VitalSign::where('patient_id', $patient->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->latest();

        if ($filter === 'critical') {
            $vitalsQuery->where('flag_status', 'critical');
        } elseif ($filter === 'vitals') {
            // all vitals (no filter needed)
        }

        $alerts = $vitalsQuery->limit(20)->get();

        $observations = Observation::where('patient_id', $patient->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->latest()
            ->limit(10)
            ->get();

        // Documents — list stored files
        $documents = $this->getPatientDocuments($patient->id);

        return view('family.alerts', compact(
            'member',
            'patient',
            'filter',
            'alerts',
            'observations',
            'documents',
        ));
    }

    // ─── 3. Document Center (standalone) ──────────────────────────

    public function documents()
    {
        [$member, $patient] = $this->memberAndPatient();
        $documents = $this->getPatientDocuments($patient->id);

        return view('family.documents', compact('member', 'patient', 'documents'));
    }

    // ─── 4. Billing & Receipts ────────────────────────────────────

    public function billing()
    {
        [$member, $patient] = $this->memberAndPatient();

        // Mock invoices — replace with real billing model when available
        $invoices = [
            [
                'id'     => '#INV-' . date('Y') . '-012',
                'period' => now()->subDays(16)->format('M d') . ' - ' . now()->format('M d, Y'),
                'amount' => 840.00,
                'status' => 'pending',
            ],
            [
                'id'     => '#INV-' . date('Y') . '-011',
                'period' => now()->subDays(31)->format('M d') . ' - ' . now()->subDays(17)->format('M d, Y'),
                'amount' => 1120.00,
                'status' => 'paid',
            ],
            [
                'id'     => '#INV-' . date('Y') . '-010',
                'period' => now()->subDays(47)->format('M d') . ' - ' . now()->subDays(32)->format('M d, Y'),
                'amount' => 950.00,
                'status' => 'paid',
            ],
        ];

        $pendingTotal = collect($invoices)->where('status', 'pending')->sum('amount');

        return view('family.billing', compact('member', 'patient', 'invoices', 'pendingTotal'));
    }

    // ─── 5. Service History ───────────────────────────────────────

    public function history()
    {
        [$member, $patient] = $this->memberAndPatient();

        // Service timeline from audit logs / consultations
        $serviceHistory = AuditLog::where('resource_id', $patient->id)
            ->whereIn('action', ['quick_vitals_entry', 'daily_health_log', 'consultation_completed', 'medication_given'])
            ->latest()
            ->limit(20)
            ->get();

        // Account summary
        $accountSummary = [
            'monthly_spend'  => '$2,840.00',
            'next_auto_pay'  => now()->endOfMonth()->format('M d, Y'),
            'sessions_logged' => $serviceHistory->count(),
        ];

        return view('family.history', compact('member', 'patient', 'serviceHistory', 'accountSummary'));
    }

    // ─── 6. Portal Settings ───────────────────────────────────────

    public function settings()
    {
        [$member, $patient] = $this->memberAndPatient();
        return view('family.settings', compact('member', 'patient'));
    }

    // ─── 6a. Update Profile ───────────────────────────────────────

    public function updateProfile(Request $request)
    {
        $member = Auth::guard('family')->user();

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|max:255|unique:family_members,email,' . $member->id,
            'phone'        => 'nullable|string|max:20',
            'relationship' => 'nullable|string|max:100',
        ]);

        $member->update($validated);

        return redirect()->route('family.settings')->with('profile_success', 'Profile updated successfully.');
    }

    // ─── 6b. Update Password ──────────────────────────────────────

    public function updatePassword(Request $request)
    {
        $member = Auth::guard('family')->user();

        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $member->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $member->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('family.settings')->with('password_success', 'Password updated successfully.');
    }

    // ─── 7. Reports (kept for backward compat) ────────────────────

    public function reports()
    {
        [$member, $patient] = $this->memberAndPatient();

        $reportDir = "weekly-reports/{$patient->id}";
        $files     = [];

        if (Storage::disk('local')->exists($reportDir)) {
            $rawFiles = Storage::disk('local')->files($reportDir);
            foreach ($rawFiles as $file) {
                $files[] = [
                    'name' => basename($file, '.pdf'),
                    'path' => $file,
                    'date' => basename($file, '.pdf'),
                ];
            }
            usort($files, fn ($a, $b) => strcmp($b['date'], $a['date']));
        }

        return view('family.reports', compact('member', 'patient', 'files'));
    }

    public function downloadReport(string $date)
    {
        [$member, $patient] = $this->memberAndPatient();
        $path = "weekly-reports/{$patient->id}/{$date}.pdf";

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'Report not found.');
        }

        return Storage::disk('local')->download($path, "health-report-{$date}.pdf");
    }

    // ─── Private helpers ──────────────────────────────────────────

    private function getPatientDocuments(int $patientId): array
    {
        $categories = [
            'monthly_care_logs'      => "documents/{$patientId}/care-logs",
            'medical_receipts'       => "documents/{$patientId}/receipts",
            'consent_and_agreements' => "documents/{$patientId}/agreements",
        ];

        $documents = [];
        foreach ($categories as $key => $dir) {
            $documents[$key] = [];
            if (Storage::disk('local')->exists($dir)) {
                foreach (Storage::disk('local')->files($dir) as $file) {
                    $documents[$key][] = [
                        'name' => basename($file),
                        'path' => $file,
                        'size' => round(Storage::disk('local')->size($file) / 1024, 1) . ' KB',
                        'date' => date('M d', Storage::disk('local')->lastModified($file)),
                    ];
                }
            }
        }

        return $documents;
    }
}
