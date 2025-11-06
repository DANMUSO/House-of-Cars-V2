<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class MfaController extends Controller
{
    public function show()
    {
        if (!Session::has('mfa_user_id')) {
            return redirect()->route('login');
        }
        
        return view('auth.mfa-verify');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $userId = Session::get('mfa_user_id');
        $user = User::find($userId);

        if (!$user || 
            $user->mfa_code !== $request->code || 
            $user->mfa_code_expires_at < now()) {
            
            return back()->withErrors(['code' => 'Invalid or expired code']);
        }

        // Clear MFA code
        $user->update([
            'mfa_code' => null,
            'mfa_code_expires_at' => null
        ]);

        // Log user in
        Auth::login($user);
        $request->session()->regenerate();
        Session::forget('mfa_user_id');

        return redirect()->intended(RouteServiceProvider::redirectBasedOnRole());
    }

    public function resend()
    {
        $userId = Session::get('mfa_user_id');
        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login');
        }

        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $user->update([
            'mfa_code' => $code,
            'mfa_code_expires_at' => now()->addMinutes(5)
        ]);

        $phone = \App\Services\SmsService::formatPhone($user->phone);
        \App\Services\SmsService::send($phone, "Your House of Cars verification code is: {$code}. Valid for 5 minutes.");

        return back()->with('success', 'New code sent!');
    }
}
