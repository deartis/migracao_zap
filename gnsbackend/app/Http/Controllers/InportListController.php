<?php

namespace App\Http\Controllers;

use App\Helpers\ArrayDataDetector;
use App\Jobs\MensagensEmMassaJob;
use App\Models\EnvioProgresso;
use App\Models\Historic;
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
        Log::info("===================== Log do CTRL ======================");

        // 1. Validação dos dados de entrada
        $validator = Validator::make($request->all(), [
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

        // 2. Recupera e verifica dados principais
        $template = $request->template;
        $statusUsuario = $this->verificaStatusUser();

        $usuario = auth()->user();
        $temLimite = $usuario->sendedMsg < $usuario->msgLimit;

        // 3. Verifica limitações do usuário
        if (!$temLimite) {
            Log::error("Usuário atingiu 100% do plano", ['user_id' => auth()->id()]);
            return response()->json(['message' => 'Você usou 100% do seu plano!'], 403);
        }

        if (!$usuario->enabled) {
            Log::info("Usuário bloqueado", ['user_id' => auth()->id()]);
            return response()->json(
                [
                    'message' => 'Você está sem saldo para mensagem ou está bloqueado. Entre em contato com o suporte.'
                ], 403);
        }

        // 4. Salva o template como última mensagem do usuário
        auth()->user()->update(['lastMessage' => $template]);

        // 5. Processamento dos contatos
        try {
            $contatos = $request->contacts;
            $erros = $this->verificaContatos($contatos);

            //Log::info($erros);

            $contatosProcessados = $this->processarContatos($contatos);

            //Log::info($contatosProcessados);

            // 6. Dispara o job para processamento assíncrono
            MensagensEmMassaJob::dispatch(
                $contatosProcessados,
                token_user(), // Função existente no seu sistema
                auth()->id(),
                $statusUsuario,
                $erros
            );

            /*Log::info("Job de envio de mensagens em massa despachado com sucesso", [
                'total_contatos' => count($contatosProcessados),
                'total_erros' => count($erros),
                'usuario_id' => auth()->id()
            ]);*/

            return response()->json(['message' => 'Mensagens sendo executadas...']);
        } catch (\Exception $e) {
            Log::error("Erro ao processar contatos para envio em massa", [
                'erro' => $e->getMessage(),
                'usuario_id' => auth()->id()
            ]);

            return response()->json(['message' => 'Ocorreu um erro ao processar a lista de contatos. Tente novamente mais tarde.'], 500);
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
        $estaHabilitado = $usuario->status === 'active';

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
            //Log::error($contato);
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
            $phoneWithSuffix = $re['phone'];
            if (!str_contains($phoneWithSuffix, '@c.us')) {
                $phoneWithSuffix .= '@c.us';
            }

            $contatosFinais[] = [
                'name' => $re['name'],
                'number' => $phoneWithSuffix,
                'message' => $re['metadata']['message'] ?? '',
            ];
        }

        return $contatosFinais;
    }

    //Gerencia o visual de progresso de envio de mensagens
    public function envioProgresso()
    {
        $userId = auth()->id();
        return EnvioProgresso::where('user_id', $userId)->first();
    }

    public function resetaProgresso()
    {
        Log::info('Veio aqui resetar');
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
            'message'=> 'Mensagens enviadas com sucesso!!!'
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
