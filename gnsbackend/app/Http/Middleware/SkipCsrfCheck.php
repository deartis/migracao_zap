<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SkipCsrfCheck
{

    protected $except = [
        'whatsapp-send',
        // adicione outras rotas que devem ignorar CSRF
    ];

    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        foreach ($this->except as $route) {
            if ($route === $path || (str_ends_with($route, '*') && str_starts_with($path, substr($route, 0, -1)))) {
                return $next($request);
            }
        }

        // Para todas as outras rotas, verifique o token CSRF normalmente
        return app(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)->handle($request, $next);
        // return $next($request);
    }
}
