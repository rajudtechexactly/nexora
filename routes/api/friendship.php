<?php

declare(strict_types=1);

use App\Modules\Friendship\Http\Controllers\FriendshipController;
use Illuminate\Support\Facades\Route;

/*
| Friendship module routes (prefix: /api/v1)
*/

Route::middleware('auth.jwt')->group(function () {
    // Listings
    Route::get('friends', [FriendshipController::class, 'friends'])->name('api.friends.index');
    Route::get('friends/suggestions', [FriendshipController::class, 'suggestions'])->name('api.friends.suggestions');
    Route::get('friends/requests/incoming', [FriendshipController::class, 'incoming'])->name('api.friends.incoming');
    Route::get('friends/requests/outgoing', [FriendshipController::class, 'outgoing'])->name('api.friends.outgoing');
    Route::get('friends/blocked', [FriendshipController::class, 'blocked'])->name('api.friends.blocked');

    // Request lifecycle (keyed by the other user's id)
    Route::post('friends/requests/{user}', [FriendshipController::class, 'sendRequest'])
        ->whereNumber('user')->name('api.friends.send');
    Route::post('friends/requests/{user}/accept', [FriendshipController::class, 'accept'])
        ->whereNumber('user')->name('api.friends.accept');
    Route::post('friends/requests/{user}/decline', [FriendshipController::class, 'decline'])
        ->whereNumber('user')->name('api.friends.decline');
    Route::delete('friends/requests/{user}', [FriendshipController::class, 'cancel'])
        ->whereNumber('user')->name('api.friends.cancel');

    // Relationship management
    Route::delete('friends/{user}', [FriendshipController::class, 'unfriend'])
        ->whereNumber('user')->name('api.friends.unfriend');
    Route::post('friends/{user}/block', [FriendshipController::class, 'block'])
        ->whereNumber('user')->name('api.friends.block');
    Route::delete('friends/{user}/block', [FriendshipController::class, 'unblock'])
        ->whereNumber('user')->name('api.friends.unblock');
});
