<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * Show the review form for a consultation
     */
    public function showReviewForm($reference)
    {
        try {
            $consultation = Consultation::where('reference', $reference)
                                       ->with('doctor')
                                       ->firstOrFail();

            // Check if consultation is completed
            if (!$consultation->isCompleted()) {
                return view('reviews.error')->with([
                    'message' => 'You can only review completed consultations.',
                    'suggestion' => 'Please wait until your consultation is marked as completed.'
                ]);
            }

            // Check if patient already reviewed this consultation
            if ($consultation->hasPatientReview()) {
                return view('reviews.error')->with([
                    'message' => 'You have already submitted a review for this consultation.',
                    'suggestion' => 'Thank you for your feedback!'
                ]);
            }

            return view('reviews.patient-review-form', compact('consultation'));

        } catch (\Exception $e) {
            return view('reviews.error')->with([
                'message' => 'Consultation not found.',
                'suggestion' => 'Please check your consultation reference and try again.'
            ]);
        }
    }

    /**
     * Store a review from a patient about a doctor
     */
    public function storePatientReview(Request $request)
    {
        try {
            $validated = $request->validate([
                'consultation_id' => 'required|exists:consultations,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
                'would_recommend' => 'nullable|boolean',
                'tags' => 'nullable|array',
            ]);

            $consultation = Consultation::with('doctor')->findOrFail($validated['consultation_id']);

            // Check if consultation is completed
            if (!$consultation->isCompleted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only review completed consultations'
                ], 400);
            }

            // Check if patient already reviewed this consultation
            if ($consultation->hasPatientReview()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reviewed this consultation'
                ], 400);
            }

            // Get patient - prefer authenticated patient, otherwise lookup by email
            $patient = null;
            
            // Check if patient is authenticated
            if (Auth::guard('patient')->check()) {
                $patient = Auth::guard('patient')->user();
                
                // Verify the consultation belongs to this patient
                if ($consultation->email !== $patient->email) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This consultation does not belong to you'
                    ], 403);
                }
            } else {
                // Get or create patient record (including soft-deleted)
                $patient = Patient::withTrashed()->where('email', $consultation->email)->first();
                if ($patient) {
                    // If patient is soft-deleted, restore it
                    if ($patient->trashed()) {
                        $patient->restore();
                        \Log::info('Restored soft-deleted patient for review', [
                            'patient_id' => $patient->id,
                            'email' => $consultation->email
                        ]);
                    }
                } else {
                    // Create new patient
                    $patient = Patient::create([
                        'name' => $consultation->full_name,
                        'email' => $consultation->email,
                        'phone' => $consultation->mobile,
                        'gender' => $consultation->gender,
                    ]);
                }
            }

            // Create review
            $review = Review::create([
                'consultation_id' => $consultation->id,
                'reviewer_type' => 'patient',
                'patient_id' => $patient->id,
                'reviewee_type' => 'doctor',
                'reviewee_doctor_id' => $consultation->doctor_id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
                'would_recommend' => $validated['would_recommend'] ?? true,
                'tags' => $validated['tags'] ?? null,
                'is_published' => true,
                'is_verified' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your feedback! Your review has been submitted successfully.',
                'review' => $review
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to store patient review: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a review from a doctor about a patient or platform
     */
    public function storeDoctorReview(Request $request)
    {
        try {
            $validated = $request->validate([
                'consultation_id' => 'required|exists:consultations,id',
                'reviewee_type' => 'required|in:patient,platform',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
                'would_recommend' => 'nullable|boolean',
                'tags' => 'nullable|array',
            ]);

            $doctor = Auth::guard('doctor')->user();
            $consultation = Consultation::findOrFail($validated['consultation_id']);

            // Check if doctor is assigned to this consultation
            if ($consultation->doctor_id !== $doctor->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only review your own consultations'
                ], 403);
            }

            // Check if consultation is completed
            if (!$consultation->isCompleted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only review completed consultations'
                ], 400);
            }

            // Check if doctor already reviewed this consultation
            if ($consultation->hasDoctorReview()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reviewed this consultation'
                ], 400);
            }

            $reviewData = [
                'consultation_id' => $consultation->id,
                'reviewer_type' => 'doctor',
                'doctor_id' => $doctor->id,
                'reviewee_type' => $validated['reviewee_type'],
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
                'would_recommend' => $validated['would_recommend'] ?? true,
                'tags' => $validated['tags'] ?? null,
                'is_published' => true,
                'is_verified' => false,
            ];

            // Set reviewee based on type
            if ($validated['reviewee_type'] === 'patient') {
                $patient = Patient::where('email', $consultation->email)->first();
                if ($patient) {
                    $reviewData['reviewee_patient_id'] = $patient->id;
                }
            }

            // Create review
            $review = Review::create($reviewData);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your feedback! Your review has been submitted successfully.',
                'review' => $review
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to store doctor review: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reviews for a specific doctor (public endpoint)
     */
    public function getDoctorReviews($doctorId)
    {
        try {
            $doctor = Doctor::findOrFail($doctorId);
            
            $reviews = Review::forDoctor($doctorId)
                            ->published()
                            ->with(['patientReviewer', 'consultation'])
                            ->latest()
                            ->paginate(10);

            // Optimize rating distribution - 1 query instead of 6
            $ratingDistribution = Review::forDoctor($doctorId)
                ->published()
                ->selectRaw('rating, COUNT(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray();

            $stats = [
                'average_rating' => $doctor->average_rating,
                'total_reviews' => $doctor->total_reviews,
                'rating_distribution' => [
                    5 => $ratingDistribution[5] ?? 0,
                    4 => $ratingDistribution[4] ?? 0,
                    3 => $ratingDistribution[3] ?? 0,
                    2 => $ratingDistribution[2] ?? 0,
                    1 => $ratingDistribution[1] ?? 0,
                ],
            ];

            return response()->json([
                'success' => true,
                'doctor' => [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                    'specialization' => $doctor->specialization,
                ],
                'reviews' => $reviews,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load reviews'
            ], 500);
        }
    }

    /**
     * Get all published reviews (for homepage/public display)
     */
    public function getPublicReviews()
    {
        try {
            $reviews = Review::published()
                            ->verified()
                            ->with(['patientReviewer', 'revieweeDoctor'])
                            ->where('reviewee_type', 'doctor')
                            ->where('rating', '>=', 4) // Only show 4-5 star reviews publicly
                            ->latest()
                            ->limit(20)
                            ->get();

            return response()->json([
                'success' => true,
                'reviews' => $reviews
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load reviews'
            ], 500);
        }
    }
}

