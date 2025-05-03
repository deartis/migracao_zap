<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendMessageRequest;
use App\Jobs\SendWhatsAppMessageJob;
use App\Models\Historic;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Services\WhatsAppService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class WhatsAppController extends Controller
{
    protected $whatsappService;

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
                $mediaFile ?? $validated['media'] ?? null, // Prioriza o arquivo enviado
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

    //==================================//
    // ==== Enviar mensagem em massa ===//
    //==================================//
    public function sendBulkMessages(Request $request)
    {
        // Forçar resposta JSON em caso de erro
        $request->headers->set('Accept', 'application/json');

        try {
            // Validação dos dados
            $validator = Validator::make($request->all(), [
                'message' => 'required_without:file',
                'file' => 'required_without:message|file|mimes:xlsx,xls',
                'media' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,pdf,docx',
                'selected_columns' => 'required_if:file,true|array',
                'selected_columns.*' => 'string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $token = $this->getUserToken();
            $userId = auth()->id();
            $batchId = uniqid();
            $filePath = $this->handleMediaUpload($request);

            // Determinar o modo de envio
            if ($request->hasFile('file')) {
                return $this->processXlsxFile($request, $token, $userId, $batchId, $filePath);
            } else {
                return $this->processTraditionalSend($request, $token, $userId, $batchId, $filePath);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro no servidor',
                'error' => $e->getMessage()
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
        }

        return response()->json([
            'success' => true,
            'message' => 'Mensagens tradicionais enfileiradas com sucesso!',
            'count' => $contacts->count(),
            'batch_id' => $batchId
        ]);
    }

    private function processXlsxFile(Request $request, string $token, int $userId, string $batchId, ?string $filePath)
    {
        $spreadsheet = IOFactory::load($request->file('file'));
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $headers = array_shift($rows);
        $selectedColumns = $request->input('selected_columns', []);
        $messageTemplate = $request->input('message', '');
        $processedCount = 0;

        foreach ($rows as $index => $row) {
            $contactData = $this->extractContactData($headers, $row, $selectedColumns);

            if ($contactData['contact']) {
                $processedCount++;
                SendWhatsAppMessageJob::dispatch(
                    $contactData['contact'],
                    $contactData['message'],
                    $token,
                    $filePath,
                    $userId,
                    $batchId,
                    $contactData['name']
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

    private function extractContactData(array $headers, array $row, array $selectedColumns): array
    {
        $contact = null;
        $name = null;
        $message = '';

        foreach ($selectedColumns as $column) {
            $colIndex = array_search($column, $headers);
            if ($colIndex !== false && isset($row[$colIndex])) {
                // Identifica colunas especiais
                if (preg_match('/(telefone|celular|contato|phone|mobile)/i', $column)) {
                    $contact = $row[$colIndex];
                } elseif (preg_match('/(nome|name|contact)/i', $column)) {
                    $name = $row[$colIndex];
                }

                // Substitui placeholders
                $message = str_replace("{{{$column}}}", $row[$colIndex], $message);
            }
        }

        return [
            'contact' => $contact,
            'name' => $name,
            'message' => $message
        ];
    }

    /*public function sendBulkMessages(Request $request)
    {
        // Validação básica para ambos os casos
        $request->validate([
            'message' => 'required_without:file',
            'file' => 'required_without:message|file|mimes:xlsx,xls',
            'media' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,pdf,docx',
            'selected_columns' => 'required_if:file,true|array',
            'selected_columns.*' => 'string' // Valida cada item do array
        ]);

        $token = $this->getUserToken();
        $filePath = null;
        $userId = auth()->id();
        $batchId = uniqid();

        // Upload de mídia (se aplicável a ambos os casos)
        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $filePath = $file->store('whatsapp-media', 'public');
        }

        // Caso 1: Envio tradicional (usando histórico)
        if ($request->has('message') && !$request->hasFile('file')) {
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
            }

            return redirect()->back()->with('success', 'Mensagens em massa enviadas com sucesso!');
        }

        // Caso 2: Envio via XLSX
        if ($request->hasFile('file')) {
            try {
                $spreadsheet = IOFactory::load($request->file('file'));
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();

                $headers = array_shift($rows);
                $selectedColumns = $request->input('selected_columns', []);
                $messageTemplate = $request->input('message', '');

                foreach ($rows as $index => $row) {
                    $contact = null;
                    $message = $messageTemplate;
                    $placeholders = [];

                    // Processar cada coluna selecionada
                    foreach ($selectedColumns as $column) {
                        $colIndex = array_search($column, $headers);
                        if ($colIndex !== false && isset($row[$colIndex])) {
                            // Identifica a coluna de contato
                            if (stripos($column, 'tel') !== false || stripos($column, 'cel') !== false || stripos($column, 'contato') !== false) {
                                $contact = $row[$colIndex];
                            }
                            // Substitui placeholders
                            $message = str_replace("{{{$column}}}", $row[$colIndex], $message);
                        }
                    }

                    if ($contact) {
                        SendWhatsAppMessageJob::dispatch(
                            $contact,
                            $message,
                            $token,
                            $filePath,
                            $userId,
                            $batchId,
                            $row[0] ?? null // Primeira coluna como nome
                        )->delay(now()->addSeconds($index * rand(4, 10)));
                    }
                }

                return redirect()->back()->with('success', count($rows) . ' mensagens do arquivo enfileiradas com sucesso!');

            } catch (\Exception $e) {
                return back()->with('error', 'Erro ao processar arquivo: ' . $e->getMessage());
            }
        }

        return back()->with('error', 'Nenhum método de envio selecionado');
    }*/

    /*private function getUserToken()
    {
        return auth()->user()->remember_token;
    }*/
    /*public function sendBulkMessages(Request $request)
    {
        $message = $request->input('message');
        $token = $this->getUserToken();
        $filePath = null;
        $userId = auth()->id();

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $filePath = $file->store('whatsapp-media', 'public');
        }

        $contacts = Historic::whereNotNull('contact')->where('user_id', $userId)->get();

        foreach ($contacts as $index => $contact) {
            SendWhatsAppMessageJob::dispatch(
                $contact->contact,
                $message,
                $token,
                $filePath,
                $userId

            )->delay(now()->addSeconds($index * rand(4, 10)));
        }

        return redirect()->back()->with('success', 'Mensagens enviadas com sucesso!');
    }*/

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
