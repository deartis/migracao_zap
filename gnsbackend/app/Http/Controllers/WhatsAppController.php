<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendMessageRequest;
use App\Imports\ContatosImport;
use App\Jobs\ProcessarEnvioWhatsapp;
use App\Jobs\SendWhatsAppMessageJob;
use App\Models\Historic;
use App\Services\WhatsAppService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
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

    /**
     * -----------------------------------------------
     * ===============================================
     * Importa os contatos
     * ===============================================
     * -----------------------------------------------
     */
    public function importarContatos(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:xls,xlsx,csv',
            'mensagem' => 'required|string'
        ]);

        $contatos = Excel::toArray(new ContatosImport, $request->file('arquivo'))[0];
        $mensagem = $request->input('mensagem');

        foreach ($contatos as $contato) {
            ProcessarEnvioWhatsapp::dispatch($contato, $mensagem);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Processamento de ' . count($contatos) . ' Contatos iniciado com sucesso.'
        ]);
    }

    //==================================//
    // ==== Enviar mensagem em massa ===//
    //==================================//
    public function sendBulkMessages(Request $request)
    {
        $message = $request->input('message');
        $token = $this->getUserToken();
        $filePath = null;

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $filePath = $file->store('whatsapp-media', 'public');
        }

        $contacts = Historic::whereNotNull('contact')->get();

        foreach ($contacts as $index => $contact) {
            SendWhatsAppMessageJob::dispatch(
                $contact->contact,
                $message,
                $token,
                $filePath // caminho do arquivo em vez do objeto
            )->delay(now()->addSeconds($index * rand(4, 10)));
        }

        return redirect()->back()->with('success', 'Mensagens enviadas com sucesso!');
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
