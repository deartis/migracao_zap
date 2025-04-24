<?php

namespace App\Http\Controllers;

use App\Models\Historic;

class HistoricController extends Controller
{
    public function index()
    {
        $totalContatos = Historic::count();
        $enviadas = Historic::where('status', 'sucesso')->count();
        $comErro = Historic::where('status', 'erro')->count();

        $historico = Historic::latest()->take(20)->get();

        return view('pages.historic', compact('totalContatos', 'enviadas', 'comErro', 'historico'));

        //return view('pages.historic');
    }
}
