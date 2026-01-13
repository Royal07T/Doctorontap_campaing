<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'specialization' => $this->specialization,
            'consultation_fee' => $this->consultation_fee,
            'location' => $this->location,
            'bio' => $this->bio,
            'is_available' => $this->is_available,
            'is_approved' => $this->is_approved,
            'average_rating' => $this->average_rating,
            'total_reviews' => $this->total_reviews,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

