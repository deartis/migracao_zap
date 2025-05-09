<?php
namespace App\Services;
class ArrayDataDetector_bkp
{
    /**
     * Detecta automaticamente quais posições em arrays contêm nomes e números de telefone
     *
     * @param array $dataArrays Array de arrays a serem analisados
     * @return array Informações sobre os índices que contêm nomes e telefones
     */
    public function detectFields(array $dataArrays)
    {
        // Inicializar pontuações
        $columnScores = [];

        // Determinar o tamanho máximo da amostra
        $sampleSize = min(count($dataArrays), 20);  // Analisa até 20 arrays

        // Obter todas as chaves possíveis de todos os arrays
        $allKeys = [];
        for ($i = 0; $i < $sampleSize; $i++) {
            if (isset($dataArrays[$i]) && is_array($dataArrays[$i])) {
                $allKeys = array_merge($allKeys, array_keys($dataArrays[$i]));
            }
        }
        $allKeys = array_unique($allKeys);

        // Inicializar pontuação para cada coluna/chave
        foreach ($allKeys as $key) {
            $columnScores[$key] = [
                'phone' => 0,
                'name' => 0
            ];
        }

        // Armazenar valores para verificar duplicatas
        $columnValues = [];
        foreach ($allKeys as $key) {
            $columnValues[$key] = [];
        }

        // Analisar cada array da amostra
        for ($i = 0; $i < $sampleSize; $i++) {
            if (!isset($dataArrays[$i]) || !is_array($dataArrays[$i])) {
                continue;
            }

            $row = $dataArrays[$i];

            // Analisar cada campo do array
            foreach ($allKeys as $key) {
                if (!isset($row[$key])) {
                    continue;
                }

                $value = $this->formatCell($row[$key]);

                if (empty($value) && $value !== '0') {
                    continue;
                }

                // Armazenar valor para verificação de duplicatas
                $columnValues[$key][] = $value;

                // Pontuar para telefones
                $this->scorePhoneValue($value, $columnScores, $key);

                // Pontuar para nomes
                $this->scoreNameValue($value, $columnScores, $key);
            }
        }

        // Verificar duplicatas em cada coluna
        foreach ($allKeys as $key) {
            if (count($columnValues[$key]) > 0) {
                $this->checkDuplicateValues($columnValues[$key], $columnScores, $key);
            }
        }

        // Encontrar a melhor coluna para telefone e nome
        $bestPhoneKey = $this->findBestColumn($columnScores, 'phone');
        $bestNameKey = $this->findBestColumn($columnScores, 'name');

        // Imprimir pontuações para debug
        // echo "Pontuações de colunas:\n";
        // print_r($columnScores);

        // Garantir que não estamos usando a mesma coluna para ambos
        if ($bestPhoneKey !== null && $bestNameKey !== null && $bestPhoneKey === $bestNameKey) {

            // Decidir com base na diferença de pontuação
            $phoneDiff = $columnScores[$bestPhoneKey]['phone'] - $columnScores[$bestPhoneKey]['name'];
            $nameDiff = $columnScores[$bestNameKey]['name'] - $columnScores[$bestNameKey]['phone'];

            if ($phoneDiff > $nameDiff) {
                // Esta coluna é melhor para telefone, encontrar segunda melhor para nome
                $bestNameKey = $this->findSecondBestColumn($columnScores, 'name', $bestPhoneKey);
            } else {
                // Esta coluna é melhor para nome, encontrar segunda melhor para telefone
                $bestPhoneKey = $this->findSecondBestColumn($columnScores, 'phone', $bestNameKey);
            }
        }

        $result = [
            'nameField' => $bestNameKey,
            'phoneField' => $bestPhoneKey,
            'scores' => $columnScores
        ];

        return $result;
    }

    /**
     * Encontra a coluna com maior pontuação para um determinado tipo
     *
     * @param array $columnScores Pontuações das colunas
     * @param string $type Tipo (name ou phone)
     * @return string|int|null Chave da melhor coluna ou null se nenhuma tiver pontuação positiva
     */
    private function findBestColumn($columnScores, $type)
    {
        $bestScore = -1;
        $bestKey = null;

        foreach ($columnScores as $key => $scores) {
            if ($scores[$type] > $bestScore) {
                $bestScore = $scores[$type];
                $bestKey = $key;
            }
        }

        // Retornar apenas se a pontuação for positiva
        return $bestScore > 0 ? $bestKey : null;
    }

    /**
     * Encontra a segunda melhor coluna para um tipo, ignorando uma coluna específica
     *
     * @param array $columnScores Pontuações das colunas
     * @param string $type Tipo (name ou phone)
     * @param string|int $ignoreKey Chave a ser ignorada
     * @return string|int|null Chave da segunda melhor coluna
     */
    private function findSecondBestColumn($columnScores, $type, $ignoreKey)
    {
        $bestScore = -1;
        $bestKey = null;

        foreach ($columnScores as $key => $scores) {
            if ($key !== $ignoreKey && $scores[$type] > $bestScore) {
                $bestScore = $scores[$type];
                $bestKey = $key;
            }
        }

        // Retornar apenas se a pontuação for positiva
        return $bestScore > 0 ? $bestKey : null;
    }

    /**
     * Pontua um valor quanto à probabilidade de ser um telefone
     *
     * @param mixed $value Valor a ser analisado
     * @param array &$columnScores Referência ao array de pontuações das colunas
     * @param string|int $columnKey Chave da coluna
     */
    private function scorePhoneValue($value, &$columnScores, $columnKey)
    {
        // Verificar se parece um telefone
        $numericValue = preg_replace('/\D+/', '', $value);

        // Se for um número de telefone válido (entre 10 e 15 dígitos)
        if (strlen($numericValue) >= 10 && strlen($numericValue) <= 15) {
            $columnScores[$columnKey]['phone'] += 5;

            // Bônus para formatos comuns de telefone brasileiro
            if (preg_match('/^(\+?55)?\s*\(?(\d{2})\)?\s*(\d{4,5})[- ]?(\d{4})$/', $value)) {
                $columnScores[$columnKey]['phone'] += 3;
            }

            // Bônus para números que começam com 55 (Brasil)
            if (preg_match('/^55/', $numericValue)) {
                $columnScores[$columnKey]['phone'] += 2;
            }

            // Bônus para números que começam com 5 (código de países latinos)
            if (preg_match('/^[5][5|1|6|7|9]/', $numericValue)) {
                $columnScores[$columnKey]['phone'] += 1;
            }
        }

        // Penalidade para valores que parecem datas
        if ($this->looksLikeDate($value)) {
            $columnScores[$columnKey]['phone'] -= 10;
        }

        // Penalidade para valores que parecem horas
        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value)) {
            $columnScores[$columnKey]['phone'] -= 10;
        }

        // Penalidade para valores muito curtos ou muito longos
        if (strlen($numericValue) < 8 || strlen($numericValue) > 20) {
            $columnScores[$columnKey]['phone'] -= 3;
        }

        // Penalidade para valores que têm palavras (nome provável)
        if (preg_match('/[A-Za-z]{3,}/', $value)) {
            $columnScores[$columnKey]['phone'] -= 5;
        }
    }

    /**
     * Pontua um valor quanto à probabilidade de ser um nome
     *
     * @param mixed $value Valor a ser analisado
     * @param array &$columnScores Referência ao array de pontuações das colunas
     * @param string|int $columnKey Chave da coluna
     */
    private function scoreNameValue($value, &$columnScores, $columnKey)
    {
        // Se tiver letras (caracteres alfabéticos)
        if (preg_match('/[A-Za-z]/', $value)) {
            $columnScores[$columnKey]['name'] += 3;

            // Bônus para valores que contêm espaços (nomes completos)
            if (strpos($value, ' ') !== false) {
                $columnScores[$columnKey]['name'] += 2;
            }

            // Bônus para valores com padrão de nome (primeira letra maiúscula)
            if (preg_match('/^[A-Z][a-z]+(\s+[A-Z][a-z]+)+$/', $value)) {
                $columnScores[$columnKey]['name'] += 5;
            }

            // Bônus para valores com comprimento típico de nomes
            $wordCount = count(explode(' ', $value));
            if ($wordCount >= 2 && $wordCount <= 5) {
                $columnScores[$columnKey]['name'] += 2;
            }

            // Bônus para valores com termos comuns em nomes brasileiros
            if (preg_match('/\b(DA|DE|DOS|DAS|SILVA|SANTOS|OLIVEIRA|SOUZA|PEREIRA|ALMEIDA|COSTA|FERREIRA)\b/i', $value)) {
                $columnScores[$columnKey]['name'] += 3;
            }

            // Bônus para "Dr." ou "Dra." no começo (provavelmente nome de médico)
            if (preg_match('/^(Dr\.?|Dra\.?|Doutor|Doutora)\s/i', $value)) {
                $columnScores[$columnKey]['name'] += 4;
            }
        }

        // Penalidade para valores que parecem numéricos
        if (is_numeric($value) || preg_match('/^\d+$/', $value)) {
            $columnScores[$columnKey]['name'] -= 10;
        }

        // Penalidade para valores que parecem datas ou horas
        if ($this->looksLikeDate($value) || preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value)) {
            $columnScores[$columnKey]['name'] -= 10;
        }

        // Penalidade para valores muito curtos
        if (strlen($value) < 5) {
            $columnScores[$columnKey]['name'] -= 3;
        }
    }

    /**
     * Verifica duplicação de valores em uma coluna e penaliza colunas com muitos valores repetidos
     *
     * @param array $values Array de valores da coluna
     * @param array &$columnScores Referência ao array de pontuações das colunas
     * @param string|int $columnKey Chave da coluna
     */
    private function checkDuplicateValues($values, &$columnScores, $columnKey)
    {
        // Contar ocorrências de cada valor
        $valueCounts = array_count_values($values);

        // Calcular o número total de valores e valores únicos
        $totalValues = count($values);
        $uniqueValues = count($valueCounts);

        // Se temos poucos valores únicos em relação ao total (muitas repetições)
        if ($totalValues > 2 && $uniqueValues < ($totalValues * 0.7)) {
            // Penalizar coluna de nomes se muitos valores são repetidos
            $columnScores[$columnKey]['name'] -= 5;

            // Verificar se há valores específicos que se repetem muitas vezes
            foreach ($valueCounts as $value => $count) {
                // Se um valor aparece mais de 2 vezes e parece um nome
                if ($count > 2 && preg_match('/[A-Za-z]/', $value) && strlen($value) > 3) {
                    // Aplicar penalidade adicional para a coluna de nomes
                    $columnScores[$columnKey]['name'] -= $count * 2;
                }
            }
        }

        // Bônus para colunas com alta variedade de valores (característica de colunas de nome)
        if ($uniqueValues > 0.9 * $totalValues && $totalValues > 5) {
            $columnScores[$columnKey]['name'] += 3;
        }
    }

    /**
     * Verifica se um valor parece uma data
     *
     * @param mixed $value Valor a ser verificado
     * @return bool True se parece uma data, false caso contrário
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

        // Datas com barras invertidas (como em output de Excel)
        if (preg_match('/^\d{1,2}\\\\\/\d{1,2}\\\\\/\d{2,4}$/', $value)) {
            return true;
        }

        return false;
    }

    /**
     * Formata uma célula de dados
     *
     * @param mixed $value Valor a ser formatado
     * @return string|null Valor formatado ou null se vazio
     */
    private function formatCell($value)
    {
        if ($value === null) {
            return null;
        }

        // Converter para string
        $value = (string) $value;

        // Remover _x000D_ (caractere especial do Excel)
        $value = str_replace('_x000D_', '', $value);

        // Remover caracteres invisíveis
        $value = preg_replace('/[\x00-\x1F\x7F]+/u', '', $value);

        // Normalizar espaços
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }

    /**
     * Extrai nomes e telefones de um array de arrays com base na detecção
     *
     * @param array $dataArrays Array de arrays a serem processados
     * @return array Array contendo arrays associativos de nome e telefone
     */
    public function extractContacts(array $dataArrays)
    {
        // Detectar os campos de nome e telefone
        $fieldsInfo = $this->detectFields($dataArrays);

        if (!isset($fieldsInfo['nameField']) || !isset($fieldsInfo['phoneField'])) {
            return [
                'success' => false,
                'message' => 'Não foi possível identificar automaticamente os campos de nome e telefone.',
                'contacts' => []
            ];
        }

        $nameField = $fieldsInfo['nameField'];
        $phoneField = $fieldsInfo['phoneField'];
        $contacts = [];

        // Novo: contador de erros apenas para telefone inválido
        $errorTypes = [
            'invalid_phone' => 0
        ];

        // Extrair os contatos
        foreach ($dataArrays as $row) {
            if (!is_array($row)) {
                // Se a linha não for array, ignora
                continue;
            }

            if (!isset($row[$nameField]) || !isset($row[$phoneField])) {
                // Se faltar campo, ignora (não conta como erro)
                continue;
            }

            $name = $this->formatCell($row[$nameField]);
            $phone = $this->sanitizePhoneNumber($row[$phoneField]);

            // Apenas validar o telefone
            if (empty($phone)) {
                $errorTypes['invalid_phone']++;
                continue;
            }

            $contacts[] = [
                'name' => $name,
                'phone' => $phone,
                'metadata' => $row
            ];
        }

        $totalErrors = $errorTypes['invalid_phone'];

        return [
            'success' => !empty($contacts),
            'message' => count($contacts) . ' contatos encontrados. ' . $totalErrors . ' telefones inválidos.',
            'contacts' => $contacts,
            'nameField' => $nameField,
            'phoneField' => $phoneField,
            'scores' => $fieldsInfo['scores'],
            'errors_summary' => $errorTypes
        ];
    }


    /**
     * Sanitiza um número de telefone
     *
     * @param mixed $value Valor a ser sanitizado
     * @return string|null Número de telefone sanitizado ou null se inválido
     */
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
            $day = (int) $matches[1];
            $month = (int) $matches[2];
            $year = (int) $matches[3];

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
}
