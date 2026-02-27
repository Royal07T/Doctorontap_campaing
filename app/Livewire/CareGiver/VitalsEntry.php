<?php

namespace App\Livewire\CareGiver;

use App\Models\Patient;
use App\Models\VitalSign;
use App\Models\AuditLog;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class VitalsEntry extends Component
{
    public Patient $patient;

    // Form fields
    public string $blood_pressure_systolic = '';
    public string $blood_pressure_diastolic = '';
    public ?string $heart_rate = null;
    public ?string $oxygen_saturation = null;
    public ?string $temperature = null;
    public ?string $blood_sugar = null;
    public ?string $respiratory_rate = null;
    public ?string $weight = null;
    public ?string $height = null;
    public string $notes = '';

    // UI state
    public bool $showSuccess = false;
    public ?string $flagStatus = null;
    public ?string $flagMessage = null;

    protected function rules(): array
    {
        return [
            'blood_pressure_systolic' => 'required|numeric|min:60|max:260',
            'blood_pressure_diastolic' => 'required|numeric|min:30|max:160',
            'heart_rate' => 'nullable|numeric|min:30|max:220',
            'oxygen_saturation' => 'nullable|numeric|min:50|max:100',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'blood_sugar' => 'nullable|numeric|min:20|max:600',
            'respiratory_rate' => 'nullable|numeric|min:5|max:60',
            'weight' => 'nullable|numeric|min:1|max:500',
            'height' => 'nullable|numeric|min:30|max:280',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    protected $messages = [
        'blood_pressure_systolic.required' => 'Systolic BP is required.',
        'blood_pressure_diastolic.required' => 'Diastolic BP is required.',
    ];

    public function mount(Patient $patient): void
    {
        $this->patient = $patient;
    }

    /**
     * Evaluate thresholds and determine flag status.
     */
    public function evaluateThresholds(): void
    {
        $this->flagStatus = 'normal';
        $this->flagMessage = null;

        $warnings = [];
        $criticals = [];

        $sys = (float) $this->blood_pressure_systolic;
        $dia = (float) $this->blood_pressure_diastolic;

        // Blood Pressure
        if ($sys && $dia) {
            if ($sys >= 180 || $dia >= 120) {
                $criticals[] = 'BP critically high (' . $sys . '/' . $dia . ')';
            } elseif ($sys < 90 || $dia < 60) {
                $criticals[] = 'BP critically low (' . $sys . '/' . $dia . ')';
            } elseif ($sys >= 140 || $dia >= 90) {
                $warnings[] = 'BP elevated (' . $sys . '/' . $dia . ')';
            }
        }

        // Heart Rate
        if ($this->heart_rate) {
            $hr = (float) $this->heart_rate;
            if ($hr > 150 || $hr < 40) {
                $criticals[] = "Heart rate {$hr} bpm";
            } elseif ($hr > 100 || $hr < 50) {
                $warnings[] = "Heart rate {$hr} bpm";
            }
        }

        // Oxygen Saturation
        if ($this->oxygen_saturation) {
            $spo2 = (float) $this->oxygen_saturation;
            if ($spo2 < 90) {
                $criticals[] = "SpO2 {$spo2}%";
            } elseif ($spo2 < 95) {
                $warnings[] = "SpO2 {$spo2}%";
            }
        }

        // Temperature
        if ($this->temperature) {
            $temp = (float) $this->temperature;
            if ($temp >= 40 || $temp < 34) {
                $criticals[] = "Temperature {$temp}°C";
            } elseif ($temp >= 38 || $temp < 35) {
                $warnings[] = "Temperature {$temp}°C";
            }
        }

        // Blood Sugar
        if ($this->blood_sugar) {
            $bs = (float) $this->blood_sugar;
            if ($bs > 300 || $bs < 54) {
                $criticals[] = "Blood sugar {$bs} mg/dL";
            } elseif ($bs > 180 || $bs < 70) {
                $warnings[] = "Blood sugar {$bs} mg/dL";
            }
        }

        if (!empty($criticals)) {
            $this->flagStatus = 'critical';
            $this->flagMessage = 'CRITICAL: ' . implode('; ', $criticals);
        } elseif (!empty($warnings)) {
            $this->flagStatus = 'warning';
            $this->flagMessage = 'Warning: ' . implode('; ', $warnings);
        }
    }

    /**
     * Real-time validation as user types BP
     */
    public function updatedBloodPressureSystolic(): void
    {
        $this->validateOnly('blood_pressure_systolic');
        if ($this->blood_pressure_systolic && $this->blood_pressure_diastolic) {
            $this->evaluateThresholds();
        }
    }

    public function updatedBloodPressureDiastolic(): void
    {
        $this->validateOnly('blood_pressure_diastolic');
        if ($this->blood_pressure_systolic && $this->blood_pressure_diastolic) {
            $this->evaluateThresholds();
        }
    }

    /**
     * Save the vital sign record
     */
    public function save(): void
    {
        $this->validate();
        $this->evaluateThresholds();

        $careGiver = Auth::guard('care_giver')->user();

        $vitalSign = VitalSign::create([
            'patient_id' => $this->patient->id,
            'caregiver_id' => $careGiver->id,
            'blood_pressure' => $this->blood_pressure_systolic . '/' . $this->blood_pressure_diastolic,
            'heart_rate' => $this->heart_rate ?: null,
            'oxygen_saturation' => $this->oxygen_saturation ?: null,
            'temperature' => $this->temperature ?: null,
            'blood_sugar' => $this->blood_sugar ?: null,
            'respiratory_rate' => $this->respiratory_rate ?: null,
            'weight' => $this->weight ?: null,
            'height' => $this->height ?: null,
            'notes' => $this->notes ?: null,
            'flag_status' => $this->flagStatus,
        ]);

        // Audit log
        AuditLog::record('created', $this->patient->id, 'vital_sign', $vitalSign->id);

        // Dispatch escalation if critical
        if ($this->flagStatus === 'critical') {
            \App\Jobs\EscalationAlertJob::dispatch($vitalSign);
        }

        $this->showSuccess = true;
        $this->dispatch('vitals-saved', patientId: $this->patient->id);

        // Reset form
        $this->reset([
            'blood_pressure_systolic', 'blood_pressure_diastolic',
            'heart_rate', 'oxygen_saturation', 'temperature',
            'blood_sugar', 'respiratory_rate', 'weight', 'height', 'notes',
        ]);
        $this->flagStatus = null;
        $this->flagMessage = null;
    }

    public function render()
    {
        return view('livewire.care-giver.vitals-entry');
    }
}
