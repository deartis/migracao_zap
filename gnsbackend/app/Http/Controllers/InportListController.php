<?php

namespace App\Http\Controllers;

use App\Imports\ContatosImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class InportListController extends Controller
{
    public function index()
    {
        return view('pages.from-sheet');
    }

    public function uploadSheet(Request $request){

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls,xml|max:2048',
        ]);

        $importador = new ContatosImport();
        Excel::import($importador, $request->file('csv_file'));

        $dados = $importador->dados;

        return view('pages.from-sheet',[
            'dados' => $dados,
            'success' => 'Arquivo importado com sucesso!',
        ]);


        /*$request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:2048',
        ]);

        //dd($request);

        $file = $request->file('csv_file');

        // Pode salvar no estorage
        $path = $file->storeAs('csv', uniqid(). '_'. $file->getClientOriginalName(), 'public');

        dd($path);

        // Aqui lê e trata o arquivo
        return redirect()->route('page.from.sheet')->with('success', 'Arquivo salvo com sucesso!');*/
    }
}
