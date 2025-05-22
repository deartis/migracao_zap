<?php
/**
 * Função para remover os acentos do texto
 *
 * @param string
 * @returns string
 */

if (!function_exists('rm_acentos')) {
    function rm_acentos($string)
    {
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        $string = preg_replace('/[^a-zA-Z0-9]/', '', $string);
        return $string;
    }
}

if (!function_exists('uniqName')) {
    function uniqName($name)
    {
        $primeiroNome = explode(' ', auth()->user()->name)[0];
        $nomeFormatado = ucfirst(strtolower($primeiroNome));

        return $nomeFormatado;
    }
}

if (!function_exists('token_user')) {
    function token_user()
    {
        $token = auth()->id();
        return $token;
    }
}

if (!function_exists('verificaStatusUser')) {
    function verificaStatusUser(): array
    {
        $user = auth()->user();
        $msgLimit = $user->msgLimit;
        $sendedMsg = $user->sendedMsg;
        $hasLimit = $msgLimit >= $sendedMsg;
        $percent = ($sendedMsg / $msgLimit) * 100;
        $percent = intval($percent);
        $enabled = $user->enabled;

        return [
            'msgLimit' => $msgLimit,
            'sendedMsg' => $sendedMsg,
            'hasLimit' => $hasLimit,
            'percent' => $percent,
            'enabled' => $enabled,
            'userm' => $user,
        ];
    }

    if (!function_exists('baseUrlApi')) {
        function baseUrlApi()
        {
            return env('WHATSAPP_API_URL', 'http://localhost:3000');
        }
    }
}

/**
 * Função para tratar e exibir os erros de envio de mensagem
 *
 * @returns String
 *
 */
if (!function_exists('historicError')) {
    function historicError($typeError)
    {
        switch ($typeError) {
            case 'invalid_number':
                return 'Número inválido';
            case 'Unknown_error':
                return 'Erro desconhecido';
            case 'limit_exceeded':
                return 'Limite de mensagem atingido';
            case 'blocked_user':
                return 'Usuário bloqueado';
            default :
                return null;
        }
    }
}
