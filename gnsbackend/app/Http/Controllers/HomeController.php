<?php

namespace App\Http\Controllers;

use App\Models\Historic;
//use Illuminate\Http\Request;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Http;
class HomeController extends Controller
{
    public function index()
    {
        // orderBy('created_at', 'desc')->paginate(10);
        $historico = Historic::where('user_id', auth()->id())->orderByDesc('updated_at')->paginate(5);
        $user = auth()->user();
        $counttotalErros = Historic::where('status', 'error')->where('user_id', auth()->id())->count();

        $limiteMensagem = $user->msgLimit;
        $mensagensEnviadas = $user->sendedMsg - $counttotalErros;
        $usoPacoteCiclo = ($mensagensEnviadas / $limiteMensagem) * 100;
        $usoPacoteCiclo = intval($usoPacoteCiclo);

        $totalErros = ($counttotalErros / $limiteMensagem) * 100;
        $totalErros = intval($totalErros);

        return view('pages.home', [
            'historico' => $historico,
            'usoPacoteCiclo' => $usoPacoteCiclo,
            'totalErros' => $totalErros,
            'contaErros' => $counttotalErros,
        ]);
    }
}
