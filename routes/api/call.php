<?php

declare(strict_types=1);

use App\Modules\Call\Http\Controllers\CallController;
use Illuminate\Support\Facades\Route;

/*
| Call module routes (prefix: /api/v1) — WebRTC signaling + call history.
| Signaling (offer/answer/ICE/hang-up) is relayed over the private
| "user.{id}" channels; media is peer-to-peer.
*/

Route::middleware('auth.jwt')->group(function () {
    Route::get('calls', [CallController::class, 'history'])->name('api.calls.history');
    Route::get('calls/ice-servers', [CallController::class, 'iceServers'])->name('api.calls.ice');

    Route::post('calls', [CallController::class, 'initiate'])->name('api.calls.initiate');
    Route::post('calls/{call}/answer', [CallController::class, 'answer'])->whereNumber('call')->name('api.calls.answer');
    Route::post('calls/{call}/candidate', [CallController::class, 'candidate'])->whereNumber('call')->name('api.calls.candidate');
    Route::post('calls/{call}/decline', [CallController::class, 'decline'])->whereNumber('call')->name('api.calls.decline');
    Route::post('calls/{call}/hangup', [CallController::class, 'hangup'])->whereNumber('call')->name('api.calls.hangup');
});
