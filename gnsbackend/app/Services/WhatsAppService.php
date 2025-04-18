<?php
// app/Services/WhatsAppService.php
namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $baseUrl;
    protected $token;

    public function __construct($baseUrl = null, $token = null)
    {
        $this->baseUrl = $baseUrl ?? env('WHATSAPP_API_URL', 'http://localhost:3000');
        $this->token = $token ?? env('WHATSAPP_API_TOKEN', 'CQpfjesS/ny0UFCNijptmQM2z1enBxLBY6Sq1C6e9d4=');
    }

    /**
     * Faz requisição HTTP para a API do WhatsApp
     *
     * @param string $endpoint
     * @param string $method
     * @param array $data
     * @param string|null $userToken
     * @return Response
     */
    protected function request(string $endpoint, string $method = 'GET', array $data = [], string $userToken = null): Response
    {
        $token = $userToken ?? $this->token;

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json'
            ])->timeout(30);

            if ($method === 'GET') {
                return $response->get("{$this->baseUrl}/{$endpoint}", $data);
            } else {
                return $response->post("{$this->baseUrl}/{$endpoint}", $data);
            }
        } catch (\Exception $e) {
            Log::error("WhatsApp API Error: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Inicia uma conexão com o WhatsApp
     */
    public function startWhatsApp(string $userToken = null)
    {
        return $this->request('start-whatsapp', 'GET', [], $userToken);
    }

    /**
     * Verifica o estado da conexão
     */
    public function checkConnection(string $userToken = null)
    {
        return $this->request('check-connection', 'GET', [], $userToken);
    }

    /**
     * Envia uma mensagem pelo WhatsApp
     */
    public function sendMessage(string $number, string $message, string $media = null, string $userToken = null)
    {
        $data = [
            'number' => $number,
            'message' => $message,
        ];

        if ($media) {
            $data['media'] = $media;
        }

        return $this->request('send-message', 'POST', $data, $userToken);
    }

    /**
     * Deleta uma sessão existente
     */
    public function deleteSession(string $userToken = null)
    {
        return $this->request('delete-session', 'GET', [], $userToken);
    }

    /**
     * Verifica saúde do servidor WhatsApp
     */
    public function healthCheck()
    {
        return $this->request('health', 'GET');
    }
}
