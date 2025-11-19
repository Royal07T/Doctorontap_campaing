<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Helpers\SecurityHelper;

class FileUploadService
{
    /**
     * Allowed MIME types for medical documents
     */
    private const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    /**
     * Maximum file size in KB (5MB)
     */
    private const MAX_FILE_SIZE = 5120;

    /**
     * Validate and upload a medical document
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return array|null Returns file information or null on failure
     */
    public function uploadMedicalDocument(UploadedFile $file, string $directory = 'medical_documents'): ?array
    {
        try {
            // Validate file
            if (!$this->validateFile($file)) {
                return null;
            }

            // Sanitize filename
            $originalName = $file->getClientOriginalName();
            $sanitizedFilename = SecurityHelper::sanitizeFilename($originalName);
            
            // Generate unique filename
            $uniqueFilename = time() . '_' . uniqid() . '_' . $sanitizedFilename;
            
            // Store file in private storage (not publicly accessible)
            $filePath = $file->storeAs($directory, $uniqueFilename, 'private');
            
            if (!$filePath) {
                Log::error('Failed to store uploaded file', [
                    'original_name' => $originalName,
                    'directory' => $directory
                ]);
                return null;
            }

            // Return file information
            return [
                'original_name' => $originalName,
                'stored_name' => $uniqueFilename,
                'path' => $filePath,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_at' => now()->toDateTimeString(),
            ];

        } catch (\Exception $e) {
            Log::error('File upload error: ' . $e->getMessage(), [
                'file' => $file->getClientOriginalName(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Validate uploaded file for security
     *
     * @param UploadedFile $file
     * @return bool
     */
    public function validateFile(UploadedFile $file): bool
    {
        // Check if file is valid
        if (!$file->isValid()) {
            Log::warning('Invalid file upload attempt', [
                'error' => $file->getError(),
                'original_name' => $file->getClientOriginalName()
            ]);
            return false;
        }

        // Check file size
        if ($file->getSize() > (self::MAX_FILE_SIZE * 1024)) {
            Log::warning('File size exceeds limit', [
                'size' => $file->getSize(),
                'max_size' => self::MAX_FILE_SIZE * 1024,
                'original_name' => $file->getClientOriginalName()
            ]);
            return false;
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            SecurityHelper::logSecurityIncident('invalid_file_mime_type', [
                'mime_type' => $mimeType,
                'original_name' => $file->getClientOriginalName()
            ]);
            return false;
        }

        // Validate file extension matches MIME type
        $extension = strtolower($file->getClientOriginalExtension());
        if (!$this->validateExtensionMatchesMime($extension, $mimeType)) {
            SecurityHelper::logSecurityIncident('mime_extension_mismatch', [
                'extension' => $extension,
                'mime_type' => $mimeType,
                'original_name' => $file->getClientOriginalName()
            ]);
            return false;
        }

        // Check for double extensions (e.g., file.php.jpg)
        if (substr_count($file->getClientOriginalName(), '.') > 1) {
            SecurityHelper::logSecurityIncident('double_extension_detected', [
                'original_name' => $file->getClientOriginalName()
            ]);
            return false;
        }

        // Additional security check: scan file content for malicious patterns
        if (!$this->scanFileContent($file)) {
            SecurityHelper::logSecurityIncident('malicious_file_content', [
                'original_name' => $file->getClientOriginalName()
            ]);
            return false;
        }

        return true;
    }

    /**
     * Validate that file extension matches MIME type
     *
     * @param string $extension
     * @param string $mimeType
     * @return bool
     */
    private function validateExtensionMatchesMime(string $extension, string $mimeType): bool
    {
        $validCombinations = [
            'pdf' => ['application/pdf'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        ];

        if (!isset($validCombinations[$extension])) {
            return false;
        }

        return in_array($mimeType, $validCombinations[$extension]);
    }

    /**
     * Scan file content for malicious patterns
     *
     * @param UploadedFile $file
     * @return bool
     */
    private function scanFileContent(UploadedFile $file): bool
    {
        try {
            // Read first 8KB of file for pattern matching
            $handle = fopen($file->getRealPath(), 'r');
            if (!$handle) {
                return false;
            }

            $content = fread($handle, 8192);
            fclose($handle);

            // Check for PHP code in supposedly non-PHP files
            $dangerous_patterns = [
                '/<\?php/i',
                '/<\?=/i',
                '/<script/i',
                '/eval\s*\(/i',
                '/exec\s*\(/i',
                '/system\s*\(/i',
                '/passthru\s*\(/i',
                '/shell_exec\s*\(/i',
                '/base64_decode\s*\(/i',
            ];

            foreach ($dangerous_patterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    return false;
                }
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Error scanning file content: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a file from storage
     *
     * @param string $filePath
     * @param string $disk
     * @return bool
     */
    public function deleteFile(string $filePath, string $disk = 'private'): bool
    {
        try {
            if (Storage::disk($disk)->exists($filePath)) {
                return Storage::disk($disk)->delete($filePath);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Error deleting file: ' . $e->getMessage(), [
                'file_path' => $filePath,
                'disk' => $disk
            ]);
            return false;
        }
    }

    /**
     * Get file URL for private files
     *
     * @param string $filePath
     * @return string|null
     */
    public function getPrivateFileUrl(string $filePath): ?string
    {
        try {
            if (Storage::disk('private')->exists($filePath)) {
                // Generate temporary URL (valid for 60 minutes)
                return Storage::disk('private')->temporaryUrl($filePath, now()->addMinutes(60));
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Error generating file URL: ' . $e->getMessage(), [
                'file_path' => $filePath
            ]);
            return null;
        }
    }
}

