<?php

namespace App\Http\Controllers;

use App\Services\PhoneValidator;
use App\Services\WhatsGwService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SingleContactController extends Controller
{

    protected $whatsGwService;

    public function __construct(WhatsGwService $whatsGwService)
    {
        $this->whatsGwService = $whatsGwService;
    }

    public function index()
    {
        //dd($this->importarChats());
        return view('pages.single-contact');
    }

    public function send(Request $request)
    {
        $user = auth()->user();
        $numero = $user->number;
        $numero = PhoneValidator::validate($numero);

        if (!$numero['valid']) {
            return response()->json([
                'mensagem' => 'Houve um problema com o número de telefone cadastrado, contate o suporte.',
                'error' => $numero['error'],
                'msg' => $numero['message']
            ]);
        }

            Log::info($request->contato);

        $response = $this->whatsGwService->sendMessage(
            $numero['number'],
            $request->contato,
            $request->mensagem
        );

        return response()->json($response);
    }

public function importarChats()
{
    $apiKey = env('WHATSGW_APIKEY'); // coloque no .env
    $phoneNumber = numeroUsuario(); // ex: 5511999999999

    $response = Http::asForm()->post('https://app.whatsgw.com.br/api/WhatsGw/GetAllChats', [
        'apikey' => $apiKey,
        'phone_number' => $phoneNumber,
    ]);

    if ($response->successful()) {
        $chats = $response->json(); // aqui deve vir uma lista com remoteJid e nome

        return  $chats;
    }

    return back()->with('error', 'Erro ao buscar os contatos do WhatsGW');
}

}
