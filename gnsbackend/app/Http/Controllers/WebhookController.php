<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WebhookController extends Controller
{

    public function receberQrCode(Request $request)
    {
        Log::info('Webhook QRCode recebido: ', $request->all());

        if ($request->input('event') !== 'qrcode') {
            return response()->json(['status' => 'ignored']);
        }

        $base64Image = $request->input('qrcode');
        $instanciaId = $request->input('w_instancia_id');

        // Remove o prefixo data:image/png;base64,
        $base64Image = preg_replace('/^data:image\/[^;]+;base64,/', '', $base64Image);

        $imageData = base64_decode($base64Image);
        $filename = "qrcodes/qrcode_$instanciaId.png";

        Storage::disk('public')->put($filename, $imageData);

        Log::info("QR Code salvo em: $filename");

        return response()->json(['status' => 'ok']);
    }
    
}
