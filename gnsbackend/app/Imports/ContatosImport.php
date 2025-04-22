<?php

namespace App\Imports;

use App\Models\Historic;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;

class ContatosImport implements ToCollection
{
    public $dados;

    public function collection(Collection $rows)
    {
        //Pega o cabeçalho (primeira linha)
        $header = $rows->first();
        $nomeIndex = null;

        $nome = null; //Variável da coluna obrigatória
        //Localiza a coluna "titulo" (exatamente assim)
        foreach ($header as $index => $coluna) {
            $nome = strtolower(trim(rm_acentos($coluna)));
            if ($nome === 'nome' || $nome === 'name' || $nome === 'nomes' || $nome === 'names') {
                $nomeIndex = $index;

                break;
            }
        }

        if (is_null($nomeIndex)) {
            return back()->with('error', 'A planilha deve conter a coluna "Nome" ou "Nomes".');
        }else{
            Historic::where('user_id', auth()->id())->delete();
        }

        // Altera a partir da segunda linha
        foreach ($rows->skip(1) as $row) {
            $nome = $row[$nomeIndex] ?? null;

            if (!$nome) continue;

            $numero = null;

            // Procura pela primeira linha que contém o número de telefone
            foreach ($row as $index => $value) {
                if ($index == $nomeIndex) continue; // Pula a coluna do título

                $numeroLimpo = preg_replace('/\D+/', '', $value); // remove tudo que não é número

                if (strlen($numeroLimpo) >= 8) {
                    $numero = $numeroLimpo;
                    break;
                }
            }

            if ($numero) {
                Historic::create([
                    'user_id' => auth()->id(),
                    'name' => $nome,
                    'contact' => $numero,
                    'status' => 'enviado',

                ]);
            }
        }
    }
}
