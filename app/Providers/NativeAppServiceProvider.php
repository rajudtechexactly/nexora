<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * On-device (NativePHP Mobile) configuration overrides.
 *
 * The mobile bundle is a thin client: it only renders the Blade/Alpine shell
 * at /app and talks to the REMOTE JWT API over HTTP. NativePHP strips the
 * database credentials at bundle time (config/nativephp.php → cleanup_env_keys:
 * DB_USERNAME, DB_PASSWORD), so the on-device Laravel cannot — and must not —
 * reach the Neon Postgres server.
 *
 * Our server defaults use the database for sessions/cache/queue. On-device
 * those drivers would try to open a Postgres connection during the very first
 * request (session middleware), the bootstrap throws, and NativePHP's
 * persistent runtime reports: "Runtime not booted. Call Runtime::boot() first."
 *
 * When NATIVEPHP_RUNNING is set (injected by the native PHP bridge, also into
 * $_SERVER), we force fully self-contained, in-memory/offline drivers so the
 * app boots without any database or external dependency. Auth state lives in
 * the JWT held in the webview's localStorage, so a server-side session is not
 * needed at all. On the server this provider is a no-op.
 */
class NativeAppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! $this->runningOnDevice()) {
            return;
        }

        config([
            // No server-side session needed — auth is a JWT in localStorage.
            'session.driver' => 'array',

            // In-memory cache; nothing on device relies on shared/persistent cache.
            'cache.default' => 'array',

            // Run any dispatched work inline; there is no queue worker on device.
            'queue.default' => 'sync',

            // Broadcasting is not used on-device (chat is a later iteration), and
            // REVERB_APP_SECRET is stripped at bundle time (cleanup_env_keys: *_SECRET),
            // so resolving the reverb/Pusher driver at boot throws and the persistent
            // runtime reports "Runtime not booted". Use the secret-less null driver.
            'broadcasting.default' => 'null',

            // Guard: if any stray query runs, hit a local sqlite file instead of
            // attempting to dial the (unreachable, credential-stripped) Postgres.
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => storage_path('app/native.sqlite'),
        ]);
    }

    private function runningOnDevice(): bool
    {
        $flag = $_SERVER['NATIVEPHP_RUNNING']
            ?? $_ENV['NATIVEPHP_RUNNING']
            ?? getenv('NATIVEPHP_RUNNING');

        return filter_var($flag, FILTER_VALIDATE_BOOLEAN);
    }
}
