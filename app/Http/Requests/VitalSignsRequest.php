<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VitalSignsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by controllers/policies
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => 'required|integer|exists:patients,id',
            'blood_pressure' => 'nullable|string|max:20|regex:/^\d{2,3}\/\d{2,3}$/',
            'oxygen_saturation' => 'nullable|numeric|min:0|max:100',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'blood_sugar' => 'nullable|numeric|min:0|max:1000',
            'height' => 'nullable|numeric|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:500',
            'heart_rate' => 'nullable|integer|min:0|max:300',
            'respiratory_rate' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
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
            'patient_id.required' => 'Patient is required.',
            'patient_id.exists' => 'Selected patient does not exist.',
            'blood_pressure.regex' => 'Blood pressure must be in format XXX/XXX (e.g., 120/80).',
            'oxygen_saturation.min' => 'Oxygen saturation must be between 0 and 100%.',
            'oxygen_saturation.max' => 'Oxygen saturation must be between 0 and 100%.',
            'temperature.min' => 'Temperature must be between 30°C and 45°C.',
            'temperature.max' => 'Temperature must be between 30°C and 45°C.',
            'blood_sugar.min' => 'Blood sugar must be a positive number.',
            'blood_sugar.max' => 'Blood sugar value seems unrealistic. Please check.',
            'height.min' => 'Height must be a positive number.',
            'height.max' => 'Height must be less than 300cm.',
            'weight.min' => 'Weight must be a positive number.',
            'weight.max' => 'Weight must be less than 500kg.',
            'heart_rate.min' => 'Heart rate must be a positive number.',
            'heart_rate.max' => 'Heart rate must be less than 300 bpm.',
            'respiratory_rate.min' => 'Respiratory rate must be a positive number.',
            'respiratory_rate.max' => 'Respiratory rate must be less than 100 per minute.',
            'notes.max' => 'Notes must not exceed 1000 characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize notes field
        if ($this->has('notes')) {
            $this->merge([
                'notes' => $this->sanitizeString($this->notes),
            ]);
        }
        
        // Sanitize blood pressure format
        if ($this->has('blood_pressure')) {
            $this->merge([
                'blood_pressure' => preg_replace('/[^0-9\/]/', '', $this->blood_pressure),
            ]);
        }
    }

    /**
     * Sanitize string input
     */
    private function sanitizeString($value): ?string
    {
        if (!is_string($value)) {
            return $value;
        }
        
        // Remove HTML tags
        $value = strip_tags($value);
        
        // Remove extra whitespace
        $value = preg_replace('/\s+/', ' ', $value);
        
        // Trim
        return trim($value);
    }
}

