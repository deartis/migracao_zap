<?php

namespace App\Services;

use App\Models\Historic;
use Illuminate\Support\Collection;

class ContatosJsonProcessor
{
    /**
     * Processa os dados JSON de contatos e salva no banco de dados
     * @param array|Collection $jsonData Os dados do JSON (pode ser um array associativo ou Collection)
     * @param int|null $userId ID do usuário (se null, usará o usuário autenticado)
     * @param string|null $message Mensagem a ser enviada (opcional)
     * @return array Informações sobre o processamento (contagem, erros, etc)
     */
    public function process($jsonData, $userId = null, $message = null)
    {
        // Se o formato for o completo vindo do frontend (com message, columns e contacts)
        if (is_array($jsonData) && isset($jsonData['contacts']) && is_array($jsonData['contacts'])) {
            $message = $jsonData['message'] ?? $message;

            // Se já temos o mapeamento de colunas definido
            $columnInfo = [];
            if (
                isset($jsonData['column_mapping']) &&
                isset($jsonData['column_mapping']['name_column']) &&
                isset($jsonData['column_mapping']['whatsapp_column'])
            ) {
                $columnInfo = [
                    'name' => $jsonData['column_mapping']['name_column'],
                    'phone' => $jsonData['column_mapping']['whatsapp_column']
                ];
            }

            // Usar os contatos do array
            $rows = collect($jsonData['contacts']);

        } else {
            // Formato simples (apenas array de contatos)
            $rows = $jsonData instanceof Collection ? $jsonData : collect($jsonData);
            $columnInfo = [];
        }

        if ($rows->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Não foram encontrados contatos para processar.',
                'count' => 0,
                'errors' => 0
            ];
        }

        // Limpar os dados
        $cleanRows = $this->cleanRows($rows);

        if ($cleanRows->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Não foram encontrados dados válidos após limpeza.',
                'count' => 0,
                'errors' => 0
            ];
        }

        // Se não temos columnInfo, analisar a estrutura para detectar colunas
        if (empty($columnInfo)) {
            $columnInfo = $this->detectColumns($cleanRows);

            if (!isset($columnInfo['phone']) || !isset($columnInfo['name'])) {
                return [
                    'success' => false,
                    'message' => 'Não foi possível identificar automaticamente as colunas de nome e telefone.',
                    'count' => 0,
                    'errors' => 0
                ];
            }
        }

        // Processar as linhas e salvar os contatos
        $result = $this->processRows($cleanRows, $columnInfo, $userId, $message);

        return $result;
    }

    /**
     * Limpa as linhas de dados, removendo valores vazios e formatando células
     */
    private function cleanRows(Collection $rows)
    {
        return $rows->filter(function ($row) {
            // Converter para Collection se for array
            $rowData = $row instanceof Collection ? $row : collect($row);

            // Limpa cada valor da linha
            $cleanedRow = $rowData->map(function ($value, $key) {
                if (is_null($value))
                    return null;

                // Se for um objeto ou array, converte para string JSON
                if (is_object($value) || is_array($value)) {
                    $value = json_encode($value);
                }

                // Remove _x000D_ e espaços extras
                $cleaned = $this->formatCell($value);

                return $cleaned === '' ? null : $cleaned;
            });

            // Só mantém a linha se tiver algum valor útil (não tudo null)
            return $cleanedRow->filter()->isNotEmpty();
        })->values();  // reindexa o array (fica 0,1,2...)
    }

    /**
     * Detecta automaticamente quais colunas/campos contêm nomes e números de telefone
     */
    private function detectColumns($dataRows)
    {
        $result = [];
        $sampleSize = min(count($dataRows), 20);  // Analisa até 20 linhas
        $columnScores = [];

        // Obter todas as chaves/colunas possíveis
        $allKeys = collect();
        $dataRows->take($sampleSize)->each(function ($row) use (&$allKeys) {
            $rowData = $row instanceof Collection ? $row : collect($row);
            $allKeys = $allKeys->merge($rowData->keys());
        });
        $allKeys = $allKeys->unique()->values();

        \Log::info($allKeys);

        // Inicializar pontuação para cada coluna
        foreach ($allKeys as $key) {
            $columnScores[$key] = [
                'phone' => 0,
                'name' => 0
            ];
        }

        // Analisar cada linha da amostra
        foreach ($dataRows->take($sampleSize) as $row) {
            $rowData = $row instanceof Collection ? $row : collect($row);

            // Analisar cada coluna
            foreach ($allKeys as $key) {
                if (!$rowData->has($key)) {
                    continue;
                }

                $value = $this->formatCell($rowData[$key]);

                if (empty($value)) {
                    continue;
                }

                // Pontuação para telefones
                $this->scorePhoneValue($value, $columnScores, $key);

                // Pontuação para nomes
                $this->scoreNameValue($value, $columnScores, $key);
            }
        }

        // Encontrar a melhor coluna para telefone
        $bestPhoneScore = -1;
        $bestPhoneKey = null;

        // Encontrar a melhor coluna para nome
        $bestNameScore = -1;
        $bestNameKey = null;

        foreach ($columnScores as $key => $scores) {
            // Verificar se esta coluna é melhor para telefone
            if ($scores['phone'] > $bestPhoneScore) {
                $bestPhoneScore = $scores['phone'];
                $bestPhoneKey = $key;
            }

            // Verificar se esta coluna é melhor para nome
            if ($scores['name'] > $bestNameScore) {
                $bestNameScore = $scores['name'];
                $bestNameKey = $key;
            }
        }

        // Garantir que não estamos usando a mesma coluna para ambos
        if ($bestPhoneKey === $bestNameKey && !is_null($bestPhoneKey)) {
            // Se houver empate, decidir pela maior diferença entre as pontuações
            if (
                $columnScores[$bestPhoneKey]['phone'] - $columnScores[$bestPhoneKey]['name'] >
                $columnScores[$bestNameKey]['name'] - $columnScores[$bestNameKey]['phone']
            ) {
                // A coluna é melhor para telefone, encontrar a segunda melhor para nome
                $secondBestNameScore = -1;
                $secondBestNameKey = null;

                foreach ($columnScores as $key => $scores) {
                    if ($key !== $bestPhoneKey && $scores['name'] > $secondBestNameScore) {
                        $secondBestNameScore = $scores['name'];
                        $secondBestNameKey = $key;
                    }
                }

                if ($secondBestNameKey !== null) {
                    $bestNameKey = $secondBestNameKey;
                }
            } else {
                // A coluna é melhor para nome, encontrar a segunda melhor para telefone
                $secondBestPhoneScore = -1;
                $secondBestPhoneKey = null;

                foreach ($columnScores as $key => $scores) {
                    if ($key !== $bestNameKey && $scores['phone'] > $secondBestPhoneScore) {
                        $secondBestPhoneScore = $scores['phone'];
                        $secondBestPhoneKey = $key;
                    }
                }

                if ($secondBestPhoneKey !== null) {
                    $bestPhoneKey = $secondBestPhoneKey;
                }
            }
        }

        // Definir os resultados apenas se as pontuações forem positivas
        if ($bestPhoneScore > 0) {
            $result['phone'] = $bestPhoneKey;
        }

        if ($bestNameScore > 0) {
            $result['name'] = $bestNameKey;
        }

        return $result;
    }

    /**
     * Processa as linhas e salva os contatos
     * @param Collection $dataRows Linhas de dados a processar
     * @param array $columnInfo Informação sobre quais colunas contêm nome e telefone
     * @param int|null $userId ID do usuário
     * @param string|null $message Mensagem a ser armazenada (opcional)
     * @return array Resultado do processamento
     */
    private function processRows($dataRows, $columnInfo, $userId = null, $message = null)
    {
        $count = 0;
        $errors = 0;
        $userId = $userId ?? auth()->id();
        $additionalData = [];
        $msgP = [];

        // Se temos uma mensagem, guardamos
        if (!empty($message)) {
            $additionalData['message'] = $message;
        }

        foreach ($dataRows as $row) {
            $rowData = $row instanceof Collection ? $row : collect($row);

            // Verificar se a linha tem os campos necessários
            if (!$rowData->has($columnInfo['name']) || !$rowData->has($columnInfo['phone'])) {
                $errors++;
                continue;
            }

            $nome = $this->formatCell($rowData[$columnInfo['name']]);
            $telefone = $this->formatCell($rowData[$columnInfo['phone']]);

            // Limpar e validar o telefone
            $telefone = $this->sanitizePhoneNumber($telefone);

            // Verificar se temos dados válidos
            if (empty($nome) || empty($telefone)) {
                $errors++;
                continue;
            }

            // Preparar dados adicionais específicos deste contato
            $contactData = [
                'user_id' => $userId,
                'name' => $nome,
                'contact' => $telefone,
                'status' => 'pendente',  // Alterado para pendente até que seja enviado
            ];

            // Adicionar dados extras da linha como metadados
            $metadata = $rowData->toArray();

            // Remover as colunas já usadas para não duplicar a informação
            unset($metadata[$columnInfo['name']]);
            unset($metadata[$columnInfo['phone']]);

            // Se temos metadados, guardar como JSON
            if (!empty($metadata)) {
                $contactData['metadata'] = json_encode($metadata);
            }

            // Se temos mensagem personalizada
            if (!empty($message)) {
                $mensagemPersonalizada = $this->replaceTemplateVariables($message, $rowData->toArray());
                $contactData['message'] = $mensagemPersonalizada;


                $msgP[] = $contactData['message'];

                //\Log::info($contactData);
                /**
                 * Saída da variável $contactData:
                 * [2025-05-06 10:23:00] local.INFO: array (
                 *   'user_id' => 2,
                 *   'name' => 'TATIANA MARIA DE ARAUJO',
                 *   'contact' => '5522998243800',
                 *   'status' => 'pendente',
                 *   'metadata' => '{"Dt.Atendto.":"08\\/11\\r\\/2024","Hora":"09:00","Dia":"SEX","Nome do Paciente":"LEONARDO AMARO RIBEIRO COSTA"}',
                 *   'message' => 'Olá LEONARDO AMARO RIBEIRO COSTA.
                 * Sua consulta está marcada para o dia 08/11
                 * /2024, às 09:00, com a Dra. TATIANA MARIA DE ARAUJO.',
                 * )
                 */
                // $contactData['message'] = $message;
            }

            // Atualiza o registro existente ou cria um novo
            $historic = Historic::updateOrCreate(
                [
                    'user_id' => $userId,
                    'contact' => $telefone
                ],
                [
                    'user_id' => $userId,
                    'contact' => $telefone,
                    'name' => $nome,
                    'status' => 'pendente'
                ]
            );
            // Se você precisar forçar a atualização do timestamp:
            $historic->touch();
            $count++;
        }

        // Recupera os contatos após o loop
        $contactsForSending = Historic::where('user_id', $userId)
            ->where('status', 'pendente')
            ->orderByDesc('id')
            ->take($count)
            ->get(['name', 'contact'])
            ->toArray();

        // Agora sim define o $resultMessage
        $resultMessage = "Importação concluída: $count contatos adicionados com sucesso.";
        if ($errors > 0) {
            $resultMessage .= " $errors linhas foram ignoradas devido a dados inválidos.";
        }

        return [
            'success' => true,
            'message' => $resultMessage,
            'count' => $count,
            'errors' => $errors,
            'contacts' => $contactsForSending,
            'arrayTest' => $msgP
        ];
    }

    private function replaceTemplateVariables(string $template, array $data)
    {
        return preg_replace_callback('/\{\{(.*?)\}\}/', function ($matches) use ($data) {
            $key = trim($matches[1]);  // Nome da variável sem {{ }}

            // Tenta buscar no $data (ignora maiúsculas/minúsculas)
            foreach ($data as $campo => $valor) {
                if (strcasecmp($campo, $key) === 0) {
                    // \Log::info("Substituindo variável: {$campo} => {$valor}");
                    return $valor;
                }
            }

            // Se não encontrar, retorna vazio ou o próprio marcador
            return '';
        }, $template);
    }

    /**
     * Pontua um valor quanto à probabilidade de ser um telefone
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

            // Bônus para valores com termos comuns em nomes
            if (preg_match('/\b(DA|DE|DOS|DAS|SILVA|SANTOS|OLIVEIRA|SOUZA)\b/i', $value)) {
                $columnScores[$columnKey]['name'] += 3;
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

    /**
     * Sanitiza um número de telefone
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

    /**
     * Formata uma célula de dados
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
}
