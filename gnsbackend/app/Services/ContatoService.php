<?php
namespace App\Services;

use Illuminate\Support\Facades\Log;

class ContatoService
{
    /**
     * Verifica a validade dos contatos fornecidos
     *
     * @param array $contatos Lista de contatos a serem verificados
     * @return array Lista de erros encontrados
     */
    public function verificaContatos(array $contatos): array
    {
        // Implementação da função verificaContatos que foi referenciada no controller original
        // Aqui você deve incluir a lógica específica da sua aplicação

        $erros = [];

        // Verificação básica de exemplos de erros possíveis
        foreach ($contatos as $index => $contato) {
            if (empty($contato['number'])) {
                $erros[] = [
                    'indice' => $index,
                    'erro' => 'Número de telefone vazio',
                    'contato' => $contato
                ];
                continue;
            }

            // Adicione mais validações conforme necessário
        }

        return $erros;
    }

    /**
     * Processa a lista de contatos para o formato adequado para envio de mensagens
     *
     * @param array $contatos Lista de contatos brutos
     * @return array Lista de contatos processados
     */
    public function processarContatos(array $contatos): array
    {
        $contatosFormatados = [];

        // Prepara os contatos para processamento
        foreach ($contatos as $contato) {
            $contatosFormatados[] = $contato;
        }

        // Utiliza o detector de dados para extrair informações de contato
        $detector = new \App\Helpers\ArrayDataDetector();
        $resultados = $detector->extractContacts($contatosFormatados)['contacts'];

        $contatosFinais = [];

        // Formata os contatos para o formato final
        foreach ($resultados as $resultado) {
            $telefone = $resultado['phone'];

            // Adiciona sufixo @c.us se não existir
            if (!str_contains($telefone, '@c.us')) {
                $telefone .= '@c.us';
            }

            $contatosFinais[] = [
                'name' => $resultado['name'],
                'number' => $telefone,
                'message' => $resultado['metadata']['message'] ?? '',
            ];
        }

        Log::info("Contatos processados com sucesso", ['total' => count($contatosFinais)]);

        return $contatosFinais;
    }
}
