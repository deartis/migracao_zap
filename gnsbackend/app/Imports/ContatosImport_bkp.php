<?php

namespace App\Imports;

use App\Models\Historic;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ContatosImport_bkp implements ToCollection
{
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            session()->flash('error', 'O arquivo está vazio.');
            return;
        }

        // Removendo a primeira linha (cabeçalho)
        $dataRows = $rows->slice(1);

        if ($dataRows->isEmpty()) {
            session()->flash('error', 'O arquivo não contém dados além do cabeçalho.');
            return;
        }

        // Índices para nome e telefone na sua estrutura específica
        $nameIndex = 4;  // Coluna E (índice 4)
        $phoneIndex = 5; // Coluna F (índice 5)

        // Para debug - remover após teste
        // dd("Primeira linha:", $dataRows->first());

        // Processar as linhas
        $count = 0;
        $errors = 0;

        foreach ($dataRows as $rowIndex => $row) {
            // Verificar se a linha tem o formato esperado (é uma Collection)
            if (!($row instanceof Collection)) {
                $errors++;
                continue;
            }

            // Verificar se as colunas necessárias existem
            if (!isset($row[$nameIndex]) || !isset($row[$phoneIndex])) {
                $errors++;
                continue;
            }

            $nome = $this->formatCell($row[$nameIndex]);
            $telefone = $this->formatCell($row[$phoneIndex]);

            // Limpar e validar o telefone
            $telefone = $this->sanitizePhoneNumber($telefone);

            // Verificar se temos dados válidos
            if (empty($nome) || empty($telefone)) {
                $errors++;
                continue;
            }

            // Para debug - remover após teste
            // dd("Nome: $nome, Telefone: $telefone");

            // Verificar se já existe
            $jaExiste = Historic::where('user_id', auth()->id())
                ->where('contact', $telefone)
                ->exists();

            if (!$jaExiste) {
                Historic::create([
                    'user_id' => auth()->id(),
                    'name' => $nome,
                    'contact' => $telefone,
                    'status' => 'enviado',
                ]);
                $count++;
            }
        }

        $message = "Importação concluída: $count contatos adicionados.";
        if ($errors > 0) {
            $message .= " $errors linhas foram ignoradas devido a dados inválidos.";
        }

        session()->flash('success', $message);
    }

    private function sanitizePhoneNumber($value)
    {
        if (empty($value)) {
            return null;
        }

        // Remover espaços, traços, parênteses e outros caracteres não numéricos
        $value = preg_replace('/\D+/', '', $value);

        // Validar tamanho mínimo
        if (strlen($value) < 10) {
            return null;
        }

        // Verificar se é um formato de data (ddmmyyyy)
        if (preg_match('/^(\d{2})(\d{2})(\d{4})$/', $value, $matches)) {
            $day = (int)$matches[1];
            $month = (int)$matches[2];
            $year = (int)$matches[3];

            // Se parece uma data válida, rejeitar
            if ($day <= 31 && $day > 0 && $month <= 12 && $month > 0 && ($year >= 2000 && $year <= 2030)) {
                return null;
            }
        }

        // Para telefones brasileiros, garantir que comecem com 55
        if (strlen($value) === 10 || strlen($value) === 11) {
            // Se não começar com 55, adicionar
            if (!str_starts_with($value, '55')) {
                $value = '55' . $value;
            }
        }

        // Verificar se não é só repetição do mesmo dígito
        if (preg_match('/^(\d)\1+$/', $value)) {
            return null;
        }

        return $value;
    }

    private function formatCell($value)
    {
        if ($value === null) {
            return null;
        }

        // Converter para string
        $value = (string)$value;

        // Remover _x000D_ (caractere especial do Excel)
        $value = str_replace('_x000D_', '', $value);

        // Remover caracteres invisíveis
        $value = preg_replace('/[\x00-\x1F\x7F]+/u', '', $value);

        // Normalizar espaços
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }
}
