<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsGwService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsgw.apiUrl');
        $this->apiKey = config('services.whatsgw.apikey');
    }

    public function sendMessage($phoneNumber, $contactPhoneNumber, $messageBody, $messageType = 'text', $messageCustomId = null)
    {
        $response = Http::asForm()->post($this->apiUrl . '/Send', [
            'apikey' => $this->apiKey,
            'phone_number' => $phoneNumber,
            'contact_phone_number' => $contactPhoneNumber,
            'message_custom_id' => $messageCustomId ?? uniqid(),
            'message_type' => $messageType,
            'message_body' => $messageBody,
        ]);

        return $response->json();
    }

    public function sendFile($phoneNumber, $contactPhoneNumber, $base64File, $fileName, $mimeType, $caption = '')
    {
        $response = Http::asForm()->post($this->apiUrl . '/Send', [
            'apikey' => $this->apiKey,
            'phone_number' => $phoneNumber,
            'contact_phone_number' => $contactPhoneNumber,
            'message_custom_id' => uniqid(),
            'message_type' => 'document',
            'message_body' => $base64File,
            'message_body_filename' => $fileName,
            'message_body_mimetype' => $mimeType,
            'message_caption' => $caption,
        ]);

        return $response->json();
    }

    public function newStance()
    {
        $user = auth()->user();

        try {
            $response = Http::asForm()->post('https://app.whatsgw.com.br/api/WhatsGw/NewInstance', [
                'apikey' => $this->apiKey,
                'type' => '1',
            ]);

            $dados = $response->json();
            $user->instance_id = $dados['w_instancia_id'];
            $user->save();

        } catch (Exception $exception) {
            Log::error("Erro: ", [$exception->getMessage()]);
            return redirect()->route('home')->with('error', 'Houve um erro ao tentar criar uma nova intância');
        }

        Log::info("Nova Intsnacia: {$dados['w_instancia_id']}");
        //return $response->json();
    }
}
