<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConsultationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow both authenticated and unauthenticated users to create consultations
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
            'first_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'last_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => ['required', 'email', 'max:255'],
            'mobile' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'age' => ['nullable', 'integer', 'min:0', 'max:150'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'problem' => ['required', 'string', 'min:10', 'max:2000'],
            'consultation_mode' => ['required', Rule::in(['voice', 'video', 'chat'])],
            'doctor_id' => ['nullable', 'exists:doctors,id'],
            'severity' => ['nullable', Rule::in(['mild', 'moderate', 'severe'])],
            'emergency_symptoms' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.regex' => 'First name can only contain letters and spaces.',
            'last_name.regex' => 'Last name can only contain letters and spaces.',
            'mobile.regex' => 'Mobile number format is invalid.',
            'problem.min' => 'Please provide more details about your problem (minimum 10 characters).',
            'problem.max' => 'Problem description is too long (maximum 2000 characters).',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input
        if ($this->has('first_name')) {
            $this->merge(['first_name' => trim(strip_tags($this->first_name))]);
        }
        if ($this->has('last_name')) {
            $this->merge(['last_name' => trim(strip_tags($this->last_name))]);
        }
        if ($this->has('email')) {
            $this->merge(['email' => strtolower(trim($this->email))]);
        }
        if ($this->has('mobile')) {
            $this->merge(['mobile' => preg_replace('/[^0-9+\-()\s]/', '', $this->mobile)]);
        }
        if ($this->has('problem')) {
            $this->merge(['problem' => trim(strip_tags($this->problem))]);
        }
    }
}

