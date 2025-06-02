<?php

namespace App\Http\Middleware;

use App\Services\PhoneValidator;
use App\Services\WhatsGwService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Closure;

class CheckInstanceConnection
{

    protected $serviceWGW;

    public function __construct(WhatsGwService $serviceWGW){
        $this->serviceWGW = $serviceWGW;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->role === 'nu') {
            if(!$user->enabled){
                Log::info('Usuário ainda bloqueado aguarde...');
                return redirect('/')->with('error', 'Usuário bloqueado, tente mais tarde!');
            }
            if (!$user->instance_id) {
                //$this->serviceWGW->newStance();
                return redirect()->route('page.connection')->with('error', 'Você não tem nenhum aparelho ativo.');
            }
        }

        return $next($request);
    }
}
