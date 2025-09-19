<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VehicleInspection;
use App\Models\CustomerVehicle;
use Illuminate\Support\Facades\DB;
use App\Models\CarImport;
use App\Models\GatePassInspection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use App\Models\User; 
use Illuminate\Support\Facades\Log;
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

             // Interior Accessories Numbers - NEW
            'head_rest_number' => 'nullable|integer|min:0|max:10',
            'floor_carpets_number' => 'nullable|integer|min:0|max:10',
            'rubber_mats_number' => 'nullable|integer|min:0|max:10',
            'cigar_lighter_number' => 'nullable|integer|min:0|max:50',
            'boot_mats_number' => 'nullable|integer|min:0|max:50',
            
            // Tools
            'jack' => 'required|in:present,absent',
            'handle' => 'required|in:present,absent',
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
            'customer_id' => 'nullable|integer',
            'imported_id' => 'nullable|integer',
            
            'inspection_notes' => 'required|string',
            'current_mileage' => 'nullable|string',
            'overall_percent' => 'required|integer',
            'exterior_percent' => 'required|integer',
            'interior_func_percent' => 'required|integer',
            'interior_acc_percent' => 'required|integer',
            'tools_percent' => 'required|integer',

            // Interior Accessories Numbers
            'head_rest_number' => 'nullable|integer|min:0|max:10',
            'floor_carpets_number' => 'nullable|integer|min:0|max:10',
            'rubber_mats_number' => 'nullable|integer|min:0|max:10',
            'cigar_lighter_number' => 'nullable|integer|min:0|max:50',
            'boot_mats_number' => 'nullable|integer|min:0|max:50',
            
            // Handle field (new)
            'handle' => 'nullable|in:present,absent',
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
            'jack','handle','spare_wheel', 'compressor', 'wheel_spanner'
        ];

        // Loop through each and update if present in the request
        foreach ($radioFields as $field) {
            if ($request->has($field)) {
                $inspection->$field = $request->input($field);
            }
        }
            // Handle number fields for interior accessories
        $numberFields = [
            'head_rest_number', 
            'floor_carpets_number', 
            'rubber_mats_number', 
            'cigar_lighter_number', 
            'boot_mats_number'
        ];

        foreach ($numberFields as $field) {
            if ($request->has($field)) {
                $inspection->$field = $request->input($field) ?: 0; // Default to 0 if empty
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

    // For CREATE modal - only uninspected customers (existing behavior)
    $customers = CustomerVehicle::whereNotIn('id', $inspectedCustomerIds)->latest()->get();
    
    // For UPDATE modals - ALL customers (including already inspected ones)
    $allCustomers = CustomerVehicle::latest()->get();
    
    $inspections = VehicleInspection::with('customerVehicle')->where('customer_id','!=', 0)->get();
    
    return view('inspection.tradein', compact('inspections', 'customers', 'allCustomers'));
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


      /**
     * Save gate pass inspection data to database
     */
      public function savegatepass(Request $request)
    {
        try {
            // Validate the request
            $validatedData = $request->validate([
                'gate_pass_id' => 'required|string|max:255',
                'spare_wheel_present' => 'boolean',
                'wheel_spanner_present' => 'boolean',
                'jack_present' => 'boolean',
                'life_saver_present' => 'boolean',
                'first_aid_kit_present' => 'boolean',
                'spare_wheel_absent' => 'boolean',
                'wheel_spanner_absent' => 'boolean',
                'jack_absent' => 'boolean',
                'life_saver_absent' => 'boolean',
                'first_aid_kit_absent' => 'boolean',
                'comments' => 'nullable|string|max:1000',
                'submitted_by' => 'nullable|exists:users,id'
            ]);

            // Get user ID
            $userId = $validatedData['submitted_by'] ?? Auth::id();
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User authentication required'
                ], 401);
            }

            // Start database transaction for consistency
            DB::beginTransaction();

            try {
                // Mark all previous versions as not latest
                GatePassInspection::where('gate_pass_id', $validatedData['gate_pass_id'])
                    ->update(['is_latest' => false]);

                // Get next version number
                $lastVersion = GatePassInspection::where('gate_pass_id', $validatedData['gate_pass_id'])
                    ->max('version') ?? 0;

                // Prepare data for saving
                $inspectionData = [
                    'gate_pass_id' => $validatedData['gate_pass_id'],
                    'submitted_by' => $userId,
                    'version' => $lastVersion + 1,
                    'is_latest' => true,
                    'spare_wheel_present' => $validatedData['spare_wheel_present'] ?? false,
                    'wheel_spanner_present' => $validatedData['wheel_spanner_present'] ?? false,
                    'jack_present' => $validatedData['jack_present'] ?? false,
                    'life_saver_present' => $validatedData['life_saver_present'] ?? false,
                    'first_aid_kit_present' => $validatedData['first_aid_kit_present'] ?? false,
                    'spare_wheel_absent' => $validatedData['spare_wheel_absent'] ?? false,
                    'wheel_spanner_absent' => $validatedData['wheel_spanner_absent'] ?? false,
                    'jack_absent' => $validatedData['jack_absent'] ?? false,
                    'life_saver_absent' => $validatedData['life_saver_absent'] ?? false,
                    'first_aid_kit_absent' => $validatedData['first_aid_kit_absent'] ?? false,
                    'comments' => $validatedData['comments'] ?? null,
                ];

                // Create new inspection record
                $inspection = GatePassInspection::create($inspectionData);

                // Commit transaction
                DB::commit();

                // Get user data for response
                $user = User::find($userId);
                $userData = [
                    'id' => $user->id,
                    'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                    'email' => $user->email,
                    'submitted_at' => $inspection->created_at->toISOString(),
                    'version' => $inspection->version
                ];

                // Get inspection history count
                $totalVersions = GatePassInspection::where('gate_pass_id', $validatedData['gate_pass_id'])->count();

                // Log the action with version info
                Log::info('Gate pass inspection saved', [
                    'gate_pass_id' => $validatedData['gate_pass_id'],
                    'user_id' => $userId,
                    'user_name' => $userData['name'],
                    'version' => $inspection->version,
                    'total_versions' => $totalVersions,
                    'is_complete' => $inspection->isComplete()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Inspection data saved successfully',
                    'data' => $inspection,
                    'user_data' => $userData,
                    'version_info' => [
                        'current_version' => $inspection->version,
                        'total_versions' => $totalVersions,
                        'is_latest' => true
                    ],
                    'is_complete' => $inspection->isComplete(),
                    'summary' => $inspection->getSummary()
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error saving gate pass inspection', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'gate_pass_id' => $request->get('gate_pass_id'),
                'user_id' => $userId ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Load latest gate pass inspection data for multiple gate passes
     */
    public function loadgatepass(Request $request)
    {
        try {
            // Validate the request
            $validatedData = $request->validate([
                'gate_pass_ids' => 'required|array',
                'gate_pass_ids.*' => 'string|max:255'
            ]);

            // Get only the latest versions of inspections
            $inspections = GatePassInspection::with('submittedBy')
                ->whereIn('gate_pass_id', $validatedData['gate_pass_ids'])
                ->where('is_latest', true)
                ->get();

            // Transform the data to include user information
            $inspectionData = $inspections->map(function ($inspection) {
                $data = $inspection->toArray();
                
                // Add user data if available
                if ($inspection->submittedBy) {
                    $data['user_data'] = [
                        'id' => $inspection->submittedBy->id,
                        'name' => trim(($inspection->submittedBy->first_name ?? '') . ' ' . ($inspection->submittedBy->last_name ?? '')),
                        'email' => $inspection->submittedBy->email,
                        'submitted_at' => $inspection->created_at->toISOString(),
                        'version' => $inspection->version
                    ];
                }
                
                // Add computed fields
                $data['is_complete'] = $inspection->isComplete();
                $data['summary'] = $inspection->getSummary();
                
                // Add version info
                $totalVersions = GatePassInspection::where('gate_pass_id', $inspection->gate_pass_id)->count();
                $data['version_info'] = [
                    'current_version' => $inspection->version,
                    'total_versions' => $totalVersions,
                    'is_latest' => $inspection->is_latest
                ];
                
                return $data;
            });

            return response()->json([
                'success' => true,
                'message' => 'Inspection data loaded successfully',
                'data' => $inspectionData,
                'count' => $inspectionData->count()
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error loading gate pass inspections', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show specific gate pass inspection (latest version)
     */
    public function showgatepass(string $gatePassId)
    {
        try {
            $inspection = GatePassInspection::with('submittedBy')
                ->where('gate_pass_id', $gatePassId)
                ->where('is_latest', true)
                ->first();

            if (!$inspection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Inspection data not found'
                ], 404);
            }

            $data = $inspection->toArray();
            
            // Add user data if available
            if ($inspection->submittedBy) {
                $data['user_data'] = [
                    'id' => $inspection->submittedBy->id,
                    'name' => trim(($inspection->submittedBy->first_name ?? '') . ' ' . ($inspection->submittedBy->last_name ?? '')),
                    'email' => $inspection->submittedBy->email,
                    'submitted_at' => $inspection->created_at->toISOString(),
                    'version' => $inspection->version
                ];
            }
            
            // Add computed fields
            $data['is_complete'] = $inspection->isComplete();
            $data['summary'] = $inspection->getSummary();
            
            // Add version info
            $totalVersions = GatePassInspection::where('gate_pass_id', $gatePassId)->count();
            $data['version_info'] = [
                'current_version' => $inspection->version,
                'total_versions' => $totalVersions,
                'is_latest' => $inspection->is_latest
            ];

            return response()->json([
                'success' => true,
                'message' => 'Inspection data retrieved successfully',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving gate pass inspection', [
                'error' => $e->getMessage(),
                'gate_pass_id' => $gatePassId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get complete history of gate pass inspections
     */
    public function historyGatepass(string $gatePassId)
    {
        try {
            $history = GatePassInspection::with('submittedBy')
                ->where('gate_pass_id', $gatePassId)
                ->orderBy('version', 'desc')
                ->get();

            if ($history->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No inspection history found'
                ], 404);
            }

            $historyData = $history->map(function($inspection) {
                return [
                    'id' => $inspection->id,
                    'version' => $inspection->version,
                    'user' => [
                        'id' => $inspection->submittedBy->id,
                        'name' => trim(($inspection->submittedBy->first_name ?? '') . ' ' . ($inspection->submittedBy->last_name ?? '')),
                        'email' => $inspection->submittedBy->email
                    ],
                    'submitted_at' => $inspection->created_at->toISOString(),
                    'is_latest' => $inspection->is_latest,
                    'is_complete' => $inspection->isComplete(),
                    'summary' => $inspection->getSummary(),
                    'comments' => $inspection->comments,
                    'inspection_data' => [
                        'spare_wheel_present' => $inspection->spare_wheel_present,
                        'spare_wheel_absent' => $inspection->spare_wheel_absent,
                        'wheel_spanner_present' => $inspection->wheel_spanner_present,
                        'wheel_spanner_absent' => $inspection->wheel_spanner_absent,
                        'jack_present' => $inspection->jack_present,
                        'jack_absent' => $inspection->jack_absent,
                        'life_saver_present' => $inspection->life_saver_present,
                        'life_saver_absent' => $inspection->life_saver_absent,
                        'first_aid_kit_present' => $inspection->first_aid_kit_present,
                        'first_aid_kit_absent' => $inspection->first_aid_kit_absent,
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'History retrieved successfully',
                'data' => $historyData,
                'total_versions' => $history->count(),
                'gate_pass_id' => $gatePassId
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving gate pass inspection history', [
                'error' => $e->getMessage(),
                'gate_pass_id' => $gatePassId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific version of gate pass inspection
     */
    public function showVersionGatepass(string $gatePassId, int $version)
    {
        try {
            $inspection = GatePassInspection::with('submittedBy')
                ->where('gate_pass_id', $gatePassId)
                ->where('version', $version)
                ->first();

            if (!$inspection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Inspection version not found'
                ], 404);
            }

            $data = $inspection->toArray();
            
            // Add user data
            if ($inspection->submittedBy) {
                $data['user_data'] = [
                    'id' => $inspection->submittedBy->id,
                    'name' => trim(($inspection->submittedBy->first_name ?? '') . ' ' . ($inspection->submittedBy->last_name ?? '')),
                    'email' => $inspection->submittedBy->email,
                    'submitted_at' => $inspection->created_at->toISOString(),
                    'version' => $inspection->version
                ];
            }
            
            // Add computed fields
            $data['is_complete'] = $inspection->isComplete();
            $data['summary'] = $inspection->getSummary();
            
            // Add version info
            $totalVersions = GatePassInspection::where('gate_pass_id', $gatePassId)->count();
            $data['version_info'] = [
                'current_version' => $inspection->version,
                'total_versions' => $totalVersions,
                'is_latest' => $inspection->is_latest
            ];

            return response()->json([
                'success' => true,
                'message' => 'Inspection version retrieved successfully',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving gate pass inspection version', [
                'error' => $e->getMessage(),
                'gate_pass_id' => $gatePassId,
                'version' => $version
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving version: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete specific version (soft delete - mark as deleted)
     */
    public function destroyVersionGatepass(string $gatePassId, int $version)
    {
        try {
            $inspection = GatePassInspection::where('gate_pass_id', $gatePassId)
                ->where('version', $version)
                ->first();

            if (!$inspection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Inspection version not found'
                ], 404);
            }

            // Don't allow deletion of the latest version
            if ($inspection->is_latest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete the latest version. Create a new version first.'
                ], 400);
            }

            $inspection->delete();

            Log::info('Gate pass inspection version deleted', [
                'gate_pass_id' => $gatePassId,
                'version' => $version,
                'deleted_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inspection version deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting gate pass inspection version', [
                'error' => $e->getMessage(),
                'gate_pass_id' => $gatePassId,
                'version' => $version
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error deleting version: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get inspection statistics
     */
    public function statisticsgatepass()
    {
        try {
            // Get only latest versions for statistics
            $latestInspections = GatePassInspection::where('is_latest', true)->get();
            
            $stats = [
                'total_gate_passes_inspected' => $latestInspections->count(),
                'complete_inspections' => 0,
                'incomplete_inspections' => 0,
                'recent_inspections' => GatePassInspection::where('created_at', '>=', now()->subDays(7))
                    ->where('is_latest', true)
                    ->count(),
                'total_versions' => GatePassInspection::count(),
                'users_with_inspections' => GatePassInspection::distinct('submitted_by')->count('submitted_by'),
                'average_versions_per_gate_pass' => 0
            ];

            // Calculate complete/incomplete inspections
            foreach ($latestInspections as $inspection) {
                if ($inspection->isComplete()) {
                    $stats['complete_inspections']++;
                } else {
                    $stats['incomplete_inspections']++;
                }
            }

            // Calculate average versions per gate pass
            if ($stats['total_gate_passes_inspected'] > 0) {
                $stats['average_versions_per_gate_pass'] = round(
                    $stats['total_versions'] / $stats['total_gate_passes_inspected'], 
                    2
                );
            }

            // Get top contributors
            $topContributors = GatePassInspection::with('submittedBy')
                ->select('submitted_by', DB::raw('COUNT(*) as contribution_count'))
                ->groupBy('submitted_by')
                ->orderBy('contribution_count', 'desc')
                ->take(5)
                ->get()
                ->map(function($contributor) {
                    return [
                        'user' => trim(($contributor->submittedBy->first_name ?? '') . ' ' . ($contributor->submittedBy->last_name ?? '')),
                        'contributions' => $contributor->contribution_count
                    ];
                });

            $stats['top_contributors'] = $topContributors;

            return response()->json([
                'success' => true,
                'message' => 'Statistics retrieved successfully',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving inspection statistics', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compare two versions of the same gate pass
     */
    public function compareVersions(string $gatePassId, Request $request)
    {
        try {
            $validatedData = $request->validate([
                'version1' => 'required|integer|min:1',
                'version2' => 'required|integer|min:1'
            ]);

            $version1 = GatePassInspection::with('submittedBy')
                ->where('gate_pass_id', $gatePassId)
                ->where('version', $validatedData['version1'])
                ->first();

            $version2 = GatePassInspection::with('submittedBy')
                ->where('gate_pass_id', $gatePassId)
                ->where('version', $validatedData['version2'])
                ->first();

            if (!$version1 || !$version2) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or both versions not found'
                ], 404);
            }

            // Compare inspection data
            $items = ['spare_wheel', 'wheel_spanner', 'jack', 'life_saver', 'first_aid_kit'];
            $differences = [];

            foreach ($items as $item) {
                $v1_present = $version1->{$item . '_present'};
                $v1_absent = $version1->{$item . '_absent'};
                $v2_present = $version2->{$item . '_present'};
                $v2_absent = $version2->{$item . '_absent'};

                if ($v1_present !== $v2_present || $v1_absent !== $v2_absent) {
                    $differences[$item] = [
                        'version1' => [
                            'present' => $v1_present,
                            'absent' => $v1_absent
                        ],
                        'version2' => [
                            'present' => $v2_present,
                            'absent' => $v2_absent
                        ]
                    ];
                }
            }

            // Compare comments
            if ($version1->comments !== $version2->comments) {
                $differences['comments'] = [
                    'version1' => $version1->comments,
                    'version2' => $version2->comments
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Version comparison completed',
                'data' => [
                    'gate_pass_id' => $gatePassId,
                    'version1' => [
                        'version' => $version1->version,
                        'user' => trim(($version1->submittedBy->first_name ?? '') . ' ' . ($version1->submittedBy->last_name ?? '')),
                        'submitted_at' => $version1->created_at->toISOString()
                    ],
                    'version2' => [
                        'version' => $version2->version,
                        'user' => trim(($version2->submittedBy->first_name ?? '') . ' ' . ($version2->submittedBy->last_name ?? '')),
                        'submitted_at' => $version2->created_at->toISOString()
                    ],
                    'differences' => $differences,
                    'has_differences' => !empty($differences)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error comparing inspection versions', [
                'error' => $e->getMessage(),
                'gate_pass_id' => $gatePassId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error comparing versions: ' . $e->getMessage()
            ], 500);
        }
    }
}