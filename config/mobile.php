<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Mobile App (NativePHP) Configuration
|--------------------------------------------------------------------------
|
| Settings consumed by the on-device mobile UI. The UI is a thin client that
| talks to the REST API over HTTP using a JWT — it never touches the database
| directly (NativePHP strips DB secrets at bundle time).
|
*/

return [
    // Base URL of the REST API the mobile client calls.
    // Emulator note: the Android emulator reaches the host machine at 10.0.2.2.
    'api_url' => env('MOBILE_API_URL', rtrim((string) env('APP_URL'), '/').'/api/v1'),

    'app_name' => env('APP_NAME', 'Nexora'),
];
