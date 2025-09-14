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
        
        if (in_array($userRole, ['Managing-Director', 'Accountant'])) {
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