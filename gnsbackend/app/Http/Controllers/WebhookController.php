<?php

namespace App\Http\Controllers;

use App\Models\Contacts;
use App\Models\Instances;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

/**
 * WebhookController
 *
 * Handles incoming webhooks for chat events and QR code updates.
 */
class WebhookController extends Controller
{
    public function webhooks(Request $request)
    {
        $data = $request->all();
        $instanciaId = $data['w_instancia_id'] ?? null;
        $user = User::where('instance_id', $instanciaId)->first();
        $instance = Instances::where('instance_id', $data['w_instancia_id'])->first();
        Log::info($instance);
        Log::info('Request: ', [$data]);

        if ($data['event'] === 'chats') {
            if ($user) {
                $userId = $user->id;

                $contatos = collect($data['chats'])->map(function ($chat) {
                    return [
                        'telefone' => $chat['id'],
                        'nome' => $chat['contact']['name'] ?? $chat['contact']['pushname'] ?? 'Sem Nome'
                    ];
                })->toArray();

                Contacts::mergeAndSaveContacts($userId, $contatos);

                Log::info("Contatos salvos para o usuário ID {$userId}");
            } else {
                Log::warning("Instância {$instanciaId} não encontrada. Contatos não salvos.");
            }

            return response()->json(['status' => 'ok']);
        }

        if ($data['event'] === 'qrcode') {
            $base64Image = $request->input('qrcode');
            $instanciaId = $request->input('w_instancia_id');

            if($instance->status === 'waiting_qrcode'){
                $instance->update(['status'=> 'waiting_connection']);
            }

            // DEFINIR O qrcode_started_at SE NÃO EXISTIR
            if (!$instance->qrcode_started_at) {
                $instance->update(['qrcode_started_at' => now()]);
                Log::info("qrcode_started_at definido para: " . now());
            }

            $diffInSeconds = abs(now()->diffInSeconds($instance->qrcode_started_at));

            Log::info("---------- Ta Aqui----------");
            Log::info("qrcode_started_at: " . $instance->qrcode_started_at);
            Log::info("now(): " . now());
            Log::info("Diferença absoluta em segundos: " . $diffInSeconds);
            Log::info("Maior que 50?: " . ($diffInSeconds > 50 ? 'SIM' : 'NÃO'));
            Log::info("---------- Passou Aqui----------");

            if ($diffInSeconds > 50) {
                $instance->update([
                    'qrcode' => null,
                    'expired_qrcode' => true,
                    'qrcode_started_at' => null,
                ]);
                return response()->json(['msg' => 'QR code expirado.']);
            }else{
                $instance->update([
                    'expired_qrcode' => false,
                ]);
            }

            // Remove o prefixo data:image/png;base64,
            $base64Image = preg_replace('/^data:image\/[^;]+;base64,/', '', $base64Image);

            $imageData = base64_decode($base64Image);
            $filename = "qrcodes/qrcode_$instanciaId.png";

            Storage::disk('public')->put($filename, $imageData);
            $instance->update([
                'qrcode' => $filename,
            ]);

            Log::info("QR Code salvo em: $filename");
        }

        if($data['event'] === 'phonestate'){

            if($data['state'] === 'connected'){
                $instance->update([
                    //'status' => 'waiting_qrcode'
                    'status' => 'connected',
                ]);
            }elseif($data['state'] === 'disconnected'){
                if($data['cause'] === 'phoneAuthed'){
                    $instance->update([
                        'qrcode'=>null,
                        'status'=>'disconnected',
                    ]);
                    Log::info('Usuario desconectou no aparelho');
                    Log::info('mudar o status da instance para desconnected');
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function setStatus(){
        //$user = auth()->user();
        Log::info('setStatus');
    }
}
