<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    protected $whatsappServiceUrl;

    public function __construct()
    {
        $this->whatsappServiceUrl = env('WHATSAPP_SERVICE_URL', 'http://localhost:3000');
    }

    public function sendMessage(Request $request)
    {
        try {
            $validated = $request->validate([
                'number' => 'required|string',
                'message' => 'required|string',
                'media' => 'nullable|string',
            ]);

            // Passa o token de autenticação para o serviço do WhatsApp
            $response = Http::withHeaders([
                'Authorization' => $request->header('Authorization')
            ])->post($this->whatsappServiceUrl . '/send-message', [
                'number' => $validated['number'],
                'message' => $validated['message'],
                'media' => $validated['media'] ?? null,
            ]);

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('Erro no WhatsAppController: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Webhook para receber mensagens (se necessário)
    public function handleWebhook(Request $request)
    {
        // Lógica para processar mensagens recebidas
        return response()->json(['success' => true]);
    }
}
