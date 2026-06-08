<?php

declare(strict_types=1);

use App\Modules\Auth\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
| Auth module routes (prefix already applied: /api/v1)
|
| Sign-up flow: register -> (OTP emailed) -> verify-otp -> token issued.
| Reset flow:   forgot-password -> (OTP emailed) -> reset-password.
*/

Route::prefix('auth')->name('api.auth.')->group(function () {
    // --- Public endpoints ---
    Route::post('register', [AuthController::class, 'register'])
        ->middleware('throttle:10,1')->name('register');

    // Verify the registration OTP; on success the first token is issued.
    Route::post('verify-otp', [AuthController::class, 'verifyOtp'])
        ->middleware('throttle:10,1')->name('verify-otp');

    // Re-send the registration OTP (e.g. it expired or never arrived).
    Route::post('resend-otp', [AuthController::class, 'resendOtp'])
        ->middleware('throttle:5,1')->name('resend-otp');

    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')->name('login');

    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
        ->middleware('throttle:5,1')->name('forgot');

    Route::post('reset-password', [AuthController::class, 'resetPassword'])
        ->middleware('throttle:5,1')->name('reset');

    // --- Authenticated endpoints ---
    Route::middleware('auth.jwt')->group(function () {
        Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::post('change-password', [AuthController::class, 'changePassword'])->name('change-password');
    });
});
