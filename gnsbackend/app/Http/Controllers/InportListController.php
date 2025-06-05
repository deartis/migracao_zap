<?php

namespace App\Http\Controllers;

use App\Helpers\ArrayDataDetector;
use App\Jobs\MensagensEmMassaJob;
use App\Models\EnvioProgresso;
use App\Models\Historic;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class InportListController extends Controller
{
    public function index()
    {
        $contatos = Historic::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('pages.from-sheet', compact('contatos'));
    }

    /**
     * Envia mensagem em massa para uma lista de contatos
     *
     * @param Request $request Requisição
     * @return JsonResponse
     */
    public function enviaMensagemEmMassaLista(Request $request): JsonResponse
    {
        Log::info('===================== Log do CTRL ======================');

        // Decodificar o campo JSON chamado 'data'
        $data = json_decode($request->input('data'), true);  // <- Aqui é importante

        if (!$data || !isset($data['contacts']) || !isset($data['template'])) {
            return response()->json(['message' => 'Dados inválidos ou mal formatados.'], 400);
        }

        // Validar os dados recebidos
        $validator = Validator::make($data, [
            'template' => 'required|string',
            'contacts' => 'required|array|min:1',
        ], [
            'template.required' => 'Você não digitou a mensagem',
            'contacts.required' => 'É necessário fornecer contatos para o envio',
            'contacts.array' => 'O formato da lista de contatos é inválido',
            'contacts.min' => 'É necessário fornecer pelo menos um contato',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $template = $data['template'];
        $contatos = $data['contacts'];
        $usuario = auth()->user();
        $statusUsuario = $this->verificaStatusUser();

        if ($usuario->sendedMsg >= $usuario->msgLimit) {
            return response()->json(['message' => 'Você usou 100% do seu plano!'], 403);
        }

        if (!$usuario->enabled) {
            return response()->json(['message' => 'Você está sem saldo para mensagem ou está bloqueado.'], 403);
        }

        auth()->user()->update(['lastMessage' => $template]);

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

            //Log::info($contatosProcessados);

            // Você pode passar $pathArquivo para o Job se desejar
            MensagensEmMassaJob::dispatch(
                $contatosProcessados,
                auth()->id(),
                $statusUsuario,
                $pathArquivo ?? null,
            );

            return response()->json(['message' => 'Mensagens sendo executadas...']);
        } catch (Exception $e) {
            Log::error('Erro ao processar contatos', [
                'erro' => $e->getMessage(),
                'usuario_id' => auth()->id()
            ]);

            return response()->json(['message' => 'Erro ao processar a lista de contatos.'], 500);
        }
    }

    /**
     * Verifica o status do usuário atual
     *
     * @return array
     */
    protected function verificaStatusUser(): array
    {
        // Mantém a implementação original da função verificaStatusUser
        $usuario = auth()->user();

        // Verifica se o usuário tem limite disponível
        $temLimite = $usuario->msgLimit > $usuario->sendedMsg;

        // Verifica se o usuário está ativo/habilitado (ajuste conforme sua aplicação)
        $estaHabilitado = $usuario->enabled === true;

        return [
            'userm' => $usuario,
            'hasLimit' => $temLimite,
            'enabled' => $estaHabilitado
        ];
    }

    /**
     * Verifica a validade dos contatos fornecidos
     *
     * @param array $contatos
     * @return array
     */
    protected function verificaContatos(array $contatos): array
    {
        // Implementação da função verificaContatos que foi referenciada no controller original
        // Ajuste conforme a lógica específica da sua aplicação

        $erros = [];

        foreach ($contatos as $index => $contato) {
            // Log::error($contato);
            if (empty($contato['number'])) {
                $erros[] = [
                    'indice' => $index,
                    'erro' => 'Número de telefone vazio',
                    'contato' => $contato
                ];
            }
        }

        return $erros;
    }

    /**
     * Processa os contatos para o formato adequado
     *
     * @param array $contatos
     * @return array
     */
    protected function processarContatos(array $contatos): array
    {
        $contatosF = [];
        $contatosFinais = [];

        foreach ($contatos as $contato) {
            $contatosF[] = $contato;
        }

        $verifica = new ArrayDataDetector();
        $result = $verifica->extractContacts($contatosF)['contacts'];

        foreach ($result as $re) {
            $contatosFinais[] = [
                'name' => $re['name'],
                'number' => $re['phone'],
                'message' => $re['metadata']['message'] ?? '',
            ];
        }

        return $contatosFinais;
    }

    // Gerencia o visual de progresso de envio de mensagens
    public function envioProgresso()
    {
        $userId = auth()->id();
        return EnvioProgresso::where('user_id', $userId)->first();
    }

    public function resetaProgresso()
    {
        $userId = auth()->id();
        $conditions = ['user_id' => $userId];

        EnvioProgresso::updateOrCreate(
            $conditions,
            [
                'user_id' => $userId,
                'total' => 0,
                'visto' => 0,
                'enviadas' => 0,
                'status' => 'parado'
            ]
        );

        return response()->json([
            'message' => 'Mensagens enviadas com sucesso!!!'
        ]);
    }

    public function getTemplate()
    {
        $tpl = auth()->user()->lastMessage;
        return response()->json([
            'template' => $tpl
        ]);
    }
}
