<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VehicleInspection;
use App\Models\CustomerVehicle;
use App\Models\CarImport;
use Illuminate\Support\Facades\Storage;
class InspectionController extends Controller
{
    public function index()
    {
        // Fetch customers not already in VehicleInspection
        $customers = CarImport::where('status', 8)->latest()->get();
        $inspections = VehicleInspection::with('carsImport')->where('imported_id','!=', 0)->get();
        return view('inspection.index', compact('inspections','customers'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'current_mileage'   => 'required|string|max:255',
            'inspection_notes' => 'required|string',
            'overall_percent'   => 'required|string|max:255',
            'exterior_percent'   => 'required|string|max:255',
            'interior_func_percent'   => 'required|string|max:255',
            'interior_acc_percent'   => 'required|string|max:255',
            'tools_percent'   => 'required|string|max:255',
            
            // Make both optional since only one will be provided
            'customer_id'   => 'nullable|integer|max:255',
            'imported_id'   => 'nullable|integer|max:255',
            
            'status'   => 'required|integer|max:255',
            
            // Exterior
            'rh_front_wing' => 'required|in:ok,damaged',
            'rh_right_wing' => 'required|in:ok,damaged',
            'lh_front_wing' => 'required|in:ok,damaged',
            'lh_right_wing' => 'required|in:ok,damaged',
            'bonnet' => 'required|in:ok,damaged',
            'rh_front_door' => 'required|in:ok,damaged',
            'rh_rear_door' => 'required|in:ok,damaged',
            'lh_front_door' => 'required|in:ok,damaged',
            'lh_rear_door' => 'required|in:ok,damaged',
            'front_bumper' => 'required|in:ok,damaged',
            'rear_bumper' => 'required|in:ok,damaged',
            'head_lights' => 'required|in:ok,damaged',
            'bumper_lights' => 'required|in:ok,damaged',
            'corner_lights' => 'required|in:ok,damaged',
            'rear_lights' => 'required|in:ok,damaged',
            
            // Interior Functional
            'radio_speakers' => 'required|in:ok,damaged',
            'seat_belt' => 'required|in:ok,damaged',
            'door_handles' => 'required|in:ok,damaged',
            
            // Interior Accessories
            'head_rest' => 'required|in:present,absent',
            'floor_carpets' => 'required|in:present,absent',
            'rubber_mats' => 'required|in:present,absent',
            'cigar_lighter' => 'required|in:present,absent',
            'boot_mats' => 'required|in:present,absent',
            
            // Tools
            'jack' => 'required|in:present,absent',
            'spare_wheel' => 'required|in:present,absent',
            'compressor' => 'required|in:present,absent',
            'wheel_spanner' => 'required|in:present,absent',
        ]);

        // Additional validation to ensure at least one ID is provided
        $request->validate([
            'customer_id' => 'required_without:imported_id',
            'imported_id' => 'required_without:customer_id',
        ]);

        // Prepare data with conditional ID logic
        $inspectionData = $validated;

        if (!empty($validated['customer_id'])) {
            // If customer_id is provided, set imported_id to 0
            $inspectionData['customer_id'] = $validated['customer_id'];
            $inspectionData['imported_id'] = 0;
        } elseif (!empty($validated['imported_id'])) {
            // If imported_id is provided, set customer_id to 0
            $inspectionData['customer_id'] = 0;
            $inspectionData['imported_id'] = $validated['imported_id'];
        } else {
            // Fallback (should not happen due to validation)
            $inspectionData['customer_id'] = 0;
            $inspectionData['imported_id'] = 0;
        }

        VehicleInspection::create($inspectionData);

        return back()->with('success', 'Vehicle inspection saved successfully!');
    }
   public function update(Request $request, $id)
    {
        $inspection = VehicleInspection::findOrFail($id);

        $validated = $request->validate([
            // Make both optional since only one will be provided
            'customer_id' => 'nullable|integer|min:0|max:100',
            'imported_id' => 'nullable|integer|min:0|max:100',
            
            'inspection_notes' => 'required|string',
            'current_mileage' => 'nullable|string|max:255',
            'overall_percent' => 'required|integer|min:0|max:100',
            'exterior_percent' => 'required|integer|min:0|max:100',
            'interior_func_percent' => 'required|integer|min:0|max:100',
            'interior_acc_percent' => 'required|integer|min:0|max:100',
            'tools_percent' => 'required|integer|min:0|max:100',
        ]);

        // Additional validation to ensure at least one ID is provided
        $request->validate([
            'customer_id' => 'required_without:imported_id',
            'imported_id' => 'required_without:customer_id',
        ]);

        // Handle conditional ID logic
        if (!empty($validated['customer_id'])) {
            // If customer_id is provided, set imported_id to 0
            $inspection->customer_id = $validated['customer_id'];
            $inspection->imported_id = 0;
        } elseif (!empty($validated['imported_id'])) {
            // If imported_id is provided, set customer_id to 0
            $inspection->customer_id = 0;
            $inspection->imported_id = $validated['imported_id'];
        } else {
            // Keep existing values if both are empty (fallback)
            // This shouldn't happen due to validation, but just in case
        }

        // Update other fields
        $inspection->current_mileage = $validated['current_mileage'];
        $inspection->inspection_notes = $validated['inspection_notes'];
        $inspection->overall_percent = $validated['overall_percent'];
        $inspection->exterior_percent = $validated['exterior_percent'];
        $inspection->interior_func_percent = $validated['interior_func_percent'];
        $inspection->interior_acc_percent = $validated['interior_acc_percent'];
        $inspection->tools_percent = $validated['tools_percent'];

        // List of radio button fields that exist in your model
        $radioFields = [
            'rh_front_wing', 'rh_right_wing', 'lh_front_wing', 'lh_right_wing', 'bonnet',
            'rh_front_door', 'rh_rear_door', 'lh_front_door', 'lh_rear_door',
            'front_bumper', 'rear_bumper', 'head_lights', 'bumper_lights', 'corner_lights', 'rear_lights',
            'radio_speakers', 'seat_belt', 'door_handles',
            'head_rest', 'floor_carpets', 'rubber_mats', 'cigar_lighter', 'boot_mats',
            'jack', 'spare_wheel', 'compressor', 'wheel_spanner'
        ];

        // Loop through each and update if present in the request
        foreach ($radioFields as $field) {
            if ($request->has($field)) {
                $inspection->$field = $request->input($field);
            }
        }

        $inspection->save();

        return response()->json([
            'message' => 'Inspection updated successfully!'
        ]);
    }
    
    public function tradeincars()
    {
        // Get IDs of customers that already exist in VehicleInspection
        $inspectedCustomerIds = VehicleInspection::pluck('customer_id');

        // Fetch customers not already in VehicleInspection
        $customers = CustomerVehicle::whereNotIn('id', $inspectedCustomerIds)->latest()->get();
        $inspections = VehicleInspection::with('customerVehicle')->where('customer_id','!=', 0)->get();
        return view('inspection.tradein', compact('inspections','customers'));
    }

    public function inspectedimported()
    {
        return view('inspection.inspectedimported');
    }
    public function inspectedtradein()
    {
        return view('inspection.inspectedtradein');
    }
    /**
     * Upload photos to inspection
     */
    public function uploadPhotos(Request $request, VehicleInspection $inspection)
{
    $request->validate([
        'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
    ]);

    try {
        \Log::info('Starting inspection photo upload', [
            'inspection_id' => $inspection->id,
            'has_photos' => $request->hasFile('photos'),
            'photo_count' => $request->hasFile('photos') ? count($request->file('photos')) : 0
        ]);

        $uploadedPhotos = [];
        $existingPhotos = $inspection->photos ?? [];
        $uploadErrors = [];
        
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photo) {
                try {
                    // Generate unique filename
                    $originalName = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $photo->getClientOriginalExtension();
                    $filename = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;
                    
                    // Upload to S3 using direct method
                    $photoPath = $this->uploadInspectionPhotoToS3Direct($photo, $filename);
                    
                    $existingPhotos[] = $photoPath;
                    
                    // Generate S3 URL for response
                    $bucket = config('filesystems.disks.s3.bucket');
                    $region = config('filesystems.disks.s3.region');
                    $photoUrl = "https://{$bucket}.s3.{$region}.amazonaws.com/{$photoPath}";
                    
                    // Add to response array with index
                    $uploadedPhotos[] = [
                        'index' => count($existingPhotos) - 1,
                        'url' => $photoUrl,
                        'path' => $photoPath,
                        'name' => $filename
                    ];
                    
                    \Log::info('Inspection photo uploaded successfully', [
                        'inspection_id' => $inspection->id,
                        'index' => $index,
                        'filename' => $filename,
                        'path' => $photoPath
                    ]);
                    
                } catch (\Exception $e) {
                    $error = "Failed to upload photo " . ($index + 1) . ": " . $e->getMessage();
                    $uploadErrors[] = $error;
                    
                    \Log::error('Inspection photo upload failed', [
                        'inspection_id' => $inspection->id,
                        'index' => $index,
                        'filename' => $photo->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Update inspection with new photos array
            $inspection->update(['photos' => $existingPhotos]);
            
            \Log::info('Inspection photos updated successfully', [
                'inspection_id' => $inspection->id,
                'total_photos' => count($existingPhotos),
                'new_photos' => count($uploadedPhotos),
                'errors' => count($uploadErrors)
            ]);
        }

        $response = [
            'success' => true,
            'photos' => $uploadedPhotos,
            'total_count' => count($existingPhotos),
            'message' => count($uploadedPhotos) . ' photo(s) uploaded successfully!'
        ];
        
        // Include warnings if some photos failed
        if (!empty($uploadErrors)) {
            $response['warnings'] = $uploadErrors;
            $response['message'] .= ' (Some photos failed to upload)';
        }

        return response()->json($response);
        
    } catch (\Exception $e) {
        \Log::error('Inspection photo upload process failed', [
            'inspection_id' => $inspection->id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Photo upload failed: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Delete a specific photo by index
 */
public function deletePhoto(VehicleInspection $inspection, $photoIndex)
{
    try {
        $photos = $inspection->photos ?? [];
        
        \Log::info('Starting inspection photo deletion', [
            'inspection_id' => $inspection->id,
            'photo_index' => $photoIndex,
            'total_photos' => count($photos)
        ]);
        
        // Check if photo index exists
        if (!isset($photos[$photoIndex])) {
            return response()->json([
                'success' => false,
                'message' => 'Photo not found!'
            ], 404);
        }

        $photoPath = $photos[$photoIndex];

        // Delete file from S3
        try {
            $this->deleteInspectionPhotoFromS3Direct($photoPath);
            
            \Log::info('Inspection photo deleted from S3', [
                'inspection_id' => $inspection->id,
                'photo_path' => $photoPath
            ]);
            
        } catch (\Exception $e) {
            \Log::warning('Failed to delete inspection photo from S3, continuing with database cleanup', [
                'inspection_id' => $inspection->id,
                'photo_path' => $photoPath,
                'error' => $e->getMessage()
            ]);
        }

        // Remove photo from array and reindex
        unset($photos[$photoIndex]);
        $photos = array_values($photos); // Reindex array

        // Update inspection
        $inspection->update(['photos' => $photos]);

        \Log::info('Inspection photo deleted successfully', [
            'inspection_id' => $inspection->id,
            'remaining_photos' => count($photos)
        ]);

        return response()->json([
            'success' => true,
            'total_count' => count($photos),
            'message' => 'Photo deleted successfully!'
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Inspection photo deletion failed', [
            'inspection_id' => $inspection->id,
            'photo_index' => $photoIndex,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to delete photo: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Get all photos for an inspection
 */
public function getPhotos(VehicleInspection $inspection)
{
    try {
        \Log::info('Getting inspection photos', [
            'inspection_id' => $inspection->id
        ]);
        
        $photos = $this->getInspectionPhotosWithUrls($inspection);

        return response()->json([
            'success' => true,
            'photos' => $photos,
            'total_count' => count($photos)
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Failed to get inspection photos', [
            'inspection_id' => $inspection->id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to get photos: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Upload inspection photo directly to S3 using AWS SDK with SSL disabled
 */
private function uploadInspectionPhotoToS3Direct($photo, $filename)
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

        $key = "inspection_photos/" . $filename;
        
        $result = $s3Client->putObject([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key'    => $key,
            'Body'   => fopen($photo->getPathname(), 'r'),
            'ContentType' => $photo->getMimeType(),
            'CacheControl' => 'max-age=31536000', // 1 year cache for images
        ]);

        \Log::info('Direct S3 inspection photo upload successful', [
            'key' => $key,
            'object_url' => $result['ObjectURL'] ?? 'N/A'
        ]);

        return $key; // Return the S3 key path

    } catch (\Exception $e) {
        \Log::error('Direct S3 inspection photo upload failed', [
            'filename' => $filename,
            'error' => $e->getMessage()
        ]);
        throw new \Exception('Direct S3 inspection photo upload failed: ' . $e->getMessage());
    }
}

/**
 * Delete inspection photo from S3
 */
private function deleteInspectionPhotoFromS3Direct($photoPath)
{
    try {
        // Skip if it's already a full URL (legacy data)
        if (str_starts_with($photoPath, 'http')) {
            \Log::info('Skipping S3 deletion of legacy URL', ['path' => $photoPath]);
            return;
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
            'Key' => $photoPath
        ]);
        
        \Log::info('Inspection photo deleted from S3', ['path' => $photoPath]);

    } catch (\Exception $e) {
        \Log::error('Failed to delete inspection photo from S3', [
            'path' => $photoPath,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}

/**
 * Get inspection photos with URLs (replaces the model method)
 */
private function getInspectionPhotosWithUrls(VehicleInspection $inspection)
{
    $photoPaths = $inspection->photos ?? [];
    $photos = [];
    
    foreach ($photoPaths as $index => $photoPath) {
        try {
            // Check if it's already a full URL (legacy data)
            if (str_starts_with($photoPath, 'http')) {
                $photoUrl = $photoPath;
            } else {
                // Generate S3 URL from the key path
                $bucket = config('filesystems.disks.s3.bucket');
                $region = config('filesystems.disks.s3.region');
                $photoUrl = "https://{$bucket}.s3.{$region}.amazonaws.com/{$photoPath}";
            }
            
            $photos[] = [
                'index' => $index,
                'url' => $photoUrl,
                'path' => $photoPath,
                'name' => basename($photoPath)
            ];
            
        } catch (\Exception $e) {
            \Log::warning('Failed to generate inspection photo URL', [
                'inspection_id' => $inspection->id,
                'index' => $index,
                'path' => $photoPath,
                'error' => $e->getMessage()
            ]);
            
            // Skip this photo if URL generation fails
            continue;
        }
    }
    
    return $photos;
}

/**
 * Generate temporary URL for inspection photo (optional - for enhanced security)
 */
private function generateInspectionPhotoTemporaryUrl($key, $minutes = 1440)
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
        \Log::error('Failed to generate inspection photo temporary URL', [
            'key' => $key,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}

/**
 * Clean up multiple inspection photos from S3 (utility method)
 */
private function cleanupInspectionPhotos($photoPaths)
{
    if (empty($photoPaths)) {
        return;
    }
    
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

        foreach ($photoPaths as $photoPath) {
            try {
                // Skip if it's already a full URL (legacy data)
                if (str_starts_with($photoPath, 'http')) {
                    \Log::info('Skipping cleanup of legacy URL', ['path' => $photoPath]);
                    continue;
                }
                
                $s3Client->deleteObject([
                    'Bucket' => config('filesystems.disks.s3.bucket'),
                    'Key' => $photoPath
                ]);
                
                \Log::info('Cleaned up inspection photo', ['path' => $photoPath]);
                
            } catch (\Exception $e) {
                \Log::warning('Failed to cleanup inspection photo', [
                    'path' => $photoPath,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
    } catch (\Exception $e) {
        \Log::error('Inspection photo cleanup process failed', [
            'error' => $e->getMessage(),
            'photo_count' => count($photoPaths)
        ]);
    }
}

    /**
     * Example of how to create CarImport with photos (if needed)
     */
    public function storeCarImport(Request $request)
    {
        $validated = $request->validate([
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // ... other validation rules
        ]);

        $photoPaths = [];
        
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPaths[] = $photo->store('car_bids', 'public');
            }
        }
        
        $validated['photos'] = json_encode($photoPaths);
        
        $carImport = CarImport::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Car import created successfully!',
            'data' => $carImport
        ]);
    }
}
