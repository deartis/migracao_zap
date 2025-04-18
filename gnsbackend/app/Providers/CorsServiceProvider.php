<?php

namespace App\Providers;

use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Support\ServiceProvider;

class CorsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->singleton(HandleCors::class);

    }
}
