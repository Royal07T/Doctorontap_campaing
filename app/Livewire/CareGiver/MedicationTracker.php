<?php

namespace App\Livewire\CareGiver;

use App\Models\MedicationLog;
use App\Models\AuditLog;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MedicationTracker extends Component
{
    public Patient $patient;

    // New med form
    public string $medicationName = '';
    public string $dosage = '';
    public ?string $scheduledTime = null;
    public string $medNotes = '';

    public bool $showForm = false;
    public bool $showSuccess = false;

    protected function rules(): array
    {
        return [
            'medicationName' => 'required|string|max:255',
            'dosage' => 'required|string|max:100',
            'scheduledTime' => 'nullable|date',
            'medNotes' => 'nullable|string|max:500',
        ];
    }

    public function mount(Patient $patient): void
    {
        $this->patient = $patient;
    }

    /**
     * Create a new pending medication entry
     */
    public function addMedication(): void
    {
        $this->validate();

        $careGiver = Auth::guard('care_giver')->user();

        $log = MedicationLog::create([
            'patient_id' => $this->patient->id,
            'caregiver_id' => $careGiver->id,
            'medication_name' => $this->medicationName,
            'dosage' => $this->dosage,
            'scheduled_time' => $this->scheduledTime ?: now(),
            'status' => MedicationLog::STATUS_PENDING,
            'notes' => $this->medNotes ?: null,
        ]);

        AuditLog::record('created', $this->patient->id, 'medication_log', $log->id);

        $this->reset(['medicationName', 'dosage', 'scheduledTime', 'medNotes']);
        $this->showForm = false;
        $this->showSuccess = true;
    }

    /**
     * Mark a medication as given
     */
    public function markGiven(int $logId): void
    {
        $log = MedicationLog::where('patient_id', $this->patient->id)->findOrFail($logId);
        $log->markAsGiven();
        AuditLog::record('updated', $this->patient->id, 'medication_log', $log->id, ['action' => 'marked_given']);
    }

    /**
     * Mark a medication as missed
     */
    public function markMissed(int $logId): void
    {
        $log = MedicationLog::where('patient_id', $this->patient->id)->findOrFail($logId);
        $log->markAsMissed();
        AuditLog::record('updated', $this->patient->id, 'medication_log', $log->id, ['action' => 'marked_missed']);
    }

    /**
     * Mark a medication as skipped
     */
    public function markSkipped(int $logId): void
    {
        $log = MedicationLog::where('patient_id', $this->patient->id)->findOrFail($logId);
        $log->update(['status' => MedicationLog::STATUS_SKIPPED]);
        AuditLog::record('updated', $this->patient->id, 'medication_log', $log->id, ['action' => 'marked_skipped']);
    }

    public function render()
    {
        $todayMeds = MedicationLog::forPatient($this->patient->id)
            ->today()
            ->orderBy('scheduled_time')
            ->get();

        $compliance = MedicationLog::complianceRate($this->patient->id, 7);

        return view('livewire.care-giver.medication-tracker', [
            'todayMeds' => $todayMeds,
            'compliance' => $compliance,
        ]);
    }
}
