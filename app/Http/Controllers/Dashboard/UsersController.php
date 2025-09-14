<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LeaveDay; // Assuming you have this model
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::withTrashed()->get();
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'phone'        => 'required|string|regex:/^254[17]\d{8}$/',
            'national_id'  => 'required|string|unique:users,national_id',
            'role'         => 'required|string|in:Managing-Director,Accountant,Showroom-Manager,Salesperson,Support-Staff,HR,General-Manager',
            'gender'       => 'required|string|in:Male,Female',
            'password'       => 'required|string', // Only for leave allocation, not saved
        ]);

        try {
            DB::beginTransaction();

            // Generate a random password (user will reset via email)
            $randomPassword = $request->password;

            // Remove gender from validated data as we don't save it to database
            $gender = $validated['gender'];
            unset($validated['gender']);

            // Add hashed password to the validated data
            $validated['password'] = Hash::make($randomPassword);

            // Save the user
            $user = User::create($validated);

            // Create leave days for non-client users
            if (!in_array($validated['role'], ['Client', 'client'])) {
                $this->createLeaveDaysForUser($user, $validated['role'], $gender);
            }

            // Send password reset link
            $this->sendPasswordResetLink($user->email);

            DB::commit();

            return response()->json([
                'message' => 'User created successfully! Password reset link sent to their email.',
                'user_id' => $user->id,
                'email_sent' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('User creation failed: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to create user. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create leave days for non-client users based on their role and gender
     */
    private function createLeaveDaysForUser(User $user, string $role, string $gender = 'Male')
    {
        // Define leave allocation based on role
        $leaveAllocation = $this->getLeaveAllocationByRole($role, $gender);
        
        foreach ($leaveAllocation as $leaveType => $days) {
            LeaveDay::create([
                'user_id' => $user->id,
                'leave_type' => $leaveType,
                'total_days' => $days,
                'used_days' => 0,
                'remaining_days' => $days,
                'year' => date('Y'), // Current year
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Get leave allocation based on user role and gender
     */
    private function getLeaveAllocationByRole(string $role, string $gender = 'Male'): array
    {
        // Determine maternity/paternity leave based on gender
        $maternityPaternityLeave = ($gender === 'Female') ? 90 : 14;

        $allocations = [
            'Managing-Director' => [
                'Annual Leave' => 30,
                'Sick Leave' => 14,
                'Maternity/Paternity Leave' => $maternityPaternityLeave,
                'Emergency Leave' => 5,
            ],
            'Accountant' => [
                'Annual Leave' => 25,
                'Sick Leave' => 12,
                'Maternity/Paternity Leave' => $maternityPaternityLeave,
                'Emergency Leave' => 3,
            ],
            'Showroom-Manager' => [
                'Annual Leave' => 25,
                'Sick Leave' => 12,
                'Maternity/Paternity Leave' => $maternityPaternityLeave,
                'Emergency Leave' => 3,
            ],
            'Salesperson' => [
                'Annual Leave' => 21,
                'Sick Leave' => 10,
                'Maternity/Paternity Leave' => $maternityPaternityLeave,
                'Emergency Leave' => 2,
            ],
            'Support-Staff' => [
                'Annual Leave' => 21,
                'Sick Leave' => 10,
                'Maternity/Paternity Leave' => $maternityPaternityLeave,
                'Emergency Leave' => 2,
            ],
        ];

        return $allocations[$role] ?? [
            'Annual Leave' => 21,
            'Sick Leave' => 10,
            'Maternity/Paternity Leave' => $maternityPaternityLeave,
            'Emergency Leave' => 2,
        ];
    }

    /**
     * Send password reset link to user
     */
    private function sendPasswordResetLink(string $email)
    {
        try {
            $status = Password::sendResetLink(['email' => $email]);

            if ($status === Password::RESET_LINK_SENT) {
                Log::info("Password reset link sent to: {$email}");
                return true;
            } else {
                Log::warning("Failed to send password reset link to: {$email}. Status: {$status}");
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Exception sending password reset link to {$email}: " . $e->getMessage());
            return false;
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Soft delete the user
            $user->delete();

            // Optionally, you might want to handle leave days for soft-deleted users
            // For example, mark them as inactive but keep the records
            LeaveDay::where('user_id', $id)->update(['status' => 'inactive']);

            return redirect()->route('users')->with('success', 'User soft-deleted successfully!');
        } catch (\Exception $e) {
            Log::error('User deletion failed: ' . $e->getMessage());
            return redirect()->route('users')->with('error', 'Failed to delete user.');
        }
    }

    public function restore($id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);
            $user->restore();

            // Reactivate leave days if they exist
            LeaveDay::where('user_id', $id)->update(['status' => 'active']);

            // If user doesn't have leave days and is not a client, create them
            if (!in_array($user->role, ['Client', 'client']) && !LeaveDay::where('user_id', $id)->exists()) {
                // Since we don't have gender info for existing users, default to Male (14 days paternity)
                $this->createLeaveDaysForUser($user, $user->role, 'Male');
            }

            return redirect()->route('users')->with('success', 'User restored successfully!');
        } catch (\Exception $e) {
            Log::error('User restoration failed: ' . $e->getMessage());
            return redirect()->route('users')->with('error', 'Failed to restore user.');
        }
    }

    public function update(Request $request)
    {
        try {
            // Validate the form input
            $validated = $request->validate([
                'id' => 'required|exists:users,id',
                'editfirst_name' => 'required|string|max:255',
                'editlast_name' => 'required|string|max:255',
                'editemail' => 'required|email|unique:users,email,' . $request->id,
                'editphone' => 'required|string|regex:/^254[17]\d{8}$/',
                'editnational_id' => 'required|string|unique:users,national_id,' . $request->id,
                'editrole' => 'required|string|in:Managing-Director,Accountant,Showroom-Manager,Salesperson,Support-Staff,HR,General-Manager',
            ]);

            $id = $request->id;

            // Find the existing user record by ID
            $user = User::findOrFail($id);
            $oldRole = $user->role;
            $newRole = $request->editrole;

            DB::beginTransaction();

            // Update the user record with validated data
            $user->update([
                'first_name' => $request->editfirst_name,
                'last_name' => $request->editlast_name,
                'email' => $request->editemail,
                'phone' => $request->editphone,
                'national_id' => $request->editnational_id,
                'role' => $request->editrole,
            ]);

            // Handle leave days based on role change
            $this->handleLeaveOnRoleChange($user, $oldRole, $newRole);

            DB::commit();

            return response()->json(['success' => 'User updated successfully']);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('User update failed: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to update user. Please try again.'
            ], 500);
        }
    }

    /**
     * Handle leave days when user role changes
     */
    private function handleLeaveOnRoleChange(User $user, string $oldRole, string $newRole)
    {
        $isOldRoleClient = in_array($oldRole, ['Client', 'client']);
        $isNewRoleClient = in_array($newRole, ['Client', 'client']);

        if ($isOldRoleClient && !$isNewRoleClient) {
            // Changed from client to employee - create leave days
            // Since we don't have gender info for existing users, default to Male (14 days paternity)
            $this->createLeaveDaysForUser($user, $newRole, 'Male');
        } elseif (!$isOldRoleClient && $isNewRoleClient) {
            // Changed from employee to client - remove/deactivate leave days
            LeaveDay::where('user_id', $user->id)->delete(); // or update status to 'inactive'
        } elseif (!$isOldRoleClient && !$isNewRoleClient && $oldRole !== $newRole) {
            // Role changed between employee types - update leave allocation
            // Since we don't have gender info for existing users, default to Male (14 days paternity)
            LeaveDay::where('user_id', $user->id)->delete();
            $this->createLeaveDaysForUser($user, $newRole, 'Male');
        }
    }

    public function show($id)
    {
        try {
            // Include soft-deleted users
            $user = User::withTrashed()->findOrFail($id);

            // Explicitly cast to an array so hidden/visible are honored
            return response()->json($user->toArray());
        } catch (\Exception $e) {
            Log::error('User show failed: ' . $e->getMessage());
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    /**
     * Resend password reset link (optional method)
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'password' => 'required|min:6',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password reset successfully!']);
    }
    /**
 * Show password reset form
 */
public function showPasswordResetForm()
{
    return view('auth.forgot-password');
}

/**
 * Send new password via SMS
 */
public function sendPasswordViaSms(Request $request)
{
    $request->validate([
        'phone' => 'required|string|regex:/^254[17]\d{8}$/',
    ]);

    try {
        // Find user by phone number
        $user = User::where('phone', $request->phone)->first();
        
        if (!$user) {
            return response()->json([
                'error' => 'No account found with this phone number.'
            ], 404);
        }

        // Generate random password
        $newPassword = $this->generateRandomPassword();
        
        // Update user password
        $user->password = Hash::make($newPassword);
        $user->save();

        // Send SMS with new password
        $this->sendPasswordResetSms($user, $newPassword);

        return response()->json([
            'message' => 'New password sent to your phone number successfully!'
        ]);

    } catch (\Exception $e) {
        Log::error('SMS password reset failed: ' . $e->getMessage());
        
        return response()->json([
            'error' => 'Failed to send password. Please try again.'
        ], 500);
    }
}

/**
 * Generate random password
 */
private function generateRandomPassword($length = 8): string
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($chars), 0, $length);
}

/**
 * Send password reset SMS
 */
private function sendPasswordResetSms(User $user, string $newPassword)
{
    try {
        $userName = trim($user->first_name . ' ' . $user->last_name);
        
        $message = "Dear {$userName}, your new password is: {$newPassword}. Please login and change it immediately for security.";

        $smsSent = SmsService::send($user->phone, $message);
        
        if ($smsSent) {
            Log::info('Password reset SMS sent successfully', [
                'user_id' => $user->id,
                'user_name' => $userName,
                'phone' => $user->phone
            ]);
        } else {
            Log::warning('Password reset SMS failed', [
                'user_id' => $user->id,
                'user_name' => $userName,
                'phone' => $user->phone
            ]);
            throw new \Exception('SMS sending failed');
        }

    } catch (\Exception $e) {
        Log::error('Error sending password reset SMS: ' . $e->getMessage());
        throw $e;
    }
}
}
