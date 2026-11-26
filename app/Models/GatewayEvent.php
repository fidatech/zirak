<?php

namespace App\Models;

class GatewayEvent extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'verified' => 'boolean'];
    }
}
