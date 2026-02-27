<?php

namespace App\Livewire\CareGiver;

use App\Models\DietPlan;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DietPlanManager extends Component
{
    public Patient $patient;
    public $carePlanId;
    public $dietPlans = [];
    public $showForm = false;

    // Form fields
    public $title = '';
    public $description = '';
    public $targetCalories = '';
    public $startDate = '';
    public $endDate = '';
    public $restrictions = '';
    public $supplements = '';
    public $dieticianNotes = '';
    public $meals = [];
    public $editingId = null;

    public function mount(Patient $patient, $carePlanId = null)
    {
        $this->patient = $patient;
        $this->carePlanId = $carePlanId;
        $this->startDate = now()->toDateString();
        $this->loadDietPlans();
    }

    public function loadDietPlans()
    {
        $query = DietPlan::forPatient($this->patient->id)->orderByDesc('created_at');
        if ($this->carePlanId) {
            $query->where('care_plan_id', $this->carePlanId);
        }
        $this->dietPlans = $query->get();
    }

    public function addMeal()
    {
        $this->meals[] = ['name' => '', 'time' => '', 'items' => '', 'calories' => '', 'notes' => ''];
    }

    public function removeMeal($index)
    {
        unset($this->meals[$index]);
        $this->meals = array_values($this->meals);
    }

    public function save()
    {
        $this->validate([
            'title'          => 'required|string|max:255',
            'startDate'      => 'required|date',
            'targetCalories' => 'nullable|numeric|min:500|max:10000',
        ]);

        $careGiver = Auth::guard('care_giver')->user();

        $mealsData = collect($this->meals)->filter(fn ($m) => !empty($m['name']))->map(function ($m) {
            return [
                'name'     => $m['name'],
                'time'     => $m['time'] ?? '',
                'items'    => array_filter(array_map('trim', explode(',', $m['items'] ?? ''))),
                'calories' => (int) ($m['calories'] ?? 0),
                'notes'    => $m['notes'] ?? '',
            ];
        })->values()->toArray();

        $data = [
            'patient_id'      => $this->patient->id,
            'care_plan_id'    => $this->carePlanId,
            'created_by'      => $careGiver->id,
            'title'           => $this->title,
            'description'     => $this->description,
            'meals'           => $mealsData,
            'restrictions'    => array_filter(array_map('trim', explode(',', $this->restrictions))),
            'supplements'     => array_filter(array_map('trim', explode(',', $this->supplements))),
            'target_calories' => $this->targetCalories ?: null,
            'start_date'      => $this->startDate,
            'end_date'        => $this->endDate ?: null,
            'dietician_notes' => $this->dieticianNotes,
            'status'          => 'active',
        ];

        if ($this->editingId) {
            DietPlan::find($this->editingId)?->update($data);
        } else {
            DietPlan::create($data);
        }

        $this->resetForm();
        $this->loadDietPlans();
        session()->flash('diet-success', 'Diet plan saved successfully.');
    }

    public function edit($id)
    {
        $plan = DietPlan::find($id);
        if (!$plan) return;

        $this->editingId      = $plan->id;
        $this->title          = $plan->title;
        $this->description    = $plan->description ?? '';
        $this->targetCalories = $plan->target_calories ?? '';
        $this->startDate      = $plan->start_date?->toDateString() ?? '';
        $this->endDate        = $plan->end_date?->toDateString() ?? '';
        $this->restrictions   = implode(', ', $plan->restrictions ?? []);
        $this->supplements    = implode(', ', $plan->supplements ?? []);
        $this->dieticianNotes = $plan->dietician_notes ?? '';
        $this->meals          = collect($plan->meals ?? [])->map(function ($m) {
            return [
                'name'     => $m['name'] ?? '',
                'time'     => $m['time'] ?? '',
                'items'    => implode(', ', $m['items'] ?? []),
                'calories' => $m['calories'] ?? '',
                'notes'    => $m['notes'] ?? '',
            ];
        })->toArray();
        $this->showForm = true;
    }

    public function toggleStatus($id)
    {
        $plan = DietPlan::find($id);
        if (!$plan) return;
        $plan->update(['status' => $plan->status === 'active' ? 'paused' : 'active']);
        $this->loadDietPlans();
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->title = $this->description = $this->targetCalories = $this->endDate = $this->restrictions = $this->supplements = $this->dieticianNotes = '';
        $this->startDate = now()->toDateString();
        $this->meals = [];
        $this->showForm = false;
    }

    public function render()
    {
        return view('livewire.care-giver.diet-plan-manager');
    }
}
