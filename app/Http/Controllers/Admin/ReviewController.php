<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Display all reviews
     */
    public function index(Request $request)
    {
        $query = Review::with(['patientReviewer', 'doctorReviewer', 'revieweeDoctor', 'revieweePatient', 'consultation']);

        // Filter by reviewer type
        if ($request->filled('reviewer_type')) {
            $query->where('reviewer_type', $request->reviewer_type);
        }

        // Filter by reviewee type
        if ($request->filled('reviewee_type')) {
            $query->where('reviewee_type', $request->reviewee_type);
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Filter by published status
        if ($request->filled('is_published')) {
            $query->where('is_published', $request->is_published);
        }

        // Filter by verified status
        if ($request->filled('is_verified')) {
            $query->where('is_verified', $request->is_verified);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                  ->orWhereHas('patientReviewer', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('revieweeDoctor', function($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $reviews = $query->latest()->paginate(20);

        return view('admin.reviews', compact('reviews'));
    }

    /**
     * Toggle published status
     */
    public function togglePublished($id)
    {
        try {
            $review = Review::findOrFail($id);
            $review->update([
                'is_published' => !$review->is_published
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review ' . ($review->is_published ? 'published' : 'unpublished') . ' successfully',
                'is_published' => $review->is_published
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update review status'
            ], 500);
        }
    }

    /**
     * Verify a review
     */
    public function verify($id)
    {
        try {
            $admin = Auth::guard('admin')->user();
            $review = Review::findOrFail($id);
            
            $review->update([
                'is_verified' => true,
                'verified_at' => now(),
                'verified_by' => $admin->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review verified successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify review'
            ], 500);
        }
    }

    /**
     * Delete a review
     */
    public function destroy($id)
    {
        try {
            $review = Review::findOrFail($id);
            $review->delete();

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review'
            ], 500);
        }
    }
}

