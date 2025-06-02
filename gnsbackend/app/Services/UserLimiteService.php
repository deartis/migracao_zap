<?php

namespace App\Services;

class UserLimiteService
{
    /**
     * Verifica o status e limites do usuário atual
     *
     * @return array Informações sobre o status do usuário
     */
    public function verificaStatusUsuario(): array
    {
        // Implementação da função verificaStatusUser que foi referenciada no controller original

        $usuario = auth()->user();

        // Verifica se o usuário tem limite disponível
        $temLimite = $usuario->msgLimit > $usuario->sendedMsg;

        // Verifica se o usuário está ativo/habilitado
        $estaHabilitado = $usuario->status === 'active'; // Ajuste conforme sua aplicação

        return [
            'userm' => $usuario,
            'hasLimit' => $temLimite,
            'enabled' => $estaHabilitado
        ];
    }
}
