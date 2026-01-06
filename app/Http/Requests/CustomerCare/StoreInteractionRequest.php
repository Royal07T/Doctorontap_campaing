<?php

namespace App\Http\Requests\CustomerCare;

use Illuminate\Foundation\Http\FormRequest;

class StoreInteractionRequest extends FormRequest
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
            'user_id' => 'required|exists:patients,id',
            'channel' => 'required|in:chat,call,email',
            'summary' => 'required|string|min:10|max:1000',
            'status' => 'sometimes|in:active,resolved,pending',
        ];
    }
}
