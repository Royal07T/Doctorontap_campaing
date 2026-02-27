<?php

namespace App\Livewire\CareGiver;

use App\Models\PhysioSession;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PhysioSessionManager extends Component
{
    public Patient $patient;
    public $carePlanId;
    public $sessions = [];
    public $showForm = false;

    // Form
    public $sessionType = 'exercise';
    public $scheduledAt = '';
    public $durationMinutes = 30;
    public $exercises = [];
    public $findings = '';
    public $treatmentNotes = '';
    public $painBefore = '';
    public $painAfter = '';
    public $mobilityScore = '';
    public $nextSessionPlan = '';
    public $editingId = null;

    public function mount(Patient $patient, $carePlanId = null)
    {
        $this->patient = $patient;
        $this->carePlanId = $carePlanId;
        $this->scheduledAt = now()->addHour()->format('Y-m-d\TH:i');
        $this->loadSessions();
    }

    public function loadSessions()
    {
        $query = PhysioSession::forPatient($this->patient->id)->orderByDesc('scheduled_at');
        if ($this->carePlanId) {
            $query->where('care_plan_id', $this->carePlanId);
        }
        $this->sessions = $query->limit(20)->get();
    }

    public function addExercise()
    {
        $this->exercises[] = ['name' => '', 'sets' => '', 'reps' => '', 'duration' => '', 'notes' => ''];
    }

    public function removeExercise($index)
    {
        unset($this->exercises[$index]);
        $this->exercises = array_values($this->exercises);
    }

    public function save()
    {
        $this->validate([
            'sessionType'    => 'required|in:assessment,exercise,massage,review',
            'scheduledAt'    => 'required|date',
            'durationMinutes'=> 'nullable|integer|min:5|max:300',
        ]);

        $careGiver = Auth::guard('care_giver')->user();

        $exercisesData = collect($this->exercises)->filter(fn ($e) => !empty($e['name']))->values()->toArray();

        $data = [
            'patient_id'        => $this->patient->id,
            'care_plan_id'      => $this->carePlanId,
            'created_by'        => $careGiver->id,
            'session_type'      => $this->sessionType,
            'scheduled_at'      => $this->scheduledAt,
            'duration_minutes'  => $this->durationMinutes ?: null,
            'exercises'         => $exercisesData,
            'findings'          => $this->findings ?: null,
            'treatment_notes'   => $this->treatmentNotes ?: null,
            'pain_level_before' => $this->painBefore ?: null,
            'pain_level_after'  => $this->painAfter ?: null,
            'mobility_score'    => $this->mobilityScore ?: null,
            'next_session_plan' => $this->nextSessionPlan ?: null,
            'status'            => 'scheduled',
        ];

        if ($this->editingId) {
            PhysioSession::find($this->editingId)?->update($data);
        } else {
            PhysioSession::create($data);
        }

        $this->resetForm();
        $this->loadSessions();
        session()->flash('physio-success', 'Physio session saved.');
    }

    public function edit($id)
    {
        $s = PhysioSession::find($id);
        if (!$s) return;

        $this->editingId      = $s->id;
        $this->sessionType    = $s->session_type;
        $this->scheduledAt    = $s->scheduled_at?->format('Y-m-d\TH:i') ?? '';
        $this->durationMinutes= $s->duration_minutes ?? 30;
        $this->exercises      = $s->exercises ?? [];
        $this->findings       = $s->findings ?? '';
        $this->treatmentNotes = $s->treatment_notes ?? '';
        $this->painBefore     = $s->pain_level_before ?? '';
        $this->painAfter      = $s->pain_level_after ?? '';
        $this->mobilityScore  = $s->mobility_score ?? '';
        $this->nextSessionPlan= $s->next_session_plan ?? '';
        $this->showForm       = true;
    }

    public function complete($id)
    {
        PhysioSession::find($id)?->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);
        $this->loadSessions();
    }

    public function cancel($id)
    {
        PhysioSession::find($id)?->update(['status' => 'cancelled']);
        $this->loadSessions();
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->sessionType = 'exercise';
        $this->scheduledAt = now()->addHour()->format('Y-m-d\TH:i');
        $this->durationMinutes = 30;
        $this->exercises = $this->findings = $this->treatmentNotes = $this->painBefore = $this->painAfter = $this->mobilityScore = $this->nextSessionPlan = '';
        $this->exercises = [];
        $this->showForm = false;
    }

    public function render()
    {
        return view('livewire.care-giver.physio-session-manager');
    }
}
