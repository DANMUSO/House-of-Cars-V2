<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AgreementFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AgreementfileController extends Controller
{
    /**
     * Handle PDF upload from AJAX request.
     */
    public function upload(Request $request)
    {
        set_time_limit(300); // 5 minutes

        $request->validate([
            'agreement_id'   => 'required|integer',
            'agreement_type' => 'required|string|max:255',
            'agreement_file' => 'required|mimes:pdf|max:1048576'
        ], [
            'agreement_file.max'   => 'The agreement file must not be larger than 1GB.',
            'agreement_file.mimes' => 'The agreement file must be a PDF.'
        ]);

        $id             = $request->agreement_id;
        $agreement_type = $request->agreement_type;
        $file           = $request->file('agreement_file');

        try {
            // Validate file upload
            if (!$file->isValid()) {
                return response()->json([
                    'error' => 'Invalid file upload. Please try again.'
                ], 422);
            }

            if ($file->getSize() > 1073741824) {
                return response()->json([
                    'error' => 'File size exceeds 1GB limit.'
                ], 422);
            }

            // Generate unique filename
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension    = $file->getClientOriginalExtension();
            $filename     = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;

            // Log upload attempt for debugging
            Log::info('Starting S3 upload', [
                'agreement_id' => $id,
                'filename' => $filename,
                'file_size' => $file->getSize(),
                'original_name' => $file->getClientOriginalName()
            ]);

            // Upload directly using AWS SDK with SSL disabled
            $filePath = $this->uploadToS3Direct($file, $filename);

            // Verify the file exists on S3 using direct method
            if (!$this->checkS3FileExists($filePath)) {
                throw new \Exception('S3 upload verification failed - file does not exist on S3 after upload.');
            }

            // Get file information
            $fileSize          = $file->getSize();
            $fileSizeFormatted = $this->formatFileSize($fileSize);

            // Save to database
            $agreement = AgreementFile::updateOrCreate(
                [
                    'agreement_id'   => $id,
                    'agreement_type' => $agreement_type
                ],
                [
                    'agreement_path'     => $filePath,
                    'original_filename'  => $file->getClientOriginalName(),
                    'file_size'          => $fileSize,
                    'mime_type'          => $file->getMimeType(),
                    'uploaded_at'        => now()
                ]
            );

            // Log successful upload for large files
            if ($fileSize > 100 * 1024 * 1024) {
                Log::info("Large PDF uploaded successfully", [
                    'agreement_id' => $id,
                    'file_size'    => $fileSizeFormatted,
                    'file_path'    => $filePath
                ]);
            }

            // Generate S3 URL using direct method
            $pdfUrl = null;
            try {
                $pdfUrl = "https://" . config('filesystems.disks.s3.bucket') . ".s3." . config('filesystems.disks.s3.region') . ".amazonaws.com/" . $filePath;
            } catch (\Exception $e) {
                Log::warning('Failed to generate S3 URL', [
                    'file_path' => $filePath,
                    'error' => $e->getMessage()
                ]);
                // Continue without URL - can be generated later
            }

            Log::info('S3 upload completed successfully', [
                'agreement_id' => $id,
                'file_path' => $filePath,
                'file_size' => $fileSizeFormatted
            ]);

            return response()->json([
                'success'      => true,
                'message'      => 'Agreement uploaded successfully',
                'pdfUrl'       => $pdfUrl,
                'fileSize'     => $fileSizeFormatted,
                'originalName' => $file->getClientOriginalName(),
                'filePath'     => $filePath
            ]);

        } catch (\Exception $e) {
            Log::error('Agreement upload failed', [
                'agreement_id' => $id,
                'agreement_type' => $agreement_type,
                'error'        => $e->getMessage(),
                'file_size'    => $file ? $file->getSize() : 'unknown',
                'trace'        => $e->getTraceAsString()
            ]);

            // Clean up failed upload if file path exists
            if (isset($filePath) && $filePath) {
                try {
                    // Use direct S3 client for cleanup
                    $s3Client = new \Aws\S3\S3Client([
                        'version' => 'latest',
                        'region'  => config('filesystems.disks.s3.region'),
                        'credentials' => [
                            'key'    => config('filesystems.disks.s3.key'),
                            'secret' => config('filesystems.disks.s3.secret'),
                        ],
                        'http' => ['verify' => false]
                    ]);
                    
                    $s3Client->deleteObject([
                        'Bucket' => config('filesystems.disks.s3.bucket'),
                        'Key' => $filePath
                    ]);
                    
                    Log::info('Cleaned up failed upload file', ['file_path' => $filePath]);
                } catch (\Exception $cleanup_error) {
                    Log::warning('Failed to clean up file after upload error', [
                        'file_path' => $filePath,
                        'cleanup_error' => $cleanup_error->getMessage()
                    ]);
                }
            }

            return response()->json([
                'error' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if file exists on S3 using direct AWS SDK
     */
    private function checkS3FileExists($key)
    {
        try {
            $s3Client = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => config('filesystems.disks.s3.region'),
                'credentials' => [
                    'key'    => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
                'http' => [
                    'verify' => false
                ]
            ]);

            $result = $s3Client->doesObjectExist(
                config('filesystems.disks.s3.bucket'),
                $key
            );

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to check S3 file existence', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Upload file directly to S3 using AWS SDK with SSL disabled
     */
    private function uploadToS3Direct($file, $filename)
    {
        try {
            $s3Client = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => config('filesystems.disks.s3.region'),
                'credentials' => [
                    'key'    => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
                'http' => [
                    'verify' => false // Disable SSL verification for development
                ]
            ]);

            $key = "agreements/" . $filename;
            
            $result = $s3Client->putObject([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key'    => $key,
                'Body'   => fopen($file->getPathname(), 'r'),
                'ContentType' => $file->getMimeType(),
            ]);

            Log::info('Direct S3 upload successful', [
                'key' => $key,
                'object_url' => $result['ObjectURL'] ?? 'N/A'
            ]);

            return $key; // Return the S3 key path

        } catch (\Exception $e) {
            Log::error('Direct S3 upload failed', [
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Direct S3 upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Test S3 connection before attempting file upload
     */
    private function testS3Connection()
    {
        try {
            // Test basic S3 connectivity
            $testFileName = 'test-connection-' . time() . '.txt';
            $testContent = 'S3 connection test';
            
            $result = Storage::disk('s3')->put($testFileName, $testContent);
            
            if (!$result) {
                throw new \Exception('S3 test upload failed');
            }
            
            // Verify file exists
            if (!Storage::disk('s3')->exists($testFileName)) {
                throw new \Exception('S3 test file verification failed');
            }
            
            // Clean up test file
            Storage::disk('s3')->delete($testFileName);
            
            Log::info('S3 connection test successful');
            
        } catch (\Exception $e) {
            Log::error('S3 connection test failed', [
                'error' => $e->getMessage(),
                'config' => [
                    'bucket' => config('filesystems.disks.s3.bucket'),
                    'region' => config('filesystems.disks.s3.region'),
                    'key_present' => !empty(config('filesystems.disks.s3.key')),
                    'secret_present' => !empty(config('filesystems.disks.s3.secret'))
                ]
            ]);
            
            throw new \Exception('S3 connection failed: ' . $e->getMessage() . '. Please check your S3 configuration.');
        }
    }

    /**
     * Format file size in human readable format
     */
    private function formatFileSize($bytes)
    {
        if ($bytes == 0) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes) / log(1024));
        $power = min($power, count($units) - 1);

        $size = $bytes / pow(1024, $power);

        return round($size, 2) . ' ' . $units[$power];
    }

    /**
     * Delete agreement file
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

            // Delete file from S3 with error handling
            if ($agreement->agreement_path) {
                try {
                    $deleteResult = $this->deleteFromS3Direct($agreement->agreement_path);
                    
                    Log::info('File deleted from S3', [
                        'agreement_id' => $agreementId,
                        'file_path' => $agreement->agreement_path,
                        'result' => $deleteResult
                    ]);
                    
                } catch (\Exception $e) {
                    Log::error('Failed to delete file from S3', [
                        'agreement_id' => $agreementId,
                        'file_path' => $agreement->agreement_path,
                        'error' => $e->getMessage()
                    ]);
                    // Continue with database deletion even if S3 deletion fails
                }
            }

            // Delete database record
            $agreement->delete();

            return response()->json([
                'success' => true,
                'message' => 'Agreement deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Agreement deletion failed', [
                'agreement_id' => $agreementId,
                'error'        => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Deletion failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show agreement file with temporary URL
     */
    public function show($id, $type)
    {
        try {
            $agreement = AgreementFile::where('agreement_id', $id)
                                    ->where('agreement_type', $type)
                                    ->first();

            if (!$agreement) {
                abort(404, 'Agreement not found');
            }

            if (!$agreement->agreement_path) {
                abort(404, 'Agreement file path not found');
            }

            // For HEAD requests (checking existence), just return 200 if agreement exists
            if (request()->isMethod('HEAD')) {
                return response('', 200);
            }

            // Check if file exists on S3 only for GET requests (actual viewing)
            if (!$this->checkS3FileExists($agreement->agreement_path)) {
                Log::error('Agreement file not found on S3', [
                    'agreement_id' => $id,
                    'agreement_type' => $type,
                    'file_path' => $agreement->agreement_path
                ]);
                abort(404, 'Agreement file not found on storage');
            }

            // Generate temporary URL using direct method (24 hours for better user experience)
            $temporaryUrl = $this->generateTemporaryUrl($agreement->agreement_path, 1440); // 24 hours

            return redirect($temporaryUrl);

        } catch (\Exception $e) {
            Log::error('Failed to show agreement', [
                'agreement_id' => $id,
                'agreement_type' => $type,
                'error' => $e->getMessage()
            ]);
            
            abort(500, 'Failed to access agreement file');
        }
    }

    /**
     * Check if agreement exists (for AJAX calls from frontend)
     */
    public function checkExists($id, $type)
    {
        try {
            $agreement = AgreementFile::where('agreement_id', $id)
                                    ->where('agreement_type', $type)
                                    ->first();

            if (!$agreement) {
                return response()->json(['exists' => false], 404);
            }

            // Check if file exists on S3
            if (!$this->checkS3FileExists($agreement->agreement_path)) {
                return response()->json(['exists' => false], 404);
            }

            return response()->json([
                'exists' => true,
                'data' => [
                    'original_filename' => $agreement->original_filename,
                    'file_size' => $this->formatFileSize($agreement->file_size),
                    'uploaded_at' => $agreement->uploaded_at->format('Y-m-d H:i:s'),
                    'mime_type' => $agreement->mime_type,
                    'url' => route('agreement.show', ['id' => $id, 'type' => $type])
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to check agreement existence', [
                'agreement_id' => $id,
                'agreement_type' => $type,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['exists' => false], 500);
        }
    }

    /**
     * Alternative destroy method (alias for delete) - matches frontend AJAX call
     */
    public function destroy($id)
    {
        try {
            // Find agreement by agreement_id (not by primary key)
            $agreement = AgreementFile::where('agreement_id', $id)->first();

            if (!$agreement) {
                return response()->json([
                    'success' => false,
                    'error' => 'Agreement not found'
                ], 404);
            }

            // Delete from S3 using direct method
            if ($agreement->agreement_path) {
                try {
                    $this->deleteFromS3Direct($agreement->agreement_path);
                    Log::info('Agreement file deleted from S3', [
                        'agreement_id' => $id,
                        'file_path' => $agreement->agreement_path
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete file from S3, continuing with database deletion', [
                        'agreement_id' => $id,
                        'file_path' => $agreement->agreement_path,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Delete from database
            $agreement->delete();

            return response()->json([
                'success' => true,
                'message' => 'Agreement deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Agreement destroy failed', [
                'agreement_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Deletion failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete file from S3 using direct AWS SDK
     */
    private function deleteFromS3Direct($key)
    {
        try {
            $s3Client = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => config('filesystems.disks.s3.region'),
                'credentials' => [
                    'key'    => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
                'http' => [
                    'verify' => false
                ]
            ]);

            $result = $s3Client->deleteObject([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $key
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to delete file from S3', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate temporary URL using direct AWS SDK
     */
    private function generateTemporaryUrl($key, $minutes = 5)
    {
        try {
            $s3Client = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => config('filesystems.disks.s3.region'),
                'credentials' => [
                    'key'    => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
                'http' => [
                    'verify' => false
                ]
            ]);

            $command = $s3Client->getCommand('GetObject', [
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $key
            ]);

            $request = $s3Client->createPresignedRequest($command, "+{$minutes} minutes");

            return (string) $request->getUri();

        } catch (\Exception $e) {
            Log::error('Failed to generate temporary URL', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get agreement file info (for AJAX requests)
     */
    public function info($id, $type)
    {
        try {
            \Log::info('Info method called', ['id' => $id, 'type' => $type]);
            
            $agreement = AgreementFile::where('agreement_id', $id)
                                    ->where('agreement_type', $type)
                                    ->first();

            \Log::info('Agreement query result', ['agreement' => $agreement ? 'found' : 'not found']);

            if (!$agreement) {
                return response()->json(['exists' => false], 404);
            }

            // Check if file exists on S3
            \Log::info('Checking S3 file existence', ['path' => $agreement->agreement_path]);
            $fileExists = $this->checkS3FileExists($agreement->agreement_path);
            \Log::info('S3 file check result', ['exists' => $fileExists]);
            
            if (!$fileExists) {
                return response()->json(['exists' => false], 404);
            }

            return response()->json([
                'exists' => true,
                'data' => [
                    'original_filename' => $agreement->original_filename,
                    'file_size' => $this->formatFileSize($agreement->file_size),
                    'uploaded_at' => $agreement->uploaded_at->format('Y-m-d H:i:s'),
                    'mime_type' => $agreement->mime_type,
                    'url' => route('agreement.show', ['id' => $id, 'type' => $type])
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Info method error', [
                'id' => $id,
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'exists' => false, 
                'error' => $e->getMessage()
            ], 500);
        }
    }
}