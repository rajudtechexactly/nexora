<?php

use Illuminate\Support\Facades\Route;

// Landing → redirect into the mobile app shell.
Route::get('/', fn () => redirect('/app'));

/*
| The NativePHP mobile app loads /app (configured as NATIVEPHP_START_URL).
| It is a single Blade-hosted client-side app (Alpine + fetch) that consumes
| the JWT REST API. Any /app/* path resolves to the same shell so the
| in-app client-side router can handle deep links.
*/
Route::view('/app', 'mobile.app')->name('mobile.app');
Route::view('/app/{any}', 'mobile.app')->where('any', '.*');
