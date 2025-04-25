<?php

namespace App\Jobs;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Auth;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contact;
    protected $message;
    protected $token;
    protected $media;

    public function __construct($contact, $message, $token, $media = null)
    {
        $this->contact = $contact;
        $this->message = $message;
        $this->token = $token;
        $this->media = $media;
    }

    public function handle(WhatsAppService $whatsappService)
    {
        try {
            // Aqui é onde envia a mensagem (você deve já ter algo assim)
            $response = app(WhatsAppService::class)->sendMessage(
                $this->contact,
                $this->message,
                $this->media,
                $this->token
            );

            // Se enviada com sucesso, atualiza o contador
            if ($response->successful()) {
                $user = Auth::user();

                if (!$user) {
                    // Se não tiver auth, busca pelo token se precisar (opcional)
                    $user = User::where('remember_token', $this->token)->first();
                }

                if ($user) {
                    // Verifica se mudou o mês
                    if ($user->lastMessage) {
                        $lastMsgMonth = Carbon::parse($user->lastMessage)->format('Y-m');
                        $currentMonth = now()->format('Y-m');

                        if ($lastMsgMonth !== $currentMonth) {
                            $mensagensSobraram = max($user->msgLimit - $user->sendedMsg, 0);

                            $user->msgLimit += $mensagensSobraram;
                            $user->sendedMsg = 0;
                        }
                    }

                    // Verifica limite antes de atualizar
                    if ($user->sendedMsg < $user->msgLimit) {
                        $user->sendedMsg++;
                        $user->lastMessage = now();
                        $user->save();
                    }
                }
            }

        } catch (Exception $e) {
            // Trata erro se quiser
            \Log::error('Erro ao enviar mensagem em massa: '.$e->getMessage());
        }
    }
}
