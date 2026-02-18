<?php

namespace App\Http\Controllers\CareGiver;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Observation;
use App\Models\Patient;
use App\Models\VitalSign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    public function storeQuickVitals(Request $request)
    {
        $careGiver = Auth::guard('care_giver')->user();

        $data = $request->validate([
            'patient_id'   => 'required|exists:patients,id',
            'bp_systolic'  => 'required|integer|min:60|max:260',
            'bp_diastolic' => 'required|integer|min:30|max:160',
            'heart_rate'   => 'required|integer|min:20|max:220',
            'spo2'         => 'required|numeric|min:40|max:100',
            'temperature'  => 'nullable|numeric|min:90|max:110',
        ]);

        $patient = Patient::findOrFail($data['patient_id']);

        if (!$careGiver->isAssignedToPatient($patient->id)) {
            abort(403, 'You are not assigned to that patient');
        }

        $bloodPressure = $data['bp_systolic'] . '/' . $data['bp_diastolic'];
        $flagStatus = $this->determineFlagStatus($bloodPressure, $data['spo2'], $data['heart_rate']);

        $vital = VitalSign::create([
            'patient_id'       => $patient->id,
            'caregiver_id'     => $careGiver->id,
            'blood_pressure'   => $bloodPressure,
            'heart_rate'       => $data['heart_rate'],
            'oxygen_saturation'=> $data['spo2'],
            'temperature'      => $data['temperature'] ?? null,
            'flag_status'      => $flagStatus,
            'notes'            => 'Quick vitals entry from caregiver dashboard.',
        ]);

        AuditLog::record(
            'quick_vitals_entry',
            $patient->id,
            'VitalSign',
            $vital->id,
            [
                'blood_pressure' => $vital->blood_pressure,
                'spo2' => $vital->oxygen_saturation,
                'heart_rate' => $vital->heart_rate,
            ]
        );

        return redirect()->route('care_giver.dashboard')->with('success', 'Quick vitals recorded for ' . $patient->name);
    }

    public function storeDailyHealthLog(Request $request)
    {
        $careGiver = Auth::guard('care_giver')->user();

        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'mood' => 'required|in:happy,neutral,low,agitated,confused',
            'steps' => 'nullable|integer|min:0|max:20000',
            'nutrition_percent' => 'nullable|numeric|min:0|max:100',
            'elimination_scale' => 'nullable|integer|min:1|max:7',
            'observation' => 'nullable|string|max:2000',
        ]);

        $patient = Patient::findOrFail($data['patient_id']);

        if (!$careGiver->isAssignedToPatient($patient->id)) {
            abort(403, 'You are not assigned to that patient');
        }

        $observation = Observation::create([
            'patient_id' => $patient->id,
            'caregiver_id' => $careGiver->id,
            'emoji_code' => $data['mood'],
            'mobility_notes' => 'Steps: ' . ($data['steps'] ?? 0),
            'behavior_notes' => $data['observation'] ?? 'Logged through daily health interface.',
            'general_notes' => 'Nutrition ' . ($data['nutrition_percent'] ?? 0) . '% · Bristol ' . ($data['elimination_scale'] ?? 'N/A'),
        ]);

        AuditLog::record(
            'daily_health_log',
            $patient->id,
            'Observation',
            $observation->id,
            [
                'mood' => $observation->emoji_code,
                'nutrition_percent' => $data['nutrition_percent'] ?? null,
                'elimination_scale' => $data['elimination_scale'] ?? null,
            ]
        );

        return redirect()->route('care_giver.dashboard')->with('success', 'Daily health log saved for ' . $patient->name);
    }

    protected function determineFlagStatus(string $bloodPressure, float $spo2, int $heartRate): string
    {
        [$systolic, $diastolic] = $this->parseBloodPressure($bloodPressure);

        if ($spo2 < 92 || $heartRate >= 130 || $systolic >= 180 || $diastolic >= 110) {
            return 'critical';
        }

        if ($spo2 < 95 || $heartRate >= 110 || $systolic >= 140 || $diastolic >= 90) {
            return 'warning';
        }

        return 'normal';
    }

    protected function parseBloodPressure(string $bloodPressure): array
    {
        $parts = explode('/', $bloodPressure);
        if (count($parts) !== 2) {
            return [0, 0];
        }

        return [
            (int) trim($parts[0]),
            (int) trim($parts[1]),
        ];
    }
}
