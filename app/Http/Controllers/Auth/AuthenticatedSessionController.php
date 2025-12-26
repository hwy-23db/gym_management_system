<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // ✅ If you are using captcha on the login page, validate it here
        // Make sure your input name is: name="captcha"
        $validator = Validator::make($request->all(), [
            'captcha' => ['required', 'captcha'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('email', 'remember'));
        }

        // Breeze default: this will attempt login using email/password
        $request->authenticate();

        // Important: regenerate session so login persists
        $request->session()->regenerate();

        // ✅ Redirect to intended page (dashboard by default)
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        auth()->guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
