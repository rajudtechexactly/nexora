<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

/**
 * Registers the broadcasting authorization endpoint behind the JWT guard
 * (the default endpoint assumes session auth, which the mobile API does not
 * use) and loads the channel authorization callbacks.
 */
class BroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Clients authorize private/presence channels here with their bearer token.
        Broadcast::routes([
            'prefix'     => 'api/v1',
            'middleware' => ['api', 'auth.jwt'],
        ]);

        require base_path('routes/channels.php');
    }
}
