<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DashboardReportController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\TrainerBookingController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\TrainerController;
use App\Http\Controllers\Api\UserMessageController;
use App\Http\Controllers\Api\UserController;
use Mews\Captcha\Facades\Captcha;

// Login endpoint - rate limiting is handled in LoginRequest class
// 5 attempts per email+IP combination with 60 second lockout
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'publicRegister']);
Route::post('/register/verify-email', [AuthController::class, 'verifyEmail']);

// Public blog endpoints
Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/blogs/{slug}', [BlogController::class, 'show']);

// System version endpoint (public - no authentication required)
Route::get('/version', function () {
    return response()->json([
        'name' => config('app.name'),
        'version' => config('app.version'),
        'environment' => config('app.env'),
        'laravel_version' => app()->version(),
    ]);
});

// CAPTCHA endpoints (public)
Route::get('/captcha', function () {
    return response()->json(['captcha' => captcha_img()]);
});

Route::get('/captcha/refresh', function () {
    return response()->json(['captcha' => captcha_img()]);
});

Route::get('/captcha/api/{config?}', function (?string $config = null) {
    return response()->json(['captcha' => Captcha::src($config)]);
});


// Note: CSRF token endpoint is not needed for token-based API authentication
// For stateful SPA authentication, use Sanctum's built-in endpoint: GET /sanctum/csrf-cookie

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    // Get current authenticated user
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'notifications_enabled' => $user->notifications_enabled,
            ]
        ]);
    });

    // Logout endpoint
    Route::post('/logout', [AuthController::class, 'logout']);

    // Update profile endpoint - Users can update their own profile
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::patch('/user/profile', [AuthController::class, 'updateProfile']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

    Route::get('/my/messages', [UserMessageController::class, 'messages']);
    Route::post('/my/messages', [UserMessageController::class, 'sendMessage']);


    // Root user can create other users (admission, trainer, user)
    Route::post('/admin/register', [AuthController::class, 'register'])
        ->middleware('administrator');

   Route::middleware('role:trainer')->prefix('trainer')->group(function () {
        Route::get('/home', [TrainerController::class, 'home']);
        Route::get('/check-in', [TrainerController::class, 'checkIn']);
        Route::post('/check-in/scan', [TrainerController::class, 'scanFromQr']);
        Route::get('/subscriptions', [TrainerController::class, 'subscriptions']);
        Route::get('/messages', [TrainerController::class, 'messages']);
        Route::post('/messages', [TrainerController::class, 'sendMessage']);
    });

    Route::middleware('role:user')->prefix('user')->group(function () {
        Route::get('/home', [UserController::class, 'home']);
        Route::get('/check-in', [UserController::class, 'checkIn']);
        Route::post('/check-in/scan', [UserController::class, 'scanFromQr']);
        Route::get('/subscriptions', [UserController::class, 'subscriptions']);
        Route::get('/messages', [UserController::class, 'messages']);
        Route::post('/messages', [UserController::class, 'sendMessage']);
    });

    // User management endpoints - ONLY accessible by administrator
    Route::middleware('administrator')->group(function () {
        // List users (active by default, add ?deleted=true for deleted users)
        Route::get('/users', [AuthController::class, 'index']);

        // Send password reset link to a user
        Route::post('/users/forgot-password', [AuthController::class, 'sendPasswordResetLink']);

        // Delete a user (soft delete)
        Route::delete('/users/{id}', [AuthController::class, 'destroy']);

        // Update a user
        Route::put('/users/{id}', [AuthController::class, 'updateUser']);
        Route::patch('/users/{id}', [AuthController::class, 'updateUser']);

        // Restore a soft-deleted user
        Route::post('/users/{id}/restore', [AuthController::class, 'restore']);

        // Permanently delete a user (force delete)
        Route::delete('/users/{id}/force', [AuthController::class, 'forceDestroy']);

        // Blog management endpoints
        Route::post('/blogs', [BlogController::class, 'store']);
        Route::put('/blogs/{blog}', [BlogController::class, 'update']);
        Route::patch('/blogs/{blog}', [BlogController::class, 'update']);
        Route::delete('/blogs/{blog}', [BlogController::class, 'destroy']);

        // Trainer pricing endpoints
        Route::get('/pricing', [PricingController::class, 'index']);
        Route::put('/pricing/monthly', [PricingController::class, 'updateMonthly']);
        Route::put('/pricing/quarterly', [PricingController::class, 'updateQuarterly']);
        Route::put('/pricing/annual', [PricingController::class, 'updateAnnual']);
        Route::put('/pricing/trainers/{user}', [PricingController::class, 'updateTrainer']);

        // Trainer booking endpoints
        Route::get('/trainer-bookings', [TrainerBookingController::class, 'index']);
        Route::get('/trainer-bookings/options', [TrainerBookingController::class, 'options']);
        Route::post('/trainer-bookings', [TrainerBookingController::class, 'store']);
        Route::patch('/trainer-bookings/{booking}/mark-paid', [TrainerBookingController::class, 'markPaid']);

        // Subscription management endpoints
        Route::prefix('subscriptions')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index']);
        Route::post('/', [SubscriptionController::class, 'store']);
        Route::get('/options', [SubscriptionController::class, 'options']);
        Route::post('/{subscription}/hold', [SubscriptionController::class, 'hold']);
        Route::post('/{subscription}/resume', [SubscriptionController::class, 'resume']);
        });

        Route::prefix('attendance')->group(function () {
            Route::get('/users', [AttendanceController::class, 'users']);
            Route::get('/qr', [AttendanceController::class, 'qr']);
            Route::get('/records', [AttendanceController::class, 'records']);
            Route::get('/checked-in', [AttendanceController::class, 'checkedIn']);
            Route::post('/scan', [AttendanceController::class, 'scan']);
            Route::post('/scan/qr', [AttendanceController::class, 'scanFromQr']);
            Route::post('/qr/refresh', [AttendanceController::class, 'refreshQr']);
        });

        Route::get('/dashboard/attendance-report', [DashboardController::class, 'attendanceReport']);
        Route::get('/dashboard/growth-summary', [DashboardController::class, 'growthSummary']);
        Route::get('/dashboard/export/{format}', [DashboardReportController::class, 'export'])
            ->whereIn('format', ['excel', 'json']);

        Route::get('/messages', [MessageController::class, 'conversations']);
        Route::get('/messages/{user}', [MessageController::class, 'thread']);
        Route::post('/messages/{user}', [MessageController::class, 'store']);
    });
});
