<?php

namespace App\Models;

use App\Filters\Filter;
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

    /**
     * 编辑模式
     *
     * @param Builder $query
     */
    public function scopeEditMode(Builder $query)
    {
        $query->withTrashed();
    }

    /**
     * 应用过滤器
     *
     * @param Builder $query
     * @param Filter  $filter
     *
     * @return mixed
     */
    public function scopeFilter(Builder $query, Filter $filter)
    {
        return $filter->apply($query);
    }
}
