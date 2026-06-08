<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Note: account verification is handled by an emailed OTP issued from
     * AuthService::register() (see App\Modules\Auth), not by the framework's
     * signed-link verification listener.
     */
    public function boot(): void
    {
        //
    }
}
