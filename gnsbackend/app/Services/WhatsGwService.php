<?php

namespace App\Services;

use App\Events\ConnectionStatusChanged;
use App\Models\Instances;
use Endroid\QrCode\Logo\Logo;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\PhoneValidator;

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

        Log::info($response->json());
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

            $response = Http::asForm()->post(config('whatsgw.apiUrl').'/NewInstance', [
                'apikey' => $this->apiKey,
                'type' => '1',
            ]);

            $dados = $response->json();

            Log::info($dados);
            Log::info('------------------------------------------------------------------');

            $instanciaId = $dados['w_instancia_id'];

            if($dados['result'] === 'success'){
                $filename = "qrcodes/qrcode_$instanciaId.png";
                Log::info("Salvou", [
                    'filename' => $filename,
                    'instancia' => $instanciaId,
                ]);
                Instances::updateOrCreate([
                    'user_id' => $user->id,
                    'instance_id' => $dados['w_instancia_id'],
                    'token' => $user->id,
                    'qrcode' => $filename
                ]);

                Log::info("Salvando o ID da Instância no Banco");
                $user->instance_id = $dados['w_instancia_id'];
                $user->save();

            }

        } catch (Exception $exception) {
            Log::error("Erro: ", [$exception->getMessage()]);
            return redirect()->route('home')->with('error', 'Houve um erro ao tentar criar uma nova intância');
        }

        Log::info("Nova Intsnacia: {$dados['w_instancia_id']}");
        return $response->json();
    }

    public function getStatus($instanceId)
    {
        try {
            $response = Http::asForm()->post($this->apiUrl . '/PhoneState', [
                'apikey' => $this->apiKey,
                'w_instancia_id' => $instanceId,
            ]);
            Log::info('=========================');
            Log::info($response);
            Log::info('=========================');

            return $response->json();

        } catch (Exception $exception) {
            Log::error("Erro ao obter status: ", [$exception->getMessage()]);
            return ['status' => 'error', 'message' => 'Erro ao obter status da instância'];
        }
    }

    public function getActiveChats(){
        try{
            $number = PhoneValidator::validate(auth()->user()->number);

            $response = Http::asForm()->post($this->apiUrl . '/GetAllChats', [
                'apikey' => $this->apiKey,
                'phone_number' => $number['number'],
            ]);

            $data = $response->json();
            //Log::info("Chats ativos: ", [$number,$data]);

            return $data;

        } catch (Exception $exception) {
            Log::error("Erro ao obter chats ativos: ", [$exception->getMessage()]);
            return ['status' => 'error', 'message' => 'Erro ao obter chats ativos'];
        }
    }
}
