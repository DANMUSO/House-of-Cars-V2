<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CarImport;
use Illuminate\Support\Facades\Storage;

class CarImportController extends Controller
{
    
    
    public function store(Request $request)
{
    $validated = $request->validate([
        'bidder_name' => 'required|string',
        'make' => 'required|string',
        'model' => 'required|string',
        'year' => 'required|numeric',
        'vin' => 'required|string',
        'engine_type' => 'required|string',
        'body_type' => 'required|string',
        'mileage' => 'required|numeric',
        'bid_amount' => 'required|numeric',
        'bid_start_date' => 'required|date',
        'bid_end_date' => 'required|date',
        'colour' => 'required|string',
        'engine_no' => 'required|string',
        'engine_capacity' => 'required|string',
        'transmission' => 'required|string',
        //'deposit' => 'required|numeric',
        'photos.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
    ]);

    $photoPaths = [];
    $uploadErrors = [];

    if ($request->hasFile('photos')) {
        try {
            \Log::info('Starting photo uploads', [
                'photo_count' => count($request->file('photos')),
                'bidder_name' => $validated['bidder_name']
            ]);

            foreach ($request->file('photos') as $index => $photo) {
                try {
                    // Generate unique filename
                    $originalName = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $photo->getClientOriginalExtension();
                    $filename = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;
                    
                    // Upload to S3 using direct method
                    $filePath = $this->uploadPhotoToS3Direct($photo, $filename);
                    
                    $photoPaths[] = $filePath;
                    
                    \Log::info('Photo uploaded successfully', [
                        'index' => $index,
                        'filename' => $filename,
                        'path' => $filePath
                    ]);
                    
                } catch (\Exception $e) {
                    $error = "Failed to upload photo " . ($index + 1) . ": " . $e->getMessage();
                    $uploadErrors[] = $error;
                    
                    \Log::error('Photo upload failed', [
                        'index' => $index,
                        'filename' => $photo->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // If all photos failed
            if (empty($photoPaths) && !empty($uploadErrors)) {
                return response()->json([
                    'error' => 'All photo uploads failed. Errors: ' . implode(', ', $uploadErrors)
                ], 500);
            }
            
        } catch (\Exception $e) {
            \Log::error('Photo upload process failed', [
                'error' => $e->getMessage(),
                'bidder_name' => $validated['bidder_name']
            ]);
            
            return response()->json([
                'error' => 'Photo upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    // Store photo paths as JSON
    $validated['photos'] = json_encode($photoPaths);

    try {
        $carImport = CarImport::create($validated);
        
        \Log::info('Car bid created successfully', [
            'id' => $carImport->id,
            'bidder_name' => $validated['bidder_name'],
            'photo_count' => count($photoPaths)
        ]);

        $response = ['message' => 'Bid created successfully'];
        
        // Include warnings if some photos failed
        if (!empty($uploadErrors)) {
            $response['warnings'] = $uploadErrors;
            $response['message'] .= ' (Some photos failed to upload)';
        }

        return response()->json($response);
        
    } catch (\Exception $e) {
        // If database save fails, clean up uploaded photos
        $this->cleanupUploadedPhotos($photoPaths);
        
        \Log::error('Failed to save car bid to database', [
            'error' => $e->getMessage(),
            'bidder_name' => $validated['bidder_name']
        ]);
        
        return response()->json([
            'error' => 'Failed to save bid: ' . $e->getMessage()
        ], 500);
    }
}

public function update(Request $request)
{
    $request->validate([
        'id' => 'required|exists:car_imports,id',
        'editbidder_name' => 'required|string|max:255',
        'editmake' => 'required|string|max:255',
        'editmodel' => 'required|string|max:255',
        'edityear' => 'required|integer',
        'editvin' => 'required|string|max:255',
        'editengine_type' => 'required|string|max:255',
        'editbody_type' => 'required|string|max:255',
        'editmileage' => 'required|numeric',
        'editbid_amount' => 'required|numeric',
        'editbid_start_date' => 'required|date',
        'editbid_end_date' => 'required|date',
        'editcolour' => 'required|string',
        'editengine_no' => 'required|string',
        'editengine_capacity' => 'required|string',
        'edittransmission' => 'required|string',
        //'editdeposit' => 'required|numeric',
        'editphotos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
    ]);

    try {
        $car = CarImport::findOrFail($request->id);
        
        \Log::info('Starting car update', [
            'car_id' => $car->id,
            'bidder_name' => $request->editbidder_name,
            'has_new_photos' => $request->hasFile('editphotos')
        ]);

        // Store old photos for cleanup if needed
        $oldPhotoPaths = json_decode($car->photos, true) ?? [];

        // Update standard fields
        $car->bidder_name = $request->editbidder_name;
        $car->make = $request->editmake;
        $car->model = $request->editmodel;
        $car->year = $request->edityear;
        $car->status = 0;
        $car->vin = $request->editvin;
        $car->engine_type = $request->editengine_type;
        $car->body_type = $request->editbody_type;
        $car->mileage = $request->editmileage;
        $car->bid_amount = $request->editbid_amount;
        $car->bid_start_date = $request->editbid_start_date;
        $car->bid_end_date = $request->editbid_end_date;
        $car->colour = $request->editcolour;
        $car->engine_no = $request->editengine_no;
        $car->engine_capacity = $request->editengine_capacity;
        $car->transmission = $request->edittransmission;

        // Handle photo updates
        if ($request->hasFile('editphotos')) {
            $photoPaths = [];
            $uploadErrors = [];

            \Log::info('Processing new photos', [
                'photo_count' => count($request->file('editphotos')),
                'old_photo_count' => count($oldPhotoPaths)
            ]);

            try {
                foreach ($request->file('editphotos') as $index => $photo) {
                    try {
                        // Generate unique filename
                        $originalName = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                        $extension = $photo->getClientOriginalExtension();
                        $filename = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;
                        
                        // Upload to S3 using direct method
                        $filePath = $this->uploadPhotoToS3Direct($photo, $filename);
                        
                        $photoPaths[] = $filePath;
                        
                        \Log::info('New photo uploaded successfully', [
                            'index' => $index,
                            'filename' => $filename,
                            'path' => $filePath
                        ]);
                        
                    } catch (\Exception $e) {
                        $error = "Failed to upload photo " . ($index + 1) . ": " . $e->getMessage();
                        $uploadErrors[] = $error;
                        
                        \Log::error('New photo upload failed', [
                            'index' => $index,
                            'filename' => $photo->getClientOriginalName(),
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                // If at least one photo uploaded successfully, update the photos
                if (!empty($photoPaths)) {
                    $car->photos = json_encode($photoPaths);
                    
                    \Log::info('Photos updated successfully', [
                        'new_photo_count' => count($photoPaths),
                        'upload_errors' => count($uploadErrors)
                    ]);
                } else {
                    // If all new photos failed, keep old photos
                    \Log::warning('All new photos failed to upload, keeping old photos');
                    
                    if (!empty($uploadErrors)) {
                        return response()->json([
                            'error' => 'All new photo uploads failed. Errors: ' . implode(', ', $uploadErrors)
                        ], 500);
                    }
                }
                
            } catch (\Exception $e) {
                \Log::error('Photo update process failed', [
                    'car_id' => $car->id,
                    'error' => $e->getMessage()
                ]);
                
                return response()->json([
                    'error' => 'Photo update failed: ' . $e->getMessage()
                ], 500);
            }
        }

        // Save the updated car
        $car->save();

        // Clean up old photos only if new photos were successfully uploaded
        if ($request->hasFile('editphotos') && !empty(json_decode($car->photos, true))) {
            $this->cleanupUploadedPhotos($oldPhotoPaths);
        }

        \Log::info('Car updated successfully', [
            'car_id' => $car->id,
            'bidder_name' => $car->bidder_name
        ]);

        $response = [
            'success' => true,
            'message' => 'Car import details updated successfully'
        ];
        
        // Include warnings if some photos failed
        if (!empty($uploadErrors)) {
            $response['warnings'] = $uploadErrors;
            $response['message'] .= ' (Some new photos failed to upload)';
        }

        return response()->json($response);
        
    } catch (\Exception $e) {
        \Log::error('Car update failed', [
            'car_id' => $request->id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'error' => 'Failed to update car: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Upload photo directly to S3 using AWS SDK with SSL disabled
 */
private function uploadPhotoToS3Direct($photo, $filename)
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

        $key = "car_bids/" . $filename;
        
        $result = $s3Client->putObject([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key'    => $key,
            'Body'   => fopen($photo->getPathname(), 'r'),
            'ContentType' => $photo->getMimeType(),
            'CacheControl' => 'max-age=31536000', // 1 year cache for images
        ]);

        \Log::info('Direct S3 photo upload successful', [
            'key' => $key,
            'object_url' => $result['ObjectURL'] ?? 'N/A'
        ]);

        return $key; // Return the S3 key path

    } catch (\Exception $e) {
        \Log::error('Direct S3 photo upload failed', [
            'filename' => $filename,
            'error' => $e->getMessage()
        ]);
        throw new \Exception('Direct S3 photo upload failed: ' . $e->getMessage());
    }
}

/**
 * Clean up uploaded photos from S3
 */
private function cleanupUploadedPhotos($photoPaths)
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
                $s3Client->deleteObject([
                    'Bucket' => config('filesystems.disks.s3.bucket'),
                    'Key' => $photoPath
                ]);
                
                \Log::info('Cleaned up old photo', ['path' => $photoPath]);
                
            } catch (\Exception $e) {
                \Log::warning('Failed to cleanup photo', [
                    'path' => $photoPath,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
    } catch (\Exception $e) {
        \Log::error('Photo cleanup process failed', [
            'error' => $e->getMessage(),
            'photo_count' => count($photoPaths)
        ]);
    }
}

/**
 * Get photo URLs for display
 */
public function getPhotoUrls($carImport)
{
    $photoPaths = json_decode($carImport->photos, true) ?? [];
    $photoUrls = [];
    
    foreach ($photoPaths as $photoPath) {
        try {
            // Generate temporary URL for photo (valid for 24 hours)
            $photoUrls[] = $this->generatePhotoTemporaryUrl($photoPath, 1440);
        } catch (\Exception $e) {
            \Log::warning('Failed to generate photo URL', [
                'path' => $photoPath,
                'error' => $e->getMessage()
            ]);
            // Skip this photo if URL generation fails
        }
    }
    
    return $photoUrls;
}

/**
 * Generate temporary URL for photo
 */
private function generatePhotoTemporaryUrl($key, $minutes = 1440)
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
        \Log::error('Failed to generate photo temporary URL', [
            'key' => $key,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}

    public function depositupdate(Request $request){
        // Validate the form input fullpayment
        $id = $request->id;
    
        // Find the existing deposit record by ID
        $deposit = CarImport::findOrFail($id);
    
        // Update the deposit record with validated data
        $deposit->update([
            'deposit' => $request->depositamount,
            'status' => '1'
        ]);
        return response()->json(['success' => 'Deposit updated successfully']);

    }
     public function fullamount(Request $request){
        // Validate the form input 
        $id = $request->id;
        // Find the existing deposit record by ID
        $deposit = CarImport::findOrFail($id);
    
        // Update the deposit record with validated data
        $deposit->update([
            'fullamount' => $request->fullamount,
            'status' => '4'
        ]);
        return response()->json(['success' => 'Full Payment updated successfully']);

    }
    public function index()
    {
        $carBids = CarImport::where('status', 0)->get(); // or paginate() if needed
        return view('carimport.index', compact('carBids'));
    }
    public function deposit()
    {
        $carBids = CarImport::where('status', 1)->get(); // or paginate() if needed
        return view('carimport.deposit', compact('carBids'));
    }
    public function winbid(Request $request){
        // Validate the form input
        $id = $request->id;
    
        // Find the existing deposit record by ID
        $deposit = CarImport::findOrFail($id);
    
        // Update the deposit record with validated data
        $deposit->update([
            'status' => '2'
        ]);
        return response()->json(['success' => 'Bid updated successfully']);
    }
    public function lostbid(Request $request){
        // Validate the form input 
        $id = $request->id;
    
        // Find the existing deposit record by ID
        $deposit = CarImport::findOrFail($id);
    
        // Update the deposit record with validated data
        $deposit->update([
            'status' => '3'
        ]);
        return response()->json(['success' => 'Bid updated successfully']);
    }
    public function confirmfullpayment(Request $request){
        // Validate the form input  
        $id = $request->id;
    
        // Find the existing deposit record by ID
        $deposit = CarImport::findOrFail($id);
    
        // Update the deposit record with validated data
        $deposit->update([
            'status' => '4'

        ]);
        return response()->json(['success' => 'Bid updated successfully']);
    }
    public function confirmimport(Request $request){
        // Validate the form input  confirmreception
        $id = $request->id;
    
        // Find the existing deposit record by ID
        $deposit = CarImport::findOrFail($id);
    
        // Update the deposit record with validated data
        $deposit->update([
            'status' => '5'
        ]);
        return response()->json(['success' => 'Bid updated successfully']);
    }
   
    public function confirmimported(Request $request){
        // Validate the form input   
        $id = $request->id;
    
        // Find the existing deposit record by ID
        $deposit = CarImport::findOrFail($id);
    
        // Update the deposit record with validated data
        $deposit->update([
            'status' => '6'
        ]);
        return response()->json(['success' => 'Bid updated successfully']);
    }
    public function portcharges(Request $request){
        // Validate the form input   
        $id = $request->id;
    
        // Find the existing deposit record by ID
        $deposit = CarImport::findOrFail($id);
    
        // Update the deposit record with validated data
        $deposit->update([
            'status' => '7'
        ]);
        return response()->json(['success' => 'Bid updated successfully']);
    }
    public function confirmreception(Request $request){
        // Validate the form input  
        $id = $request->id;
    
        // Find the existing deposit record by ID
        $deposit = CarImport::findOrFail($id);
    
        // Update the deposit record with validated data
        $deposit->update([
            'status' => '8'
        ]);
        return response()->json(['success' => 'Bid updated successfully']);
    }

    public function shipping()
    {
        $carBids = CarImport::where('status', 4)->get();
        return view('carimport.shipping', compact('carBids'));
    }
    public function wonbids()
    {
        $carBids = CarImport::where('status', 2)->get();
        return view('carimport.wonbid', compact('carBids'));
    }
    public function lostbids()
    {
        $carBids = CarImport::where('status', 3)->get();
        return view('carimport.lostbid', compact('carBids'));
    }
    public function fullpayment()
    {
        $carBids = CarImport::all();
        return view('carimport.fullpayment', compact('carBids'));
    }
    public function shipment()
    {
        $carBids = CarImport::where('status', 5)->get();
        return view('carimport.shipment', compact('carBids'));
    }
    public function customduty()
    {
        $carBids = CarImport::where('status', 6)->get();
        return view('carimport.customduty', compact('carBids'));
    }
    public function receipt()
    {
        $carBids = CarImport::where('status', 7)->get();
        return view('carimport.receipt', compact('carBids'));
    }
    public function received()
    {
        $carBids = CarImport::where('status', 8)->get();
        return view('carimport.imported-received', compact('carBids'));
    }
    
}
