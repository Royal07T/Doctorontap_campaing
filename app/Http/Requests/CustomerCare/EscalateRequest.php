<?php

namespace App\Http\Requests\CustomerCare;

use Illuminate\Foundation\Http\FormRequest;

class EscalateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->guard('customer_care')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'escalated_to_type' => 'required|in:admin,doctor',
            'escalated_to_id' => 'required|integer',
            'reason' => 'required|string|min:10|max:1000',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->input('escalated_to_type');
            $id = $this->input('escalated_to_id');

            if ($type === 'admin') {
                $exists = \App\Models\AdminUser::where('id', $id)->exists();
                if (!$exists) {
                    $validator->errors()->add('escalated_to_id', 'The selected admin does not exist.');
                }
            } elseif ($type === 'doctor') {
                $exists = \App\Models\Doctor::where('id', $id)->exists();
                if (!$exists) {
                    $validator->errors()->add('escalated_to_id', 'The selected doctor does not exist.');
                }
            }
        });
    }
}
