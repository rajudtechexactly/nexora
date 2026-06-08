<?php

declare(strict_types=1);

use App\Modules\Chat\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

/*
| Chat module routes (prefix: /api/v1) — conversations + messages.
| Realtime delivery is over the private "conversation.{id}" Reverb channel.
*/

Route::middleware('auth.jwt')->group(function () {
    Route::get('conversations', [ChatController::class, 'index'])->name('api.chat.index');
    Route::post('conversations', [ChatController::class, 'store'])->name('api.chat.store');

    Route::get('conversations/{conversation}/messages', [ChatController::class, 'messages'])
        ->whereNumber('conversation')->name('api.chat.messages');
    Route::post('conversations/{conversation}/messages', [ChatController::class, 'send'])
        ->whereNumber('conversation')->name('api.chat.send');
    Route::post('conversations/{conversation}/read', [ChatController::class, 'read'])
        ->whereNumber('conversation')->name('api.chat.read');
});
