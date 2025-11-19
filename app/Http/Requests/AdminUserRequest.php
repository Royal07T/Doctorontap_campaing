<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class AdminUserRequest extends FormRequest
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
        $userId = $this->route('id');
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        
        return [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s\-\']+$/',
            'email' => 'required|email:rfc,dns|max:255|unique:admin_users,email' . ($userId ? ',' . $userId : ''),
            'password' => [
                $isUpdate ? 'nullable' : 'required',
                'string',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
                'confirmed',
            ],
            'is_active' => 'nullable|boolean',
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
            'name.required' => 'Name is required.',
            'name.regex' => 'Name can only contain letters, spaces, hyphens, and apostrophes.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->sanitizeString($this->name),
            'email' => $this->sanitizeEmail($this->email),
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
}

