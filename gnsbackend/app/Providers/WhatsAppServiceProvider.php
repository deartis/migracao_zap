<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class WhatsAppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(WhatsAppService::class, function ($app){
            return new WhatsAppService(
                config('services.whatsapp.url'),
                config('services.whatsapp.token')
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
