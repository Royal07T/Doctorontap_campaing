<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Get doctor reviews (public)
     */
    public function getDoctorReviews($doctorId)
    {
        $doctor = Doctor::findOrFail($doctorId);
        $reviews = Review::where('doctor_id', $doctorId)
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'doctor' => $doctor,
                'reviews' => $reviews,
                'average_rating' => $doctor->average_rating,
                'total_reviews' => $doctor->total_reviews,
            ]
        ]);
    }

    /**
     * Create a review
     */
    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'consultation_id' => 'required|exists:consultations,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        // SECURITY: Sanitize comment to prevent XSS
        $data = $request->all();
        if (isset($data['comment'])) {
            $data['comment'] = strip_tags(trim($data['comment']));
        }

        $user = Auth::user();
        
        // Only patients can create reviews
        if ($user->getMorphClass() !== 'Patient') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if consultation belongs to patient
        $consultation = \App\Models\Consultation::findOrFail($request->consultation_id);
        if ($consultation->patient_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if review already exists
        $existingReview = Review::where('consultation_id', $request->consultation_id)->first();
        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'Review already exists for this consultation',
            ], 400);
        }

        $review = Review::create([
            'doctor_id' => $request->doctor_id,
            'consultation_id' => $request->consultation_id,
            'patient_id' => $user->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => false, // Requires admin approval
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully. It will be published after admin approval.',
            'data' => $review,
        ], 201);
    }

    /**
     * Get user's reviews
     */
    public function myReviews(Request $request)
    {
        $user = Auth::user();
        
        if ($user->getMorphClass() !== 'Patient') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $reviews = Review::where('patient_id', $user->id)
            ->with(['doctor', 'consultation'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }
}

