<?php

namespace App\Http\Controllers;

use App\Models\Historic;

class HistoricController extends Controller
{
    public function index()
    {
        $user = auth()->id();
        $totalContatos = Historic::where('user_id', $user)->count();
        $enviadas = Historic::where('status', 'enviado')->where('user_id', $user)->count();
        $comErro = Historic::where('status', 'erro')->where('user_id', $user)->count();

        $historico = Historic::latest()->take(20)->where('user_id', $user)->get();

        return view('pages.historic', compact('totalContatos', 'enviadas', 'comErro', 'historico'));

        //return view('pages.historic');
    }
}
