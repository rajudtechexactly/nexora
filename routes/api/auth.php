<?php

declare(strict_types=1);

use App\Modules\Auth\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
| Auth module routes (prefix already applied: /api/v1)
*/

Route::prefix('auth')->name('api.auth.')->group(function () {
    // --- Public endpoints ---
    Route::post('register', [AuthController::class, 'register'])
        ->middleware('throttle:10,1')->name('register');

    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')->name('login');

    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
        ->middleware('throttle:5,1')->name('forgot');

    Route::post('reset-password', [AuthController::class, 'resetPassword'])
        ->middleware('throttle:5,1')->name('reset');

    // Signed verification link (clicked from email; no bearer token required).
    Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware('signed')->name('email.verify');

    // --- Authenticated endpoints ---
    Route::middleware('auth.jwt')->group(function () {
        Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::post('email/resend', [AuthController::class, 'resendVerification'])
            ->middleware('throttle:5,1')->name('email.resend');
        Route::post('change-password', [AuthController::class, 'changePassword'])->name('change-password');
    });
});
