<?php

namespace App\Jobs;

use App\Models\EnvioProgresso;
use App\Models\Historic;
use App\Services\PhoneValidator;
use App\Services\WhatsAppService;
use App\Services\WhatsGwService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class MensagensEmMassaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número de tentativas máximas do job
     */
    public $tries = 3;

    /**
     * Tempo de espera antes de nova tentativa em segundos
     */
    public $backoff = 60;

    /**
     * Timeout do job em segundos
     */
    public $timeout = 600;  // 10 minutos

    /**
     * Lista de contatos para envio
     *
     * @var array
     */
    protected $contatos;

    /**
     * Token de autorização
     *
     * @var string
     */
    protected $token;

    /**
     * ID do usuário
     *
     * @var int
     */
    protected $idUser;

    /**
     * Lista de erros
     *
     * @var array
     */
    protected $errors;

    /**
     * Aqruivo para enviar com a imagem
     *
     * @var file
     */
    protected $pathArquivo;

    /**
     * Status do usuário
     *
     * @var array
     */
    protected $stt;

    /**
     * Serviço de WhatsApp
     *
     * @var WhatsAppService
     */
    protected $whatsappService;

    protected $whatsGwService;

    /**
     * Tamanho do lote para processamento
     *
     * @var int
     */
    protected $tamanhoBatch = 15;

    /**
     * Construtor do job
     *
     * @param array $contatos Lista de contatos para envio
     * @param string $token Token de autenticação
     * @param int $idUser ID do usuário
     * @param array $stt Status do usuário e limites
     * @param array $errors Lista de erros pré-existentes (opcional)
     */
    public function __construct(array $contatos, string $token, int $idUser, array $stt, array $errors = [], $pathArquivo)
    {
        $this->contatos = $contatos;
        $this->token = $token;
        $this->idUser = $idUser;
        $this->stt = $stt;
        $this->errors = $errors;
        $this->pathArquivo = $pathArquivo;



        // Cria a instância do service já com o token (mantendo compatibilidade)
        // $this->whatsappService = new WhatsAppService(null, $this->token);
        $this->whatsGwService = new WhatsGwService();
    }

    /**
     * Execute o job
     *
     * @return void
     */
    public function handle(): void
    {
        // Verifica se o usuário ainda tem limite antes de iniciar
        if ($this->stt['userm']->sendedMsg >= $this->stt['userm']->msgLimit) {
            Log::warning('Job cancelado: Usuário não possui limite disponível', [
                'user_id' => $this->idUser,
            ]);
            return;
        }

        // Processa os contatos em lotes para melhor performance
        $lotes = array_chunk($this->contatos, $this->tamanhoBatch);

        /*Log::info('Iniciando processamento de mensagens em massa', [
            'total_contatos' => count($this->contatos),
            'total_lotes' => count($lotes),
            'user_id' => $this->idUser
        ]);*/

        // Processa cada lote
        foreach ($lotes as $indiceLote => $lote) {
            $this->processarLote($lote, $indiceLote + 1, count($lotes));
        }

        /*Log::info('Processamento de mensagens em massa concluído', [
            'total_enviados' => count($this->contatos) - count($this->errors),
            'total_erros' => count($this->errors),
            'user_id' => $this->idUser
        ]);*/
    }

    /**
     * Processa um lote de contatos
     *
     * @param array $lote Lote de contatos
     * @param int $numeroLote Número do lote atual
     * @param int $totalLotes Total de lotes
     * @return void
     */
    protected function processarLote(array $lote, int $numeroLote, int $totalLotes): void
    {
        // Busca o progresso existente ou cria um novo
        $conditions = ['user_id' => $this->idUser];

        /*Log::info("Processando lote {$numeroLote}/{$totalLotes}", [
            'contatos_no_lote' => count($lote),
            'user_id' => $this->idUser,
            'path_arquivo' => $this->pathArquivo,
        ]);*/

        // Inicializa ou atualiza o progresso no início do processamento
        EnvioProgresso::updateOrCreate(
            $conditions,
            [
                'total' => count($lote),
                'visto' => 0,
                'enviadas' => 0,
                'status' => 'em_andamento'
            ]
        );

        foreach ($lote as $indice => $contato) {
            /*Log::info("Enviando mensagem para contato {$indice} do lote {$numeroLote}", [
                'user_id' => $this->idUser,
                'contato' => $contato
            ]);*/

            // Verifica se o usuário ainda tem limite antes de cada envio
            if (!$this->planLimit()) {
                Log::warning('Limite de mensagens atingido durante o processamento', [
                    'user_id' => $this->idUser,
                    'lote_atual' => $numeroLote,
                    'mensagens_processadas' => (($numeroLote - 1) * $this->tamanhoBatch) + $indice
                ]);

                // Atualiza para status finalizado com erro
                EnvioProgresso::updateOrCreate(
                    $conditions,
                    [
                        'erro' => 'Limite de mensagens atingido',
                        'status' => 'finalizado',
                    ]
                );
                return;
            }

            // Log::info($contato);

            $this->enviarMensagem($contato);
            // Log::info($indice + 1);

            // Atualiza apenas o contador de enviadas
            EnvioProgresso::updateOrCreate(
                $conditions,
                [
                    'enviadas' => $indice + 1,
                ]
            );

            // Delay aleatório entre envios para evitar bloqueios
            if ($indice < count($lote) - 1) {
                sleep(rand(30, 33));  // Espera entre 30 e 33 segundos
            }
        }

        // Atualiza para status finalizado após concluir o lote
        EnvioProgresso::updateOrCreate(
            $conditions,
            [
                'status' => 'finalizado',
            ]
        );
    }

    /**
     * Envia uma mensagem para um contato
     *
     * @param array $contato Dados do contato
     * @return void
     */
    protected function enviarMensagem(array $contato): void
    {
        $name = $contato['name'] ?? 'desconhecido';

        try {
            $validate = PhoneValidator::validate($contato['number']);
            $validateNumberUser = PhoneValidator::validate($this->stt['userm']->number);
            $contatoMsg = $validate['number'];
            $numberValid = $validate['valid'];
            $mensagem = $contato['message'] ?? '';  // ou template, conforme estrutura
            $caminhoCompleto = storage_path('app/public/' . $this->pathArquivo);

            if ($numberValid) {

                Log::info($numberValid);

                if (is_file($caminhoCompleto)) {
                    // Lê o arquivo e converte para base64
                    $arquivoBase64 = base64_encode(file_get_contents($caminhoCompleto));
                    $arquivoNome = basename($caminhoCompleto);
                    $arquivoMime = mime_content_type($caminhoCompleto);

                    $this->whatsGwService->sendFile(
                        $validateNumberUser['number'],  // seu número (remetente)
                        $contatoMsg,  // número do destinatário
                        $arquivoBase64,  // base64 puro
                        $arquivoNome,  // nome do arquivo
                        $arquivoMime,  // mimetype
                        $mensagem  // legenda opcional
                    );

                } else {
                    Log::error("Arquivo não encontrado: $caminhoCompleto");
                    $this->whatsGwService->sendMessage(
                        $validateNumberUser['number'],  // seu número (remetente)
                        $contatoMsg,
                        $mensagem
                    );
                }
                $this->saveHistoric($this->idUser, $contatoMsg, 'success', $name, '-');
            } else {
                // Número inválido, registra erro
                $contatoMsg = $validate['number'] ?? 'desconhecido';
                Log::error("Descrição do Erro: ", [$validate['message']]);
                $this->saveHistoric($this->idUser, $contatoMsg, 'error', $name, $validate['message']);
            }
        } catch (Exception $e) {
            $contactNumber = $contato['number'] ?? 'desconhecido';

            Log::error($e->getMessage());
            $this->saveHistoric($this->idUser, $contactNumber, 'error', $name, 'Erro desconhecido ao enviar mensagem');
        }
    }

    /*protected function enviarMensagem(array $contato): void
    {
        Log::info($contato);
        try {
            // Verificação para garantir que o número está no formato correto
            $validate = PhoneValidator::validate($contato['number']);
            Log::info('========================================================');
            Log::info($validate);
            Log::info('========================================================');
            $contatoMsg = $validate['number'];
            $numberValid = $validate['valid'];

            /*if (!empty($number) && !str_contains($number, '@c.us')) {
                $number .= '@c.us';
            }

            Log::info("Caminho do arquivo: $this->pathArquivo");

            if ($this->pathArquivo) {
                $response = $this->whatsGwService->sendFile(
                    numeroUsuario(), // Número do usuário formatado
                    $contatoMsg,
                    $arquivo['base64'],
                    $arquivo['nome'],
                    $arquivo['mimetype'],
                    $mensagem
                );
            } else {
                $response = $this->whatsGwService->sendMessage(
                    $numero['number'],
                    $contato,
                    $mensagem
                );
            }

            // $response = $this->whatsGw->sendFile();

            /*$response = $this->whatsappService->sendMessage(
                $number,                    // Número formatado
                $contato['message'],        // Mensagem
                $contato['media'] ?? null   // Mídia (opcional)
            );*/

    /*$contactNumber = $contato['number'] ?? 'desconhecido';
            $contactName = $contato['name'] ?? 'desconhecido';

            if ($response->successful()) {
                Log::info("Mensagem enviada com sucesso para: {$this->mascaraNumeroCelular($contactNumber)}");
                // Salva histórico de sucesso
                $this->saveHistoric($this->idUser, $contactNumber, 'success', $contactName);
            } else {
                $erro = 'invalid_number';
                Log::error("Falha ao enviar para {$this->mascaraNumeroCelular($contactNumber)}: " . $erro);
                $this->errors[] = $contactNumber;

                // Salva histórico de erro
                $this->saveHistoric($this->idUser, $contactNumber, 'error', $contactName, $erro);
            }
        } catch (Exception $e) {
            $name = $contato['name'] ?? 'desconhecido';
            $contactNumber = $contato['number'] ?? 'desconhecido';

            Log::error("Erro ao enviar para $name ({$this->mascaraNumeroCelular($contactNumber)}): " . $e->getMessage());
            $this->errors[] = $contactNumber;

            // Salva histórico de erro
            $this->saveHistoric($this->idUser, $contactNumber, 'error', $name, $e->getMessage());
        }
    }*/

    /**
     * Verifica se o usuário tem limite disponível antes de iniciar
     *
     * @return bool
     */
    protected function verificarLimiteInicial(): bool
    {
        $user = $this->stt['userm'];
        $hasLimit = $this->stt['hasLimit'] ?? false;
        $enabled = $this->stt['enabled'] ?? false;

        if (!$hasLimit || !$enabled || $user->sendedMsg >= $user->msgLimit) {
            return false;
        }

        return true;
    }

    /**
     * Verifica e atualiza o limite do plano do usuário
     * Implementação mantida idêntica do código original para compatibilidade
     *
     * @return bool
     */
    protected function planLimit(): bool
    {
        $stt = $this->stt;
        $user = $stt['userm'];
        $hasLimit = $user->msgLimit > $user->sendedMsg;

        if ($user->sendedMsg >= $user->msgLimit) {
            Log::error('Você não tem saldo ou está bloqueado', [
                'user' => $stt['userm']->id,
                'sent' => $user->sendedMsg,
                'limit' => $user->msgLimit,
                'hasLimit' => $hasLimit,
                'enabled' => $user->enabled ?? false
            ]);
            return false;
        }

        $user->sendedMsg++;
        $user->save();

        return true;
    }

    /**
     * Salva o histórico de envio
     * Implementação mantida do código original
     *
     * @param int $userId ID do usuário
     * @param string $contact Número do contato
     * @param string $status Status do envio
     * @param string|null $name Nome do contato
     * @param string|null $errorType Tipo de erro
     * @return void
     */
    protected function saveHistoric($userId, $contact, $status, $name = null, $errorType = null): void
    {
        // $formatNumber = str_replace('@c.us', '', $contact);

        try {
            Historic::create([
                'user_id' => $userId,
                'contact' => $contact,
                'status' => $status,
                'name' => $name,
                'errorType' => $errorType
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao salvar histórico: ' . $e->getMessage());
        }
    }

    /**
     * Mascara o número de celular para logs (privacidade)
     *
     * @param string $numero Número completo
     * @return string Número mascarado
     */
    protected function mascaraNumeroCelular(string $numero): string
    {
        // Remove o sufixo @c.us se existir
        $numeroLimpo = str_replace('@c.us', '', $numero);

        // Se o número tiver menos de 6 caracteres, retorna parcialmente mascarado
        if (strlen($numeroLimpo) < 6) {
            return substr($numeroLimpo, 0, 2) . '****';
        }

        // Mantém os dois primeiros e os dois últimos dígitos visíveis
        $inicio = substr($numeroLimpo, 0, 2);
        $fim = substr($numeroLimpo, -2);
        $meio = str_repeat('*', strlen($numeroLimpo) - 4);

        return $inicio . $meio . $fim;
    }

    /**
     * Chamado quando o job falha
     *
     * @param Exception $exception
     * @return void
     */
    public function failed(Exception $exception): void
    {
        Log::critical('Job de envio em massa falhou', [
            'erro' => $exception->getMessage(),
            'user_id' => $this->idUser,
            'total_contatos' => count($this->contatos),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
