<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ProcessarEnvioWhatsapp implements ShouldQueue
{
    use Queueable, SerializesModels, InteractsWithQueue, Dispatchable;

    protected  $contato;
    protected $mensagem;
    /**
     * Create a new job instance.
     */
    public function __construct($contato, $mensagem)
    {
        $this->contato = $contato;
        $this->mensagem = $mensagem;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Coluna esperada para o telefone no Excel
        $telefone = $this->contato['telefone'] ?? $contato['celular'] ?? $this->contato[0];

        //Formata o número se necessário
        $telefone = preg_replace('/[^0-9]/', '', $telefone);

        // Fazer chamada para seu servidor WhatsApp
        Http::post('http://localhost:3000/send-message',[
            'numero' => $telefone,
            'mensagem' => $this->mensagem
        ]);

        sleep(rand(2, 30));
    }
}
