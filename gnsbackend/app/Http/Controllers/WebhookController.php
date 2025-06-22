<?php

namespace App\Http\Controllers;

use App\Models\Contacts;
use App\Models\Instances;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        Log::info('Request: ', [$data]);

        if ($data['event'] === 'chats') {
            $instanciaId = $data['w_instancia_id'] ?? null;

            // Recupera o usuário pela instância
            $instancia = User::where('instance_id', $instanciaId)->first();

            if ($instancia) {
                $userId = $instancia->id;

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

            // Remove o prefixo data:image/png;base64,
            $base64Image = preg_replace('/^data:image\/[^;]+;base64,/', '', $base64Image);

            $imageData = base64_decode($base64Image);
            $filename = "qrcodes/qrcode_$instanciaId.png";

            Storage::disk('public')->put($filename, $imageData);

            Log::info("QR Code salvo em: $filename");
        }

        if($data['event'] === 'phonestate'){
            if($data['state'] === 'connected'){
                $this->setStatus();
            }elseif($data['state'] === 'disconnected'){
                if($data['cause'] === 'phoneAuthed'){
                    Log::info('Usuario desconectou no aparelho');
                    Log::info('mudar o status da instance para desconnected');
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function setStatus(){
        $user = auth()->user();
        Log::info($user);
    }
}
