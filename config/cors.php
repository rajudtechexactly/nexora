<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS)
|--------------------------------------------------------------------------
|
| The NativePHP mobile webview runs on a different origin than the remote
| API server, so the API must permit cross-origin requests. The token lives
| in a bearer header (not cookies), so credentials are not required.
|
*/

return [
    'paths' => ['api/*', 'broadcasting/auth'],

    'allowed_methods' => ['*'],

    // Mobile webviews present non-standard / null origins; allow all and rely
    // on JWT for authorization. Tighten to specific web origins in production.
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => false,
];
