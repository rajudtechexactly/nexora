<?php

declare(strict_types=1);

use App\Modules\Notification\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

/*
| Notification module routes (prefix: /api/v1).
| New notifications are also pushed live over the private "user.{id}" channel.
*/

Route::middleware('auth.jwt')->group(function () {
    Route::get('notifications', [NotificationController::class, 'index'])->name('api.notifications.index');
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('api.notifications.unread');
    Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('api.notifications.read-all');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'read'])
        ->whereNumber('notification')->name('api.notifications.read');
});
