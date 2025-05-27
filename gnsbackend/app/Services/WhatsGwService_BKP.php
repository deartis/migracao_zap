<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsGwService_BKP
{
    protected $apiUrl = 'https://app.whatsgw.com.br/api/WhatsGw';
    protected $apiKey;

    public function __construct()
    {
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
        $response = Http::asForm()->post($this->apiUrl.'/Send', [
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


}
