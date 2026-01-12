<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show login form
     */
    public function show()
    {
        return view('auth.login');
    }

    /**
     * Handle login request (WITH IMAGE CAPTCHA)
     */
    public function login(Request $request)
    {
          $captchaLength = config('captcha.default.length', 6);

        // ✅ Validate input + CAPTCHA
        $request->validate([
              'identifier' => ['required_without:email','string', 'max:255'],
              'email'      => ['required_without:identifier','email', 'max:255'],
              'password'   => ['required'],
            // 'email'   => ['required', 'string', 'max:255'],
            // 'password'=> ['required'],
            'captcha' => ['required', 'captcha', "digits:{$captchaLength}"],
        ], [
            'captcha.captcha' => 'Invalid captcha. Please try again.',
            'captcha.digits' => 'Captcha must be numbers only.',
        ]);

        $identifier = trim((string) ($request->input('identifier') ?? $request->input('email')));
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $user = User::query()->where($field, $identifier)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'identifier' => 'Invalid Credentials.',
            ]);
        }

        if ($field === 'phone' && $user->role === 'administrator') {
            throw ValidationException::withMessages([
                'email' => 'Administrators must sign in with their email address.',
            ]);
        }

        if ($field === 'email' && $user->role !== 'administrator') {
            throw ValidationException::withMessages([
                'email' => 'Please sign in with your phone number.',
            ]);
        }

        // ✅ Attempt authentication
        if (! Auth::attempt(
            [$field => $identifier, 'password' => $request->input('password')],
            $request->boolean('remember')
        )) {
            throw ValidationException::withMessages([
                'email' => 'Invalid email or password.',
            ]);
        }

        // ✅ Regenerate session (security)
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
