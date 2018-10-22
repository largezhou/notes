<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use SoftDeletes;

    protected $casts = [
        'created_at' => 'string',
        'updated_at' => 'string',
    ];

    public static function getVisibleBooks()
    {
        return static::where('hidden', false)->orderBy('updated_at', 'desc')->get();
    }
}
