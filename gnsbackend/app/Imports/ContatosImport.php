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
        $header = $rows->first();
        $nomeIndex = null;

        foreach ($header as $index => $coluna) {
            $nome = strtolower(trim(rm_acentos($coluna)));
            if (in_array($nome, ['nome', 'nomes', 'name', 'names'])) {
                $nomeIndex = $index;
                break;
            }
        }

        if (is_null($nomeIndex)) {
            session()->flash('error', 'A planilha deve conter a coluna "Nome" ou "Nomes".');
            return;
        }

        foreach ($rows->skip(1) as $row) {
            $nome = $row[$nomeIndex] ?? null;
            if (!$nome) continue;

            $numero = null;

            foreach ($row as $index => $value) {
                if ($index == $nomeIndex) continue;

                $numeroLimpo = preg_replace('/\D+/', '', $value);

                if (strlen($numeroLimpo) >= 8) {
                    $numero = $numeroLimpo;
                    break;
                }
            }

            if ($numero) {
                // Verifica se esse número já existe para o usuário atual
                $jaExiste = Historic::where('user_id', auth()->id())
                    ->where('contact', $numero)
                    ->exists();

                if (!$jaExiste) {
                    Historic::create([
                        'user_id' => auth()->id(),
                        'name' => $nome,
                        'contact' => $numero,
                        'status' => 'enviado',
                    ]);
                }
            }
        }

        session()->flash('success', 'Lista importada com sucesso!');
    }
}
