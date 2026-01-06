<?php

namespace App\Http\Requests\CustomerCare;

use Illuminate\Foundation\Http\FormRequest;

class AddNoteRequest extends FormRequest
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
            'note' => 'required|string|min:5|max:2000',
            'is_internal' => 'sometimes|boolean',
        ];
    }
}
