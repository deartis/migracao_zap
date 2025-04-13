<?php

// app/Http/Controllers/Api/WhatsAppController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WhatsAppController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Inicia o WhatsApp e retorna QR code ou status
     */
    public function start(Request $request): JsonResponse
    {
        try {
            $response = $this->whatsappService->startWhatsApp($this->getUserToken($request));
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Verifica o status da conexão
     */
    public function checkStatus(Request $request): JsonResponse
    {
        try {
            $response = $this->whatsappService->checkConnection($this->getUserToken($request));
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Envia uma mensagem
     */
    public function sendMessage(SendMessageRequest $request): JsonResponse
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
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Encerra uma sessão
     */
    public function deleteSession(Request $request): JsonResponse
    {
        try {
            $response = $this->whatsappService->deleteSession($this->getUserToken($request));
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtém o token do usuário autenticado
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
}
