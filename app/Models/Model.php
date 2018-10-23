<?php

namespace App\Models;

use App\Filters\BookFilter;
use Illuminate\Database\Eloquent\Builder;

class Model extends \Illuminate\Database\Eloquent\Model
{
    protected $casts = [
        'created_at' => 'string',
        'updated_at' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('onlyShown', function (Builder $builder) {
            $builder->where('hidden', false);
        });
    }

    public function scopeFilter(Builder $query, BookFilter $filter)
    {
        return $filter->apply($query);
    }
}
