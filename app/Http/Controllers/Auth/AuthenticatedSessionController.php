<?php

namespace App\Http\Controllers\Auth;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Services\SmsService;
use Illuminate\Support\Facades\Session;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */

     public function create()
        {
            // Check if the user is authenticated
            if (auth()->check()) {
                // Redirect to the correct dashboard based on their role
                return redirect(RouteServiceProvider::redirectBasedOnRole());
            }

            // If not authenticated, show the login page
            return view('auth.login');
        }
    /**
     * Handle an incoming authentication request.
     */
   
public function store(LoginRequest $request): RedirectResponse
{
    // Validate credentials WITHOUT logging in
    if (!Auth::validate($request->only('email', 'password'))) {
        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    // Get the user
    $user = User::where('email', $request->email)->first();

    // Generate 6-digit code
    $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Save code with 5 min expiry
    $user->update([
        'mfa_code' => $code,
        'mfa_code_expires_at' => now()->addMinutes(5)
    ]);

    // Send SMS
    $phone = SmsService::formatPhone($user->phone);
    SmsService::send($phone, "Your House of Cars verification code is: {$code}. Valid for 5 minutes.");

    // Store user ID in session
    Session::put('mfa_user_id', $user->id);

    return redirect()->route('mfa.verify');
}

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login'); 
    }
}
