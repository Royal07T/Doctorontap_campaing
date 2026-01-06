<?php

namespace App\Http\Requests\CustomerCare;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
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
            'category' => 'required|in:billing,appointment,technical,medical',
            'subject' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:10|max:2000',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'status' => 'sometimes|in:open,pending,resolved,escalated',
        ];
    }
}
