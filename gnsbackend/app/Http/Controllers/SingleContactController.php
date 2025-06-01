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

        $contato = $request->contato;
        $mensagem = $request->mensagem;
        $arquivo = $request->arquivo;

        if ($arquivo) {
            $response = $this->whatsGwService->sendFile(
                $numero['number'],
                $contato,
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

        return response()->json($response);
    }

    public function importarChats()
{
    $apiKey = config('whatsgw.apiKey');
    $apiUrl = config('whatsgw.apiUrl');
    $phoneNumber = numeroUsuario();

    $response = Http::asForm()->post($apiUrl.'/GetAllChats', [
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
