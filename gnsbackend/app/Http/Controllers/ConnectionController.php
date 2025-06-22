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

    public function index(WhatsGwService $whatsGwService)
    {
        $user = auth()->user();
        $instance = Instances::where('user_id', $user->id)->first();

        if(!$user->instance_id){
            $instance->instance_id = null;
            $instance->connected = false;
            $instance->status = 'disconnected';
            $instance->qrcode = null;
            $instance->save();

            $mostrar_modal = true;
        }else{
            $mostrar_modal = false;
        }

        $status = $whatsGwService->getStatus($user->instance_id);

        Log::info("Connection",[$status]);

        if($status['result'] === 'success' && $status['phone_state'] === 'connected'){
            $instance->connected = true;
            $instance->status = "connected";
            $instance->save();
            Log::info('Ta connectado');
        }

        return view('pages.connect', [
            'connected' => $instance->connected,
            'qrcode' => $instance->qrcode,
            'mostrar_modal' => $mostrar_modal
        ]);
    }

    public function newInstance(WhatsGwService $whatsGwService, Request $request){
        $user = auth()->user();
        $status = $whatsGwService->getStatus($user->instance_id);

        if(!$user->instance_id){
            Log::info("Gerando nova Instancia");
            $whatsGwService->newStance();
            return redirect()->route('page.connection')->with('success','Aqui gera a nova instancia');
        }

        Log::info($status);
    }

    public function status(WhatsGwService $whatsGwService)
    {
        try {
            $user = auth()->user();
            $status = $whatsGwService->getStatus($user->instance_id);

            Log::info('==========  Status: ' . $status['phone_state'] . ' ===========');

            return response()->json([
                'status' => $status['phone_state'],
                'number' => $status['phone_number'],
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

    public function gerarQrCode()
    {
        $user = auth()->user();
        $instanciaId = $user->instance_id;
        $apiKey = config('whatsgw.apiKey');

        //$this->restartInstance($apiKey, $instanciaId, '0');

        $path = "qrcodes/qrcode_$user->id.png";
        //Log::info($path);

        if (!Storage::disk('public')->exists($path)) {
            return response()->json([
                'error' => 'QR Code ainda não disponível.'
            ], 404);
        }

        $contents = Storage::disk('public')->get($path);
        $base64 = base64_encode($contents);

        return response()->json([
            'qrcode_base64' => 'data:image/png;base64,' . $base64,
        ]);
    }

    public function qrcode(): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user->instance_id) {
                return response()->json([
                    'error' => 'Instance ID não encontrado'
                ], 400);
            }

            $path = "qrcodes/qrcode_{$user->id}.png";

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
