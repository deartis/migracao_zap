<?php

namespace App\Services;

class PhoneValidator
{
    /**
     * Lista de códigos de países com seus prefixos e tamanhos válidos
     *
     * @var array
     */
    protected static $countryCodes = [
        // País => [prefixo, comprimento mínimo, comprimento máximo]
        'BR' => ['55', 10, 11], // Brasil: 55 + DDD(2) + número(8-9)
        'US' => ['1', 10, 10],  // EUA/Canadá: 1 + número(10)
        'PT' => ['351', 9, 9],  // Portugal: 351 + número(9)
        // Adicione mais países conforme necessário
    ];

    /**
     * Valida e formata um número de telefone
     *
     * @param string $number O número de telefone para validar
     * @return array ['valid' => bool, 'number' => string, 'error' => string|null]
     */
    public static function validate($number)
    {
        // Remove todos os caracteres não numéricos
        $cleaned = preg_replace('/[^0-9]/', '', $number);

        // Verifica se há algum dígito
        if (empty($cleaned)) {
            return [
                'valid' => false,
                'number' => $number,
                'error' => 'empty_number',
                'message' => 'Número vazio ou sem dígitos'
            ];
        }

        // Se começar com + ou 00, remove
        if (substr($number, 0, 1) === '+') {
            $cleaned = preg_replace('/[^0-9]/', '', substr($number, 1));
        } elseif (substr($cleaned, 0, 2) === '00') {
            $cleaned = substr($cleaned, 2);
        }

        // Muito curto para ser um número válido
        if (strlen($cleaned) < 8) {
            return [
                'valid' => false,
                'number' => $number,
                'error' => 'too_short',
                'message' => 'Número muito curto'
            ];
        }

        // Detecta o país com base no prefixo
        $detectedCountry = null;
        $detectedPrefix = null;

        foreach (self::$countryCodes as $country => $specs) {
            $prefix = $specs[0];
            if (substr($cleaned, 0, strlen($prefix)) === $prefix) {
                $detectedCountry = $country;
                $detectedPrefix = $prefix;
                break;
            }
        }

        // Caso seja um número brasileiro sem o +55
        // Tenta detectar se é um número nacional com DDD apenas
        if (!$detectedCountry && strlen($cleaned) >= 10 && strlen($cleaned) <= 11) {
            // Provavelmente é um número BR (10 ou 11 dígitos com DDD)
            // No Brasil, números de celular têm 11 dígitos (com o 9 na frente)
            // e números fixos têm 10 dígitos
            $detectedCountry = 'BR';
            $detectedPrefix = '55';
            $cleaned = '55' . $cleaned;
        }

        // Se não for detectado por nenhuma regra, assume BR como padrão
        if (!$detectedCountry) {
            $detectedCountry = 'BR';
            $detectedPrefix = '55';
            $cleaned = '55' . $cleaned;
        }

        // Valida o comprimento específico para o país
        $countrySpecs = self::$countryCodes[$detectedCountry];
        $minLength = $countrySpecs[1] + strlen($detectedPrefix);
        $maxLength = $countrySpecs[2] + strlen($detectedPrefix);

        if (strlen($cleaned) < $minLength || strlen($cleaned) > $maxLength) {
            return [
                'valid' => false,
                'number' => $number,
                'error' => 'invalid_length',
                'message' => "Comprimento inválido para {$detectedCountry} (deve ter entre {$countrySpecs[1]} e {$countrySpecs[2]} dígitos após o prefixo)"
            ];
        }

        // No caso do Brasil, validar DDDs válidos (01-99, excluindo alguns inválidos)
        if ($detectedCountry === 'BR') {
            $ddd = substr($cleaned, 2, 2);
            $invalidDDDs = ['00', '01', '02', '03', '04', '05', '06', '09', '20', '23', '25', '26', '29', '30', '36', '39', '40', '50', '52', '56', '57', '58', '59', '60', '70', '72', '76', '78', '80', '90'];

            if (in_array($ddd, $invalidDDDs) || intval($ddd) > 99) {
                return [
                    'valid' => false,
                    'number' => $number,
                    'error' => 'invalid_area_code',
                    'message' => "DDD {$ddd} não existe no Brasil"
                ];
            }
        }

        // Formata o número no padrão E.164 (padrão internacional)
        $formattedNumber = '+' . $cleaned;

        return [
            'valid' => true,
            'number' => $formattedNumber,
            'countryCode' => $detectedCountry,
            'error' => null,
            'message' => null
        ];
    }
}
