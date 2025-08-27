<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerVehicle;
use Illuminate\Support\Facades\Storage;

class TradeInController extends Controller
{
    public function index()
    {
           
       $vehicles = CustomerVehicle::where('status', 1)->get(); // Fetch all vehicle records

        return view('tradein.index', compact('vehicles'));
    }

    public function store(Request $request)
{
    // Validate input
    $validated = $request->validate([
        'Customer_Name'   => 'required|string|max:255',
        'Phone_No'        => 'required|string|max:20',
        'email'           => 'required|email|max:255',
        'Vehicle_Make'    => 'required|string|max:255',
        'model'       => 'required|string|min:0',
        'Sell_Type'       => 'required|numeric|min:0',
        'Status'          => 'required|numeric|min:0',
        'Chasis_No'       => 'required|string|max:255|unique:customer_vehicles,chasis_no',
        'Number_Plate'    => 'required|string|max:255|unique:customer_vehicles,number_plate',
        'Minimum_Price'   => 'required|numeric|min:0',
        'photos.*'        => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
    ]);

    try {
        \Log::info('Starting customer vehicle creation', [
            'customer_name' => $validated['Customer_Name'],
            'vehicle_make' => $validated['Vehicle_Make'],
            'has_photos' => $request->hasFile('photos')
        ]);

        // Store images to S3
        $photoPaths = [];
        $uploadErrors = [];
        
        if ($request->hasFile('photos')) {
            \Log::info('Processing customer vehicle photos', [
                'photo_count' => count($request->file('photos'))
            ]);

            foreach ($request->file('photos') as $index => $photo) {
                try {
                    // Generate unique filename
                    $originalName = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $photo->getClientOriginalExtension();
                    $filename = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;
                    
                    // Upload to S3 using direct method
                    $filePath = $this->uploadCustomerVehiclePhotoToS3Direct($photo, $filename);
                    
                    $photoPaths[] = $filePath;
                    
                    \Log::info('Customer vehicle photo uploaded successfully', [
                        'index' => $index,
                        'filename' => $filename,
                        'path' => $filePath
                    ]);
                    
                } catch (\Exception $e) {
                    $error = "Failed to upload photo " . ($index + 1) . ": " . $e->getMessage();
                    $uploadErrors[] = $error;
                    
                    \Log::error('Customer vehicle photo upload failed', [
                        'index' => $index,
                        'filename' => $photo->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // If all photos failed
            if (empty($photoPaths) && !empty($uploadErrors)) {
                return response()->json([
                    'message' => 'All photo uploads failed. Errors: ' . implode(', ', $uploadErrors)
                ], 500);
            }
        }

        // Create vehicle record
        $vehicle = CustomerVehicle::create([
            'customer_name'  => $validated['Customer_Name'],
            'phone_no'       => $validated['Phone_No'],
            'email'          => $validated['email'],
            'vehicle_make'   => $validated['Vehicle_Make'],
            'chasis_no'      => $validated['Chasis_No'],
            'model'      => $validated['model'],
            'number_plate'   => $validated['Number_Plate'],
            'sell_type'      => $validated['Sell_Type'],
            'status'         => $validated['Status'],
            'minimum_price'  => $validated['Minimum_Price'],
            'photos'         => json_encode($photoPaths), // Save S3 paths
        ]);

        \Log::info('Customer vehicle created successfully', [
            'id' => $vehicle->id,
            'customer_name' => $vehicle->customer_name,
            'photo_count' => count($photoPaths)
        ]);

        $response = [
            'message' => 'Vehicle information saved successfully.',
            'data' => $vehicle
        ];
        
        // Include warnings if some photos failed
        if (!empty($uploadErrors)) {
            $response['warnings'] = $uploadErrors;
            $response['message'] .= ' (Some photos failed to upload)';
        }

        return response()->json($response, 201);
        
    } catch (\Exception $e) {
        // Clean up uploaded photos if database save fails
        if (!empty($photoPaths)) {
            $this->cleanupCustomerVehiclePhotos($photoPaths);
        }
        
        \Log::error('Customer vehicle creation failed', [
            'error' => $e->getMessage(),
            'customer_name' => $validated['Customer_Name'] ?? 'unknown'
        ]);
        
        return response()->json([
            'message' => 'Failed to save vehicle information: ' . $e->getMessage()
        ], 500);
    }
}

public function update(Request $request)
{
    // Validate the request data
    $validatedData = $request->validate([
        'sell_typev1'       => 'required|integer|in:1,2',
        'customer_namev1'   => 'required|string|max:255',
        'phone_nov1'        => 'required|string|max:20',
        'emailv1'           => 'required|email|max:255',
        'vehicle_makev1'    => 'required|string|max:255',
        'chasis_nov1'       => 'required|string|max:255',
        'number_platev1'    => 'required|string|max:255',
        'minimum_pricev1'   => 'required|numeric|min:0',
        'modelv1'   => 'required|string|max:255',
        'photosv1.*'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    try {
        // Find the vehicle by ID
        $vehicle = CustomerVehicle::findOrFail($request->id);
        
        \Log::info('Starting customer vehicle update', [
            'id' => $vehicle->id,
            'customer_name' => $validatedData['customer_namev1'],
            'has_new_photos' => $request->hasFile('photosv1')
        ]);

        // Store old photos for cleanup if needed
        $oldPhotoPaths = json_decode($vehicle->photos, true) ?? [];

        // Build update array
        $updateData = [
            'sell_type'      => $validatedData['sell_typev1'],
            'customer_name'  => $validatedData['customer_namev1'],
            'phone_no'       => $validatedData['phone_nov1'],
            'email'          => $validatedData['emailv1'],
            'model'          => $validatedData['modelv1'],
            'vehicle_make'   => $validatedData['vehicle_makev1'],
            'chasis_no'      => $validatedData['chasis_nov1'],
            'number_plate'   => $validatedData['number_platev1'],
            'minimum_price'  => $validatedData['minimum_pricev1'],
        ];

        // Handle photos only if present
        if ($request->hasFile('photosv1')) {
            $photoPaths = [];
            $uploadErrors = [];
            
            \Log::info('Processing new customer vehicle photos', [
                'new_photo_count' => count($request->file('photosv1')),
                'existing_photo_count' => count($oldPhotoPaths)
            ]);

            foreach ($request->file('photosv1') as $index => $photo) {
                try {
                    // Generate unique filename
                    $originalName = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $photo->getClientOriginalExtension();
                    $filename = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;
                    
                    // Upload to S3 using direct method
                    $filePath = $this->uploadCustomerVehiclePhotoToS3Direct($photo, $filename);
                    
                    $photoPaths[] = $filePath;
                    
                    \Log::info('New customer vehicle photo uploaded successfully', [
                        'index' => $index,
                        'filename' => $filename,
                        'path' => $filePath
                    ]);
                    
                } catch (\Exception $e) {
                    $error = "Failed to upload photo " . ($index + 1) . ": " . $e->getMessage();
                    $uploadErrors[] = $error;
                    
                    \Log::error('New customer vehicle photo upload failed', [
                        'index' => $index,
                        'filename' => $photo->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // If at least one photo uploaded successfully, update the photos (replace old ones)
            if (!empty($photoPaths)) {
                $updateData['photos'] = json_encode($photoPaths);
                
                \Log::info('Customer vehicle photos updated successfully', [
                    'new_photo_count' => count($photoPaths),
                    'upload_errors' => count($uploadErrors)
                ]);
            } else {
                // If all new photos failed, keep old photos
                \Log::warning('All new customer vehicle photos failed to upload, keeping old photos');
                
                if (!empty($uploadErrors)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'All new photo uploads failed. Errors: ' . implode(', ', $uploadErrors)
                    ], 500);
                }
            }
        }

        // Perform update
        $vehicle->update($updateData);

        // Clean up old photos only if new photos were successfully uploaded
        if ($request->hasFile('photosv1') && isset($updateData['photos'])) {
            $this->cleanupCustomerVehiclePhotos($oldPhotoPaths);
        }

        \Log::info('Customer vehicle updated successfully', [
            'id' => $vehicle->id,
            'customer_name' => $vehicle->customer_name
        ]);

        $response = [
            'success' => true,
            'message' => 'Vehicle information updated successfully!'
        ];
        
        // Include warnings if some photos failed
        if (!empty($uploadErrors)) {
            $response['warnings'] = $uploadErrors;
            $response['message'] .= ' (Some new photos failed to upload)';
        }

        return response()->json($response);

    } catch (\Exception $e) {
        \Log::error('Customer vehicle update failed', [
            'id' => $request->id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to update vehicle information: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Upload customer vehicle photo directly to S3 using AWS SDK with SSL disabled
 */
private function uploadCustomerVehiclePhotoToS3Direct($photo, $filename)
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

        $key = "customer_vehicles/" . $filename;
        
        $result = $s3Client->putObject([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key'    => $key,
            'Body'   => fopen($photo->getPathname(), 'r'),
            'ContentType' => $photo->getMimeType(),
            'CacheControl' => 'max-age=31536000', // 1 year cache for images
        ]);

        \Log::info('Direct S3 customer vehicle photo upload successful', [
            'key' => $key,
            'object_url' => $result['ObjectURL'] ?? 'N/A'
        ]);

        return $key; // Return the S3 key path

    } catch (\Exception $e) {
        \Log::error('Direct S3 customer vehicle photo upload failed', [
            'filename' => $filename,
            'error' => $e->getMessage()
        ]);
        throw new \Exception('Direct S3 customer vehicle photo upload failed: ' . $e->getMessage());
    }
}

/**
 * Clean up customer vehicle photos from S3
 */
private function cleanupCustomerVehiclePhotos($photoPaths)
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
                
                \Log::info('Cleaned up customer vehicle photo', ['path' => $photoPath]);
                
            } catch (\Exception $e) {
                \Log::warning('Failed to cleanup customer vehicle photo', [
                    'path' => $photoPath,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
    } catch (\Exception $e) {
        \Log::error('Customer vehicle photo cleanup process failed', [
            'error' => $e->getMessage(),
            'photo_count' => count($photoPaths)
        ]);
    }
}

/**
 * Get customer vehicle photo URLs for display
 */
public function getCustomerVehiclePhotoUrls($customerVehicle)
{
    $photoPaths = json_decode($customerVehicle->photos, true) ?? [];
    $photoUrls = [];
    
    foreach ($photoPaths as $photoPath) {
        try {
            // Check if it's already a full URL (legacy data)
            if (str_starts_with($photoPath, 'http')) {
                $photoUrls[] = $photoPath;
                continue;
            }
            
            // Generate temporary URL for photo (valid for 24 hours)
            $photoUrls[] = $this->generateCustomerVehiclePhotoTemporaryUrl($photoPath, 1440);
            
        } catch (\Exception $e) {
            \Log::warning('Failed to generate customer vehicle photo URL', [
                'path' => $photoPath,
                'error' => $e->getMessage()
            ]);
            
            // Fallback to direct S3 URL
            $bucket = config('filesystems.disks.s3.bucket');
            $region = config('filesystems.disks.s3.region');
            $photoUrls[] = "https://{$bucket}.s3.{$region}.amazonaws.com/{$photoPath}";
        }
    }
    
    return $photoUrls;
}

/**
 * Generate temporary URL for customer vehicle photo
 */
private function generateCustomerVehiclePhotoTemporaryUrl($key, $minutes = 1440)
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
        \Log::error('Failed to generate customer vehicle photo temporary URL', [
            'key' => $key,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}
}
