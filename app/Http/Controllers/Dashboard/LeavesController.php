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

class LeavesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get leave applications with proper relationships
        $leaveApplications = LeaveApplication::with(['user', 'leaveDay'])
            ->orderBy('created_at', 'desc')
            ->get();
        
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
        $user = User::find($userId);
        
        // Base leave types that apply to everyone
        $defaultLeaveTypes = [
            'Annual Leave' => 25,
            'Sick Leave' => 12,
            'Emergency Leave' => 3,
        ];

        // Add Maternity/Paternity Leave with dynamic days
        // You can customize this logic based on your business rules
        $maternityPaternityDays = 90; // Default
        
        // You can check user's gender/role or other criteria here if needed
        // For example, if you have a gender field in users table:
        // if (isset($user->gender)) {
        //     $maternityPaternityDays = ($user->gender === 'female') ? 90 : 14;
        // }
        
        $defaultLeaveTypes['Maternity/Paternity Leave'] = $maternityPaternityDays;

        foreach ($defaultLeaveTypes as $leaveType => $totalDays) {
            LeaveDay::updateOrCreate(
                [
                    'user_id' => $userId,
                    'leave_type' => $leaveType,
                    'year' => date('Y'),
                ],
                [
                    'total_days' => $totalDays,
                    'used_days' => 0,
                    'remaining_days' => $totalDays,
                    'status' => 'active',
                ]
            );
        }
    }

    /**
     * Get handover suggestions based on user role/department
     */
    private function getHandoverSuggestions()
    {
        $currentUser = Auth::user();
        $suggestions = collect();

        try {
            // Get users excluding current user
            $query = User::where('id', '!=', $currentUser->id)
                         ->whereNull('deleted_at'); // Exclude soft deleted users if applicable

            // Role hierarchy for handover suggestions
            $roleHierarchy = [
                'Support-Staff' => ['Support-Staff', 'Salesperson'],
                'Salesperson' => ['Salesperson', 'Support-Staff'],
                'Showroom-Manager' => ['Showroom-Manager', 'Managing-Director', 'Accountant'],
                'Accountant' => ['Accountant', 'Managing-Director', 'Showroom-Manager'],
                'Managing-Director' => ['Managing-Director', 'Showroom-Manager', 'Accountant'],
            ];

            $currentUserRole = $currentUser->role ?? 'Support-Staff';

            // Get suggested roles for current user
            $suggestedRoles = $roleHierarchy[$currentUserRole] ?? ['Support-Staff', 'Salesperson'];

            // Try to find users with suggested roles in order of preference
            foreach ($suggestedRoles as $role) {
                $roleUsers = $query->where('role', $role)
                                  ->select('id', 'first_name', 'last_name', 'email', 'role')
                                  ->take(10)
                                  ->get();
                
                if ($roleUsers->isNotEmpty()) {
                    // Add full name for display
                    $roleUsers = $roleUsers->map(function($user) {
                        $user->name = trim($user->first_name . ' ' . $user->last_name);
                        return $user;
                    });
                    
                    $suggestions = $suggestions->merge($roleUsers);
                }
            }

            // If no specific role matches found, get any available users
            if ($suggestions->isEmpty()) {
                $fallbackUsers = $query->select('id', 'first_name', 'last_name', 'email', 'role')
                                      ->take(10)
                                      ->get()
                                      ->map(function($user) {
                                          $user->name = trim($user->first_name . ' ' . $user->last_name);
                                          return $user;
                                      });
                
                $suggestions = $fallbackUsers;
            }

            // Remove duplicates and limit results
            $suggestions = $suggestions->unique('id')->take(15);

        } catch (\Exception $e) {
            Log::error('Error getting handover suggestions: ' . $e->getMessage());
        }

        return $suggestions;
    }

    /**
     * Check if user can approve leaves (without role package)
     */
    private function canApproveLeaves($user = null)
    {
        $user = $user ?? Auth::user();
        
        // Check if user has role field
        if (isset($user->role)) {
            $allowedRoles = ['manager', 'managing-director', 'supervisor', 'admin', 'hr'];
            return in_array(strtolower($user->role), $allowedRoles);
        }
        
        // Check if user has is_admin field
        if (isset($user->is_admin)) {
            return $user->is_admin == 1;
        }
        
        // Check if user has permissions field (JSON)
        if (isset($user->permissions)) {
            $permissions = is_string($user->permissions) ? json_decode($user->permissions, true) : $user->permissions;
            return in_array('approve-leaves', $permissions ?? []);
        }
        
        // Default: only specific user IDs can approve (change as needed)
        $adminUserIds = [1]; // Add admin user IDs here
        return in_array($user->id, $adminUserIds);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'handover_person' => 'required|string|max:255',
            'reason' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $userId = Auth::id();
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            
            // Calculate total days (excluding weekends)
            $totalDays = $this->calculateWorkingDays($startDate, $endDate);

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

            // Check if user has enough leave days
            if ($leaveDay->remaining_days < $totalDays) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient leave balance. You have {$leaveDay->remaining_days} days remaining for {$validated['leave_type']}."
                ], 400);
            }

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
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'total_days' => $totalDays,
                'handover_person' => $validated['handover_person'],
                'reason' => $validated['reason'],
                'status' => 'Pending',
                'applied_date' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Leave application submitted successfully!',
                'data' => $leaveApplication->load(['user', 'leaveDay'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Leave application failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit leave application. Please try again.'
            ], 500);
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
    public function approve(Request $request, LeaveApplication $leave)
    {
        
        try {
            DB::beginTransaction();

            if ($leave->status !== 'Pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This application has already been processed.'
                ], 400);
            }

            // Double-check if user still has enough leave days
            $leaveDay = $leave->leaveDay;
            if ($leaveDay->remaining_days < $leave->total_days) {
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
                'comments' => $request->comments ?? null,
            ]);

            // Deduct leave days from the user's balance
            $leaveDay->update([
                'used_days' => $leaveDay->used_days + $leave->total_days,
                'remaining_days' => $leaveDay->remaining_days - $leave->total_days,
            ]);

            DB::commit();

            Log::info("Leave approved for user {$leave->user->name}. Deducted {$leave->total_days} days from {$leave->leave_type}. Remaining: {$leaveDay->remaining_days} days.");

            return response()->json([
                'success' => true,
                'message' => "Leave application approved successfully! {$leave->total_days} days deducted from {$leave->leave_type} balance."
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Leave approval failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve leave application.'
            ], 500);
        }
    }

    /**
     * Reject a leave application
     */
    public function reject(Request $request, LeaveApplication $leave)
    {
       

        try {
            if ($leave->status !== 'Pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This application has already been processed.'
                ], 400);
            }

            $leave->update([
                'status' => 'Rejected',
                'approved_by' => Auth::id(),
                'approved_date' => now(),
                'comments' => $request->comments ?? 'Application rejected',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Leave application rejected successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Leave rejection failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject leave application.'
            ], 500);
        }
    }

    /**
     * Cancel a leave application and restore days if already approved
     */
    public function cancel(LeaveApplication $leave)
    {
        try {
            DB::beginTransaction();

            // Only allow cancellation by the applicant
            if ($leave->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only cancel your own applications.'
                ], 403);
            }

            if (!in_array($leave->status, ['Pending', 'Approved'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This application cannot be cancelled.'
                ], 400);
            }

            // If application was approved, restore the leave days
            if ($leave->status === 'Approved') {
                $leaveDay = $leave->leaveDay;
                $leaveDay->update([
                    'used_days' => $leaveDay->used_days - $leave->total_days,
                    'remaining_days' => $leaveDay->remaining_days + $leave->total_days,
                ]);

                Log::info("Leave cancelled for user {$leave->user->name}. Restored {$leave->total_days} days to {$leave->leave_type}. New balance: {$leaveDay->remaining_days} days.");
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
            Log::error('Leave cancellation failed: ' . $e->getMessage());
            
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
     * Calculate working days between two dates (excluding weekends)
     */
    private function calculateWorkingDays(Carbon $startDate, Carbon $endDate): int
    {
        $workingDays = 0;
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            if (!in_array($currentDate->dayOfWeek, [0, 6])) {
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

        // 

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