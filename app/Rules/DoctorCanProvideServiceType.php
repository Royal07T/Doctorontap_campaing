<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Doctor;

class DoctorCanProvideServiceType implements ValidationRule
{
    protected $serviceType;
    protected $doctorId;

    public function __construct($doctorId, $serviceType)
    {
        $this->doctorId = $doctorId;
        $this->serviceType = $serviceType;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $doctor = Doctor::find($this->doctorId);

        if (!$doctor) {
            $fail('The selected doctor does not exist.');
            return;
        }

        // Validate based on service type
        if ($this->serviceType === 'full_consultation') {
            if (!$doctor->canConductFullConsultation()) {
                $fail('This doctor cannot conduct full consultations. International doctors are restricted to second opinions only.');
                return;
            }
        } elseif ($this->serviceType === 'second_opinion') {
            if (!$doctor->canProvideSecondOpinion()) {
                $fail('This doctor is not authorized to provide second opinions.');
                return;
            }
        }
    }
}
