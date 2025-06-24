<?php

namespace App\Http\Middleware;

use App\Models\Instances;
use App\Services\WhatsGwService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Closure;

class CheckInstanceConnection
{
    protected $serviceWGW;

    public function __construct(WhatsGwService $serviceWGW)
    {
        $this->serviceWGW = $serviceWGW;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Early return se não for usuário 'nu'
        if (!$user || $user->role !== 'nu') {
            return $next($request);
        }

        // Verificar se usuário está habilitado
        if (!$user->enabled) {
            Log::info('Usuário bloqueado tentando acessar', ['user_id' => $user->id]);
            return redirect('/')->with('error', 'Usuário bloqueado, tente mais tarde!');
        }

        // Verificar se tem instance_id
        if (!$user->instance_id) {
            Log::info('Usuário sem instância', ['user_id' => $user->id]);
            return redirect()->route('page.connection')
                ->with('error', 'Você não tem nenhum aparelho ativo.');
        }

        // Buscar instância
        $instance = $this->getUserInstance($user);
        if (!$instance) {
            Log::warning('Instância não encontrada', ['user_id' => $user->id, 'instance_id' => $user->instance_id]);
            return redirect()->route('page.connection')
                ->with('error', 'Instância não encontrada.');
        }

        // Verificar conexão apenas se necessário
        if ($instance->status === 'connected') {
            $validationResult = $this->validateInstanceConnection($user, $instance);
            if ($validationResult !== true) {
                return $validationResult; // Retorna redirect response
            }
        }

        return $next($request);
    }

    /**
     * Busca a instância do usuário com cache
     */
    private function getUserInstance($user): ?Instances
    {
        $cacheKey = "user_instance_{$user->id}";

        return Cache::remember($cacheKey, 300, function() use ($user) { // 5 minutos de cache
            return Instances::where('user_id', $user->id)->first();
        });
    }

    /**
     * Valida a conexão da instância
     */
    private function validateInstanceConnection($user, $instance)
    {
        try {
            // Cache do status da instância por 1 minuto
            $cacheKey = "instance_status_{$user->instance_id}";
            $status = Cache::remember($cacheKey, 60, function() use ($user) {
                return $this->serviceWGW->getStatus($user->instance_id);
            });

            // DEBUG: Vamos ver o que está vindo da API
            Log::info('DEBUG - Status completo da API', [
                'user_id' => $user->id,
                'instance_id' => $user->instance_id,
                'status_completo' => $status,
                'phone_number_exists' => isset($status['phone_number']),
                'phone_number_value' => $status['phone_number'] ?? 'NAO_EXISTE',
                'phone_number_empty' => empty($status['phone_number'] ?? null),
                'user_number' => $user->number
            ]);

            // Se não tem phone_number ou está vazio, permite prosseguir
            if (empty($status['phone_number'])) {
                Log::info('Instância conectada mas sem número definido', [
                    'user_id' => $user->id,
                    'instance_id' => $user->instance_id
                ]);
                return true;
            }

            // DEBUG: Vamos ver a comparação dos números
            Log::info('DEBUG - Comparando números', [
                'user_id' => $user->id,
                'numero_cadastrado' => $user->number,
                'numero_conectado' => $status['phone_number'],
                'sao_iguais' => ($status['phone_number'] === $user->number),
                'tipo_cadastrado' => gettype($user->number),
                'tipo_conectado' => gettype($status['phone_number']),
                'length_cadastrado' => strlen($user->number),
                'length_conectado' => strlen($status['phone_number'])
            ]);

            // AQUI É A VERIFICAÇÃO PRINCIPAL: Se tem número E é diferente do cadastrado
            if ($status['phone_number'] !== $user->number) {
                Log::warning('Número conectado é diferente do cadastrado', [
                    'user_id' => $user->id,
                    'numero_cadastrado' => $user->number,
                    'numero_conectado' => $status['phone_number']
                ]);

                return redirect()->route('page.connection')
                    ->with('error', 'Você está tentando se conectar com um número diferente do cadastrado. Entre em contato com o suporte ou use o aparelho com número do seu cadastro.');
            }

            // Se chegou até aqui, o número conectado é igual ao cadastrado
            Log::info('Número conectado confere com o cadastro', [
                'user_id' => $user->id,
                'phone_number' => $status['phone_number']
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Erro ao validar conexão da instância', [
                'user_id' => $user->id,
                'instance_id' => $user->instance_id,
                'error' => $e->getMessage()
            ]);

            // Em caso de erro na API, permite prosseguir para não bloquear o usuário
            return true;
        }
    }
}
