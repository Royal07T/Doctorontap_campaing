<?php

namespace App\Services;

use App\Models\CarePlan;
use App\Models\Patient;

/**
 * Determines which features are available to a patient based
 * on their active care plan tier (Meridian / Executive / Sovereign).
 *
 * Used by Livewire components and Blade to conditionally
 * render sections of the caregiver dashboard.
 */
class CarePlanScopingService
{
    /**
     * Resolve the active care plan for a patient (cached per request)
     */
    public function resolveActivePlan(Patient $patient): ?CarePlan
    {
        // If already loaded via eager-load, use it
        if ($patient->relationLoaded('activeCarePlan') && $patient->activeCarePlan) {
            return $patient->activeCarePlan;
        }

        return CarePlan::forPatient($patient->id)
            ->active()
            ->latest('start_date')
            ->first();
    }

    /**
     * Does the patient have any active care plan at all?
     */
    public function hasActivePlan(Patient $patient): bool
    {
        return $this->resolveActivePlan($patient) !== null;
    }

    /**
     * Get the plan type string (meridian|executive|sovereign) or null
     */
    public function planType(Patient $patient): ?string
    {
        return $this->resolveActivePlan($patient)?->plan_type;
    }

    // ──────────────────────────────────────────────
    // Feature gates
    // ──────────────────────────────────────────────

    /**
     * Can this patient have vitals recorded by a caregiver?
     * (All plans include vitals.)
     */
    public function canRecordVitals(Patient $patient): bool
    {
        return $this->hasActivePlan($patient);
    }

    /**
     * Can this patient have observations recorded?
     * (All plans include observations.)
     */
    public function canRecordObservations(Patient $patient): bool
    {
        return $this->hasActivePlan($patient);
    }

    /**
     * Can this patient have medication logs?
     * (All plans include medication tracking.)
     */
    public function canLogMedication(Patient $patient): bool
    {
        return $this->hasActivePlan($patient);
    }

    /**
     * Does this patient's plan include physician review?
     * (Executive + Sovereign only.)
     */
    public function hasPhysicianReview(Patient $patient): bool
    {
        $plan = $this->resolveActivePlan($patient);
        return $plan?->hasPhysicianReview() ?? false;
    }

    /**
     * Does this patient's plan include weekly PDF reports?
     * (Executive + Sovereign only.)
     */
    public function hasWeeklyReports(Patient $patient): bool
    {
        $plan = $this->resolveActivePlan($patient);
        return $plan?->hasWeeklyReports() ?? false;
    }

    /**
     * Does this patient's plan include the dietician module?
     * (Sovereign only.)
     */
    public function hasDietician(Patient $patient): bool
    {
        $plan = $this->resolveActivePlan($patient);
        return $plan?->hasDietician() ?? false;
    }

    /**
     * Does this patient's plan include physiotherapy?
     * (Sovereign only.)
     */
    public function hasPhysiotherapy(Patient $patient): bool
    {
        $plan = $this->resolveActivePlan($patient);
        return $plan?->hasPhysiotherapy() ?? false;
    }

    // ──────────────────────────────────────────────
    // Utility: get a feature map for Blade / Livewire
    // ──────────────────────────────────────────────

    /**
     * Return an associative array of all feature flags for a patient.
     * Useful for passing to a Livewire component or Blade view.
     */
    public function featureMap(Patient $patient): array
    {
        return [
            'has_plan'           => $this->hasActivePlan($patient),
            'plan_type'          => $this->planType($patient),
            'vitals'             => $this->canRecordVitals($patient),
            'observations'       => $this->canRecordObservations($patient),
            'medication'         => $this->canLogMedication($patient),
            'physician_review'   => $this->hasPhysicianReview($patient),
            'weekly_reports'     => $this->hasWeeklyReports($patient),
            'dietician'          => $this->hasDietician($patient),
            'physiotherapy'      => $this->hasPhysiotherapy($patient),
        ];
    }
}
