<?php

namespace App\Http\Controllers;

use App\Models\Historic;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        //orderBy('created_at', 'desc')->paginate(10);
        $historico = Historic::where('user_id', auth()->id())->orderByDesc('updated_at')->paginate(10);
        $user = auth()->user();

        //dd(auth()->id());

        //=====================================================
        // Porcentagem de uso do plano
        //=====================================================
        $limiteMensagem = $user->msgLimit;
        $mensagensEnviadas = $user->sendedMsg;
        $usoPacoteCiclo = ($mensagensEnviadas / $limiteMensagem) * 100;
        $usoPacoteCiclo = intval($usoPacoteCiclo);

        //=====================================================
        // Total de erros
        //=====================================================
        $totalErros = Historic::where('status', 'error')->where('user_id', auth()->id())->count();

        //dd($limiteMensagem, $mensagensEnviadas, $usoPacoteCiclo);
        //$usoPacoteCiclo = null;

        return view('pages.home', [
            'historico' => $historico,
            'usoPacoteCiclo' => $usoPacoteCiclo,
            'totalErros' => $totalErros,
        ]);
    }
}
