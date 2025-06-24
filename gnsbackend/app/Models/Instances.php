<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperInstances
 */
class Instances extends Model
{
    protected $fillable = ['user_id', 'instance_id', 'token', 'connected', 'status', 'qrcode','qrcode_started_at','expired_qrcode'];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
