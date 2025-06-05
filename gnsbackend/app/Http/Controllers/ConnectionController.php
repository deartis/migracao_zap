<?php

namespace App\Http\Controllers;

use App\Models\Instances;
use App\Services\WhatsGwService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class ConnectionController extends Controller
{
    private const WHATSGW_BASE_URL = 'https://app.whatsgw.com.br/api/WhatsGw/';

    /**
     * Exibe a página de conexão
     */
    public function index(WhatsGwService  $whatsGwService)
    {
        $instance = Instances::where('user_id', auth()->id())->first();
        $user = auth()->user();
        Log::info($instance);

        //$whatsGwService->newStance();
        return view('pages.connect');
    }

    /**
     * Verifica o status da conexão WhatsApp
     */
    public function status(): JsonResponse
    {
        try {
            $user = auth()->user();

            // Verifica se pelo menos tem instance_id
            if (!$user->instance_id) {
                return response()->json([
                    'status' => 'NOT_INITIALIZED',
                    'error' => 'Instância não criada'
                ], 400);
            }

            // Se não tem número, significa que é primeira conexão
            if (!$user->number) {
                return response()->json([
                    'status' => 'AWAITING_FIRST_CONNECTION',
                    'message' => 'Aguardando primeira conexão via QR Code'
                ]);
            }

            // Tenta verificar status com timeout menor para falhar rápido
            $response = Http::timeout(5)->asForm()->post(self::WHATSGW_BASE_URL . 'PhoneState', [
                'apikey' => config('whatsgw.apiKey'),
                'phone_number' => $user->number,
                'w_instancia_id' => $user->instance_id,
            ]);

            if (!$response->successful()) {
                Log::info('Status check failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                // Se falhou, assume que precisa reconectar
                return response()->json([
                    'status' => 'DISCONNECTED',
                    'message' => 'Necessário reconectar'
                ]);
            }

            $responseData = $response->json();

            return response()->json([
                'status' => ($responseData['conectado'] ?? false) ? 'CONNECTED' : 'DISCONNECTED',
                'phone_number' => $user->number,
                'instance_id' => $user->instance_id
            ]);

        } catch (Exception $e) {
            Log::error('Exceção ao verificar status WhatsApp', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'status' => 'DISCONNECTED',
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    public function generateInitialQrCode(): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user->instance_id) {
                return response()->json([
                    'error' => 'Instância não encontrada'
                ], 400);
            }

            // Gera QR Code para primeira conexão
            $response = Http::timeout(10)->asForm()->post(self::WHATSGW_BASE_URL . 'QRCode', [
                'apikey' => config('whatsgw.apiKey'),
                'w_instancia_id' => $user->instance_id,
            ]);

            if (!$response->successful()) {
                Log::error('Erro ao gerar QR Code inicial', [
                    'status_code' => $response->status(),
                    'response' => $response->body()
                ]);

                return response()->json([
                    'error' => 'Erro ao gerar QR Code'
                ], 500);
            }

            $responseData = $response->json();

            return response()->json([
                'qrcode_base64' => $responseData['qrcode_base64'] ?? null,
                'message' => 'QR Code gerado para primeira conexão'
            ]);

        } catch (Exception $e) {
            Log::error('Exceção ao gerar QR Code inicial', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Retorna o QR Code em base64
     */
    public function qrcode(): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user->instance_id) {
                return response()->json([
                    'error' => 'Instance ID não encontrado'
                ], 400);
            }

            $path = "qrcodes/qrcode_{$user->instance_id}.png";

            if (!Storage::disk('public')->exists($path)) {
                return response()->json([
                    'error' => 'QR Code não disponível',
                    'message' => 'QR Code expirado ou ainda não foi gerado'
                ], 404);
            }

            // Verifica se o arquivo não é muito antigo (ex: mais de 5 minutos)
            $fileTime = Storage::disk('public')->lastModified($path);
            $currentTime = time();
            $maxAge = 5 * 60; // 5 minutos

            if (($currentTime - $fileTime) > $maxAge) {
                // Remove arquivo antigo
                Storage::disk('public')->delete($path);

                return response()->json([
                    'error' => 'QR Code expirado',
                    'message' => 'QR Code muito antigo, gere um novo'
                ], 410); // 410 Gone
            }

            $base64 = base64_encode(Storage::disk('public')->get($path));

            return response()->json([
                'qrcode_base64' => 'data:image/png;base64,' . $base64,
                'generated_at' => date('Y-m-d H:i:s', $fileTime)
            ]);

        } catch (Exception $e) {
            Log::error('Erro ao buscar QR Code', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'error' => 'Erro interno ao buscar QR Code'
            ], 500);
        }
    }

    /**
     * Reinicia a instância do WhatsApp
     */
    public function restartInstance(): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user->instance_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Instance ID não encontrado'
                ], 400);
            }

            // Remove QR Code antigo se existir
            $oldQrPath = "qrcodes/qrcode_{$user->instance_id}.png";
            if (Storage::disk('public')->exists($oldQrPath)) {
                Storage::disk('public')->delete($oldQrPath);
            }

            $response = Http::timeout(15)->asForm()->post(self::WHATSGW_BASE_URL . 'RestartInstance', [
                'apikey' => config('whatsgw.apiKey'),
                'w_instancia_id' => $user->instance_id,
                'type' => 0,
            ]);

            if (!$response->successful()) {
                Log::error('Erro ao reiniciar instância WhatsApp', [
                    'status_code' => $response->status(),
                    'response' => $response->body(),
                    'user_id' => $user->id,
                    'instance_id' => $user->instance_id
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Erro na API externa ao reiniciar instância'
                ], 500);
            }

            Log::info('Instância WhatsApp reiniciada com sucesso', [
                'user_id' => $user->id,
                'instance_id' => $user->instance_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Instância reiniciada com sucesso. Aguarde o novo QR Code...'
            ]);

        } catch (Exception $e) {
            Log::error('Exceção ao reiniciar instância WhatsApp', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Desconecta o dispositivo WhatsApp
     */
    public function disconnect(): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user->instance_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Instance ID não encontrado'
                ], 400);
            }

            $response = Http::timeout(10)->asForm()->post(self::WHATSGW_BASE_URL . 'LogoutDevice', [
                'apikey' => config('whatsgw.apiKey'),
                'w_instancia_id' => $user->instance_id,
            ]);

            if (!$response->successful()) {
                Log::error('Erro ao desconectar WhatsApp', [
                    'status_code' => $response->status(),
                    'response' => $response->body(),
                    'user_id' => $user->id,
                    'instance_id' => $user->instance_id
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Erro na API externa ao desconectar'
                ], 500);
            }

            // Remove QR Code após desconectar
            $qrPath = "qrcodes/qrcode_{$user->instance_id}.png";
            if (Storage::disk('public')->exists($qrPath)) {
                Storage::disk('public')->delete($qrPath);
            }

            Log::info('WhatsApp desconectado com sucesso', [
                'user_id' => $user->id,
                'instance_id' => $user->instance_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Desconectado com sucesso'
            ]);

        } catch (Exception $e) {
            Log::error('Exceção ao desconectar WhatsApp', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Limpa QR Codes antigos (pode ser chamado por um comando/job)
     */
    public function cleanupOldQrCodes(): JsonResponse
    {
        try {
            $qrcodesPath = 'qrcodes/';
            $files = Storage::disk('public')->files($qrcodesPath);
            $deletedCount = 0;
            $currentTime = time();
            $maxAge = 10 * 60; // 10 minutos

            foreach ($files as $file) {
                if (str_contains($file, 'qrcode_')) {
                    $fileTime = Storage::disk('public')->lastModified($file);

                    if (($currentTime - $fileTime) > $maxAge) {
                        Storage::disk('public')->delete($file);
                        $deletedCount++;
                    }
                }
            }

            Log::info("Limpeza de QR Codes antigos executada", [
                'deleted_count' => $deletedCount
            ]);

            return response()->json([
                'success' => true,
                'deleted_count' => $deletedCount
            ]);

        } catch (Exception $e) {
            Log::error('Erro na limpeza de QR Codes', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro na limpeza'
            ], 500);
        }
    }

//    public function verificarStatus()
//    {
//        $user = auth()->user();
//        $response = Http::asForm()->post('https://app.whatsgw.com.br/api/WhatsGw/PhoneState', [
//            'apikey' => config('whatsgw.apiKey'),
//            'phone_number' => $user->number,
//            'w_instancia_id' => $user->instance_id,
//        ]);
//
//        return response()->json($response->json());
//
//        /*$user = auth()->user();
//        $resposta = Http::asForm()->post('https://app.whatsgw.com.br/api/WhatsGw/PhoneState', [
//            'apikey' => config('whatsgw.apiKey'),
//            'phone_number' => $user->number,
//            'w_instancia_id' => $user->instance_id,
//        ]);
//
//        if ($resposta->successful()) {
//            return response()->json($resposta->json());
//        } else {
//            return response()->json(['status' => 'erro', 'message' => 'Erro ao verificar status'], 500);
//        }*/
//    }
//
//    public function gerarQrCode()
//    {
//        $user = auth()->user();
//        $instanciaId = $user->instance_id;
//        $apiKey = config('whatsgw.apiKey');
//
//        //$this->restartInstance($apiKey, $instanciaId, '0');
//
//        $path = "qrcodes/qrcode_$instanciaId.png";
//        //Log::info($path);
//
//        if (!Storage::disk('public')->exists($path)) {
//            return response()->json([
//                'error' => 'QR Code ainda não disponível.'
//            ], 404);
//        }
//
//        $contents = Storage::disk('public')->get($path);
//        $base64 = base64_encode($contents);
//
//        return response()->json([
//            'qrcode_base64' => 'data:image/png;base64,' . $base64,
//        ]);
//    }
//
//    protected function restartInstance($apiKey, $w_instancia_id, $type){
//        $response = Http::asForm()->post('https://app.whatsgw.com.br/api/WhatsGw/RestartInstance', [
//            'apikey' => $apiKey,
//            'w_instancia_id' => $w_instancia_id,
//            'type' => $type,
//        ]);
//
//        Log::info("Resultado $response");
//    }
//
//    public function status()
//    {
//        $user = auth()->user();
//        $apikey = config('whatsgw.apiKey');
//        $phoneNumber = $user->number; // Coloque o número correto aqui
//        $instanciaId = $user->instance_id; // Substitua se necessário
//
//        $response = Http::asForm()->post('https://app.whatsgw.com.br/api/WhatsGw/PhoneState', [
//            'apikey' => $apikey,
//            'phone_number' => $phoneNumber,
//            'w_instancia_id' => $instanciaId,
//        ]);
//
//        if ($response->successful()) {
//            $data = $response->json();
//            return response()->json([
//                'connected' => $data['data']['connected'] ?? false,
//                'status' => $data['data']['status'] ?? 'desconhecido'
//            ]);
//        }
//
//        return response()->json([
//            'connected' => false,
//            'status' => 'erro na comunicação com a API'
//        ], 500);
//    }
}
