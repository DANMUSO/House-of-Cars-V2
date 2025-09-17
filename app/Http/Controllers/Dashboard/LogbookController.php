<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CarLogbook;
use App\Models\CustomerVehicle;
use App\Models\CarImport;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LogbookController extends Controller
{
    public function index()
    {
        // Get cars available for new logbooks
        $importedCars = CarImport::where('status', 8)->latest()->get();
        $tradeInCars = CustomerVehicle::latest()->get();
        
        // Get all logbooks with relationships
        $logbooks = CarLogbook::with(['carsImport', 'customerVehicle'])
                             ->latest()
                             ->get();
        
        return view('logbook.index', compact('logbooks', 'importedCars', 'tradeInCars'));
    }

     public function store(Request $request)
{
    try {
        \Log::info('Logbook store method called', ['request_data' => $request->all()]);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'document_type' => 'required|string|in:logbook,registration,insurance,service_record,other',
            'document_date' => 'nullable|date',
            'issued_by' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|string|in:active,archived,expired',
            
            // Car IDs - one is required
            'customer_id' => 'nullable|integer',
            'imported_id' => 'nullable|integer',
            
            // Documents
            'documents.*' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png,gif|max:51200' // 50MB max
        ]);

        \Log::info('Validation passed', ['validated_data' => $validated]);


        // Handle conditional ID logic
        if (!empty($validated['customer_id'])) {
            $logbookData['customer_id'] = $validated['customer_id'];
            $logbookData['imported_id'] = 0;
        } elseif (!empty($validated['imported_id'])) {
            $logbookData['customer_id'] = 0;
            $logbookData['imported_id'] = $validated['imported_id'];
        } else {
            $logbookData['customer_id'] = 0;
            $logbookData['imported_id'] = 0;
        }

        // Remove IDs from validated data and merge with conditional logic
        unset($validated['customer_id'], $validated['imported_id']);
        $logbookData = array_merge($validated, $logbookData);

        // Handle document uploads
        $documentPaths = [];
        $totalSize = 0;
        
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                try {
                    $originalName = pathinfo($document->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $document->getClientOriginalExtension();
                    $filename = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;
                    
                    $documentPath = $this->uploadLogbookDocumentToS3($document, $filename);
                    $documentPaths[] = $documentPath;
                    $totalSize += $document->getSize();
                    
                } catch (\Exception $e) {
                    \Log::error('Logbook document upload failed', [
                        'filename' => $document->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ]);
                    
                    // Clean up already uploaded files
                    $this->cleanupLogbookDocuments($documentPaths);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to upload document: ' . $document->getClientOriginalName()
                    ], 500);
                }
            }
        }

        $logbookData['documents'] = $documentPaths;
        $logbookData['file_count'] = count($documentPaths);
        $logbookData['file_size'] = $this->formatFileSize($totalSize);

        $logbook = CarLogbook::create($logbookData);
        
        \Log::info('Logbook created successfully', ['logbook_id' => $logbook->id]);

        return response()->json([
            'success' => true,
            'message' => 'Car logbook saved successfully!',
            'logbook' => $logbook
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validation failed', ['errors' => $e->errors()]);
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Logbook creation failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to create logbook: ' . $e->getMessage()
        ], 500);
    }
}
/**
 * Get all documents for a logbook
 */
public function getDocuments(CarLogbook $logbook)
{
    try {
        $documents = $this->getLogbookDocumentsWithUrls($logbook);

        return response()->json([
            'success' => true,
            'documents' => $documents,
            'total_count' => count($documents)
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Failed to get logbook documents', [
            'logbook_id' => $logbook->id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to get documents: ' . $e->getMessage()
        ], 500);
    }
}
/**
 * Show the form for editing the specified logbook.
 */
public function edit(CarLogbook $logbook)
{
    try {
        \Log::info('Edit method called with logbook ID: ' . $logbook->id);
        
        // Format the logbook data for the edit form
        $logbookData = [
            'id' => $logbook->id,
            'title' => $logbook->title,
            'description' => $logbook->description,
            'document_type' => $logbook->document_type,
            'document_date' => $logbook->document_date ? $logbook->document_date->format('Y-m-d') : null,
            'expiry_date' => $logbook->expiry_date ? $logbook->expiry_date->format('Y-m-d') : null,
            'issued_by' => $logbook->issued_by,
            'reference_number' => $logbook->reference_number,
            'status' => $logbook->status,
            'notes' => $logbook->notes,
            'customer_id' => $logbook->customer_id > 0 ? $logbook->customer_id : null,
            'imported_id' => $logbook->imported_id > 0 ? $logbook->imported_id : null,
            'car_type' => $logbook->customer_id > 0 ? 'tradein' : 'imported',
        ];
        
        return response()->json([
            'success' => true,
            'logbook' => $logbookData
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Failed to get logbook for edit', [
            'logbook_id' => $logbook->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to load logbook for editing: ' . $e->getMessage()
        ], 500);
    }
}
/**
 * Get logbook documents with URLs
 */
private function getLogbookDocumentsWithUrls(CarLogbook $logbook)
{
    $documentPaths = $logbook->documents ?? [];
    $documents = [];
    
    foreach ($documentPaths as $index => $documentPath) {
        try {
            if (str_starts_with($documentPath, 'http')) {
                $documentUrl = $documentPath;
            } else {
                $bucket = config('filesystems.disks.s3.bucket');
                $region = config('filesystems.disks.s3.region');
                $documentUrl = "https://{$bucket}.s3.{$region}.amazonaws.com/{$documentPath}";
            }
            
            $documents[] = [
                'index' => $index,
                'url' => $documentUrl,
                'path' => $documentPath,
                'name' => basename($documentPath),
                'type' => $this->getFileType($documentPath)
            ];
            
        } catch (\Exception $e) {
            \Log::warning('Failed to generate document URL', [
                'logbook_id' => $logbook->id,
                'document_path' => $documentPath,
                'error' => $e->getMessage()
            ]);
            continue;
        }
    }
    
    return $documents;
}

/**
 * Get file type from path
 */
private function getFileType($path)
{
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    
    $types = [
        'pdf' => 'PDF Document',
        'doc' => 'Word Document',
        'docx' => 'Word Document',
        'jpg' => 'Image',
        'jpeg' => 'Image',
        'png' => 'Image',
        'gif' => 'Image'
    ];
    
    return $types[$extension] ?? 'Unknown';
}
    public function update(Request $request, $id)
{
    try {
        $logbook = CarLogbook::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'document_type' => 'required|string|in:logbook,registration,insurance,service_record,other',
            'document_date' => 'nullable|date',
            'issued_by' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|string|in:active,archived,expired',
            
            // Car IDs
            'customer_id' => 'nullable|integer',
            'imported_id' => 'nullable|integer',
        ]);

        // Handle conditional ID logic
        if (!empty($validated['customer_id'])) {
            $logbook->customer_id = $validated['customer_id'];
            $logbook->imported_id = 0;
        } elseif (!empty($validated['imported_id'])) {
            $logbook->customer_id = 0;
            $logbook->imported_id = $validated['imported_id'];
        }

        // Update other fields
        $logbook->title = $validated['title'];
        $logbook->description = $validated['description'];
        $logbook->document_type = $validated['document_type'];
        $logbook->document_date = $validated['document_date'];
        $logbook->issued_by = $validated['issued_by'];
        $logbook->reference_number = $validated['reference_number'];
        $logbook->expiry_date = $validated['expiry_date'];
        $logbook->notes = $validated['notes'];
        $logbook->status = $validated['status'];

        $logbook->save();

        return response()->json([
            'success' => true,
            'message' => 'Logbook updated successfully!'
        ]);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Failed to update logbook', [
            'logbook_id' => $id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to update logbook: ' . $e->getMessage()
        ], 500);
    }
}
public function show($id)
{
    try {
        \Log::info('Show method called with ID: ' . $id);
        
        $logbook = CarLogbook::with(['carsImport', 'customerVehicle'])->findOrFail($id);
        
        \Log::info('Logbook found: ' . $logbook->id);
        
        // Determine car type and details
        $carType = 'unknown';
        $carDetails = 'No car details';
        
        if ($logbook->customer_id > 0 && $logbook->customerVehicle) {
            $carType = 'tradein';
            $car = $logbook->customerVehicle;
            $carDetails = "{$car->make} {$car->model} ({$car->year}) - {$car->registration_number}";
        } elseif ($logbook->imported_id > 0 && $logbook->carsImport) {
            $carType = 'imported';
            $car = $logbook->carsImport;
            $carDetails = "{$car->make} {$car->model} ({$car->year}) - {$car->chassis_number}";
        }
        
        $responseData = [
            'success' => true,
            'logbook' => [
                'id' => $logbook->id,
                'title' => $logbook->title,
                'description' => $logbook->description,
                'document_type' => $logbook->document_type,
                'formatted_document_type' => ucfirst(str_replace('_', ' ', $logbook->document_type)),
                'document_date' => $logbook->document_date ? $logbook->document_date->format('Y-m-d') : null,
                'expiry_date' => $logbook->expiry_date ? $logbook->expiry_date->format('Y-m-d') : null,
                'issued_by' => $logbook->issued_by,
                'reference_number' => $logbook->reference_number,
                'status' => $logbook->status,
                'notes' => $logbook->notes,
                'car_details' => $carDetails,
                'car_type' => $carType,
                'customer_id' => $logbook->customer_id,
                'imported_id' => $logbook->imported_id,
                'document_count' => is_array($logbook->documents) ? count($logbook->documents) : 0,
                'file_size' => $logbook->file_size,
                'created_at' => $logbook->created_at->format('M d, Y H:i'),
                'updated_at' => $logbook->updated_at->format('M d, Y H:i'),
            ]
        ];
        
        \Log::info('Response data prepared', $responseData);
        
        return response()->json($responseData);
        
    } catch (\Exception $e) {
        \Log::error('Failed to show logbook', [
            'logbook_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to load logbook details: ' . $e->getMessage()
        ], 500);
    }
}

public function destroy($id)
{
    try {
        $logbook = CarLogbook::findOrFail($id);
        
        // Clean up documents from S3
        if ($logbook->documents && is_array($logbook->documents)) {
            $this->cleanupLogbookDocuments($logbook->documents);
        }
        
        $logbook->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Logbook deleted successfully!'
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Failed to delete logbook', [
            'logbook_id' => $id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to delete logbook: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * Upload documents to logbook
     */
    public function uploadDocuments(Request $request, CarLogbook $logbook)
    {
        $request->validate([
            'documents.*' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png,gif|max:51200'
        ]);

        try {
            $uploadedDocuments = [];
            $existingDocuments = $logbook->documents ?? [];
            $uploadErrors = [];
            $totalSize = 0;
            
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $index => $document) {
                    try {
                        $originalName = pathinfo($document->getClientOriginalName(), PATHINFO_FILENAME);
                        $extension = $document->getClientOriginalExtension();
                        $filename = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;
                        
                        $documentPath = $this->uploadLogbookDocumentToS3($document, $filename);
                        $existingDocuments[] = $documentPath;
                        $totalSize += $document->getSize();
                        
                        $bucket = config('filesystems.disks.s3.bucket');
                        $region = config('filesystems.disks.s3.region');
                        $documentUrl = "https://{$bucket}.s3.{$region}.amazonaws.com/{$documentPath}";
                        
                        $uploadedDocuments[] = [
                            'index' => count($existingDocuments) - 1,
                            'url' => $documentUrl,
                            'path' => $documentPath,
                            'name' => $filename,
                            'size' => $this->formatFileSize($document->getSize())
                        ];
                        
                    } catch (\Exception $e) {
                        $error = "Failed to upload document " . ($index + 1) . ": " . $e->getMessage();
                        $uploadErrors[] = $error;
                    }
                }

                // Update logbook with new documents
                $currentSize = $this->calculateTotalFileSize($existingDocuments);
                $logbook->update([
                    'documents' => $existingDocuments,
                    'file_count' => count($existingDocuments),
                    'file_size' => $this->formatFileSize($currentSize)
                ]);
            }

            $response = [
                'success' => true,
                'documents' => $uploadedDocuments,
                'total_count' => count($existingDocuments),
                'message' => count($uploadedDocuments) . ' document(s) uploaded successfully!'
            ];
            
            if (!empty($uploadErrors)) {
                $response['warnings'] = $uploadErrors;
                $response['message'] .= ' (Some documents failed to upload)';
            }

            return response()->json($response);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a specific document by index
     */
    public function deleteDocument(CarLogbook $logbook, $documentIndex)
    {
        try {
            $documents = $logbook->documents ?? [];
            
            if (!isset($documents[$documentIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found!'
                ], 404);
            }

            $documentPath = $documents[$documentIndex];

            // Delete from S3
            try {
                $this->deleteLogbookDocumentFromS3($documentPath);
            } catch (\Exception $e) {
                \Log::warning('Failed to delete logbook document from S3', [
                    'logbook_id' => $logbook->id,
                    'document_path' => $documentPath,
                    'error' => $e->getMessage()
                ]);
            }

            // Remove from array and reindex
            unset($documents[$documentIndex]);
            $documents = array_values($documents);

            // Calculate new total size
            $totalSize = $this->calculateTotalFileSize($documents);

            // Update logbook
            $logbook->update([
                'documents' => $documents,
                'file_count' => count($documents),
                'file_size' => $this->formatFileSize($totalSize)
            ]);

            return response()->json([
                'success' => true,
                'total_count' => count($documents),
                'message' => 'Document deleted successfully!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all documents for a logbook
     */
 
    /**
     * Upload logbook document to S3
     */
    private function uploadLogbookDocumentToS3($document, $filename)
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

            $key = "car_logbooks/" . $filename;
            
            $result = $s3Client->putObject([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key'    => $key,
                'Body'   => fopen($document->getPathname(), 'r'),
                'ContentType' => $document->getMimeType(),
                'CacheControl' => 'max-age=31536000',
            ]);

            return $key;

        } catch (\Exception $e) {
            throw new \Exception('S3 document upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete logbook document from S3
     */
    private function deleteLogbookDocumentFromS3($documentPath)
    {
        try {
            if (str_starts_with($documentPath, 'http')) {
                return; // Skip legacy URLs
            }
            
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

            $s3Client->deleteObject([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $documentPath
            ]);

        } catch (\Exception $e) {
            throw $e;
        }
    }


    /**
     * Clean up logbook documents
     */
    private function cleanupLogbookDocuments($documentPaths)
    {
        foreach ($documentPaths as $documentPath) {
            try {
                $this->deleteLogbookDocumentFromS3($documentPath);
            } catch (\Exception $e) {
                \Log::warning('Failed to cleanup logbook document', [
                    'path' => $documentPath,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Format file size
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Calculate total file size from S3 paths
     */
    private function calculateTotalFileSize($documentPaths)
    {
        // This is a simplified version - in production you might want to
        // store file sizes in database or query S3 for actual sizes
        return count($documentPaths) * 1024 * 1024; // Assume 1MB per file for now
    }

}