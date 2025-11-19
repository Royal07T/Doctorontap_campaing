<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TreatmentPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by controllers
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Medical Format Fields
            'presenting_complaint' => 'required|string|max:2000',
            'history_of_complaint' => 'required|string|max:5000',
            'past_medical_history' => 'nullable|string|max:2000',
            'family_history' => 'nullable|string|max:2000',
            'drug_history' => 'nullable|string|max:2000',
            'social_history' => 'nullable|string|max:2000',
            'diagnosis' => 'required|string|max:2000',
            'investigation' => 'nullable|string|max:5000',
            'treatment_plan' => 'required|string|max:5000',
            
            // Additional fields
            'prescribed_medications' => 'nullable|array',
            'prescribed_medications.*.name' => 'required_with:prescribed_medications|string|max:255',
            'prescribed_medications.*.dosage' => 'required_with:prescribed_medications|string|max:255',
            'prescribed_medications.*.frequency' => 'required_with:prescribed_medications|string|max:255',
            'prescribed_medications.*.duration' => 'required_with:prescribed_medications|string|max:255',
            
            'follow_up_instructions' => 'nullable|string|max:2000',
            'lifestyle_recommendations' => 'nullable|string|max:2000',
            
            'referrals' => 'nullable|array',
            'referrals.*.specialist' => 'required_with:referrals|string|max:255',
            'referrals.*.reason' => 'required_with:referrals|string|max:500',
            'referrals.*.urgency' => 'required_with:referrals|in:routine,urgent,emergency',
            
            'next_appointment_date' => 'nullable|date|after:today',
            'additional_notes' => 'nullable|string|max:2000',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'presenting_complaint.required' => 'Presenting complaint is required.',
            'presenting_complaint.max' => 'Presenting complaint must not exceed 2000 characters.',
            'history_of_complaint.required' => 'History of complaint is required.',
            'history_of_complaint.max' => 'History of complaint must not exceed 5000 characters.',
            'diagnosis.required' => 'Diagnosis is required.',
            'treatment_plan.required' => 'Treatment plan is required.',
            'next_appointment_date.after' => 'Next appointment must be a future date.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize text fields
        $textFields = [
            'presenting_complaint',
            'history_of_complaint',
            'past_medical_history',
            'family_history',
            'drug_history',
            'social_history',
            'diagnosis',
            'investigation',
            'treatment_plan',
            'follow_up_instructions',
            'lifestyle_recommendations',
            'additional_notes',
        ];
        
        $sanitized = [];
        foreach ($textFields as $field) {
            if ($this->has($field)) {
                $sanitized[$field] = $this->sanitizeString($this->input($field));
            }
        }
        
        if (!empty($sanitized)) {
            $this->merge($sanitized);
        }
    }

    /**
     * Sanitize string input (allow basic medical formatting)
     */
    private function sanitizeString($value): ?string
    {
        if (!is_string($value)) {
            return $value;
        }
        
        // Remove dangerous HTML tags but keep basic formatting
        $value = strip_tags($value, '<br><p><ul><ol><li><strong><em>');
        
        // Remove extra whitespace
        $value = preg_replace('/\s+/', ' ', $value);
        
        // Trim
        return trim($value);
    }
}

