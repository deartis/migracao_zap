<?php

namespace App\Imports;

use App\Models\Historic;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ContatosImport implements ToCollection
{
    public function collection(Collection $rows)
    {

        // Remove cabeçalho, se tiver — você pode pular essa parte se já trata cabeçalho fora
        // $rows = $rows->slice(1);

        $cleanRows = $rows->filter(function ($row) {
            // Limpa cada célula da linha
            $cleanedRow = $row->map(function ($value) {
                if (is_null($value)) return null;

                // Remove _x000D_ e espaços extras
                $cleaned = trim(str_replace('_x000D_', '', $value));

                // Se depois de limpar ficou vazio, vira null
                return $cleaned === '' ? null : $cleaned;
            });

            // Só mantém a linha se tiver algum valor útil (não tudo null)
            return $cleanedRow->filter()->isNotEmpty();
        })->values(); // reindexa o array (fica 0,1,2...)

        // Aqui você chama o processamento (Job, Controller, etc)
        foreach ($cleanRows as $row) {
            // Exemplo: dispatch(new ProcessContactJob($row));
            logger()->info('Linha limpa:', $row->toArray());
        }

        if ($rows->isEmpty()) {
            session()->flash('error', 'O arquivo está vazio.');
            return;
        }

        // Verificar se há dados além do cabeçalho
        $dataRows = $rows->slice(1);

        if ($dataRows->isEmpty()) {
            session()->flash('error', 'O arquivo não contém dados além do cabeçalho.');
            return;
        }

        // Analisar a estrutura das linhas para detectar automaticamente as colunas
        $columnInfo = $this->detectColumns($dataRows);

        if (!isset($columnInfo['phone']) || !isset($columnInfo['name'])) {
            session()->flash('error', 'Não foi possível identificar automaticamente as colunas de nome e telefone.');
            return;
        }

        $nameIndex = $columnInfo['name'];
        $phoneIndex = $columnInfo['phone'];

        // Processar as linhas
        $count = 0;
        $errors = 0;

        foreach ($dataRows as $rowIndex => $row) {
            // Verificar se a linha tem o formato esperado (é uma Collection)
            if (!($row instanceof Collection) || !isset($row[$nameIndex]) || !isset($row[$phoneIndex])) {
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

        $message = "Importação concluída: $count contatos adicionados com sucesso.";
        if ($errors > 0) {
            $message .= " $errors linhas foram ignoradas devido a dados inválidos.";
        }

        session()->flash('success', $message);
    }

    /**
     * Detecta automaticamente quais colunas contêm nomes e números de telefone
     */
    private function detectColumns($dataRows)
    {
        $result = [];
        $sampleSize = min(count($dataRows), 20); // Analisa até 20 linhas
        $columnScores = [];

        // Pegar a primeira linha para saber quantas colunas analisar
        $firstRow = $dataRows->first();
        if (!($firstRow instanceof Collection)) {
            return $result;
        }

        $columnCount = count($firstRow->toArray());

        // Inicializar pontuação para cada coluna
        for ($i = 0; $i < $columnCount; $i++) {
            $columnScores[$i] = [
                'phone' => 0,
                'name' => 0
            ];
        }

        // Analisar cada linha da amostra
        foreach ($dataRows->take($sampleSize) as $row) {
            if (!($row instanceof Collection)) {
                continue;
            }

            // Analisar cada coluna
            for ($i = 0; $i < $columnCount; $i++) {
                if (!isset($row[$i])) {
                    continue;
                }

                $value = $this->formatCell($row[$i]);

                if (empty($value)) {
                    continue;
                }

                // Pontuação para telefones
                $this->scorePhoneValue($value, $columnScores, $i);

                // Pontuação para nomes
                $this->scoreNameValue($value, $columnScores, $i);
            }
        }

        // Encontrar a melhor coluna para telefone
        $bestPhoneScore = -1;
        $bestPhoneIndex = -1;

        // Encontrar a melhor coluna para nome
        $bestNameScore = -1;
        $bestNameIndex = -1;

        for ($i = 0; $i < $columnCount; $i++) {
            // Verificar se esta coluna é melhor para telefone
            if ($columnScores[$i]['phone'] > $bestPhoneScore) {
                $bestPhoneScore = $columnScores[$i]['phone'];
                $bestPhoneIndex = $i;
            }

            // Verificar se esta coluna é melhor para nome
            if ($columnScores[$i]['name'] > $bestNameScore) {
                $bestNameScore = $columnScores[$i]['name'];
                $bestNameIndex = $i;
            }
        }

        // Garantir que não estamos usando a mesma coluna para ambos
        if ($bestPhoneIndex === $bestNameIndex) {
            // Se houver empate, decidir pela maior diferença entre as pontuações
            if ($columnScores[$bestPhoneIndex]['phone'] - $columnScores[$bestPhoneIndex]['name'] >
                $columnScores[$bestNameIndex]['name'] - $columnScores[$bestNameIndex]['phone']) {

                // A coluna é melhor para telefone, encontrar a segunda melhor para nome
                $secondBestNameScore = -1;
                $secondBestNameIndex = -1;

                for ($i = 0; $i < $columnCount; $i++) {
                    if ($i !== $bestPhoneIndex && $columnScores[$i]['name'] > $secondBestNameScore) {
                        $secondBestNameScore = $columnScores[$i]['name'];
                        $secondBestNameIndex = $i;
                    }
                }

                if ($secondBestNameIndex !== -1) {
                    $bestNameIndex = $secondBestNameIndex;
                }
            } else {
                // A coluna é melhor para nome, encontrar a segunda melhor para telefone
                $secondBestPhoneScore = -1;
                $secondBestPhoneIndex = -1;

                for ($i = 0; $i < $columnCount; $i++) {
                    if ($i !== $bestNameIndex && $columnScores[$i]['phone'] > $secondBestPhoneScore) {
                        $secondBestPhoneScore = $columnScores[$i]['phone'];
                        $secondBestPhoneIndex = $i;
                    }
                }

                if ($secondBestPhoneIndex !== -1) {
                    $bestPhoneIndex = $secondBestPhoneIndex;
                }
            }
        }

        // Definir os resultados apenas se as pontuações forem positivas
        if ($bestPhoneScore > 0) {
            $result['phone'] = $bestPhoneIndex;
        }

        if ($bestNameScore > 0) {
            $result['name'] = $bestNameIndex;
        }

        return $result;
    }                                                                                                                                                                                                                                                                                                                                                                                                                                                           

    /**
     * Pontua um valor quanto à probabilidade de ser um telefone
     */
    private function scorePhoneValue($value, &$columnScores, $columnIndex)
    {
        // Verificar se parece um telefone
        $numericValue = preg_replace('/\D+/', '', $value);

        // Se for um número de telefone válido (entre 10 e 15 dígitos)
        if (strlen($numericValue) >= 10 && strlen($numericValue) <= 15) {
            $columnScores[$columnIndex]['phone'] += 5;

            // Bônus para formatos comuns de telefone brasileiro
            if (preg_match('/^(\+?55)?\s*\(?(\d{2})\)?\s*(\d{4,5})[- ]?(\d{4})$/', $value)) {
                $columnScores[$columnIndex]['phone'] += 3;
            }

            // Bônus para números que começam com 55 (Brasil)
            if (preg_match('/^55/', $numericValue)) {
                $columnScores[$columnIndex]['phone'] += 2;
            }
        }

        // Penalidade para valores que parecem datas
        if ($this->looksLikeDate($value)) {
            $columnScores[$columnIndex]['phone'] -= 10;
        }

        // Penalidade para valores que parecem horas
        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value)) {
            $columnScores[$columnIndex]['phone'] -= 10;
        }

        // Penalidade para valores muito curtos ou muito longos
        if (strlen($numericValue) < 8 || strlen($numericValue) > 20) {
            $columnScores[$columnIndex]['phone'] -= 3;
        }

        // Penalidade para valores que têm palavras (nome provável)
        if (preg_match('/[A-Za-z]{3,}/', $value)) {
            $columnScores[$columnIndex]['phone'] -= 5;
        }
    }

    /**
     * Pontua um valor quanto à probabilidade de ser um nome
     */
    private function scoreNameValue($value, &$columnScores, $columnIndex)
    {
        // Se tiver letras (caracteres alfabéticos)
        if (preg_match('/[A-Za-z]/', $value)) {
            $columnScores[$columnIndex]['name'] += 3;

            // Bônus para valores que contêm espaços (nomes completos)
            if (strpos($value, ' ') !== false) {
                $columnScores[$columnIndex]['name'] += 2;
            }

            // Bônus para valores com padrão de nome (primeira letra maiúscula)
            if (preg_match('/^[A-Z][a-z]+(\s+[A-Z][a-z]+)+$/', $value)) {
                $columnScores[$columnIndex]['name'] += 5;
            }

            // Bônus para valores com comprimento típico de nomes
            $wordCount = count(explode(' ', $value));
            if ($wordCount >= 2 && $wordCount <= 5) {
                $columnScores[$columnIndex]['name'] += 2;
            }

            // Bônus para valores com termos comuns em nomes
            if (preg_match('/\b(DA|DE|DOS|DAS|SILVA|SANTOS|OLIVEIRA|SOUZA)\b/i', $value)) {
                $columnScores[$columnIndex]['name'] += 3;
            }
        }

        // Penalidade para valores que parecem numéricos
        if (is_numeric($value) || preg_match('/^\d+$/', $value)) {
            $columnScores[$columnIndex]['name'] -= 10;
        }

        // Penalidade para valores que parecem datas ou horas
        if ($this->looksLikeDate($value) || preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value)) {
            $columnScores[$columnIndex]['name'] -= 10;
        }

        // Penalidade para valores muito curtos
        if (strlen($value) < 5) {
            $columnScores[$columnIndex]['name'] -= 3;
        }
    }

    /**
     * Verifica se um valor parece uma data
     */
    private function looksLikeDate($value)
    {
        // Formatos comuns de data
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{2,4}$/', $value)) {
            return true;
        }

        if (preg_match('/^\d{1,2}-\d{1,2}-\d{2,4}$/', $value)) {
            return true;
        }

        if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $value)) {
            return true;
        }

        // Datas abreviadas
        if (preg_match('/^\d{1,2}\/\d{1,2}$/', $value)) {
            return true;
        }

        return false;
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
        if ((strlen($value) === 10 || strlen($value) === 11) && !str_starts_with($value, '55')) {
            $value = '55' . $value;
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
