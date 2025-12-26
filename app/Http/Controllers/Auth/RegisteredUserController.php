<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\EmailVerificationCodeMail;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:administrator,trainer,user'],
        ]);

        // Generate 6-digit OTP
        $verificationCode = rand(100000, 999999);

        // Create user (NOT logged in yet)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'email_verification_code' => $verificationCode,
            'email_verification_expires_at' => now()->addMinutes(10),
        ]);

        // Send verification email
        Mail::to($user->email)->send(
            new EmailVerificationCodeMail($verificationCode)
        );

        // Redirect to OTP verification page
        return redirect()->route('verify.form')
            ->with('email', $user->email)
            ->with('success', 'Verification code sent to your email.');
    }
}
