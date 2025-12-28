<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (Blade Admin + SPA host)
|--------------------------------------------------------------------------
|
| Web (Blade) Auth uses sessions/cookies:
|   - GET  /login
|   - POST /login
|   - GET  /register
|   - POST /register
|   - POST /logout
| and redirects to /dashboard (RouteServiceProvider::HOME)
|
| API Auth uses tokens and stays in routes/api.php:
|   - POST /api/login
|   - POST /api/register
|   - POST /api/logout
|
| SPA is served under /app/* so it doesn't conflict with /login, /register, /dashboard
|
*/

// Home page (optional)
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Blade dashboard (admin dashboard)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

 Route::view('/attendance', 'pages.attendance')->name('attendance.index');
    Route::view('/reports', 'pages.reports')->name('reports.index');
    Route::view('/users', 'pages.users')->name('users.index');
    Route::view('/subscriptions', 'pages.subscriptions')->name('subscriptions.index');
    Route::view('/pricing', 'pages.pricing')->name('pricing.index');
    Route::view('/trainer-bookings', 'pages.trainer-bookings')->name('trainer-bookings.index');
    Route::view('/messages', 'pages.messages')->name('messages.index');
    Route::view('/blogs', 'pages.blogs')->name('blogs.index');

/*
|--------------------------------------------------------------------------
| Blade Auth Routes
|--------------------------------------------------------------------------
| This loads Laravel’s built-in auth routes (Breeze/Jetstream/etc.)
| Make sure routes/auth.php exists in your project.
*/
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Optional CAPTCHA routes (ONLY if you use captcha in Blade forms)
|--------------------------------------------------------------------------
*/
Route::get('/captcha', function () {
    return captcha_img();
})->name('captcha');

Route::get('/captcha-refresh', function () {
    return response()->json(['captcha' => captcha_img()]);
})->name('captcha.refresh');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


/*
|--------------------------------------------------------------------------
| SPA Routes
|--------------------------------------------------------------------------
| Your SPA frontend should live at /app/*
| Example:
|   /app/login
|   /app/dashboard
|   /app/users
|
| Create: resources/views/app.blade.php
| That blade should load your compiled JS/CSS for the SPA.
*/
Route::view('/app/{any?}', 'app')
    ->where('any', '.*')
    ->name('spa');
