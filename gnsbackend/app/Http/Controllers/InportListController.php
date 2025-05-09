<?php

namespace App\Http\Controllers;

use App\Imports\ContatosImport;
use App\Jobs\MensagensEmMassaJob;
use App\Models\Historic;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;
use App\Services\ArrayDataDetector;

class InportListController extends Controller
{
    public function index()
    {
        $contatos = Historic::where('user_id', auth()->id())
        ->latest()
        ->get();

        return view('pages.from-sheet', compact('contatos'));
    }

    // Enviar mensagem em massa para a lista
    public function enviaMensagemEmMassaLista(Request $request)
    {
        $verifica = new ArrayDataDetector();
        $contatos = $request->contacts;
        $contatosF = [];
        $contatosFinais = [];
        $erros = $this->verificaContatos($contatos);

        foreach($contatos as $contato){
            $contatosF[] = $contato;
        }

        $result = $verifica->extractContacts($contatosF)['contacts'];

        foreach ($result as $re){
            $phoneWithSuffix = $re['phone'];
            if (!str_contains($phoneWithSuffix, '@c.us')) {
                $phoneWithSuffix .= '@c.us';
            }

            $contatosFinais[] = [
                'name' => $re['name'],
                'number' => $phoneWithSuffix,
                'message' => $re['metadata']['message'],
            ];
        }

        MensagensEmMassaJob::dispatch(
            $contatosFinais,
            token_user(),
            auth()->id(),
            $erros
        );

        return response()->json(['message' => 'Mensagens sendo executadas...']);
    }


    private function verificaContatos($arrayContatos){
        $verifica = new ArrayDataDetector();
        $contatosVerificados = $verifica->extractContacts($arrayContatos);

        return $contatosVerificados;
    }

    public function uploadSheet(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,xlsx,xls,xml,ods|max:2048',
        ]);

        $file = $request->file('csv_file');
        $extension = strtolower($file->getClientOriginalExtension());

        // Renomeia apenas para uso interno (não salva no disco)
        $renomeado = $file->move(sys_get_temp_dir(),
            Str::slug(pathinfo($file->getClientOriginalName(),
                PATHINFO_FILENAME)) . '__' . auth()->id() . '__' . time() . '.' . $extension);

        //dd($renomeado->getRealPath());
        $importador = new ContatosImport();

        if ($extension === 'csv') {
            Excel::import($importador, $renomeado->getRealPath(), ExcelFormat::CSV);
        } else {
            Excel::import($importador, $renomeado->getRealPath());
        }

        return redirect()->route('page.from.sheet')->with('success', 'Lista salva com sucesso!');
    }

    /*public function uploadSheet(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,xlsx,xls,xml,ods|max:2048',
        ]);

        $file = $request->file('csv_file');

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = Str::slug($originalName) . '_' .time() . '.' . $extension;

        $path = $file->storeAs('uploads', $filename);

        $importador = new ContatosImport();

        if ($extension === 'csv') {
            Excel::import($importador, storage_path('app/' . $path), ExcelFormat::CSV);
        } else {
            Excel::import($importador, storage_path('app/' . $path));
        }

        $dados = $importador->dados;

        return redirect()->route('page.from.sheet', [$dados])->with('success', 'Lista salva com sucesso!');
    }*/
}
