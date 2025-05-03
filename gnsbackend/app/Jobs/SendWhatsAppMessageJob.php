<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Historic;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\WhatsAppService;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contact;
    protected $message;
    protected $token;
    protected $media;
    protected $userId; // Novo: armazenar ID do usuário

    public function __construct($contact, $message, $token, $media = null, $userId = null)
    {
        $this->contact = $contact;
        $this->message = $message;
        $this->token = $token;
        $this->media = $media;
        $this->userId = $userId; // Armazena o ID do usuário quando o job é criado
    }

    protected $tries = 3; // Número de tentativas antes de considerar o job como falho
    protected $backoff = [30, 60, 120]; // Tempo entre as novas tentativas (em segundos)

    /**
     * Determina se o job deve ser descartado após falhar todas as tentativas
     *
     * @param Exception $exception
     * @return bool
     */
    public function failed(Exception $exception)
    {
        // Registra falha permanente do job
        \Log::error('WhatsApp job falhou permanentemente: ' . $exception->getMessage(), [
            'contact' => $this->contact,
            'userId' => $this->userId,
            'exception' => $exception->getTraceAsString()
        ]);

        // Aqui você pode adicionar código para notificar o usuário sobre falha
        // por exemplo, guardando em um log de erros no banco de dados que pode
        // ser exibido na interface do usuário
    }

    public function handle(WhatsAppService $whatsappService)
    {
        try {

            // Recupera usuário
            $user = null;
            if ($this->userId) {
                $user = User::find($this->userId);
            } elseif ($this->token) {
                $user = User::where('remember_token', $this->token)->first();
            }

            if (!$user) {
                \Log::warning('Usuário não encontrado ao enviar mensagem WhatsApp', [
                    'token' => $this->token,
                    'userId' => $this->userId
                ]);

                // Registra o erro no histórico com usuário desconhecido
                $this->saveHistoric(null, $this->contact, 'error', 'Usuário não encontrado', 'auth_error');
                return;
            }

            // Validação básica do número de telefone
            if (!$this->isValidPhoneNumber($this->contact)) {
                \Log::warning('Número de telefone inválido: ' . $this->contact, [
                    'userId' => $this->userId
                ]);

                // Registra o erro no histórico
                $this->saveHistoric($user->id, $this->contact, 'error', null, 'invalid_number');
                return;
            }

            // Verifica se mudou o mês (antes de qualquer envio)
            if ($user->lastMessage) {
                $lastMsgMonth = Carbon::parse($user->lastMessage)->format('Y-m');
                $currentMonth = now()->format('Y-m');

                if ($lastMsgMonth !== $currentMonth) {
                    $mensagensSobraram = max($user->msgLimit - $user->sendedMsg, 0);
                    $user->msgLimit += $mensagensSobraram;
                    $user->sendedMsg = 0;
                    $user->save();
                }
            }

            // Verifica limite antes de enviar
            if ($user->sendedMsg >= $user->msgLimit) {
                \Log::warning('Limite de mensagens excedido para o usuário', [
                    'userId' => $user->id,
                    'sent' => $user->sendedMsg,
                    'limit' => $user->msgLimit
                ]);

                // Registra no histórico o limite excedido
                $this->saveHistoric($user->id, $this->contact, 'error', null, 'limit_exceeded');
                return;
            }

            // Envia a mensagem
            $response = app(WhatsAppService::class)->sendMessage(
                $this->contact,
                $this->message,
                $this->media,
                $this->token
            );

            // Processa o resultado do envio
            if ($response->successful()) {
                // Registra sucesso em log
                \Log::info('Mensagem WhatsApp enviada com sucesso', [
                    'contact' => $this->contact,
                    'userId' => $user->id
                ]);

                // Registra sucesso no histórico
                $this->saveHistoric($user->id, $this->contact, 'success');

                // IMPORTANTE: Incrementa o contador APENAS em caso de sucesso
                $user->sendedMsg++;
                $user->lastMessage = now();
                $user->save();
            } else {
                // Analisa erro retornado pela API do WhatsApp
                $errorMessage = $response->json('error.message') ?? 'Erro desconhecido';
                $errorCode = $response->json('error.code') ?? 'Sem código';

                \Log::error('Erro ao enviar mensagem WhatsApp', [
                    'contact' => $this->contact,
                    'errorCode' => $errorCode,
                    'errorMessage' => $errorMessage,
                    'status' => $response->status()
                ]);

                // Registra falha no histórico
                $this->saveHistoric(
                    $user->id,
                    $this->contact,
                    'error',
                    null,
                    $this->getErrorTypeFromCode($errorCode, $response->status())
                );

                // NÃO incrementa o contador de mensagens enviadas quando falha
            }

        } catch (Exception $e) {
            \Log::error('Erro ao processar job de WhatsApp: ' . $e->getMessage(), [
                'contact' => $this->contact,
                'exception' => $e
            ]);

            // Registra exceção no histórico
            if ($this->userId) {
                $this->saveHistoric($this->userId, $this->contact, 'error', null, 'exception');
            }

            // NÃO incrementa o contador de mensagens enviadas quando ocorre exceção
            // NÃO relança a exceção - isso evita novas tentativas e continua para a próxima mensagem
        }
    }

    /**
     * Salva registro no histórico
     *
     * @param int|null $userId
     * @param string $contact
     * @param string $status
     * @param string|null $name
     * @param string|null $errorType
     * @return void
     */
    protected function saveHistoric($userId, $contact, $status, $name = null, $errorType = null)
    {
        try {
            \App\Models\Historic::create([
                'user_id' => $userId,
                'contact' => $contact,
                'status' => $status,
                'name' => $name,
                'errorType' => $errorType
            ]);
        } catch (Exception $e) {
            \Log::error('Erro ao salvar histórico: ' . $e->getMessage());
        }
    }

    /**
     * Determina o tipo de erro com base no código de erro e status HTTP
     *
     * @param string $errorCode
     * @param int $httpStatus
     * @return string
     */
    protected function getErrorTypeFromCode($errorCode, $httpStatus)
    {
        // Mapeamento de códigos de erro para tipos mais amigáveis
        $errorMapping = [
            // Exemplos - ajuste conforme os códigos da sua API WhatsApp
            'invalid_number' => 'invalid_number',
            'auth_error' => 'auth_error',
            'message_timed_out' => 'timeout',
            'rate_limit' => 'rate_limit'
        ];

        // Mapeia status HTTP para tipos de erro
        $httpMapping = [
            401 => 'auth_error',
            403 => 'forbidden',
            404 => 'not_found',
            429 => 'rate_limit',
            500 => 'server_error',
            502 => 'server_error',
            503 => 'server_error',
            504 => 'timeout'
        ];

        // Verifica se o código de erro está no mapeamento
        if (isset($errorMapping[$errorCode])) {
            return $errorMapping[$errorCode];
        }

        // Verifica se o status HTTP está no mapeamento
        if (isset($httpMapping[$httpStatus])) {
            return $httpMapping[$httpStatus];
        }

        // Tipo de erro padrão
        return 'unknown_error';
    }

    /**
     * Valida se o número de telefone parece correto
     *
     * @param string $number
     * @return bool
     */
    protected function isValidPhoneNumber($number)
    {
        // Remove caracteres não numéricos
        $cleaned = preg_replace('/[^0-9+]/', '', $number);

        // Regra básica: deve ter pelo menos X dígitos
        if (strlen($cleaned) < 10) {
            return false;
        }

        // Adicione mais validações conforme necessário para seu país/formato

        return true;
    }

    /**
     * Determina se deve tentar novamente com base no código de erro
     *
     * @param string $errorCode
     * @param int $httpStatus
     * @return bool
     */
    protected function shouldRetryError($errorCode, $httpStatus)
    {
        // Códigos de erro temporários que podem ser resolvidos em nova tentativa
        $temporaryErrors = [
            // Adicione aqui os códigos de erro específicos da sua API WhatsApp
            // que indicam problemas temporários
            'temporary_error',
            'rate_limit_exceeded',
            'internal_server_error'
        ];

        // Status HTTP que geralmente indicam problemas temporários
        $temporaryHttpStatus = [429, 500, 502, 503, 504];

        return in_array($errorCode, $temporaryErrors) ||
            in_array($httpStatus, $temporaryHttpStatus);
    }
}
