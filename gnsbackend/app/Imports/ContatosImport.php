<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class ContatosImport implements ToArray
{
    /**
    * @param array $rows
    */
    public function array(array $rows)
    {
        // Remove o cabeçalho se necessário
        array_shift($rows);
        return $rows;
    }
}
