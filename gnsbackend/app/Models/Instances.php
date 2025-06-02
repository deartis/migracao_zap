<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperInstances
 */
class Instances extends Model
{
    protected $fillable = ['user_id', 'instance_id', 'token', 'connected'];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
