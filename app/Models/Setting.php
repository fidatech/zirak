<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One admin setting. "value" is encrypted; read it through App\Services\Settings. */
class Setting extends Model
{
    protected $fillable = ['key', 'value', 'updated_by'];

    protected $hidden = ['value'];

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
