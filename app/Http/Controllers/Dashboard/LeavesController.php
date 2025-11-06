<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LeaveDay;
use App\Models\LeaveApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\SmsService;

class LeavesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
    {
        $userRole = Auth::user()->role;
        
        // Apply role-based filtering
        if (in_array($userRole, ['Managing-Director','General-Manager', 'HR'])) {
            // Show all leave applications for Managing Director and HR
            $leaveApplications = LeaveApplication::with(['user', 'leaveDay'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Show only user's own leave applications for other roles
            $leaveApplications = LeaveApplication::with(['user', 'leaveDay'])
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Get current user's ACTIVE leave balances only
        $userLeaveBalances = collect();
        if (Auth::check()) {
            $userLeaveBalances = LeaveDay::where('user_id', Auth::id())
                ->where('year', date('Y'))
                ->where('status', 'active')
                ->get()
                ->keyBy('leave_type');
                        
            // If user has no leave balances, create default ones
            if ($userLeaveBalances->isEmpty()) {
                $this->createDefaultLeaveBalances(Auth::id());
                                
                // Reload the leave balances
                $userLeaveBalances = LeaveDay::where('user_id', Auth::id())
                    ->where('year', date('Y'))
                    ->where('status', 'active')
                    ->get()
                    ->keyBy('leave_type');
            }
        }
                
        // Get handover suggestions based on user role
        $handoverSuggestions = $this->getHandoverSuggestions();
                
        // If it's an AJAX request, return JSON
        if (request()->ajax()) {
            return response()->json([
                'leaveApplications' => $leaveApplications,
                'userLeaveBalances' => $userLeaveBalances,
                'handoverSuggestions' => $handoverSuggestions
            ]);
        }
                
        return view('leaves.index', compact('leaveApplications', 'userLeaveBalances', 'handoverSuggestions'));
    }

    /**
     * Create default leave balances for a user
     */
    private function createDefaultLeaveBalances($userId)
{
    $defaultLeaveTypes = [
        'Annual Leave' => ['days' => 25, 'hours' => 200],
        'Sick Leave' => ['days' => 12, 'hours' => 96],
        'Emergency Leave' => ['days' => 3, 'hours' => 24],
        'Maternity/Paternity Leave' => ['days' => 90, 'hours' => 0],
    ];

    foreach ($defaultLeaveTypes as $leaveType => $allocation) {
        LeaveDay::updateOrCreate(
            ['user_id' => $userId, 'leave_type' => $leaveType, 'year' => date('Y')],
            [
                'total_days' => $allocation['days'],
                'used_days' => 0,
                'remaining_days' => $allocation['days'],
                'total_hours' => $allocation['hours'],
                'used_hours' => 0,
                'remaining_hours' => $allocation['hours'],
                'status' => 'active',
            ]
        );
    }
}
    /**
     * Send SMS notification to handover person when they are assigned
     */
    private function sendHandoverNotificationToAssignee($leaveApplication, $applicantUser)
    {
        try {
            // Try to find the handover person by name
            $handoverPersonName = $leaveApplication->handover_person;
            
            // Parse the handover person name (remove email if present)
            $cleanName = trim(explode('(', $handoverPersonName)[0]);
            
            // Try to find user by matching first and last name combination
            $handoverUser = User::where(function($query) use ($cleanName) {
                $nameParts = explode(' ', $cleanName);
                if (count($nameParts) >= 2) {
                    $firstName = $nameParts[0];
                    $lastName = implode(' ', array_slice($nameParts, 1));
                    
                    $query->where(function($q) use ($firstName, $lastName) {
                        $q->where('first_name', 'LIKE', "%{$firstName}%")
                          ->where('last_name', 'LIKE', "%{$lastName}%");
                    })->orWhere(function($q) use ($cleanName) {
                        // Also try matching against concatenated full name
                        $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$cleanName}%"]);
                    });
                } else {
                    // Single name provided
                    $query->where('first_name', 'LIKE', "%{$cleanName}%")
                          ->orWhere('last_name', 'LIKE', "%{$cleanName}%");
                }
            })
            ->whereNotNull('phone')
            ->first();

            if (!$handoverUser) {
                Log::warning('Handover person not found or no phone number', [
                    'leave_application_id' => $leaveApplication->id,
                    'handover_person' => $handoverPersonName,
                    'search_name' => $cleanName
                ]);
                return;
            }

            $applicantName = trim($applicantUser->first_name . ' ' . $applicantUser->last_name);
            $startDate = Carbon::parse($leaveApplication->start_date)->format('M d, Y');
            $endDate = Carbon::parse($leaveApplication->end_date)->format('M d, Y');
            $handoverUserName = trim($handoverUser->first_name . ' ' . $handoverUser->last_name);
            
            $message = "Hello {$handoverUserName}, you have been assigned as handover person for {$applicantName}'s {$leaveApplication->leave_type} from {$startDate} to {$endDate} ({$leaveApplication->total_days} days). Please coordinate with {$applicantName} for duty handover. Reason: {$leaveApplication->reason}";

            $smsSent = SmsService::send($handoverUser->phone, $message);
            
            if ($smsSent) {
                Log::info('Handover notification SMS sent to assigned person', [
                    'leave_application_id' => $leaveApplication->id,
                    'applicant' => $applicantName,
                    'handover_person' => $handoverUserName,
                    'handover_phone' => $handoverUser->phone
                ]);
            } else {
                Log::warning('Handover notification SMS failed to assigned person', [
                    'leave_application_id' => $leaveApplication->id,
                    'applicant' => $applicantName,
                    'handover_person' => $handoverUserName,
                    'handover_phone' => $handoverUser->phone
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error sending handover notification to assigned person: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to handover person when leave is approved
     */
    private function sendHandoverApprovalNotification($leave)
    {
        try {
            // Try to find the handover person by name
            $handoverPersonName = $leave->handover_person;
            
            // Parse the handover person name (remove email if present)
            $cleanName = trim(explode('(', $handoverPersonName)[0]);
            
            // Try to find user by matching first and last name combination
            $handoverUser = User::where(function($query) use ($cleanName) {
                $nameParts = explode(' ', $cleanName);
                if (count($nameParts) >= 2) {
                    $firstName = $nameParts[0];
                    $lastName = implode(' ', array_slice($nameParts, 1));
                    
                    $query->where(function($q) use ($firstName, $lastName) {
                        $q->where('first_name', 'LIKE', "%{$firstName}%")
                          ->where('last_name', 'LIKE', "%{$lastName}%");
                    })->orWhere(function($q) use ($cleanName) {
                        // Also try matching against concatenated full name
                        $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$cleanName}%"]);
                    });
                } else {
                    // Single name provided
                    $query->where('first_name', 'LIKE', "%{$cleanName}%")
                          ->orWhere('last_name', 'LIKE', "%{$cleanName}%");
                }
            })
            ->whereNotNull('phone')
            ->first();

            if (!$handoverUser) {
                Log::warning('Handover person not found for approval notification', [
                    'leave_application_id' => $leave->id,
                    'handover_person' => $handoverPersonName
                ]);
                return;
            }

            $applicantName = trim($leave->user->first_name . ' ' . $leave->user->last_name);
            $startDate = Carbon::parse($leave->start_date)->format('M d, Y');
            $endDate = Carbon::parse($leave->end_date)->format('M d, Y');
            $handoverUserName = trim($handoverUser->first_name . ' ' . $handoverUser->last_name);
            
            $message = "Hi {$handoverUserName}, {$applicantName}'s {$leave->leave_type} from {$startDate} to {$endDate} has been APPROVED. Please prepare for duty handover. Contact {$applicantName} to discuss handover details.";

            $smsSent = SmsService::send($handoverUser->phone, $message);
            
            if ($smsSent) {
                Log::info('Handover approval notification SMS sent', [
                    'leave_application_id' => $leave->id,
                    'applicant' => $applicantName,
                    'handover_person' => $handoverUserName,
                    'handover_phone' => $handoverUser->phone
                ]);
            } else {
                Log::warning('Handover approval notification SMS failed', [
                    'leave_application_id' => $leave->id,
                    'applicant' => $applicantName,
                    'handover_person' => $handoverUserName
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error sending handover approval notification: ' . $e->getMessage());
        }
    }

/**
 * Get handover suggestions - show only the highest priority level available
 */
/**
 * Get handover suggestions - show only the first available role in priority order
 */
private function getHandoverSuggestions()
{
    $currentUser = Auth::user();
    $suggestions = collect();

    try {
        // Role hierarchy - FLAT priority list (not grouped by levels)
        $roleHierarchy = [
            'Support-Staff' => ['Support-Staff', 'Salesperson', 'Showroom-Manager', 'Accountant', 'General-Manager', 'Managing-Director'],
            'Salesperson' => ['Salesperson', 'Support-Staff', 'Showroom-Manager', 'Accountant', 'General-Manager', 'Managing-Director'],
            'Showroom-Manager' => ['Showroom-Manager', 'General-Manager', 'Managing-Director', 'Accountant', 'Salesperson', 'Support-Staff'],
            'Accountant' => ['Accountant', 'General-Manager', 'Managing-Director', 'Showroom-Manager', 'Salesperson', 'Support-Staff'],
            'General-Manager' => ['General-Manager', 'Managing-Director', 'Showroom-Manager', 'Accountant', 'Salesperson', 'Support-Staff'],
            'Managing-Director' => ['Managing-Director', 'General-Manager', 'Showroom-Manager', 'Accountant', 'Salesperson', 'Support-Staff']
        ];

        $currentUserRole = $currentUser->role ?? 'Support-Staff';
        $priorityRoles = $roleHierarchy[$currentUserRole] ?? $roleHierarchy['Support-Staff'];

        // Go through roles in priority order and STOP at the first one that has users
        foreach ($priorityRoles as $index => $role) {
            $roleUsers = User::where('id', '!=', $currentUser->id)
                            ->whereNull('deleted_at')
                            ->where('role', $role)
                            ->select('id', 'first_name', 'last_name', 'email', 'role')
                            ->get();
            
            if ($roleUsers->isNotEmpty()) {
                // Found users in this role - use ONLY these users and stop
                $suggestions = $roleUsers->map(function($user) use ($index) {
                    $user->name = trim($user->first_name . ' ' . $user->last_name);
                    // Set suggestion level based on position in priority
                    if ($index === 0) {
                        $user->suggestion_level = 'primary';
                    } elseif ($index <= 2) {
                        $user->suggestion_level = 'secondary';
                    } else {
                        $user->suggestion_level = 'tertiary';
                    }
                    return $user;
                });
                
                break; // CRITICAL: Stop here - don't look at any other roles
            }
        }

        // Final fallback ONLY if absolutely no users found in any role
        if ($suggestions->isEmpty()) {
            $fallbackUsers = User::where('id', '!=', $currentUser->id)
                                 ->whereNull('deleted_at')
                                 ->select('id', 'first_name', 'last_name', 'email', 'role')
                                 ->take(15)
                                 ->get()
                                 ->map(function($user) {
                                     $user->name = trim($user->first_name . ' ' . $user->last_name);
                                     $user->suggestion_level = 'fallback';
                                     return $user;
                                 });
            
            $suggestions = $fallbackUsers;
        } else {
            // Limit to reasonable number
            $suggestions = $suggestions->take(15);
        }

    } catch (\Exception $e) {
        Log::error('Error getting handover suggestions: ' . $e->getMessage());
    }

    return $suggestions;
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'leave_type' => 'required|string',
        'leave_duration_type' => 'required|in:days,hours',
        'start_date' => 'required|date|after_or_equal:today',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'start_time' => 'required_if:leave_duration_type,hours|nullable|date_format:H:i',
        'end_time' => 'required_if:leave_duration_type,hours|nullable|date_format:H:i|after:start_time',
        'total_days' => 'nullable|integer|min:1',
        'total_hours' => 'nullable|numeric|min:0.5|max:8',
        'handover_person' => 'required|string|max:255',
        'reason' => 'required|string|max:1000',
    ]);

    try {
        DB::beginTransaction();

        $userId = Auth::id();
        $user = Auth::user();
        $isDaysType = $validated['leave_duration_type'] === 'days';
        
        // Calculate total days or hours based on duration type
        if ($isDaysType) {
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $totalDays = $this->calculateWorkingDays($startDate, $endDate);
            $totalHours = 0;
        } else {
            // For hours
            $totalDays = 0;
            $totalHours = floatval($validated['total_hours']);
        }

        // Find the user's leave balance for the selected leave type
        $leaveDay = LeaveDay::where('user_id', $userId)
            ->where('leave_type', $validated['leave_type'])
            ->where('year', date('Y'))
            ->where('status', 'active')
            ->first();

        if (!$leaveDay) {
            // Try to create default leave balances if they don't exist
            $this->createDefaultLeaveBalances($userId);
            
            $leaveDay = LeaveDay::where('user_id', $userId)
                ->where('leave_type', $validated['leave_type'])
                ->where('year', date('Y'))
                ->where('status', 'active')
                ->first();
                
            if (!$leaveDay) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected leave type is not available for your account. Please contact HR.'
                ], 400);
            }
        }

        // Check balance based on duration type
        if ($isDaysType) {
            if ($leaveDay->remaining_days < $totalDays) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient leave balance. You have {$leaveDay->remaining_days} days remaining."
                ], 400);
            }
        } else {
            if ($leaveDay->remaining_hours < $totalHours) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient leave balance. You have {$leaveDay->remaining_hours} hours remaining."
                ], 400);
            }
        }

        // Set dates for overlap check
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = $isDaysType ? Carbon::parse($validated['end_date']) : $startDate->copy();

        // Check for overlapping leave applications
        $overlapping = LeaveApplication::where('user_id', $userId)
            ->whereIn('status', ['Pending', 'Approved'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                          ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();

        if ($overlapping) {
            return response()->json([
                'success' => false,
                'message' => 'You have overlapping leave applications for the selected dates.'
            ], 400);
        }

        // Create leave application
        $leaveApplication = LeaveApplication::create([
            'user_id' => $userId,
            'leave_day_id' => $leaveDay->id,
            'leave_type' => $validated['leave_type'],
            'leave_duration_type' => $validated['leave_duration_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $isDaysType ? $validated['end_date'] : $validated['start_date'],
            'start_time' => $isDaysType ? null : $validated['start_time'],
            'end_time' => $isDaysType ? null : $validated['end_time'],
            'total_days' => $totalDays,
            'total_hours' => $totalHours,
            'handover_person' => $validated['handover_person'],
            'reason' => $validated['reason'],
            'status' => 'Pending',
            'applied_date' => now(),
        ]);

        DB::commit();

        // Send SMS notification to General Managers
        try {
            $this->sendLeaveApplicationNotificationToManagers($leaveApplication, $user);
        } catch (\Exception $smsException) {
            Log::error('SMS error during leave application: ' . $smsException->getMessage());
        }

        // Send SMS notification to handover person
        try {
            $this->sendHandoverNotificationToAssignee($leaveApplication, $user);
        } catch (\Exception $smsException) {
            Log::error('SMS error during handover notification: ' . $smsException->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Leave application submitted successfully!',
            'data' => $leaveApplication->load(['user', 'leaveDay'])
        ]);

    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Leave application failed: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to submit leave application. Please try again.'
        ], 500);
    }
}

    /**
     * Send SMS notification to General Managers when leave is applied
     */
    private function sendLeaveApplicationNotificationToManagers($leaveApplication, $applicantUser)
    {
        try {
            // Get all General Managers
            $generalManagers = User::where('role', 'General-Manager')
                ->whereNotNull('phone')
                ->get();

            $applicantName = trim($applicantUser->first_name . ' ' . $applicantUser->last_name);
            $startDate = Carbon::parse($leaveApplication->start_date)->format('M d, Y');
            $endDate = Carbon::parse($leaveApplication->end_date)->format('M d, Y');
            
            $message = "New leave application from {$applicantName} for {$leaveApplication->leave_type} from {$startDate} to {$endDate} ({$leaveApplication->total_days} days). Reason: {$leaveApplication->reason}. Please review and take action.";

            foreach ($generalManagers as $manager) {
                $smsSent = SmsService::send($manager->phone, $message);
                
                if ($smsSent) {
                    Log::info('Leave application notification SMS sent to General Manager', [
                        'leave_application_id' => $leaveApplication->id,
                        'applicant' => $applicantName,
                        'manager' => $manager->first_name . ' ' . $manager->last_name,
                        'manager_phone' => $manager->phone
                    ]);
                } else {
                    Log::warning('Leave application notification SMS failed to General Manager', [
                        'leave_application_id' => $leaveApplication->id,
                        'applicant' => $applicantName,
                        'manager' => $manager->first_name . ' ' . $manager->last_name,
                        'manager_phone' => $manager->phone
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error sending leave application notification to managers: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveApplication $leave)
    {
        $leave->load(['user', 'leaveDay']);
        
        return response()->json([
            'success' => true,
            'data' => $leave
        ]);
    }

    /**
     * Approve a leave application and deduct days
     */
    public function approve(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // Find the leave application by ID
            $leave = LeaveApplication::with('user')->findOrFail($id);
            
            Log::info("Attempting to approve leave application {$id}. Current status: {$leave->status}");

            // Check if user has permission to approve leaves
            if (!in_array(auth()->user()->role, ['Managing-Director','General-Manager', 'HR'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to approve leave applications.'
                ], 403);
            }

            // Additional check: Users cannot approve their own applications
            if ($leave->user_id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot approve your own leave application.'
                ], 403);
            }

            // Refresh model to get latest data
            $leave->refresh();

            if ($leave->status !== 'Pending') {
                return response()->json([
                    'success' => false,
                    'message' => "This application has already been processed. Current status: {$leave->status}"
                ], 400);
            }

            // Double-check if user still has enough leave days
            $leaveDay = $leave->leaveDay;
            if ($leaveDay && $leaveDay->remaining_days < $leave->total_days) {
                return response()->json([
                    'success' => false,
                    'message' => 'User no longer has sufficient leave balance. Current balance: ' . $leaveDay->remaining_days . ' days.'
                ], 400);
            }

            // Update application status
            $leave->update([
                'status' => 'Approved',
                'approved_by' => Auth::id(),
                'approved_date' => now(),
                'comments' => $request->input('comments'),
            ]);

            // Deduct leave days from the user's balance (if leaveDay exists)
            if ($leaveDay) {
                if ($leave->leave_duration_type === 'days') {
                    $leaveDay->update([
                        'used_days' => $leaveDay->used_days + $leave->total_days,
                        'remaining_days' => $leaveDay->remaining_days - $leave->total_days,
                    ]);
                } else {
                    $leaveDay->update([
                        'used_hours' => $leaveDay->used_hours + $leave->total_hours,
                        'remaining_hours' => $leaveDay->remaining_hours - $leave->total_hours,
                    ]);
                }
            }

            DB::commit();

            // Send SMS notification to applicant about approval
            try {
                $this->sendLeaveApprovalNotificationToApplicant($leave);
            } catch (\Exception $smsException) {
                Log::error('SMS error during leave approval: ' . $smsException->getMessage());
                // Don't fail the approval if SMS fails
            }

            // Send SMS notification to handover person about approval
            try {
                $this->sendHandoverApprovalNotification($leave);
            } catch (\Exception $smsException) {
                Log::error('SMS error during handover approval notification: ' . $smsException->getMessage());
                // Don't fail the approval if SMS fails
            }

            Log::info("Leave approved for user {$leave->user->first_name} {$leave->user->last_name}. Application ID: {$id}");

            return response()->json([
                'success' => true,
                'message' => "Leave application approved successfully!"
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Leave approval failed for ID {$id}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve leave application. Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send SMS notification to applicant when leave is approved
     */
    private function sendLeaveApprovalNotificationToApplicant($leave)
    {
        try {
            $applicantPhone = $leave->user->phone;
            
            if (!$applicantPhone) {
                Log::warning('No phone number found for leave applicant', [
                    'leave_application_id' => $leave->id,
                    'user_id' => $leave->user_id
                ]);
                return;
            }

            $applicantName = trim($leave->user->first_name . ' ' . $leave->user->last_name);
            $startDate = Carbon::parse($leave->start_date)->format('M d, Y');
            $endDate = Carbon::parse($leave->end_date)->format('M d, Y');
            $approver = Auth::user();
            $approverName = trim($approver->first_name . ' ' . $approver->last_name);

            $message = "Dear {$applicantName}, your {$leave->leave_type} application from {$startDate} to {$endDate} ({$leave->total_days} days) has been APPROVED. Enjoy your leave!";

            $smsSent = SmsService::send($applicantPhone, $message);
            
            if ($smsSent) {
                Log::info('Leave approval notification SMS sent to applicant', [
                    'leave_application_id' => $leave->id,
                    'applicant' => $applicantName,
                    'applicant_phone' => $applicantPhone,
                    'approver' => $approverName
                ]);
            } else {
                Log::warning('Leave approval notification SMS failed to applicant', [
                    'leave_application_id' => $leave->id,
                    'applicant' => $applicantName,
                    'applicant_phone' => $applicantPhone
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error sending leave approval notification to applicant: ' . $e->getMessage());
        }
    }

    /**
     * Reject a leave application
     */
    public function reject(Request $request, $id)
    {
        try {
            // Find the leave application by ID
            $leave = LeaveApplication::with('user')->findOrFail($id);
            
            Log::info("Attempting to reject leave application {$id}. Current status: {$leave->status}");

            // Check if user has permission to reject leaves
            if (!in_array(auth()->user()->role, ['Managing-Director','General-Manager', 'HR'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to reject leave applications.'
                ], 403);
            }

            // Additional check: Users cannot reject their own applications
            if ($leave->user_id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot reject your own leave application.'
                ], 403);
            }

            // Refresh model to get latest data
            $leave->refresh();

            if ($leave->status !== 'Pending') {
                Log::warning("Cannot reject leave application {$id}. Status is '{$leave->status}' instead of 'Pending'");
                
                return response()->json([
                    'success' => false,
                    'message' => "This application has already been processed. Current status: {$leave->status}"
                ], 400);
            }

            $leave->update([
                'status' => 'Rejected',
                'approved_by' => Auth::id(),
                'approved_date' => now(),
                'comments' => $request->input('comments', 'Application rejected'),
            ]);

            // Send SMS notification to applicant about rejection
            try {
                $this->sendLeaveRejectionNotificationToApplicant($leave, $request->input('comments'));
            } catch (\Exception $smsException) {
                Log::error('SMS error during leave rejection: ' . $smsException->getMessage());
                // Don't fail the rejection if SMS fails
            }

            Log::info("Leave application {$id} rejected successfully by user " . Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Leave application rejected successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error("Leave rejection failed for ID {$id}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject leave application. Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send SMS notification to applicant when leave is rejected
     */
    private function sendLeaveRejectionNotificationToApplicant($leave, $comments = null)
    {
        try {
            $applicantPhone = $leave->user->phone;
            
            if (!$applicantPhone) {
                Log::warning('No phone number found for leave applicant', [
                    'leave_application_id' => $leave->id,
                    'user_id' => $leave->user_id
                ]);
                return;
            }

            $applicantName = trim($leave->user->first_name . ' ' . $leave->user->last_name);

            $message = "Dear {$applicantName}, your {$leave->leave_type} application has been REJECTED.";
            
            if ($comments) {
                $message .= " Reason: {$comments}";
            }
            
            $message .= " Please contact HR for more information.";

            $smsSent = SmsService::send($applicantPhone, $message);
            
            if ($smsSent) {
                Log::info('Leave rejection notification SMS sent to applicant', [
                    'leave_application_id' => $leave->id,
                    'applicant' => $applicantName,
                    'applicant_phone' => $applicantPhone
                ]);
            } else {
                Log::warning('Leave rejection notification SMS failed to applicant', [
                    'leave_application_id' => $leave->id,
                    'applicant' => $applicantName,
                    'applicant_phone' => $applicantPhone
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error sending leave rejection notification to applicant: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a leave application and restore days if already approved
     */
    public function cancel(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // Find the leave application by ID
            $leave = LeaveApplication::findOrFail($id);

            // Only allow cancellation by the applicant
            if ($leave->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only cancel your own applications.'
                ], 403);
            }

            // Refresh model to get latest data
            $leave->refresh();

            if (!in_array($leave->status, ['Pending', 'Approved'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This application cannot be cancelled.'
                ], 400);
            }

            // If application was approved, restore the leave days
             if ($leave->status === 'Approved' && $leave->leaveDay) {
            $leaveDay = $leave->leaveDay;
            if ($leave->leave_duration_type === 'days') {
                $leaveDay->update([
                    'used_days' => $leaveDay->used_days - $leave->total_days,
                    'remaining_days' => $leaveDay->remaining_days + $leave->total_days,
                ]);
            } else {
                $leaveDay->update([
                    'used_hours' => $leaveDay->used_hours - $leave->total_hours,
                    'remaining_hours' => $leaveDay->remaining_hours + $leave->total_hours,
                ]);
            }
        }

            $leave->update([
                'status' => 'Cancelled',
                'cancelled_date' => now(),
            ]);

            DB::commit();

            $message = $leave->status === 'Approved' 
                ? "Leave application cancelled successfully! {$leave->total_days} days have been restored to your {$leave->leave_type} balance."
                : 'Leave application cancelled successfully!';

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Leave cancellation failed for ID {$id}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel leave application.'
            ], 500);
        }
    }

    /**
     * Get user's active leave balances
     */
    public function getUserLeaveBalance()
    {
        $leaveBalances = LeaveDay::where('user_id', Auth::id())
            ->where('year', date('Y'))
            ->where('status', 'active')
            ->get();

        // If no balances found, create default ones
        if ($leaveBalances->isEmpty()) {
            $this->createDefaultLeaveBalances(Auth::id());
            $leaveBalances = LeaveDay::where('user_id', Auth::id())
                ->where('year', date('Y'))
                ->where('status', 'active')
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $leaveBalances
        ]);
    }

    
    /**
     * Calculate working days between two dates (excluding only Sunday)
     */
    private function calculateWorkingDays(Carbon $startDate, Carbon $endDate): int
    {
        $workingDays = 0;
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // Skip only Sunday (0) - Saturday (6) is now a working day
            if ($currentDate->dayOfWeek !== 0) {
                $workingDays++;
            }
            $currentDate->addDay();
        }

        return $workingDays;
    }

    /**
     * Get leave statistics for dashboard
     */
    public function getLeaveStatistics()
    {
        $currentYear = date('Y');
        
        $stats = [
            'total_applications' => LeaveApplication::whereYear('created_at', $currentYear)->count(),
            'pending_applications' => LeaveApplication::where('status', 'Pending')->whereYear('created_at', $currentYear)->count(),
            'approved_applications' => LeaveApplication::where('status', 'Approved')->whereYear('created_at', $currentYear)->count(),
            'rejected_applications' => LeaveApplication::where('status', 'Rejected')->whereYear('created_at', $currentYear)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get handover suggestions via API
     */
    public function getHandoverSuggestionsApi()
    {
        $suggestions = $this->getHandoverSuggestions();
        
        return response()->json([
            'success' => true,
            'data' => $suggestions
        ]);
    }
}