<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (prefix: /api/v1)
|--------------------------------------------------------------------------
|
| Each module contributes its own route group. Public routes (auth) sit
| outside the auth.jwt middleware; everything else requires a valid token.
|
*/

Route::get('/ping', fn () => response()->json([
    'success' => true,
    'message' => 'Nexora API is alive.',
    'version' => 'v1',
]))->name('api.ping');

// Module route files are required here as each module is built.
require __DIR__.'/api/auth.php';
require __DIR__.'/api/user.php';
require __DIR__.'/api/friendship.php';
require __DIR__.'/api/post.php';
require __DIR__.'/api/reel.php';
require __DIR__.'/api/chat.php';
require __DIR__.'/api/call.php';
require __DIR__.'/api/notification.php';
