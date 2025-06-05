<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnvioMensagemRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Regras de validação para os dados de entrada
     *
     * @return array
     */
    public function rules()
    {
        return [
            'template' => 'required|string',
            'contacts' => 'required|array|min:1',
            'contacts.*.name' => 'sometimes|string',
            'contacts.*.number' => 'required|string',
            'contacts.*.message' => 'sometimes|string',
        ];
    }

    /**
     * Mensagens personalizadas para as regras de validação
     *
     * @return array
     */
    public function messages()
    {
        return [
            'template.required' => 'É necessário fornecer um template de mensagem.',
            'contacts.required' => 'É necessário fornecer pelo menos um contato.',
            'contacts.array' => 'A lista de contatos deve ser um array.',
            'contacts.min' => 'É necessário fornecer pelo menos um contato.',
            'contacts.*.number.required' => 'Todos os contatos devem ter um número.',
        ];
    }
}