<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WebhookController extends Controller
{
    public function receberQrCode(Request $request)
    {
        // Loga a requisição para debug
        //Log::info('Webhook QRCode recebido: ', $request->all());

        $event = $request->input('event');

        // Verifica se é um evento de QRCode
        if ($event === 'qrcode') {
            $base64Image = $request->input('qrcode'); // Ex: data:image/png;base64,iVBORw0...

            // Extrai apenas o conteúdo base64 (remove o prefixo data:image/png;base64,)
            if (preg_match('/^data:image\/png;base64,/', $base64Image)) {
                $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
            }

            // Decodifica o base64
            $imageData = base64_decode($base64Image);

            // Define nome único do arquivo
            $filename = 'qrcodes/qrcode_'.$request->input('w_instancia_id').'.png';

            // Salva no disco "public" (storage/app/public/qrcodes)
            Storage::disk('public')->put($filename, $imageData);

            Log::info('QR Code salvo com sucesso em: ' . $filename);
        }

        return response()->json(['status' => 'ok']);
    }
}
