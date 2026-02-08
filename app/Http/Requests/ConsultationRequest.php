<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DoctorCanProvideServiceType;

class ConsultationRequest extends FormRequest
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
            // Personal Details
            'first_name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[a-zA-Z\s\-\']+$/'],
            'last_name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[a-zA-Z\s\-\']+$/'],
            'gender' => 'required|in:male,female',
            'age' => 'required|integer|min:1|max:120',
            'mobile' => ['required', 'string', 'regex:/^(\+234|0)[0-9]{10}$/'],
            'email' => 'required|email:rfc,dns|max:255',
            
            // Triage Block
            'problem' => 'required|string|min:10|max:500',
            'medical_documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // Max 5MB per file
            'severity' => 'required|in:mild,moderate,severe',
            'emergency_symptoms' => 'nullable|array',
            'emergency_symptoms.*' => 'string|max:255',
            
            // Doctor's Choice
            'doctor' => 'nullable|integer|exists:doctors,id',
            'consult_mode' => 'required|in:voice,video,chat',
            
            // Service Type (Full Consultation or Second Opinion)
            'service_type' => 'nullable|in:full_consultation,second_opinion',
            'second_opinion_documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // Max 10MB for lab results
            'second_opinion_notes' => 'nullable|string|max:2000',
            
            // Consent
            'informed_consent' => 'required|accepted',
            'data_privacy' => 'required|accepted',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate doctor can provide the requested service type
            if ($this->has('doctor') && $this->has('service_type')) {
                $doctorId = $this->input('doctor');
                $serviceType = $this->input('service_type', 'full_consultation');
                
                $doctor = \App\Models\Doctor::find($doctorId);
                
                if ($doctor) {
                    if ($serviceType === 'full_consultation' && !$doctor->canConductFullConsultation()) {
                        $validator->errors()->add('doctor', 'This doctor cannot conduct full consultations. International doctors are restricted to second opinions only.');
                    }
                    
                    if ($serviceType === 'second_opinion' && !$doctor->canProvideSecondOpinion()) {
                        $validator->errors()->add('doctor', 'This doctor is not authorized to provide second opinions.');
                    }
                }
            }
            
            // Ensure second opinion requests have documents
            if ($this->input('service_type') === 'second_opinion') {
                if (!$this->hasFile('second_opinion_documents') && !$this->hasFile('medical_documents')) {
                    $validator->errors()->add('second_opinion_documents', 'Please upload medical documents or lab results for second opinion review.');
                }
            }
        });
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
            'gender.required' => 'Please select your gender.',
            'gender.in' => 'Gender must be either Male or Female.',
            'age.required' => 'Age is required.',
            'age.integer' => 'Age must be a valid number.',
            'age.min' => 'Age must be at least 1.',
            'age.max' => 'Age cannot exceed 120.',
            'mobile.required' => 'Mobile number is required.',
            'mobile.regex' => 'Please enter a valid Nigerian phone number (e.g., +2348012345678 or 08012345678).',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.dns' => 'Please enter a valid email address with a valid domain.',
            'problem.required' => 'Please describe your medical problem.',
            'problem.min' => 'Problem description must be at least 10 characters.',
            'medical_documents.*.mimes' => 'Medical documents must be PDF, JPG, PNG, DOC, or DOCX files.',
            'medical_documents.*.max' => 'Each medical document must not exceed 5MB.',
            'severity.required' => 'Please indicate the severity of your condition.',
            'consult_mode.required' => 'Please select a consultation mode.',
            'service_type.in' => 'Please select a valid service type (Full Consultation or Second Opinion).',
            'second_opinion_documents.*.mimes' => 'Second opinion documents must be PDF, JPG, PNG, DOC, or DOCX files.',
            'second_opinion_documents.*.max' => 'Each document must not exceed 10MB.',
            'second_opinion_notes.max' => 'Second opinion notes cannot exceed 2000 characters.',
            'informed_consent.required' => 'You must accept the informed consent.',
            'informed_consent.accepted' => 'You must accept the informed consent to proceed.',
            'data_privacy.required' => 'You must accept the data privacy policy.',
            'data_privacy.accepted' => 'You must accept the data privacy policy to proceed.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize string inputs
        $this->merge([
            'first_name' => $this->sanitizeString($this->first_name),
            'last_name' => $this->sanitizeString($this->last_name),
            'email' => $this->sanitizeEmail($this->email),
            'mobile' => $this->sanitizePhone($this->mobile),
            'problem' => $this->sanitizeString($this->problem),
            'service_type' => $this->input('service_type', 'full_consultation'), // Default to full consultation
            'second_opinion_notes' => $this->sanitizeString($this->second_opinion_notes),
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
        
        // Remove HTML tags
        $value = strip_tags($value);
        
        // Remove extra whitespace
        $value = preg_replace('/\s+/', ' ', $value);
        
        // Trim
        return trim($value);
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
        
        // Remove all non-numeric characters except +
        return preg_replace('/[^0-9+]/', '', trim($value));
    }
}

