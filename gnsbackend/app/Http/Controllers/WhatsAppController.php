<?php
namespace App\Http\Controllers;

use App\Http\Requests\SendMessageRequest;
use App\Imports\ContatosImport;
use App\Jobs\ProcessarEnvioWhatsapp;
use App\Services\WhatsAppService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;

class WhatsAppController extends Controller
{
    protected $whatsappService;

    //==========================
    //= Construtor da classe   =
    //==========================
    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * -----------------------------------------------
     * ===============================================
     *  Inicia o WhatsApp e retorna QR code ou status
     * ===============================================
     * -----------------------------------------------
     */
    public function start(Request $request): JsonResponse
    {
        try {
            $response = $this->whatsappService->startWhatsApp($this->getUserToken($request));
            return response()->json($response->json(), $response->status());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * -----------------------------------------------
     * ===============================================
     * Verifica o status da conexão
     * ===============================================
     * -----------------------------------------------
     */
    public function checkStatus(Request $request): JsonResponse
    {
        try {
            $response = $this->whatsappService->checkConnection($this->getUserToken($request));
            return response()->json($response->json(), $response->status());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * -----------------------------------------------
     * ===============================================
     * Envia uma mensagem
     * ===============================================
     * -----------------------------------------------
     */
    public function sendMessage(SendMessageRequest $request)
    {
        try {
            $validated = $request->validated();

            $response = $this->whatsappService->sendMessage(
                $validated['number'],
                $validated['message'],
                $validated['media'] ?? null,
                $this->getUserToken($request)
            );

            return response()->json($response->json(), $response->status());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * -----------------------------------------------
     * ===============================================
     * Encerra uma sessão
     * ===============================================
     * -----------------------------------------------
     */
    public function deleteSession(Request $request): JsonResponse
    {
        try {
            $response = $this->whatsappService->deleteSession($this->getUserToken($request));
            return response()->json($response->json(), $response->status());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * -----------------------------------------------
     * ===============================================
     * Obtém o token do usuário autenticado
     * ===============================================
     * -----------------------------------------------
     */
    protected function getUserToken(Request $request): ?string
    {
        // Adapte conforme seu sistema de autenticação
        // Exemplo usando Sanctum/autenticação padrão do Laravel
        if ($request->user()) {
            return $request->user()->api_token ?? null;
        }

        return null;
    }

    /**
     * -----------------------------------------------
     * ===============================================
     * Importa os contatos
     * ===============================================
     * -----------------------------------------------
     */
    public function importarContatos(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:xls,xlsx,csv',
            'mensagem' => 'required|string'
        ]);

        $contatos = Excel::toArray(new ContatosImport, $request->file('arquivo'))[0];
        $mensagem = $request->input('mensagem');

        foreach ($contatos as $contato){
            ProcessarEnvioWhatsapp::dispatch($contato, $mensagem);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Processamento de ' . count($contatos) . ' Contatos iniciado com sucesso.'
        ]);
    }

}
