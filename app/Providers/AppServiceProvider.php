<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider; // <-- Tambahkan Import Ini

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
     */
    public function boot(): void
    {
        // --- PAKSA HTTPS DI PRODUCTION ---
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
