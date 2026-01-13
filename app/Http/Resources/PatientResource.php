<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'age' => $this->age,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'blood_group' => $this->blood_group,
            'genotype' => $this->genotype,
            'has_consulted' => $this->has_consulted,
            'consultations_count' => $this->consultations_count,
            'last_consultation_at' => $this->last_consultation_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

