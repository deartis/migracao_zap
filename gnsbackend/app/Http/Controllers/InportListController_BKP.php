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

class InportListController_BKP extends Controller
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
        \Log::info("========================================================");
        \Log::info("===================== Log do CTRL ======================");
        \Log::info("========================================================");
        //Recupera o template do array para salvar no DB
        $tpl = $request->template;
        $stt = verificaStatusUser();
        $user = $stt['userm'];
        $limit = $user->sendedMsg >= $user->msgLimit;

        //===================================================//
        // Verifica se tem o template no array
        //==================================================//

        if($limit){
           \Log::error("Você usou 100% do seu plano");
            return response()->json(['message' => 'Você usou 100% do seu plano!']);
        }
        if (isset($tpl)) {
            \Log::info('Salvando o template como última mensagem');

            //Salva o template no Array
            auth()->user()->update([
                'lastMessage' => $tpl
            ]);

            //Retira a key template do array
            unset($request['template']);
        } else {
            \Log::info('Deu Ruim, não achei o template');

            //Retorna uma mensagem avisando que não há mensagem no request
            return response()->json(['message' => 'Você não digitou a mensagem']);
        }


        if(!$stt['hasLimit'] || !$stt['enabled']){
            \Log::info("Você está sem saldo para mensagem ou está bloqueado\n Entre em contato com o suporte.");
         return  response()->json(['message'=> 'Você usou 100% do seu pacote!']);
        }

        $verifica = new ArrayDataDetector();
        $contatos = $request->contacts;
        $contatosF = [];
        $contatosFinais = [];
        $erros = $this->verificaContatos($contatos);

        foreach ($contatos as $contato) {
            $contatosF[] = $contato;
        }

        $result = $verifica->extractContacts($contatosF)['contacts'];

        foreach ($result as $re) {
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

        //\Log::info($contatos);
        \Log::info(" ");
        \Log::info("========================================================");
        \Log::info("===================== Log do Job =======================");
        \Log::info("========================================================");
        MensagensEmMassaJob::dispatch(
            $contatosFinais,
            token_user(),
            auth()->id(),
            $stt,
            $erros,
        );

        return response()->json(['message' => 'Mensagens sendo executadas...']);
    }


    private function verificaContatos($arrayContatos)
    {
        $verifica = new ArrayDataDetector();
        return $verifica->extractContacts($arrayContatos);
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

}
