<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AgreementFile;
use App\Models\GentlemanAgreement;
use App\Models\HirePurchaseAgreement;
use App\Models\InCash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AgreementfileController extends Controller
{
    /**
     * Handle PDF upload from AJAX request.
     */
    public function upload(Request $request)
{
    // Set time limit for large file processing
    set_time_limit(300); // 5 minutes
    
    $request->validate([
        'agreement_id' => 'required|integer',
        'agreement_type'   => 'required|string|max:255',
        'agreement_file' => 'required|mimes:pdf|max:1048576' // Max 1GB (1024*1024 KB)
    ], [
        'agreement_file.max' => 'The agreement file must not be larger than 1GB.',
        'agreement_file.mimes' => 'The agreement file must be a PDF.'
    ]);

    $id = $request->agreement_id;
    $agreement_type = $request->agreement_type;
    $file = $request->file('agreement_file');

    try {
        // Additional file validation
        if (!$file->isValid()) {
            return response()->json([
                'error' => 'Invalid file upload. Please try again.'
            ], 422);
        }

        // Check file size in bytes (1GB = 1073741824 bytes)
        if ($file->getSize() > 1073741824) {
            return response()->json([
                'error' => 'File size exceeds 1GB limit.'
            ], 422);
        }

        // Generate unique filename to prevent conflicts
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $filename = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;

        // Store file with custom name
        $filePath = $file->storeAs("agreements", $filename, "public");

        // Verify file was stored successfully
        if (!Storage::disk('public')->exists($filePath)) {
            return response()->json([
                'error' => 'Failed to store file. Please try again.'
            ], 500);
        }

        // Get file size for logging/display
        $fileSize = $file->getSize();
        $fileSizeFormatted = $this->formatFileSize($fileSize);

        // Save/update DB record with additional metadata agreement_type
         $agreement = AgreementFile::updateOrCreate(
            [
                'agreement_id' => $id,         // Search criteria
                'agreement_type' => $agreement_type  // Include both in search
            ],
            [
                'agreement_path' => $filePath, // Values to create or update
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $fileSize,
                'mime_type' => $file->getMimeType(),
                'uploaded_at' => now()
            ]
        );

        // Log successful upload for large files
        if ($fileSize > 100 * 1024 * 1024) { // Log files larger than 100MB
            \Log::info("Large PDF uploaded", [
                'agreement_id' => $id,
                'file_size' => $fileSizeFormatted,
                'file_path' => $filePath
            ]);
        }

        // Return success response with file info
        return response()->json([
            'success' => true,
            'message' => 'Agreement uploaded successfully',
            'pdfUrl' => Storage::url($filePath),
            'fileSize' => $fileSizeFormatted,
            'originalName' => $file->getClientOriginalName()
        ]);

    } catch (\Exception $e) {
        // Log error for debugging
        \Log::error('Agreement upload failed', [
            'agreement_id' => $id,
            'error' => $e->getMessage(),
            'file_size' => $file ? $file->getSize() : 'unknown'
        ]);

        // Clean up any partially uploaded files
        if (isset($filePath) && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        return response()->json([
            'error' => 'Upload failed: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Format file size in human readable format
 */
private function formatFileSize($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $power = floor(log($bytes) / log(1024));
    $power = min($power, count($units) - 1);
    
    $size = $bytes / pow(1024, $power);
    
    return round($size, 2) . ' ' . $units[$power];
}

/**
 * Additional method to handle file deletion
 */
public function delete($agreementId)
{
    try {
        $agreement = AgreementFile::where('agreement_id', $agreementId)->first();
        
        if (!$agreement) {
            return response()->json([
                'error' => 'Agreement not found.'
            ], 404);
        }

        // Delete physical file
        if (Storage::disk('public')->exists($agreement->agreement_path)) {
            Storage::disk('public')->delete($agreement->agreement_path);
        }

        // Delete database record
        $agreement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Agreement deleted successfully'
        ]);

    } catch (\Exception $e) {
        \Log::error('Agreement deletion failed', [
            'agreement_id' => $agreementId,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'error' => 'Deletion failed: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Show the agreement PDF.
     */
    public function show($id, $type)
        {
            $agreement = AgreementFile::where('agreement_id', $id)
                                    ->where('agreement_type', $type)
                                    ->first();

            if (!$agreement || !Storage::disk('public')->exists($agreement->agreement_path)) {
                abort(404);
            }

            return response()->file(storage_path("app/public/{$agreement->agreement_path}"));
        }
    /**
 * Delete the agreement file.
 */
public function destroy($id)
{
    $agreement = AgreementFile::where('agreement_id', $id)->first();
    
    if (!$agreement) {
        return response()->json(['error' => 'Agreement not found'], 404);
    }
    
    // Delete file from storage
    if (Storage::disk('public')->exists($agreement->file_path)) {
        Storage::disk('public')->delete($agreement->file_path);
    }
    
    // Delete database record
    $agreement->delete();
    
    return response()->json(['message' => 'Agreement deleted successfully']);
}
}
