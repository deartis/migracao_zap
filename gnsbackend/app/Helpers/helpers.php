<?php

/**
 * Função para remover os acentos do texto
 *
 * @param string
 * @returns string
*/

if(!function_exists('rm_acentos')){
    function rm_acentos($string){
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        $string = preg_replace('/[^a-zA-Z0-9]/', '', $string);
        return $string;
    }
}

if(!function_exists('uniqName')){
    function uniqName($name){
        $primeiroNome = explode(' ', auth()->user()->name)[0];
        $nomeFormatado = ucfirst(strtolower($primeiroNome));

        return $nomeFormatado;
    }
}
