<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperEnvioProgresso
 */
class EnvioProgresso extends Model
{
    protected $table = 'envio_progresso';
    protected $fillable = [
        'user_id',
        'total',
        'enviadas',
        'status',
        'totalLote',
        'visto',
        'Erro',
    ];
}
