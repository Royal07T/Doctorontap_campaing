<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MedicalDocumentController extends Controller
{
    /**
     * Get documents for a consultation
     */
    public function index($id)
    {
        $consultation = Consultation::findOrFail($id);
        $user = Auth::user();
        $userType = $user->getMorphClass();

        // Check authorization
        if ($userType === 'Patient' && $consultation->patient_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($userType === 'Doctor' && $consultation->doctor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $documents = $consultation->medical_documents ?? [];

        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    /**
     * Store medical document
     */
    public function store(Request $request, $id)
    {
        $consultation = Consultation::findOrFail($id);
        $user = Auth::user();

        // Only patient or doctor can upload documents
        $userType = $user->getMorphClass();
        if ($userType === 'Patient' && $consultation->patient_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($userType === 'Doctor' && $consultation->doctor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'document' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        // Implementation would handle file upload
        // This is a placeholder
        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully'
        ], 201);
    }

    /**
     * Download medical document
     */
    public function download($id, $filename)
    {
        $consultation = Consultation::findOrFail($id);
        $user = Auth::user();
        $userType = $user->getMorphClass();

        // Check authorization
        if ($userType === 'Patient' && $consultation->patient_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($userType === 'Doctor' && $consultation->doctor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Implementation would return file download
        return response()->json([
            'success' => false,
            'message' => 'Download functionality to be implemented'
        ], 501);
    }

    /**
     * View medical document
     */
    public function view($id, $filename)
    {
        $consultation = Consultation::findOrFail($id);
        $user = Auth::user();
        $userType = $user->getMorphClass();

        // Check authorization
        if ($userType === 'Patient' && $consultation->patient_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($userType === 'Doctor' && $consultation->doctor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Implementation would return file view
        return response()->json([
            'success' => false,
            'message' => 'View functionality to be implemented'
        ], 501);
    }
}

