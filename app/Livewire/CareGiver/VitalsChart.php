<?php

namespace App\Livewire\CareGiver;

use App\Models\Patient;
use App\Models\VitalSign;
use Livewire\Component;

class VitalsChart extends Component
{
    public Patient $patient;
    public int $days = 7;
    public array $chartData = [];

    public function mount(Patient $patient, int $days = 7): void
    {
        $this->patient = $patient;
        $this->days = $days;
        $this->loadData();
    }

    public function loadData(): void
    {
        $vitals = VitalSign::where('patient_id', $this->patient->id)
            ->where('created_at', '>=', now()->subDays($this->days))
            ->orderBy('created_at')
            ->get();

        $labels = [];
        $systolic = [];
        $diastolic = [];
        $heartRate = [];
        $spo2 = [];
        $temperature = [];
        $bloodSugar = [];
        $flags = [];

        foreach ($vitals as $v) {
            $labels[] = $v->created_at->format('M d H:i');

            // Parse blood pressure
            $bp = explode('/', $v->blood_pressure ?? '');
            $systolic[] = isset($bp[0]) ? (float) $bp[0] : null;
            $diastolic[] = isset($bp[1]) ? (float) $bp[1] : null;
            $heartRate[] = $v->heart_rate ? (float) $v->heart_rate : null;
            $spo2[] = $v->oxygen_saturation ? (float) $v->oxygen_saturation : null;
            $temperature[] = $v->temperature ? (float) $v->temperature : null;
            $bloodSugar[] = $v->blood_sugar ? (float) $v->blood_sugar : null;
            $flags[] = $v->flag_status ?? 'normal';
        }

        $this->chartData = [
            'labels' => $labels,
            'systolic' => $systolic,
            'diastolic' => $diastolic,
            'heartRate' => $heartRate,
            'spo2' => $spo2,
            'temperature' => $temperature,
            'bloodSugar' => $bloodSugar,
            'flags' => $flags,
        ];
    }

    public function setDays(int $days): void
    {
        $this->days = $days;
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.care-giver.vitals-chart');
    }
}
