<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PricingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Admin\TrainerBookingController;
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
Route::view('/users', 'pages.users')->middleware(['auth', 'administrator'])->name('users.index');
Route::view('/subscriptions', 'pages.subscriptions')
    ->middleware(['auth', 'administrator'])
    ->name('subscriptions.index');
Route::get('/pricing', [PricingController::class, 'index'])
    ->middleware(['auth', 'administrator'])
    ->name('pricing.index');
Route::put('/pricing/monthly', [PricingController::class, 'updateMonthly'])
    ->middleware(['auth', 'administrator'])
    ->name('pricing.update-monthly');
Route::put('/pricing/quarterly', [PricingController::class, 'updateQuarterly'])
    ->middleware(['auth', 'administrator'])
    ->name('pricing.update-quarterly');
Route::put('/pricing/annual', [PricingController::class, 'updateAnnual'])
    ->middleware(['auth', 'administrator'])
    ->name('pricing.update-annual');
Route::put('/pricing/trainers/{user}', [PricingController::class, 'updateTrainer'])
    ->middleware(['auth', 'administrator'])
    ->name('pricing.update-trainer');
Route::get('/trainer-bookings', [TrainerBookingController::class, 'index'])
    ->middleware(['auth', 'administrator'])
    ->name('trainer-bookings.index');
Route::post('/trainer-bookings', [TrainerBookingController::class, 'store'])
    ->middleware(['auth', 'administrator'])
    ->name('trainer-bookings.store');
Route::patch('/trainer-bookings/{booking}/mark-paid', [TrainerBookingController::class, 'markPaid'])
    ->middleware(['auth', 'administrator'])
    ->name('trainer-bookings.mark-paid');
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

Route::middleware(['auth', 'administrator'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/deleted', [UserController::class, 'deleted'])->name('users.deleted');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');

    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::post('/', [SubscriptionController::class, 'store'])->name('store');
        Route::get('/options', [SubscriptionController::class, 'options'])->name('options');
        Route::post('/{subscription}/hold', [SubscriptionController::class, 'hold'])->name('hold');
        Route::post('/{subscription}/resume', [SubscriptionController::class, 'resume'])->name('resume');
    });
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
