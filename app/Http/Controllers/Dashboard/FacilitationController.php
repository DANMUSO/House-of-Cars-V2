<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Facilitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\SmsService;

class FacilitationController extends Controller
{
    public function index()
    {
        $userRole = Auth::user()->role;
        
        if (in_array($userRole, ['Managing-Director','General-Manager', 'Accountant'])) {
            // Show all facilitations for Managing Director and Accountant
            $facilitations = Facilitation::with('requester')
                                    ->orderBy('created_at', 'desc')
                                    ->get();
        } else {
            // Show only user's own facilitations for other roles
            $facilitations = Facilitation::where('request_id', Auth::id())
                                    ->with('requester')
                                    ->orderBy('created_at', 'desc')
                                    ->get();
        }
        return view('facilitation.index', compact('facilitations'));
    }

    public function store(Request $request)
    {
        $facilitation = Facilitation::create([
            'request'    => $request->frequest, 
            'amount'     => $request->famount,
            'comment'     => $request->fcomment,
            'status'     => 1,
            'request_id' => Auth::user()->id,
        ]);

        // Send SMS notification to Accountants when facilitation is requested
        try {
            $this->sendFacilitationRequestNotificationToAccountants($facilitation, Auth::user());
        } catch (\Exception $smsException) {
            Log::error('SMS error during facilitation request: ' . $smsException->getMessage());
            // Don't fail the facilitation request if SMS fails
        }
        
        return response()->json([
            'message' => 'Request submitted successfully!',
        ]);
    }
/**
 * Upload receipt for facilitation request (aligned with database schema)
 */
public function uploadReceipt(Request $request, $id)
{
    $request->validate([
        'receipt' => 'required|file|mimes:pdf,jpg,jpeg,png,gif|max:10240' // 10MB max
    ]);

    try {
        $facilitation = Facilitation::findOrFail($id);
        
        
        // Get existing receipts or initialize empty array
        $existingReceipts = $facilitation->receipt_documents ?? [];
        
        // For single receipt, replace existing one
        if (!empty($existingReceipts)) {
            try {
                $this->deleteReceiptFromS3($existingReceipts[0]);
            } catch (\Exception $e) {
                Log::warning('Failed to delete old receipt', [
                    'facilitation_id' => $facilitation->id,
                    'old_receipt' => $existingReceipts[0]
                ]);
            }
        }

        $document = $request->file('receipt');
        $originalName = pathinfo($document->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $document->getClientOriginalExtension();
        $filename = 'receipt_' . $facilitation->id . '_' . $originalName . '_' . time() . '.' . $extension;
        
        $documentPath = $this->uploadReceiptToS3($document, $filename);
        
        // Store as single item array
        $facilitation->update([
            'receipt_documents' => [$documentPath],
            'receipt_count' => 1,
            'receipt_file_size' => $this->formatFileSize($document->getSize())
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Receipt uploaded successfully!'
        ]);
        
    } catch (\Exception $e) {
        Log::error('Receipt upload failed', [
            'facilitation_id' => $id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to upload receipt: ' . $e->getMessage()
        ], 500);
    }
}
/**
 * Delete receipt for facilitation request
 */
public function deleteReceipt($id)
{
    try {
        $facilitation = Facilitation::findOrFail($id);
        
        if (empty($facilitation->receipt_documents)) {
            return response()->json([
                'success' => false,
                'message' => 'No receipt found!'
            ], 404);
        }

        // Delete from S3
        foreach ($facilitation->receipt_documents as $receiptPath) {
            $this->deleteReceiptFromS3($receiptPath);
        }

        $facilitation->update([
            'receipt_documents' => null,
            'receipt_count' => 0,
            'receipt_file_size' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Receipt deleted successfully!'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to delete receipt: ' . $e->getMessage()
        ], 500);
    }
}
/**
 * S3 helper methods
 */
private function uploadReceiptToS3($document, $filename)
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

        $key = "facilitation_receipts/" . $filename;
        
        $s3Client->putObject([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key'    => $key,
            'Body'   => fopen($document->getPathname(), 'r'),
            'ContentType' => $document->getMimeType(),
            'CacheControl' => 'max-age=31536000',
        ]);

        return $key;

    } catch (\Exception $e) {
        throw new \Exception('S3 receipt upload failed: ' . $e->getMessage());
    }
}

private function deleteReceiptFromS3($documentPath)
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

        $s3Client->deleteObject([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $documentPath
        ]);

    } catch (\Exception $e) {
        throw $e;
    }
}

private function formatFileSize($bytes)
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
    /**
     * Send SMS notification to Accountants when facilitation is requested
     */
    private function sendFacilitationRequestNotificationToAccountants($facilitation, $requesterUser)
    {
        try {
            // Get all Accountants
            $accountants = User::where('role', 'Accountant')
                ->whereNotNull('phone')
                ->get();

            $requesterName = trim($requesterUser->first_name . ' ' . $requesterUser->last_name);
            
            $message = "New facilitation request from {$requesterName}. Please review and take action.";

            foreach ($accountants as $accountant) {
                $smsSent = SmsService::send($accountant->phone, $message);
                
                if ($smsSent) {
                    Log::info('Facilitation request notification SMS sent to Accountant', [
                        'facilitation_id' => $facilitation->id,
                        'requester' => $requesterName,
                        'accountant' => $accountant->first_name . ' ' . $accountant->last_name,
                        'accountant_phone' => $accountant->phone
                    ]);
                } else {
                    Log::warning('Facilitation request notification SMS failed to Accountant', [
                        'facilitation_id' => $facilitation->id,
                        'requester' => $requesterName,
                        'accountant' => $accountant->first_name . ' ' . $accountant->last_name,
                        'accountant_phone' => $accountant->phone
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error sending facilitation request notification to accountants: ' . $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        // Validate the form input
        $id = $request->id;

        // Find the existing employee record by ID
        $Facilitation = Facilitation::findOrFail($id);

        // Update the employee record with validated data
        $Facilitation->update([
            'request' => $request->editrequest,
            'amount' => $request->editamount,
            'comment' => $request->editcomment,
        ]);

        return response()->json(['success' => 'Request updated successfully']);
    }

    public function approve(Request $request)
    {
        $id = $request->id;

        // Find the existing facilitation record by ID
        $facilitation = Facilitation::with('requester')->findOrFail($id);
    
        // Update the facilitation record with validated data
        $facilitation->update([
            'status' => 2,
        ]);

        // Send SMS notification to staff about approval
        try {
            $this->sendFacilitationApprovalNotificationToStaff($facilitation);
        } catch (\Exception $smsException) {
            Log::error('SMS error during facilitation approval: ' . $smsException->getMessage());
            // Don't fail the approval if SMS fails
        }

        return redirect()->route('Facilitation.requests')->with('success', 'Request updated successfully!');
    }

    /**
     * Send SMS notification to staff when facilitation is approved
     */
    private function sendFacilitationApprovalNotificationToStaff($facilitation)
    {
        try {
            $staffPhone = $facilitation->requester->phone;
            
            if (!$staffPhone) {
                Log::warning('No phone number found for facilitation requester', [
                    'facilitation_id' => $facilitation->id,
                    'user_id' => $facilitation->request_id
                ]);
                return;
            }

            $staffName = trim($facilitation->requester->first_name . ' ' . $facilitation->requester->last_name);

            $message = "Dear {$staffName}, your facilitation request has been APPROVED. The amount will be processed shortly.";

            $smsSent = SmsService::send($staffPhone, $message);
            
            if ($smsSent) {
                Log::info('Facilitation approval notification SMS sent to staff', [
                    'facilitation_id' => $facilitation->id,
                    'staff' => $staffName,
                    'staff_phone' => $staffPhone
                ]);
            } else {
                Log::warning('Facilitation approval notification SMS failed to staff', [
                    'facilitation_id' => $facilitation->id,
                    'staff' => $staffName,
                    'staff_phone' => $staffPhone
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error sending facilitation approval notification to staff: ' . $e->getMessage());
        }
    }

    public function reject(Request $request)
    {
        $id = $request->id;

        // Find the existing facilitation record by ID
        $facilitation = Facilitation::with('requester')->findOrFail($id);
    
        // Update the facilitation record with validated data
        $facilitation->update([
            'status' => 3,
        ]);

        // Send SMS notification to staff about rejection
        try {
            $this->sendFacilitationRejectionNotificationToStaff($facilitation);
        } catch (\Exception $smsException) {
            Log::error('SMS error during facilitation rejection: ' . $smsException->getMessage());
            // Don't fail the rejection if SMS fails
        }

        return redirect()->route('Facilitation.requests')->with('success', 'Request rejected successfully!');
    }

    /**
     * Send SMS notification to staff when facilitation is rejected
     */
    private function sendFacilitationRejectionNotificationToStaff($facilitation)
    {
        try {
            $staffPhone = $facilitation->requester->phone;
            
            if (!$staffPhone) {
                Log::warning('No phone number found for facilitation requester', [
                    'facilitation_id' => $facilitation->id,
                    'user_id' => $facilitation->request_id
                ]);
                return;
            }

            $staffName = trim($facilitation->requester->first_name . ' ' . $facilitation->requester->last_name);

            $message = "Dear {$staffName}, your facilitation request has been REJECTED. Please contact the accounts department for more information.";

            $smsSent = SmsService::send($staffPhone, $message);
            
            if ($smsSent) {
                Log::info('Facilitation rejection notification SMS sent to staff', [
                    'facilitation_id' => $facilitation->id,
                    'staff' => $staffName,
                    'staff_phone' => $staffPhone
                ]);
            } else {
                Log::warning('Facilitation rejection notification SMS failed to staff', [
                    'facilitation_id' => $facilitation->id,
                    'staff' => $staffName,
                    'staff_phone' => $staffPhone
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error sending facilitation rejection notification to staff: ' . $e->getMessage());
        }
    }
}