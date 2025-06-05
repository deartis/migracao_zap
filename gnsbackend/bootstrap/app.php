<?php
ini_set('memory_limit', '2048M');

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckInstanceConnection;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registre o middleware aqui
        $middleware->alias([
            'role' => CheckRole::class,
            'check.instance' => CheckInstanceConnection::class,
        ]);

        $middleware->validateCsrfTokens(except: [
        'stripe/*',
        'webhookqrcode',
        'webhookqrcode*', // Exemplo de rota que deve ignorar CSRF
        'whatsapp-send',
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withProviders([
        App\Providers\WhatsAppServiceProvider::class,
    ])
    ->create();
