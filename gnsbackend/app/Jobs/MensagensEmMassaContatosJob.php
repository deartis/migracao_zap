<?php

namespace App\Jobs;

use App\Models\Historic;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class MensagensEmMassaContatosJob implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $mensagem;
    protected $contatos;
    protected $media;
    protected $baseUrl;
    protected $token;
    protected $whatsappService;

    /**
     * Create a new job instance.
     */
    public function __construct($user, string $mensagem, array $contatos, $media, $baseUrl, $token)
    {
        $this->user = $user;
        $this->mensagem = $mensagem;
        $this->contatos = $contatos;
        $this->media = $media;
        $this->baseUrl = $baseUrl;
        $this->token = $token;
        $this->whatsappService = new WhatsAppService($baseUrl, $token);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $contaErros=0;
        $user = $this->user;

        Log::info($user);
        foreach ($this->contatos as $contato){
            $numero = $contato['numero'];
            $nome = $contato['nome'] ?? 'Desconhecido';
            $sndMsg = $this->user->sendedMsg;

            if($this->checkPlan() === null){
                //Log::info("Mensagem foi enviada, Tem limite e Não está bloqueado");
                $this->enviarMensagem($contato);
                $this->user->sendedMsg = $sndMsg+1;
                $this->user->save();
                Log::info("Enviou a mensagem: ". $this->checkPlan() . " MSG: ". $this->user->sendedMsg);
            }else{
                $contaErros++;
                $this->salvaHistorico($user->id, $numero, 'error', $nome, $this->checkPlan());
                Log::info("Erro do Foreach: ". $this->checkPlan(). " - C= $contaErros");
            }
            sleep(rand(3,10));
        }

        Log::info("Finalizado o envio de mensagem!");
    }

    protected function enviarMensagem(array $contato) : void {
        try{
            $numero = $contato['numero'];
            $nome = $contato['nome'] ?? 'Desconhecido';

            $mensagem = $this->mensagem;
            $media = $this->media;

            $response = $this->whatsappService->sendMessage(
                $numero,
                $mensagem,
                $media,
                $this->token
            );
            if($response->successful()){
                Log::info("Mensagem enviada com sucesso para: ". $numero);
                $this->salvaHistorico(
                    $this->user->id,
                    $numero,
                    'success',
                    $nome
                );
            }else {
                Log::error("Um erro ocorreu ao envia está mensagem: Function enviarMensagem()");
                $this->salvaHistorico(
                    $this->user->id,
                    $numero,
                    'Error',
                    $nome,
                    'invalid_number'
                );
            }
        }catch (\Exception $e){
            Log::error("Erro ao enviar para $nome - $numero: " . $e->getMessage());

            $this->salvaHistorico(
                $this->user->id,
                $numero,
                'Error',
                $nome,
                'Unknown_error'
            );
        }
    }

    protected function salvaHistorico($userId, $contact, $status, $name=null, $errorType = null) : void {
        try{
            Historic::create([
                'user_id' => $userId,
                'contact' => $contact,
                'status' => $status,
                'name' => $name,
                'errorType' => $errorType
            ]);
        }catch (\Exception $e){
            Log::error('Erro ao salvar histórico: ' . $e->getMessage());
        }
    }

    protected function checkPlan(){
        $resultado  = null;
        $user       = $this->user;
        $userFree   = $user->enabled;
        $msgLimit   = $user->msgLimit;
        $sendedMsg  = $user->sendedMsg;
        $hasLimit   = $msgLimit > $sendedMsg;

        if(!$hasLimit){
            Log::error("Limite de mensagem atingido!");
            $resultado =  'limit_exceeded';
            return $resultado;
        }elseif (!$userFree){
            Log::error("Usuário bloqueado!");
            $resultado =  'blocked_user';
            return $resultado;
        }

        return $resultado;
    }
}
