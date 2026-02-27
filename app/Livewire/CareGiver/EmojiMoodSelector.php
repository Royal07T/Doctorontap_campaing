<?php

namespace App\Livewire\CareGiver;

use App\Models\Observation;
use App\Models\AuditLog;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EmojiMoodSelector extends Component
{
    public Patient $patient;

    public ?string $selectedMood = null;
    public string $mobilityNotes = '';
    public ?int $painLevel = null;
    public string $behaviorNotes = '';
    public string $generalNotes = '';

    public bool $showSuccess = false;
    public array $recentObservations = [];

    protected function rules(): array
    {
        return [
            'selectedMood' => 'required|string|in:' . implode(',', array_keys(Observation::MOOD_OPTIONS)),
            'painLevel' => 'nullable|integer|min:0|max:10',
            'mobilityNotes' => 'nullable|string|max:500',
            'behaviorNotes' => 'nullable|string|max:1000',
            'generalNotes' => 'nullable|string|max:1000',
        ];
    }

    public function mount(Patient $patient): void
    {
        $this->patient = $patient;
        $this->loadRecent();
    }

    public function loadRecent(): void
    {
        $this->recentObservations = Observation::forPatient($this->patient->id)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($obs) => [
                'id' => $obs->id,
                'emoji' => $obs->mood_emoji,
                'label' => $obs->mood_label,
                'pain' => $obs->pain_level,
                'time' => $obs->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    public function selectMood(string $mood): void
    {
        $this->selectedMood = $mood;
    }

    public function save(): void
    {
        $this->validate();

        $careGiver = Auth::guard('care_giver')->user();

        $observation = Observation::create([
            'patient_id' => $this->patient->id,
            'caregiver_id' => $careGiver->id,
            'emoji_code' => $this->selectedMood,
            'mobility_notes' => $this->mobilityNotes ?: null,
            'pain_level' => $this->painLevel,
            'behavior_notes' => $this->behaviorNotes ?: null,
            'general_notes' => $this->generalNotes ?: null,
        ]);

        AuditLog::record('created', $this->patient->id, 'observation', $observation->id);

        $this->showSuccess = true;
        $this->reset(['selectedMood', 'mobilityNotes', 'painLevel', 'behaviorNotes', 'generalNotes']);
        $this->loadRecent();
        $this->dispatch('observation-saved', patientId: $this->patient->id);
    }

    public function render()
    {
        return view('livewire.care-giver.emoji-mood-selector', [
            'moodOptions' => Observation::MOOD_OPTIONS,
        ]);
    }
}
