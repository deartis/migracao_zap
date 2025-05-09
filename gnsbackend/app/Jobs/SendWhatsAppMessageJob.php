<?php

namespace App\Jobs;

use App\Models\Historic;
use App\Models\User;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Exception;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contact;
    protected $message;
    protected $token;
    protected $media;
    protected $userId;
    protected $tries = 3;
    protected $backoff = [30, 60, 120];

    public function __construct($contact, $message, $token, $media = null, $userId = null)
    {
        // Se veio string, transforma em array com número
        if (is_string($contact)) {
            $this->contact = ['name' => '', 'number' => $contact];
        } elseif (is_array($contact) && isset($contact['number'])) {
            $this->contact = $contact;
        } else {
            throw new \InvalidArgumentException('O parâmetro $contact deve ser string ou array com chave "number"');
        }

        $this->message = $message;
        $this->token = $token;
        $this->media = $media;
        $this->userId = $userId;
    }

    public function handle(WhatsAppService $whatsappService)
    {
        try {
            \Log::info('Sá peste veio aqui karai');

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

                $this->saveHistoric(null, $this->contact['number'], 'error', 'Usuário não encontrado', 'auth_error');
                return;
            }

            if (!$this->isValidPhoneNumber($this->contact['number'])) {
                \Log::warning('Número de telefone inválido: ' . $this->contact['number'], [
                    'userId' => $this->userId
                ]);

                $this->saveHistoric($user->id, $this->contact['number'], 'error', null, 'invalid_number');
                return;
            }

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

            if ($user->sendedMsg >= $user->msgLimit) {
                \Log::warning('Limite de mensagens excedido para o usuário', [
                    'userId' => $user->id,
                    'sent' => $user->sendedMsg,
                    'limit' => $user->msgLimit
                ]);

                $this->saveHistoric($user->id, $this->contact['number'], 'error', null, 'limit_exceeded');
                return;
            }

            // $finalMessage = str_replace('{{nome}}', $this->contact['name'], $this->message);

            // Se veio metadata no contact, usa
            $metadata = $this->contact['metadata'] ?? [];

            // Adiciona o nome no metadata também pra garantir que {{nome}} funcione
            $metadata['nome'] = $this->contact['name'];

            // Gera a mensagem final personalizada
            $finalMessage = $this->replacePlaceholders($this->message, $metadata);

            $response = $whatsappService->sendMessage(
                $this->contact['number'],
                $finalMessage,
                $this->media,
                $this->token
            );

            if ($response->successful()) {
                \Log::info('Mensagem WhatsApp enviada com sucesso', [
                    'contact' => $this->contact['number'],
                    'userId' => $user->id
                ]);

                $this->saveHistoric($user->id, $this->contact['number'], 'success');

                $user->sendedMsg++;
                $user->lastMessage = now();
                $user->save();
            } else {
                $errorMessage = $response->json('error.message') ?? 'Erro desconhecido';
                $errorCode = $response->json('error.code') ?? 'Sem código';

                \Log::error('Erro ao enviar mensagem WhatsApp', [
                    'contact' => $this->contact['number'],
                    'errorCode' => $errorCode,
                    'errorMessage' => $errorMessage,
                    'status' => $response->status()
                ]);

                $this->saveHistoric(
                    $user->id,
                    $this->contact['number'],
                    'error',
                    null,
                    $this->getErrorTypeFromCode($errorCode, $response->status())
                );
            }
        } catch (Exception $e) {
            \Log::error('Erro ao processar job de WhatsApp: ' . $e->getMessage(), [
                'contact' => $this->contact,
                'exception' => $e
            ]);

            if ($this->userId) {
                $this->saveHistoric($this->userId, $this->contact['number'], 'error', null, 'exception');
            }
        }
    }

    protected function saveHistoric($userId, $contact, $status, $name = null, $errorType = null)
    {
        try {
            Historic::create([
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

    protected function getErrorTypeFromCode($errorCode, $httpStatus)
    {
        $errorMapping = [
            'invalid_number' => 'invalid_number',
            'auth_error' => 'auth_error',
            'message_timed_out' => 'timeout',
            'rate_limit' => 'rate_limit'
        ];

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

        if (isset($errorMapping[$errorCode])) {
            return $errorMapping[$errorCode];
        }

        if (isset($httpMapping[$httpStatus])) {
            return $httpMapping[$httpStatus];
        }

        return 'unknown_error';
    }

    protected function isValidPhoneNumber($number)
    {
        $cleaned = preg_replace('/[^0-9+]/', '', $number);

        return strlen($cleaned) >= 10;
    }

    protected function shouldRetryError($errorCode, $httpStatus)
    {
        $temporaryErrors = [
            'temporary_error',
            'rate_limit_exceeded',
            'internal_server_error'
        ];

        $temporaryHttpStatus = [429, 500, 502, 503, 504];

        return in_array($errorCode, $temporaryErrors) ||
            in_array($httpStatus, $temporaryHttpStatus);
    }

    /**
     * Substitui os placeholders na mensagem com os valores do metadata.
     *
     * @param string $message
     * @param array $metadata
     * @return string
     */
    protected function replacePlaceholders($message, $metadata)
    {
        return preg_replace_callback('/{{\s*(.+?)\s*}}/', function ($matches) use ($metadata) {
            $key = $matches[1];  // Nome dentro das chaves

            // Tenta achar no metadata ignorando case e espaços
            foreach ($metadata as $metaKey => $metaValue) {
                $normalizedMetaKey = strtolower(str_replace([' ', '.', '_'], '', $metaKey));
                $normalizedPlaceholder = strtolower(str_replace([' ', '.', '_'], '', $key));

                if ($normalizedMetaKey === $normalizedPlaceholder) {
                    return $metaValue;
                }
            }

            // Se não achar, retorna vazio
            return '';
        }, $message);
    }
}
