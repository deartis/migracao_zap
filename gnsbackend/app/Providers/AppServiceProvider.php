<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('titulo', function ($expressao) {
            return "<h1 class='fw-bold mb-4'><?= $expressao ?></h1>";
        });

        Blade::if('bloqueado', function () {
            return Auth::check() && Auth::user()->enabled === false;
        });

        // Forçar HTTPS quando acessado via ngrok
        if (request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }
    }
}
