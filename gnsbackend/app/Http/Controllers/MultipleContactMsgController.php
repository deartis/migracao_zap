<?php

namespace App\Http\Controllers;

use App\Jobs\MensagensEmMassaJob;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsGwService;

class MultipleContactMsgController extends Controller
{
    /**
     * @throws ConnectionException
     */
    public function index()
    {
        return view('pages.from-contacts');
    }

    public function enviaMensagemContatosWhatsapp(Request $request)
    {
        Log::info($request);
        $validator = $request->validate([
            'mensagem' => 'required|string',
            'contatos' => 'required|json',
            'arquivo' => 'nullable|file|max:10240', // até 10MB, por exemplo
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $mensagem = $request->mensagem;
        $contatos = json_decode($request->contatos, true);
        $arquivo = $request->file('arquivo');

        $usuario = auth()->user();
        $statusUsuario = $this->verificaStatusUser();

        if ($usuario->sendedMsg >= $usuario->msgLimit) {
            return response()->json(['message' => 'Você usou 100% do seu plano!'], 403);
        }

        if (!$usuario->enabled) {
            return response()->json(['message' => 'Você está sem saldo para mensagem ou está bloqueado.'], 403);
        }

        // Aqui você trata o arquivo (se houver)
        if ($request->hasFile('arquivo')) {
            $arquivo = $request->file('arquivo');

            // Exemplo: salvar o arquivo temporariamente
            $pathArquivo = $arquivo->store('arquivos_mensagens', 'public');
        } else {
            $pathArquivo = null;
        }

        try {
            $erros = $this->verificaContatos($contatos);
            $contatosProcessados = $this->processarContatos($contatos);

            // Você pode passar $pathArquivo para o Job se desejar
            //Log::info($statusUsuario);
            MensagensEmMassaJob::dispatch(
                $contatosProcessados,
                auth()->id(),
                $statusUsuario,
                $pathArquivo ?? null,
            );

            return response()->json(['message' => 'Mensagens sendo executadas...']);
        } catch (\Exception $e) {
            Log::error('Erro ao processar contatos', [
                'erro' => $e->getMessage(),
                'usuario_id' => auth()->id()
            ]);

            return response()->json(['message' => 'Erro ao processar a lista de contatos.'], 500);
        }

        //MensagensEmMassaContatosJob::dispatch($user, $mensagem, $contatos, $media, baseUrlApi(), token_user());
        return response()->json(['message' => 'Executando envio de mensagens em massa!'], 200);
    }

    protected function verificaStatusUser(): array
    {
        // Mantém a implementação original da função verificaStatusUser
        $usuario = auth()->user();

        // Verifica se o usuário tem limite disponível
        $temLimite = $usuario->msgLimit > $usuario->sendedMsg;

        // Verifica se o usuário está ativo/habilitado (ajuste conforme sua aplicação)
        $estaHabilitado = $usuario->status === 'active';

        return [
            'userm' => $usuario,
            'hasLimit' => $temLimite,
            'enabled' => $estaHabilitado
        ];
    }
}
