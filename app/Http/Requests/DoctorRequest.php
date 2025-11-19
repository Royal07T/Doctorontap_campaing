<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DoctorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $doctorId = $this->route('id');
        
        return [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s\-\'\.]+$/',
            'email' => 'required|email:rfc,dns|max:255|unique:doctors,email' . ($doctorId ? ',' . $doctorId : ''),
            'phone' => ['required', 'string', 'max:20', 'regex:/^(\+?[0-9]{1,4})?[0-9]{10,15}$/'],
            'gender' => 'required|in:Male,Female',
            'specialization' => 'nullable|string|max:255',
            'consultation_fee' => 'nullable|numeric|min:0|max:1000000',
            'location' => 'nullable|string|max:255',
            'experience' => 'nullable|string|max:255',
            'languages' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
            'is_available' => 'nullable|boolean',
            'mdcn_license_current' => 'nullable|in:yes,no',
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
            'name.required' => 'Doctor name is required.',
            'name.regex' => 'Name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'phone.required' => 'Phone number is required.',
            'phone.regex' => 'Please enter a valid phone number.',
            'gender.required' => 'Gender is required.',
            'gender.in' => 'Gender must be either Male or Female.',
            'consultation_fee.numeric' => 'Consultation fee must be a number.',
            'consultation_fee.min' => 'Consultation fee must be 0 or greater.',
            'consultation_fee.max' => 'Consultation fee seems unrealistic.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $sanitized = [
            'name' => $this->sanitizeString($this->name),
            'email' => $this->sanitizeEmail($this->email),
            'phone' => $this->sanitizePhone($this->phone),
        ];
        
        if ($this->has('specialization')) {
            $sanitized['specialization'] = $this->sanitizeString($this->specialization);
        }
        
        if ($this->has('location')) {
            $sanitized['location'] = $this->sanitizeString($this->location);
        }
        
        if ($this->has('experience')) {
            $sanitized['experience'] = $this->sanitizeString($this->experience);
        }
        
        if ($this->has('languages')) {
            $sanitized['languages'] = $this->sanitizeString($this->languages);
        }
        
        $this->merge($sanitized);
    }

    /**
     * Sanitize string input
     */
    private function sanitizeString($value): ?string
    {
        if (!is_string($value)) {
            return $value;
        }
        
        return trim(strip_tags($value));
    }

    /**
     * Sanitize email input
     */
    private function sanitizeEmail($value): ?string
    {
        if (!is_string($value)) {
            return $value;
        }
        
        return filter_var(trim(strtolower($value)), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize phone input
     */
    private function sanitizePhone($value): ?string
    {
        if (!is_string($value)) {
            return $value;
        }
        
        return preg_replace('/[^0-9+]/', '', trim($value));
    }
}

