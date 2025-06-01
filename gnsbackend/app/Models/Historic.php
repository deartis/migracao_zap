<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperHistoric
 */
class Historic extends Model
{
    protected $table = 'historic';

    protected $fillable = [
        'user_id',
        'contact',
        'status',
        'name',
        'errorType'
    ];
}
