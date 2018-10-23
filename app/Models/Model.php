<?php

namespace App\Models;

class Model extends \Illuminate\Database\Eloquent\Model
{
    protected $casts = [
        'created_at' => 'string',
        'updated_at' => 'string',
    ];
}
