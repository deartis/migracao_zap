<?php

namespace App\Http\Controllers;

use App\Models\Historic;

class HistoricController extends Controller
{
    public function index()
    {
        $user = auth()->id();
        $totalContatos = Historic::where('user_id', $user)->count();
        $enviadas = Historic::where('status', 'success')->where('user_id', $user)->count();
        $comErro = Historic::where('status', 'error')->where('user_id', $user)->count();

        $historico = Historic::where('user_id', auth()->id())->orderByDesc('updated_at')->paginate(10);

        return view('pages.historic', compact('totalContatos', 'enviadas', 'comErro', 'historico'));

        //return view('pages.historic');
    }
}
