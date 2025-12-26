<?php

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
