<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MedicalDocumentController extends Controller
{
    /**
     * Download a medical document securely with authentication and authorization
     *
     * @param Request $request
     * @param int $consultationId
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response
     */
    public function download(Request $request, $consultationId, $filename)
    {
        try {
            // Find the consultation
            $consultation = Consultation::findOrFail($consultationId);
            
            // Authorization checks
            $user = auth()->user();
            $authorized = false;
            $userType = null;
            
            // Check if user is authenticated
            if (!$user) {
                // Check for admin, doctor, nurse, or patient auth
                if (auth()->guard('admin')->check()) {
                    $user = auth()->guard('admin')->user();
                    $userType = 'admin';
                    $authorized = true; // Admins can access all documents
                } elseif (auth()->guard('doctor')->check()) {
                    $user = auth()->guard('doctor')->user();
                    $userType = 'doctor';
                    // Doctors can only access their assigned consultations
                    $authorized = $consultation->doctor_id == $user->id;
                } elseif (auth()->guard('nurse')->check()) {
                    $user = auth()->guard('nurse')->user();
                    $userType = 'nurse';
                    // Nurses can only access consultations they're assigned to
                    $authorized = $consultation->nurse_id == $user->id;
                } elseif (auth()->guard('patient')->check()) {
                    $user = auth()->guard('patient')->user();
                    $userType = 'patient';
                    // Patients can access their own consultations
                    $authorized = $consultation->email == $user->email;
                } elseif (auth()->guard('canvasser')->check()) {
                    $user = auth()->guard('canvasser')->user();
                    $userType = 'canvasser';
                    // Canvassers can access consultations they created
                    $authorized = $consultation->canvasser_id == $user->id;
                }
            }
            
            // Deny access if not authorized
            if (!$authorized || !$user) {
                Log::warning('Unauthorized medical document access attempt', [
                    'consultation_id' => $consultationId,
                    'filename' => $filename,
                    'user_id' => $user?->id ?? null,
                    'user_type' => $userType ?? 'unauthenticated',
                    'ip_address' => $request->ip(),
                ]);
                
                abort(403, 'Unauthorized access to medical document.');
            }
            
            // Verify the file belongs to this consultation
            $documents = $consultation->medical_documents ?? [];
            $documentFound = false;
            
            foreach ($documents as $document) {
                if ($document['stored_name'] === $filename) {
                    $documentFound = true;
                    break;
                }
            }
            
            if (!$documentFound) {
                Log::warning('Medical document not found in consultation', [
                    'consultation_id' => $consultationId,
                    'filename' => $filename,
                ]);
                
                abort(404, 'Document not found.');
            }
            
            // Build the file path
            $filePath = 'medical_documents/' . $filename;
            
            // Check if file exists in storage
            if (!Storage::exists($filePath)) {
                Log::error('Medical document file missing from storage', [
                    'consultation_id' => $consultationId,
                    'filename' => $filename,
                    'path' => $filePath,
                ]);
                
                abort(404, 'Document file not found.');
            }
            
            // Log the access (HIPAA audit trail)
            Log::channel('audit')->info('Medical Document Downloaded', [
                'action' => 'download',
                'consultation_id' => $consultationId,
                'filename' => $filename,
                'user_type' => $userType,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);
            
            // Return the file for download
            return Storage::download($filePath, $document['original_name'] ?? $filename);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Consultation not found for document download', [
                'consultation_id' => $consultationId,
                'filename' => $filename,
            ]);
            
            abort(404, 'Consultation not found.');
        } catch (\Exception $e) {
            Log::error('Error downloading medical document', [
                'consultation_id' => $consultationId,
                'filename' => $filename,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            abort(500, 'Error downloading document.');
        }
    }
    
    /**
     * View a medical document inline (for images/PDFs) with authentication
     *
     * @param Request $request
     * @param int $consultationId
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function view(Request $request, $consultationId, $filename)
    {
        try {
            // Find the consultation
            $consultation = Consultation::findOrFail($consultationId);
            
            // Authorization checks (same as download)
            $user = auth()->user();
            $authorized = false;
            $userType = null;
            
            if (!$user) {
                if (auth()->guard('admin')->check()) {
                    $user = auth()->guard('admin')->user();
                    $userType = 'admin';
                    $authorized = true;
                } elseif (auth()->guard('doctor')->check()) {
                    $user = auth()->guard('doctor')->user();
                    $userType = 'doctor';
                    $authorized = $consultation->doctor_id == $user->id;
                } elseif (auth()->guard('nurse')->check()) {
                    $user = auth()->guard('nurse')->user();
                    $userType = 'nurse';
                    $authorized = $consultation->nurse_id == $user->id;
                } elseif (auth()->guard('patient')->check()) {
                    $user = auth()->guard('patient')->user();
                    $userType = 'patient';
                    $authorized = $consultation->email == $user->email;
                } elseif (auth()->guard('canvasser')->check()) {
                    $user = auth()->guard('canvasser')->user();
                    $userType = 'canvasser';
                    $authorized = $consultation->canvasser_id == $user->id;
                }
            }
            
            if (!$authorized || !$user) {
                abort(403, 'Unauthorized access.');
            }
            
            // Verify file belongs to consultation
            $documents = $consultation->medical_documents ?? [];
            $documentFound = false;
            
            foreach ($documents as $document) {
                if ($document['stored_name'] === $filename) {
                    $documentFound = true;
                    break;
                }
            }
            
            if (!$documentFound) {
                abort(404, 'Document not found.');
            }
            
            $filePath = 'medical_documents/' . $filename;
            
            if (!Storage::exists($filePath)) {
                abort(404, 'File not found.');
            }
            
            // Log the view access
            Log::channel('audit')->info('Medical Document Viewed', [
                'action' => 'view',
                'consultation_id' => $consultationId,
                'filename' => $filename,
                'user_type' => $userType,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);
            
            // Return file inline
            return Storage::response($filePath);
            
        } catch (\Exception $e) {
            Log::error('Error viewing medical document', [
                'error' => $e->getMessage(),
            ]);
            
            abort(500, 'Error viewing document.');
        }
    }
    
    /**
     * Download a treatment plan attachment securely with authentication
     *
     * @param Request $request
     * @param int $id
     * @param string $file
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response
     */
    public function downloadTreatmentPlanAttachment(Request $request, $id, $file)
    {
        try {
            // Find the consultation
            $consultation = Consultation::findOrFail($id);
            
            // Authorization checks
            $user = auth()->user();
            $authorized = false;
            $userType = null;
            
            // Check if user is authenticated
            if (!$user) {
                if (auth()->guard('admin')->check()) {
                    $user = auth()->guard('admin')->user();
                    $userType = 'admin';
                    $authorized = true;
                } elseif (auth()->guard('doctor')->check()) {
                    $user = auth()->guard('doctor')->user();
                    $userType = 'doctor';
                    $authorized = $consultation->doctor_id == $user->id;
                } elseif (auth()->guard('patient')->check()) {
                    $user = auth()->guard('patient')->user();
                    $userType = 'patient';
                    // Patients can access their own consultations
                    $authorized = ($consultation->patient_id && $consultation->patient_id == $user->id) || 
                                  ($consultation->email && $consultation->email == $user->email) ||
                                  ($user->email && $consultation->email && strtolower($consultation->email) === strtolower($user->email));
                }
            }
            
            // Deny access if not authorized
            if (!$authorized || !$user) {
                Log::warning('Unauthorized treatment plan attachment access attempt', [
                    'consultation_id' => $id,
                    'filename' => $file,
                    'user_id' => $user?->id ?? null,
                    'user_type' => $userType ?? 'unauthenticated',
                    'ip_address' => $request->ip(),
                ]);
                
                abort(403, 'Unauthorized access to treatment plan attachment.');
            }
            
            // Verify the file belongs to this consultation
            $attachments = $consultation->treatment_plan_attachments ?? [];
            $attachmentFound = null;
            
            foreach ($attachments as $attachment) {
                if (($attachment['stored_name'] ?? basename($attachment['path'] ?? '')) === $file) {
                    $attachmentFound = $attachment;
                    break;
                }
            }
            
            if (!$attachmentFound) {
                Log::warning('Treatment plan attachment not found in consultation', [
                    'consultation_id' => $id,
                    'filename' => $file,
                ]);
                
                abort(404, 'Attachment not found.');
            }
            
            // Build the file path
            $filePath = $attachmentFound['path'] ?? 'treatment_plan_attachments/' . $file;
            
            // Check if file exists in storage
            if (!Storage::exists($filePath)) {
                Log::error('Treatment plan attachment file missing from storage', [
                    'consultation_id' => $id,
                    'filename' => $file,
                    'path' => $filePath,
                ]);
                
                abort(404, 'Attachment file not found.');
            }
            
            // Log the access (HIPAA audit trail)
            Log::channel('audit')->info('Treatment Plan Attachment Downloaded', [
                'action' => 'download',
                'consultation_id' => $id,
                'filename' => $file,
                'user_type' => $userType,
                'user_id' => $user->id,
                'user_email' => $user->email ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);
            
            // Return the file for download
            return Storage::download($filePath, $attachmentFound['original_name'] ?? $file);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Consultation not found for treatment plan attachment download', [
                'consultation_id' => $id,
                'filename' => $file,
            ]);
            
            abort(404, 'Consultation not found.');
        } catch (\Exception $e) {
            Log::error('Error downloading treatment plan attachment', [
                'consultation_id' => $id,
                'filename' => $file,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            abort(500, 'Error downloading attachment.');
        }
    }
}

