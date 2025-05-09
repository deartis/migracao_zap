<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendMessageRequest;
use App\Jobs\SendWhatsAppMessageJob;
use App\Models\Historic;
use App\Services\ArrayDataDetector;
use App\Services\ContatosJsonProcessor;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;
use Log;

class WhatsAppController extends Controller
{
    protected WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function start(Request $request): JsonResponse
    {
        try {
            $response = $this->whatsappService->startWhatsApp($this->getUserToken());
            return response()->json($response->json(), $response->status());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function sendMessage(SendMessageRequest $request)
    {
        try {
            $validated = $request->validated();
            $user = auth()->user();

            $now = now();
            $currentMonth = $now->format('Y-m');

            // Verifica se é um novo mês
            if ($user->lastMessage) {
                $lastMsgMonth = Carbon::parse($user->lastMessage)->format('Y-m');

                if ($lastMsgMonth !== $currentMonth) {
                    $mensagensSobraram = max($user->msgLimit - $user->sendedMsg, 0);
                    $user->msgLimit += $mensagensSobraram;
                    $user->sendedMsg = 0;
                }
            }

            // Verifica limite de envio
            if ($user->sendedMsg >= $user->msgLimit) {
                return response()->json(['error' => 'Limite de mensagens atingido para este mês.'], 403);
            }

            // Processa arquivo de mídia se existir
            $mediaPath = null;
            $mediaFile = null;

            \Log::info('Conteúdo do request:', $request->all());
            if ($request->hasFile('media')) {
                \Log::info('Arquivo media:', [
                    'nome' => $request->file('media')->getClientOriginalName(),
                    'mime' => $request->file('media')->getMimeType(),
                    'tamanho' => $request->file('media')->getSize(),
                ]);
            } else {
                \Log::warning('Nenhum arquivo de mídia detectado no request.');
            }

            if ($request->hasFile('media')) {
                $file = $request->file('media');

                // Verifica se o arquivo é válido
                if ($file->isValid()) {
                    // Obtém o caminho temporário do arquivo
                    $mediaPath = $file->getPathname();

                    // Se precisar do arquivo como objeto, não apenas o caminho
                    $mediaFile = [
                        'path' => $mediaPath,
                        'mimetype' => $file->getMimeType(),
                        'filename' => $file->getClientOriginalName()
                    ];
                }
            }

            // Envia mensagem
            $response = $this->whatsappService->sendMessage(
                $validated['number'],
                $validated['message'],
                $mediaFile ?? $validated['media'] ?? null,  // Prioriza o arquivo enviado
                $this->getUserToken()
            );

            // Se sucesso, atualiza contadores e salva
            if ($response->successful()) {
                $user->increment('sendedMsg');
                $user->lastMessage = $now;
                $user->save();
            }

            return response()->json($response->json(), $response->status());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function healthCheck(Request $request)
    {
        $token = $this->getUserToken();
        $response = $this->whatsappService->healthCheck();
        return response()->json($response->json());
    }

    public function checkConnection(Request $request)
    {
        $token = $this->getUserToken();
        $response = $this->whatsappService->checkConnection($token);
        return response()->json($response->json());
    }

    public function startWhatsApp(Request $request)
    {
        $token = $this->getUserToken();

        $response = $this->whatsappService->startWhatsApp($token);
        return response()->json($response->json());
    }

    private function getUserToken()
    {
        $user = auth()->user()->id;
        return $user;
    }

    /*protected function getUserToken(Request $request): ?string
    {
        // Adapte conforme seu sistema de autenticação
        // Exemplo usando Sanctum/autenticação padrão do Laravel
        if ($request->user()) {
            return $request->user()->api_token ?? null;
        }

        return null;
    }*/

    // ==================================//
    // ==== Enviar mensagem em massa ===//
    // ================================//
    public function sendBulkMessages(Request $request)
    {
        try {
            //Token
            $token = auth()->user()->id;

            // Validar os campos obrigatórios
            $request->validate([
                'message' => 'required|string',
                'selected_columns' => 'required|array',
                'column_mapping' => 'required|array',
                'contacts' => 'required|array',
            ]);

            // Preparar os dados
            $dados = [
                'message' => $request->message,
                'selected_columns' => $request->selected_columns,
                'column_mapping' => $request->column_mapping,
                'contacts' => $request->contacts,
            ];

            // Processar os contatos
            $processor = new ContatosJsonProcessor();
            $resultado = $processor->process($dados);

            \Log::info($resultado);

            if (is_string($resultado)) {
                // Se processador retornou erro
                return response()->json([
                    'success' => false,
                    'message' => $resultado
                ], 400);
            }

            if (!isset($resultado['success']) || !$resultado['success'] || empty($resultado['contacts'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum contato válido encontrado.'
                ], 400);
            }



            // Disparar o job PARA CADA CONTATO
            foreach ($resultado['contacts'] as $contato) {
                SendWhatsAppMessageJob::dispatch(
                    [
                        'name' => $contato['nome'],
                        'number' => $contato['numero'],
                        'metadata' => [
                            'Nome do Paciente' => $contato['nome'],
                            'Dt.Atendto.' => $contato['data_consulta'] ?? '',
                            'Hora' => $contato['hora_consulta'] ?? '',
                            'Nome do Responsável' => $contato['responsavel'] ?? ''
                        ]
                    ],
                    $request->message,
                    $token,
                    null,
                    auth()->id()
                );
            }

            // 5️⃣ Retornar sucesso
            return response()->json([
                'success' => true,
                'message' => 'Mensagens enviadas com sucesso.',
                'count' => count($resultado['contacts'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro no envio de mensagens: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar: ' . $e->getMessage()
            ], 500);
        }
    }

    private function handleMediaUpload(Request $request): ?string
    {
        if (!$request->hasFile('media')) {
            return null;
        }

        return $request->file('media')->store('whatsapp-media', 'public');
    }

    private function processTraditionalSend(Request $request, string $token, int $userId, string $batchId, ?string $filePath)
    {
        $message = $request->input('message');
        $contacts = Historic::whereNotNull('contact')
            ->where('user_id', $userId)
            ->get();

        foreach ($contacts as $index => $contact) {
            SendWhatsAppMessageJob::dispatch(
                $contact->contact,
                $message,
                $token,
                $filePath,
                $userId,
                $batchId
            )->delay(now()->addSeconds($index * rand(4, 10)));
            // \Log::info('Veio aqui no processTraditionalSend');
        }

        return response()->json([
            'success' => true,
            'message' => 'Mensagens tradicionais enfileiradas com sucesso!',
            'count' => $contacts->count(),
            'batch_id' => $batchId
        ]);
    }

    private function processXlsxFile(Request $request, string $token, int $userId, string $batchId, ?string $filePath, string $messageTemplate)
    {
        $spreadsheet = IOFactory::load($request->file('file'));
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $headers = array_shift($rows);
        $selectedColumns = $request->input('selected_columns', []);
        $processedCount = 0;

        $messageTemplate = $request->input('message', '');

        foreach ($rows as $index => $row) {
            // Limpa os _x000D_ e espaços extras da linha
            $cleanRow = array_map(function ($value) {
                if (is_null($value)) {
                    return null;
                }
                $cleaned = trim(str_replace('_x000D_', '', $value));
                return $cleaned === '' ? null : $cleaned;
            }, $row);

            // Se a linha inteira estiver vazia (todos os valores nulos), pula
            if (count(array_filter($cleanRow)) === 0) {
                continue;
            }

            // Usa a linha limpa normalmente
            $contactData = $this->extractContactData($headers, $cleanRow, $selectedColumns, $messageTemplate);


            if ($contactData['contact']) {
                $processedCount++;
                SendWhatsAppMessageJob::dispatch(
                    $contactData['contact'],
                    $contactData['message_final'],
                    $token,
                    $filePath,
                    $userId,
                    $batchId,
                    $contactData['name'],
                    $messageTemplate
                )->delay(now()->addSeconds($index * rand(4, 10)));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Mensagens do arquivo enfileiradas com sucesso!',
            'count' => $processedCount,
            'batch_id' => $batchId
        ]);
    }

    private function extractContactData(array $headers, array $row, array $selectedColumns, string $messageTemplate): array
    {
        // Limpa os _x000D_ de todas as células
        foreach ($row as &$value) {
            if (!is_null($value)) {
                $value = trim(str_replace('_x000D_', '', $value));
            }
        }
        unset($value);

        /*\Log::info('extractContactData:', [
            'headers' => $headers,
            'row' => $row,
            'selectedColumns' => $selectedColumns
        ]);*/

        $contact = null;
        $name = null;
        $messageFinal = $messageTemplate;  // Começa com o template original

        foreach ($selectedColumns as $column) {
            $colIndex = array_search($column, $headers);
            if ($colIndex !== false && isset($row[$colIndex])) {
                $value = $row[$colIndex];

                // Sanitiza o valor para evitar interpretação como data
                $safeValue = is_string($value) ? trim($value) : $value;

                // Substitui os placeholders de forma segura
                $messageFinal = str_replace(
                    ["{{{$column}}}", "{{ $column }}", '{{' . trim($column) . '}}'],
                    $safeValue,
                    $messageFinal
                );

                // Identifica colunas especiais
                if (preg_match('/(telefone|celular|contato|phone|mobile|whatsapp|zap|contatos)/i', $column)) {
                    $contact = $value;
                } elseif (preg_match('/(nome|name|contact)/i', $column)) {
                    $name = $value;
                }

                // Substitui os placeholders no template
                $messageFinal = str_replace("{{{$column}}}", $value, $messageFinal);

                // \Log::info($messageFinal);
            }
        }

        return [
            'contact' => $contact,
            'name' => $name,
            'message_final' => $messageFinal  // Mensagem já formatada
        ];
    }

    public function dashboard()
    {
        $totalContatos = Historic::count();
        $enviadas = Historic::where('status', 'sucesso')->count();
        $comErro = Historic::where('status', 'erro')->count();

        $historico = Historic::latest()->take(20)->get();

        return view('pages.dashboard', compact('totalContatos', 'enviadas', 'comErro', 'historico'));
    }

    public function responder(Request $request)
    {
        $numero = $request->input('numero');
        $mensagem = $request->input('mensagem');

        // Aqui você chama seu client do whatsapp_web.js, via eventos, jobs ou API

        // Exemplo de retorno simples:
        return redirect()->back()->with('success', 'Mensagem enviada com sucesso!');
    }
}
