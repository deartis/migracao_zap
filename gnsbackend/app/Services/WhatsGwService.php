<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsGwService
{
    protected $apiUrl = 'https://app.whatsgw.com.br/api/WhatsGw';
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.whatsgw.apikey');
    }

    public function sendMessage($phoneNumber, $contactPhoneNumber, $messageBody, $messageType = 'text', $messageCustomId = null)
    {
        $response = Http::asForm()->post($this->apiUrl.'/Send', [
            'apikey' => $this->apiKey,
            'phone_number' => $phoneNumber,
            'contact_phone_number' => $contactPhoneNumber,
            'message_custom_id' => $messageCustomId ?? uniqid(),
            'message_type' => $messageType,
            'message_body' => $messageBody,
        ]);

        return $response->json();
    }
}
