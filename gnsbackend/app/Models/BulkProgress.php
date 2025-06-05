<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperBulkProgress
 */
class BulkProgress extends Model
{
    protected $table = 'bulk_progress';

    protected $fillable = [
        'user_id',
        'total',
        'sent',
        'errors',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
