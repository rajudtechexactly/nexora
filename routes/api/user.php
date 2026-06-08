<?php

declare(strict_types=1);

use App\Modules\User\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
| User / Profile module routes (prefix: /api/v1)
*/

Route::middleware('auth.jwt')->group(function () {
    Route::get('users/search', [UserController::class, 'search'])->name('api.users.search');

    Route::prefix('profile')->name('api.profile.')->group(function () {
        Route::patch('/', [UserController::class, 'update'])->name('update');
        Route::post('avatar', [UserController::class, 'uploadAvatar'])->name('avatar');
        Route::post('cover', [UserController::class, 'uploadCover'])->name('cover');
        Route::delete('/', [UserController::class, 'deactivate'])->name('deactivate');
    });

    // Public profile view by username (kept last so it doesn't shadow /search).
    Route::get('users/{username}', [UserController::class, 'show'])->name('api.users.show');
});
