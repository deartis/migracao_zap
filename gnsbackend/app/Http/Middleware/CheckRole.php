<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        \Log::info($role);
        \Log::info(auth()->user()->role);
        // Se não estiver autenticado, redireciona para login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Se o usuário não tiver a role exigida, retorna 403
        if (auth()->user()->role !== $role) {
            abort(403, 'Acesso restrito para ' . $role . '.');
        }
        return $next($request);
    }
}
