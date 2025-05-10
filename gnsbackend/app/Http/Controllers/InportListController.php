<?php

namespace App\Http\Controllers;

use App\Imports\ContatosImport;
use App\Jobs\MensagensEmMassaJob;
use App\Models\Historic;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;
use App\Services\ArrayDataDetector;
use App\Http\Requests\EnvioMensagemRequest;
use App\Services\ContatoService;
use App\Services\UserLimiteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class InportListController extends Controller
{
    protected ContatoService $contatoService;
    protected UserLimiteService $limiteService;

    /**
     * Construtor com injeção de dependências
     */
    public function __construct(ContatoService $contatoService, UserLimiteService $limiteService)
    {
        $this->contatoService = $contatoService;
        $this->limiteService = $limiteService;
    }
    public function index()
    {
        $contatos = Historic::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('pages.from-sheet', compact('contatos'));
    }

    /**
     * Envia mensagem em massa para uma lista de contatos
     *
     * @param EnvioMensagemRequest $request Requisição validada
     * @return JsonResponse
     */
    public function enviaMensagemEmMassaLista(EnvioMensagemRequest $request): JsonResponse
    {
        Log::info("Iniciando envio de mensagem em massa");

        // Verifica limite do usuário
        $statusUsuario = $this->limiteService->verificaStatusUsuario();

        if (!$statusUsuario['hasLimit'] || !$statusUsuario['enabled']) {
            Log::warning("Usuário sem saldo ou bloqueado", ['usuario_id' => auth()->id()]);
            return response()->json(['message' => __('mensagens.sem_saldo')], 403);
        }

        $usuario = $statusUsuario['userm'];
        if ($usuario->sendedMsg >= $usuario->msgLimit) {
            Log::warning("Usuário atingiu 100% do limite", ['usuario_id' => auth()->id()]);
            return response()->json(['message' => __('mensagens.limite_atingido')], 403);
        }

        // Recupera e salva template
        $template = $request->template;
        if (empty($template)) {
            Log::warning("Template de mensagem não fornecido", ['usuario_id' => auth()->id()]);
            return response()->json(['message' => __('mensagens.template_vazio')], 400);
        }

        // Salva última mensagem do usuário
        auth()->user()->update(['lastMessage' => $template]);

        // Processa lista de contatos
        try {
            $contatos = $request->contacts;
            $erros = $this->contatoService->verificaContatos($contatos);
            $contatosProcessados = $this->contatoService->processarContatos($contatos);

            // Despacha job para envio em segundo plano
            MensagensEmMassaJob::dispatch(
                $contatosProcessados,
                $this->getUserToken(),
                auth()->id(),
                $statusUsuario,
                $erros
            );

            Log::info("Job de envio de mensagens em massa despachado com sucesso", [
                'total_contatos' => count($contatosProcessados),
                'total_erros' => count($erros),
                'usuario_id' => auth()->id()
            ]);

            return response()->json(['message' => __('mensagens.enviando')]);
        } catch (\Exception $e) {
            Log::error("Erro ao processar contatos para envio em massa", [
                'erro' => $e->getMessage(),
                'usuario_id' => auth()->id()
            ]);

            return response()->json(['message' => __('mensagens.erro_processamento')], 500);
        }
    }

    /**
     * Obtém o token do usuário atual
     *
     * @return string
     */
    protected function getUserToken(): string
    {
        // Aqui você deve chamar a função token_user() que foi definida no seu sistema
        return token_user();
    }
}
