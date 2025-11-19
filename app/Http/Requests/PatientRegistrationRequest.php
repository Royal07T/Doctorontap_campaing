<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PatientRegistrationRequest extends FormRequest
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
        return [
            'first_name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[a-zA-Z\s\-\']+$/'],
            'last_name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[a-zA-Z\s\-\']+$/'],
            'email' => 'required|email:rfc,dns|max:255|unique:patients,email',
            'phone' => ['required', 'string', 'regex:/^(\+234|0)[0-9]{10}$/'],
            'gender' => 'required|in:male,female,other',
            'age' => 'required|integer|min:1|max:120',
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
            'first_name.required' => 'First name is required.',
            'first_name.min' => 'First name must be at least 2 characters.',
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens, and apostrophes.',
            'last_name.required' => 'Last name is required.',
            'last_name.min' => 'Last name must be at least 2 characters.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, and apostrophes.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'phone.required' => 'Phone number is required.',
            'phone.regex' => 'Please enter a valid Nigerian phone number (e.g., +2348012345678 or 08012345678).',
            'gender.required' => 'Please select gender.',
            'gender.in' => 'Gender must be male, female, or other.',
            'age.required' => 'Age is required.',
            'age.integer' => 'Age must be a valid number.',
            'age.min' => 'Age must be at least 1.',
            'age.max' => 'Age cannot exceed 120.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_name' => $this->sanitizeString($this->first_name),
            'last_name' => $this->sanitizeString($this->last_name),
            'email' => $this->sanitizeEmail($this->email),
            'phone' => $this->sanitizePhone($this->phone),
        ]);
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

