<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendMessageRequest;
use App\Jobs\SendWhatsAppMessageJob;
use App\Models\Historic;
use App\Models\Instances;
use App\Services\ArrayDataDetector;
use App\Services\ContatosJsonProcessor;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;
use Log;

class WhatsAppController extends Controller
{
    public function showQRCode(Request $request){
        //Verifica de já existe uma sessão ativa
        $activeSession = Instances::where('user_id', auth()->id())
        ->where('status', 'connected')
        ->first();

        if($activeSession){
            return redirect()->route('home')->with('error', 'Você já está condectado!');
        }

        // Chama a API do whatsgw para gerar p QRCode
    }

    public function dashboard(Request $request)
    {
        // Verifica se o usuário já está conectado
        $activeSession = Instances::where('user_id', auth()->id())
            ->where('status', 'connected')
            ->first();

        if ($activeSession) {
            return redirect()->route('home')->with('error', 'Você já está conectado!');
        }

        // Chama a API do whatsgw para gerar o QR Code
        $whatsAppService = new WhatsAppService();
        $qrCodeData = $whatsAppService->generateQRCode();

        return view('pages.dashboard', compact('qrCodeData'));
    }

    public function status(Request $request)
    {
        // Verifica se o usuário já está conectado
        $activeSession = Instances::where('user_id', auth()->id())
            ->where('status', 'connected')
            ->first();

        if ($activeSession) {
            return response()->json(['status' => 'connected']);
        }

        // Se não estiver conectado, retorna o status desconectado
        return response()->json(['status' => 'disconnected']);
    }
}

