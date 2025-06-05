<?php

namespace App\Http\Controllers;

use App\Models\Instances;
use App\Services\PhoneValidator;
use App\Services\WhatsGwService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class ConnectionController extends Controller
{
    /**
     * Exibe a página de conexão
     */
    public function index()
    {
        //$instance = Instances::where('user_id', auth()->id())->first();
        $user = auth()->user();
        $haInstancia = true;
        if (!$user->instance_id) {
            $haInstancia = false;
        }
        return view('pages.connect', compact('haInstancia'));
    }

    public function conectarWgw(WhatsGwService $whatsGwService)
    {
        $user = auth()->user();

        if (!$user->instance_id) {
            Log::error('Você ainda não tem aparelho conectado!');
            Log::info('Criando uma nova instância...');
            $whatsGwService->newStance();
        }
    }

    public function gerarQrCode()
    {
        $user = auth()->user();
        $instanciaId = $user->instance_id;
        $apiKey = config('whatsgw.apiKey');

        //$this->restartInstance($apiKey, $instanciaId, '0');

        $path = "qrcodes/qrcode_$instanciaId.png";
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

    public function status()
    {
        $user = auth()->user();
        $apikey = config('whatsgw.apiKey');
        $phoneNumber = PhoneValidator::validate($user->number)['number'];
        $instanciaId = $user->instance_id;

        $response = Http::asForm()->post(config('whatsgw.apiUrl') . '/PhoneState', [
            'apikey' => $apikey,
            'phone_number' => $phoneNumber,
            'w_instancia_id' => $instanciaId,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $status = $data['phone_state'] ?? 'desconhecido';

            // ✅ Se estiver conectado, exclui o QR code salvo
            if (strtolower($status) === 'connected') {
                $path = "qrcodes/qrcode_{$instanciaId}.png";
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                    \Log::info("QR Code excluído: $path");
                }
            }

            return response()->json([
                'connected' => $status === 'connected',
                'status' => $status,
                'number' => $data['phone_number'] ?? 'desconhecido'
            ]);
        }

        return response()->json([
            'connected' => false,
            'status' => 'erro na comunicação com a API'
        ], 500);
    }

    public function resetarInstancia(WhatsGwService $whatsGwService)
    {
        $user = auth()->user();

        if (!$user->instance_id) {
            return response()->json(['error' => 'Instância não encontrada.'], 400);
        }

        $apiKey = config('whatsgw.apiKey');
        $instanciaId = $user->instance_id;

        // Envia requisição para resetar a instância
        $response = Http::asForm()->post(config('services.whatsgw.apiUrl') . '/RestartInstance', [
            'apikey' => $apiKey,
            'w_instancia_id' => $instanciaId,
            'type' => '1', // zera a sessão atual
        ]);

        if ($response->successful()) {
            // Apaga o QR Code antigo (se existir)
            Storage::disk('public')->delete("qrcodes/qrcode_{$instanciaId}.png");

            return response()->json(['success' => true, 'message' => 'Instância reiniciada com sucesso.']);
        }

        return response()->json(['error' => 'Erro ao reiniciar a instância.'], 500);
    }

    /*public function desconectar()
    {
        $user = auth()->user();
        $apiKey = config('whatsgw.apiKey');
        $instanciaId = $user->instance_id;

        // Resetar instância no WhatsGW
        $response = Http::asForm()->post(config('whatsgw.apiUrl') . '/RestartInstance', [
            'apikey' => $apiKey,
            'w_instancia_id' => $instanciaId,
            'type' => '0', // Resetar completamente
        ]);

        // Apagar QR Code da instância
        $qrPath = "qrcodes/qrcode_{$instanciaId}.png";
        if (Storage::disk('public')->exists($qrPath)) {
            Storage::disk('public')->delete($qrPath);
        }

        // Opcional: remover número do usuário
        $user->update(['number' => null]);

        return response()->json(['message' => 'Desconectado com sucesso.']);
    }*/

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

}
