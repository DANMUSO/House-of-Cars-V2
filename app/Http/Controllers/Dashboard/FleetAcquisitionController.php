<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\FleetAcquisition;
use App\Models\FleetPayment; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FleetAcquisitionController extends Controller
{
   public function index(Request $request)
{
    // Base query
    $query = FleetAcquisition::query();
    
    // Apply filters
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    
    if ($request->filled('financing_institution')) {
        $query->where('financing_institution', 'LIKE', '%' . $request->financing_institution . '%');
    }
    
    if ($request->filled('vehicle_category')) {
        $query->where('vehicle_category', $request->vehicle_category);
    }
    
    if ($request->filled('date_from')) {
        $query->whereDate('created_at', '>=', $request->date_from);
    }
    
    if ($request->filled('date_to')) {
        $query->whereDate('created_at', '<=', $request->date_to);
    }
    
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('vehicle_make', 'LIKE', "%{$search}%")
              ->orWhere('vehicle_model', 'LIKE', "%{$search}%")
              ->orWhere('chassis_number', 'LIKE', "%{$search}%")
              ->orWhere('registration_number', 'LIKE', "%{$search}%");
        });
    }
    
    // Get filtered results
    $fleetAcquisitions = $query->orderBy('created_at', 'desc')->get();
    
    // Calculate statistics
    $statistics = [
        'total_fleet' => FleetAcquisition::count(),
        'total_investment' => FleetAcquisition::sum('purchase_price'),
        'total_outstanding' => FleetAcquisition::sum('outstanding_balance'),
        'total_paid' => FleetAcquisition::sum('amount_paid'),
        'active_loans' => FleetAcquisition::where('status', 'active')->count(),
        'completed_loans' => FleetAcquisition::where('status', 'completed')->count(),
        'pending_approvals' => FleetAcquisition::where('status', 'pending')->count(),
        'avg_interest_rate' => FleetAcquisition::avg('interest_rate'),
        'avg_loan_duration' => FleetAcquisition::avg('loan_duration_months'),
    ];
    
    // Get unique financing institutions for filter dropdown
    $financingInstitutions = FleetAcquisition::distinct()
        ->pluck('financing_institution')
        ->filter();
    
    return view('fleetacquisition.index', compact(
        'fleetAcquisitions',
        'statistics',
        'financingInstitutions'
    ));
}

public function store(Request $request)
{
    $request->validate([
        // Vehicle Information
        'vehicle_make' => 'required|string|max:255',
        'vehicle_model' => 'required|string|max:255',
        'vehicle_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
        'engine_capacity' => 'required|string|max:50',
        'chassis_number' => 'required|string|unique:fleet_acquisitions,chassis_number|max:255',
        'engine_number' => 'required|string|unique:fleet_acquisitions,engine_number|max:255',
        'registration_number' => 'nullable|string|max:20',
        'vehicle_category' => 'required|in:commercial,passenger,utility,special_purpose',
        'purchase_price' => 'required|numeric|min:0',
        'market_value' => 'required|numeric|min:0',
        'vehicle_photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        
        // Financial Details
        'down_payment' => 'required|numeric|min:0',
        'interest_rate' => 'required|numeric|min:0|max:100',
        'loan_duration_months' => 'required|integer|min:1|max:120',
        'first_payment_date' => 'required|date',
        'insurance_premium' => 'nullable|numeric|min:0',
        
        // Legal & Compliance
        'hp_agreement_number' => 'required|string|unique:fleet_acquisitions,hp_agreement_number|max:255',
        'logbook_custody' => 'required|in:financier,company',
        'insurance_policy_number' => 'nullable|string|max:255',
        'insurance_company' => 'nullable|string|max:255',
        'insurance_expiry_date' => 'nullable|date',
        'company_kra_pin' => 'required|string|max:20',
        'business_permit_number' => 'nullable|string|max:255',
        
        // Vendor/Financier Information
        'financing_institution' => 'required|string|max:255',
        'financier_contact_person' => 'nullable|string|max:255',
        'financier_phone' => 'nullable|string|max:20',
        'financier_email' => 'nullable|email|max:255',
        'financier_agreement_ref' => 'nullable|string|max:255',
    ]);

    DB::beginTransaction();
    
    try {
        \Log::info('Starting fleet acquisition creation', [
            'vehicle_make' => $request->vehicle_make,
            'vehicle_model' => $request->vehicle_model,
            'has_photos' => $request->hasFile('vehicle_photos')
        ]);

        // Handle photo uploads to S3
        $photosPaths = [];
        $uploadErrors = [];
        
        if ($request->hasFile('vehicle_photos')) {
            \Log::info('Processing fleet vehicle photos', [
                'photo_count' => count($request->file('vehicle_photos'))
            ]);

            foreach ($request->file('vehicle_photos') as $index => $photo) {
                try {
                    // Generate unique filename
                    $originalName = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $photo->getClientOriginalExtension();
                    $filename = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;
                    
                    // Upload to S3 using direct method
                    $filePath = $this->uploadFleetPhotoToS3Direct($photo, $filename);
                    
                    $photosPaths[] = $filePath;
                    
                    \Log::info('Fleet photo uploaded successfully', [
                        'index' => $index,
                        'filename' => $filename,
                        'path' => $filePath
                    ]);
                    
                } catch (\Exception $e) {
                    $error = "Failed to upload photo " . ($index + 1) . ": " . $e->getMessage();
                    $uploadErrors[] = $error;
                    
                    \Log::error('Fleet photo upload failed', [
                        'index' => $index,
                        'filename' => $photo->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // If all photos failed but some were required
            if (empty($photosPaths) && !empty($uploadErrors)) {
                \Log::warning('All fleet photos failed to upload', [
                    'errors' => $uploadErrors
                ]);
            }
        }

        // Calculate financial details
        $purchasePrice = $request->purchase_price;
        $downPayment = $request->down_payment;
        $interestRate = $request->interest_rate / 100;
        $months = $request->loan_duration_months;
        
        $principalAmount = $purchasePrice - $downPayment;
        $totalInterest = $principalAmount * $interestRate * ($months / 12);
        $totalAmountPayable = $principalAmount + $totalInterest;
        $monthlyInstallment = $totalAmountPayable / $months;
        
        \Log::info('Financial calculations completed', [
            'principal_amount' => $principalAmount,
            'total_interest' => $totalInterest,
            'monthly_installment' => $monthlyInstallment
        ]);
        
        $fleetAcquisition = FleetAcquisition::create(array_merge($request->all(), [
            'vehicle_photos' => $photosPaths,
            'monthly_installment' => $monthlyInstallment,
            'total_interest' => $totalInterest,
            'total_amount_payable' => $totalAmountPayable,
            'outstanding_balance' => $totalAmountPayable,
            'amount_paid' => 0,
            'payments_made' => 0,
            'status' => 'pending'
        ]));

        DB::commit();
        
        \Log::info('Fleet acquisition created successfully', [
            'id' => $fleetAcquisition->id,
            'vehicle_make' => $fleetAcquisition->vehicle_make,
            'photo_count' => count($photosPaths)
        ]);
        
        $response = [
            'success' => true,
            'message' => 'Fleet acquisition record created successfully',
            'data' => $fleetAcquisition
        ];
        
        // Include warnings if some photos failed
        if (!empty($uploadErrors)) {
            $response['warnings'] = $uploadErrors;
            $response['message'] .= ' (Some photos failed to upload)';
        }
        
        return response()->json($response);
        
    } catch (\Exception $e) {
        DB::rollback();
        
        // Clean up uploaded photos if database save fails
        if (!empty($photosPaths)) {
            $this->cleanupFleetPhotos($photosPaths);
        }
        
        \Log::error('Fleet acquisition creation failed', [
            'error' => $e->getMessage(),
            'vehicle_make' => $request->vehicle_make ?? 'unknown'
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error creating fleet acquisition record: ' . $e->getMessage()
        ], 500);
    }
}

public function update(Request $request, $id)
{
    $fleetAcquisition = FleetAcquisition::findOrFail($id);
    
    $request->validate([
        // Vehicle Information
        'vehicle_make' => 'required|string|max:255',
        'vehicle_model' => 'required|string|max:255',
        'vehicle_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
        'engine_capacity' => 'required|string|max:50',
        'chassis_number' => 'required|string|max:255|unique:fleet_acquisitions,chassis_number,' . $id,
        'engine_number' => 'required|string|max:255|unique:fleet_acquisitions,engine_number,' . $id,
        'registration_number' => 'nullable|string|max:20',
        'vehicle_category' => 'required|in:commercial,passenger,utility,special_purpose',
        'purchase_price' => 'required|numeric|min:0',
        'market_value' => 'required|numeric|min:0',
        'vehicle_photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        
        // Financial Details
        'down_payment' => 'required|numeric|min:0',
        'interest_rate' => 'required|numeric|min:0|max:100',
        'loan_duration_months' => 'required|integer|min:1|max:120',
        'first_payment_date' => 'required|date',
        'insurance_premium' => 'nullable|numeric|min:0',
        
        // Other fields validation...
        'hp_agreement_number' => 'required|string|max:255|unique:fleet_acquisitions,hp_agreement_number,' . $id,
        'financing_institution' => 'required|string|max:255',
    ]);

    DB::beginTransaction();
    
    try {
        \Log::info('Starting fleet acquisition update', [
            'id' => $id,
            'vehicle_make' => $request->vehicle_make,
            'has_new_photos' => $request->hasFile('vehicle_photos'),
            'replace_photos' => $request->has('replace_photos') && $request->replace_photos == '1'
        ]);

        // Handle photo uploads
        $photosPaths = $fleetAcquisition->vehicle_photos ?? [];
        $oldPhotoPaths = $photosPaths; // Keep reference for cleanup
        $uploadErrors = [];
        
        if ($request->hasFile('vehicle_photos')) {
            \Log::info('Processing new fleet photos', [
                'new_photo_count' => count($request->file('vehicle_photos')),
                'existing_photo_count' => count($photosPaths),
                'replace_mode' => $request->has('replace_photos') && $request->replace_photos == '1'
            ]);

            // Delete old photos if replacing
            if ($request->has('replace_photos') && $request->replace_photos == '1') {
                \Log::info('Replacing existing photos - will cleanup old photos after successful upload');
                $photosPaths = []; // Clear current paths, will add new ones
            }
            
            // Add new photos
            foreach ($request->file('vehicle_photos') as $index => $photo) {
                try {
                    // Generate unique filename
                    $originalName = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $photo->getClientOriginalExtension();
                    $filename = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;
                    
                    // Upload to S3 using direct method
                    $filePath = $this->uploadFleetPhotoToS3Direct($photo, $filename);
                    
                    $photosPaths[] = $filePath;
                    
                    \Log::info('New fleet photo uploaded successfully', [
                        'index' => $index,
                        'filename' => $filename,
                        'path' => $filePath
                    ]);
                    
                } catch (\Exception $e) {
                    $error = "Failed to upload photo " . ($index + 1) . ": " . $e->getMessage();
                    $uploadErrors[] = $error;
                    
                    \Log::error('New fleet photo upload failed', [
                        'index' => $index,
                        'filename' => $photo->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // If all new photos failed and we were replacing, revert to old photos
            if (empty($photosPaths) && $request->has('replace_photos') && $request->replace_photos == '1') {
                $photosPaths = $oldPhotoPaths;
                \Log::warning('All new photos failed, reverting to old photos');
            }
        }
        
        // Recalculate financial details
        $purchasePrice = $request->purchase_price;
        $downPayment = $request->down_payment;
        $interestRate = $request->interest_rate / 100;
        $months = $request->loan_duration_months;
        
        $principalAmount = $purchasePrice - $downPayment;
        $totalInterest = $principalAmount * $interestRate * ($months / 12);
        $totalAmountPayable = $principalAmount + $totalInterest;
        $monthlyInstallment = $totalAmountPayable / $months;
        
        \Log::info('Updated financial calculations', [
            'principal_amount' => $principalAmount,
            'total_interest' => $totalInterest,
            'monthly_installment' => $monthlyInstallment
        ]);
        
        $fleetAcquisition->update(array_merge($request->all(), [
            'vehicle_photos' => $photosPaths,
            'monthly_installment' => $monthlyInstallment,
            'total_interest' => $totalInterest,
            'total_amount_payable' => $totalAmountPayable,
            'outstanding_balance' => $totalAmountPayable - $fleetAcquisition->amount_paid,
        ]));

        // Clean up old photos only if we successfully uploaded new ones and were replacing
        if ($request->hasFile('vehicle_photos') && 
            $request->has('replace_photos') && 
            $request->replace_photos == '1' && 
            !empty($photosPaths) && 
            $photosPaths !== $oldPhotoPaths) {
            
            $this->cleanupFleetPhotos($oldPhotoPaths);
        }

        DB::commit();
        
        \Log::info('Fleet acquisition updated successfully', [
            'id' => $fleetAcquisition->id,
            'vehicle_make' => $fleetAcquisition->vehicle_make,
            'final_photo_count' => count($photosPaths)
        ]);
        
        $response = [
            'success' => true,
            'message' => 'Fleet acquisition record updated successfully',
            'data' => $fleetAcquisition
        ];
        
        // Include warnings if some photos failed
        if (!empty($uploadErrors)) {
            $response['warnings'] = $uploadErrors;
            $response['message'] .= ' (Some photos failed to upload)';
        }
        
        return response()->json($response);
        
    } catch (\Exception $e) {
        DB::rollback();
        
        \Log::error('Fleet acquisition update failed', [
            'id' => $id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error updating fleet acquisition record: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Upload fleet photo directly to S3 using AWS SDK with SSL disabled
 */
private function uploadFleetPhotoToS3Direct($photo, $filename)
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

        $key = "fleet_photos/" . $filename;
        
        $result = $s3Client->putObject([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key'    => $key,
            'Body'   => fopen($photo->getPathname(), 'r'),
            'ContentType' => $photo->getMimeType(),
            'CacheControl' => 'max-age=31536000', // 1 year cache for images
        ]);

        \Log::info('Direct S3 fleet photo upload successful', [
            'key' => $key,
            'object_url' => $result['ObjectURL'] ?? 'N/A'
        ]);

        return $key; // Return the S3 key path

    } catch (\Exception $e) {
        \Log::error('Direct S3 fleet photo upload failed', [
            'filename' => $filename,
            'error' => $e->getMessage()
        ]);
        throw new \Exception('Direct S3 fleet photo upload failed: ' . $e->getMessage());
    }
}

/**
 * Clean up fleet photos from S3
 */
private function cleanupFleetPhotos($photoPaths)
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
                
                \Log::info('Cleaned up fleet photo', ['path' => $photoPath]);
                
            } catch (\Exception $e) {
                \Log::warning('Failed to cleanup fleet photo', [
                    'path' => $photoPath,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
    } catch (\Exception $e) {
        \Log::error('Fleet photo cleanup process failed', [
            'error' => $e->getMessage(),
            'photo_count' => count($photoPaths)
        ]);
    }
}

/**
 * Get fleet photo URLs for display
 */
public function getFleetPhotoUrls($fleetAcquisition)
{
    $photoPaths = $fleetAcquisition->vehicle_photos ?? [];
    $photoUrls = [];
    
    foreach ($photoPaths as $photoPath) {
        try {
            // Check if it's already a full URL
            if (str_starts_with($photoPath, 'https://')) {
                $photoUrls[] = $photoPath;
                continue;
            }
            
            // Generate temporary URL for photo (valid for 24 hours)
            $photoUrls[] = $this->generateFleetPhotoTemporaryUrl($photoPath, 1440);
            
        } catch (\Exception $e) {
            \Log::warning('Failed to generate fleet photo URL', [
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
 * Generate temporary URL for fleet photo
 */
private function generateFleetPhotoTemporaryUrl($key, $minutes = 1440)
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
        \Log::error('Failed to generate fleet photo temporary URL', [
            'key' => $key,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}

    public function show($id)
    {
        try {
            $fleetAcquisition = FleetAcquisition::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $fleetAcquisition
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fleet acquisition not found'
            ], 404);
        }
    }

  
    public function destroy($id)
    {
        try {
            $fleetAcquisition = FleetAcquisition::findOrFail($id);
            
            // Delete associated photos
            if ($fleetAcquisition->vehicle_photos) {
                foreach ($fleetAcquisition->vehicle_photos as $photo) {
                    Storage::disk('public')->delete($photo);
                }
            }
            
            $fleetAcquisition->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Fleet acquisition record deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting fleet acquisition record: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approve($id)
    {
        try {
            $fleetAcquisition = FleetAcquisition::findOrFail($id);
            $fleetAcquisition->update(['status' => 'approved']);
            
            return response()->json([
                'success' => true,
                'message' => 'Fleet acquisition approved successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error approving fleet acquisition: ' . $e->getMessage()
            ], 500);
        }
    }

    public function manage($id)
    {
        try {
            $fleet = FleetAcquisition::with('payments')->findOrFail($id);
            $payments = $fleet->payments()->orderBy('payment_date', 'desc')->get();
            
            // ===== ACCURATE CALCULATIONS =====
            
            // 1. Calculate the loan amount (what needs to be paid after down payment)
            $loanAmount = $fleet->purchase_price - $fleet->down_payment;
            
            // 2. Get only CONFIRMED payments (exclude pending)
            $confirmedPayments = $fleet->payments()
                ->where('status', 'confirmed')
                ->sum('payment_amount');
            
            // 3. Calculate accurate outstanding balance
            $accurateOutstanding = max(0, $loanAmount - $confirmedPayments);
            
            // 4. Calculate accurate paid percentage
            $paidPercentage = 0;
            if ($loanAmount > 0) {
                $paidPercentage = min(100, ($confirmedPayments / $loanAmount) * 100);
            }
            
            // 5. Count confirmed payments for progress display
            $confirmedPaymentsCount = $fleet->payments()
                ->where('status', 'confirmed')
                ->count();
            
            // ===== UPDATE FLEET OBJECT WITH ACCURATE VALUES =====
            
            // Update the fleet object with calculated values (for display)
            $fleet->amount_paid = $confirmedPayments;
            $fleet->outstanding_balance = $accurateOutstanding;
            $fleet->payments_made = $confirmedPaymentsCount;
            
            // ===== RECALCULATE PAYMENT BALANCES =====
            
            // Get all confirmed payments in chronological order
            $confirmedPaymentsList = $fleet->payments()
                ->where('status', 'confirmed')
                ->orderBy('payment_date')
                ->orderBy('created_at')
                ->get();
            
            // Recalculate running balances for each payment
            $runningBalance = $loanAmount; // Start with total loan amount
            
            foreach ($confirmedPaymentsList as $payment) {
                // Set balance before this payment
                $payment->balance_before = $runningBalance;
                
                // Subtract payment amount
                $runningBalance -= $payment->payment_amount;
                
                // Set balance after this payment (never go below 0)
                $payment->balance_after = max(0, $runningBalance);
                
                // Save the updated balances to database
                $payment->save();
            }
            
            // ===== PERSIST ACCURATE VALUES TO DATABASE =====
            
            // Update the fleet record with accurate values
            $fleet->update([
                'amount_paid' => $confirmedPayments,
                'outstanding_balance' => $accurateOutstanding
            ]);
            
            // ===== AUTO-UPDATE STATUS BASED ON PAYMENT PROGRESS =====
            
            if ($accurateOutstanding <= 0 && $confirmedPayments > 0) {
                // Loan is fully paid
                if ($fleet->status !== 'completed') {
                    $fleet->update(['status' => 'completed']);
                    $fleet->status = 'completed'; // Update the object too
                }
            } elseif ($confirmedPayments > 0 && $fleet->status === 'approved') {
                // First payment made, change to active
                $fleet->update(['status' => 'active']);
                $fleet->status = 'active'; // Update the object too
            }
            
            // ===== ADDITIONAL DATA FOR VIEW =====
            
            // Add computed properties for JavaScript
            $fleet->loan_amount = $loanAmount;
            $fleet->confirmed_payments_total = $confirmedPayments;
            $fleet->confirmed_payments_count = $confirmedPaymentsCount;
            
            // Debug information (remove in production)
            $debugInfo = [
                'purchase_price' => $fleet->purchase_price,
                'down_payment' => $fleet->down_payment,
                'loan_amount' => $loanAmount,
                'confirmed_payments' => $confirmedPayments,
                'outstanding_calculated' => $accurateOutstanding,
                'paid_percentage' => round($paidPercentage, 2),
                'total_payments_count' => $payments->count(),
                'confirmed_payments_count' => $confirmedPaymentsCount,
                'pending_payments_count' => $payments->where('status', 'pending')->count(),
            ];
            
            // Log for debugging (remove in production)
            \Log::info("Fleet {$id} accurate calculations:", $debugInfo);
            
            return view('fleetacquisition.manage-fleet', compact('fleet', 'payments', 'paidPercentage'));
            
        } catch (\Exception $e) {
            \Log::error("Error in manage fleet {$id}: " . $e->getMessage());
            return redirect()->route('fleetacquisition')->with('error', 'Fleet acquisition not found.');
        }
    }

    public function storePayment(Request $request, $id)
    {
        $request->validate([
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:bank_transfer,cheque,cash,mobile_money',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        
        try {
            $fleet = FleetAcquisition::findOrFail($id);
            
            // Validate payment amount doesn't exceed outstanding balance
            if ($request->payment_amount > $fleet->outstanding_balance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount cannot exceed outstanding balance.'
                ], 400);
            }
            
            // Get current balance
            $balanceBefore = $fleet->outstanding_balance;
            $balanceAfter = $balanceBefore - $request->payment_amount;
            
            // Create payment record
            $payment = FleetPayment::create([
                'fleet_acquisition_id' => $fleet->id,
                'payment_amount' => $request->payment_amount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'payment_number' => $fleet->payments()->count() + 1,
                'notes' => $request->notes,
                'status' => 'confirmed',
                'processed_by' => auth()->user()->name ?? 'System'
            ]);
            
            // Update fleet acquisition
            $fleet->amount_paid += $request->payment_amount;
            $fleet->outstanding_balance = $balanceAfter;
            $fleet->payments_made += 1;
            
            // Update status if needed
            if ($fleet->status == 'approved') {
                $fleet->status = 'active';
            }
            
            if ($fleet->outstanding_balance <= 0) {
                $fleet->status = 'completed';
                $fleet->completion_date = now();
            }
            
            $fleet->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'data' => [
                    'payment' => $payment,
                    'new_balance' => $fleet->outstanding_balance,
                    'paid_percentage' => ($fleet->amount_paid / $fleet->total_amount_payable) * 100
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error recording payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deletePhoto(Request $request, $id)
    {
        try {
            $fleetAcquisition = FleetAcquisition::findOrFail($id);
            $photoIndex = $request->photo_index;
            
            if (isset($fleetAcquisition->vehicle_photos[$photoIndex])) {
                $photoPath = $fleetAcquisition->vehicle_photos[$photoIndex];
                Storage::disk('public')->delete($photoPath);
                
                $photos = $fleetAcquisition->vehicle_photos;
                unset($photos[$photoIndex]);
                $photos = array_values($photos); // Re-index array
                
                $fleetAcquisition->update(['vehicle_photos' => $photos]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Photo deleted successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Photo not found'
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting photo: ' . $e->getMessage()
            ], 500);
        }
    }
}