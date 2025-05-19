<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Jobs\MensagensEmMassaContatosJob;

class MultipleContactMsgController extends Controller
{
    /**
     * @throws ConnectionException
     */
    public function index()
    {
        $token = token_user();
        $baseUrl = env('WHATSAPP_API_URL', 'http://localhost:3000');
        $response = Http::withToken($token)->get($baseUrl . '/contacts');
        $contatos = $response->successful() ? $response->json()['contacts'] : [];

        // Log::info($contatos);

        return view('pages.from-contacts', compact('contatos'));
    }

    public function enviaMensagemContatosWhatsapp(Request $request)
    {
        Log::info($request->all());
        $request->validate([
            'mensagem' => 'required|string',
            'contatos' => 'required|array|min:1',
            'contatos.*.numero' => 'required|string',
            'contatos.*.nome' => 'nullable|string',
        ],
            [
                'mensagem.required' => 'Você não digitou a mensagem',
                'contatos.required' => 'É necessário fornecer contatos para o envio',
                'contatos.array' => 'O formato da lista de contatos é inválido',
                'contatos.min' => 'É necessário fornecer pelo menos um contato',
                'contatos.*.numero.required' => 'Número do contato é obrigatório',
            ]);

        $mensagem = $request->input('mensagem');
        $contatos = $request->input('contatos');

        MensagensEmMassaContatosJob::dispatch($mensagem, $contatos);


        return response()->json(['message' => 'Executando envio de mensagens em massa!'], 200);
    }
}
